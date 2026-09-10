<?php

namespace App\Services;

use App\Models\SlaSetting;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function createTicket(User $user, array $data, array $attachments = []): Ticket
    {
        $ticket = DB::transaction(function () use ($user, $data, $attachments) {
            $year = Carbon::now()->format('Y');

            // Generate next ticket number: HD-YYYY-XXXXXX
            $latestTicket = Ticket::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = 1;
            if ($latestTicket && preg_match('/HD-\d{4}-(\d+)/', $latestTicket->ticket_number, $matches)) {
                $sequence = (int) $matches[1] + 1;
            }
            $ticketNumber = sprintf('HD-%s-%06d', $year, $sequence);

            // Compute SLA due date
            $slaDueAt = $this->calculateSlaDueAt($data['priority'] ?? Ticket::PRIORITY_MEDIUM);

            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'category_id' => $data['category_id'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? Ticket::PRIORITY_MEDIUM,
                'status' => Ticket::STATUS_OPEN,
                'opened_at' => Carbon::now(),
                'sla_due_at' => $slaDueAt,
            ]);

            // Save attachments
            foreach ($attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $this->saveAttachment($ticket, null, $user, $file);
                }
            }

            // Log activity
            $this->logActivity(
                ticket: $ticket,
                user: $user,
                type: 'created',
                description: "Ticket created by {$user->name}",
                newValue: $ticketNumber
            );

            return $ticket;
        });

        $this->notificationService->notifyTicketCreated($ticket);

        return $ticket;
    }

    public function addReply(Ticket $ticket, User $user, string $message, bool $isInternal = false, array $attachments = []): TicketReply
    {
        $reply = DB::transaction(function () use ($ticket, $user, $message, $isInternal, $attachments) {
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $message,
                'is_internal' => $isInternal,
            ]);

            // Update first replied at if this is an agent replying publicly for the first time
            if (!$isInternal && $user->isStaff() && $ticket->first_replied_at === null) {
                $ticket->update(['first_replied_at' => Carbon::now()]);
            }

            // If an agent replies to an open ticket, transition to in_progress
            if ($user->isStaff() && $ticket->status === Ticket::STATUS_OPEN) {
                $this->updateStatus($ticket, $user, Ticket::STATUS_IN_PROGRESS);
            }

            // Save attachments
            foreach ($attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $this->saveAttachment($ticket, $reply, $user, $file);
                }
            }

            // Log activity
            $activityType = $isInternal ? 'internal_note_added' : 'reply_added';
            $desc = $isInternal
                ? "Added an internal note"
                : "Replied to the ticket";

            $this->logActivity($ticket, $user, $activityType, $desc);

            return $reply;
        });

        $this->notificationService->notifyReplyAdded($ticket, $reply, $user);

        return $reply;
    }

    public function updateStatus(Ticket $ticket, User $actor, string $newStatus): Ticket
    {
        $oldStatus = $ticket->status;
        if ($oldStatus === $newStatus) {
            return $ticket;
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === Ticket::STATUS_RESOLVED) {
            $updates['resolved_at'] = Carbon::now();
        } elseif ($newStatus === Ticket::STATUS_CLOSED) {
            $updates['closed_at'] = Carbon::now();
            if (!$ticket->resolved_at) {
                $updates['resolved_at'] = Carbon::now();
            }
        } elseif (in_array($oldStatus, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED]) && in_array($newStatus, [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS])) {
            // Reopening ticket
            $updates['resolved_at'] = null;
            $updates['closed_at'] = null;
        }

        $ticket->update($updates);

        $activityType = match ($newStatus) {
            Ticket::STATUS_RESOLVED => 'resolved',
            Ticket::STATUS_CLOSED => 'closed',
            default => ($oldStatus === Ticket::STATUS_RESOLVED || $oldStatus === Ticket::STATUS_CLOSED) ? 'reopened' : 'status_changed',
        };

        $desc = match ($activityType) {
            'resolved' => "Ticket marked as Resolved by {$actor->name}",
            'closed' => "Ticket closed by {$actor->name}",
            'reopened' => "Ticket reopened by {$actor->name}",
            default => "Status changed from {$oldStatus} to {$newStatus} by {$actor->name}",
        };

        $this->logActivity($ticket, $actor, $activityType, $desc, $oldStatus, $newStatus);

        $this->notificationService->notifyStatusChanged($ticket, $oldStatus, $newStatus, $actor);

        return $ticket;
    }

    public function updatePriority(Ticket $ticket, User $actor, string $newPriority): Ticket
    {
        $oldPriority = $ticket->priority;
        if ($oldPriority === $newPriority) {
            return $ticket;
        }

        // Recalculate SLA due date
        $slaDueAt = $this->calculateSlaDueAt($newPriority, $ticket->opened_at ?? Carbon::now());

        $ticket->update([
            'priority' => $newPriority,
            'sla_due_at' => $slaDueAt,
        ]);

        $this->logActivity(
            $ticket,
            $actor,
            'priority_changed',
            "Priority changed from {$oldPriority} to {$newPriority} by {$actor->name}",
            $oldPriority,
            $newPriority
        );

        $this->notificationService->notifyPriorityChanged($ticket, $oldPriority, $newPriority, $actor);

        return $ticket;
    }

    public function assignAgent(Ticket $ticket, User $actor, ?User $agent): Ticket
    {
        $oldAgent = $ticket->assignedAgent;
        $agentId = $agent?->id;

        if ($ticket->assigned_to === $agentId) {
            return $ticket;
        }

        $updates = ['assigned_to' => $agentId];
        if ($agentId && $ticket->status === Ticket::STATUS_OPEN) {
            $updates['status'] = Ticket::STATUS_IN_PROGRESS;
        }

        $ticket->update($updates);

        $desc = $agent
            ? "Ticket assigned to {$agent->name} by {$actor->name}"
            : "Ticket unassigned by {$actor->name}";

        $this->logActivity(
            $ticket,
            $actor,
            'assigned',
            $desc,
            $oldAgent?->name ?? 'Unassigned',
            $agent?->name ?? 'Unassigned'
        );

        $this->notificationService->notifyTicketAssigned($ticket, $agent, $actor);

        return $ticket;
    }

    public function saveAttachment(Ticket $ticket, ?TicketReply $reply, User $user, UploadedFile $file): TicketAttachment
    {
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        $path = $file->store("attachments/{$ticket->id}", 'public');

        return TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'reply_id' => $reply?->id,
            'user_id' => $user->id,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
        ]);
    }

    public function logActivity(Ticket $ticket, ?User $user, string $type, string $description, ?string $oldValue = null, ?string $newValue = null): TicketActivity
    {
        return TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'activity_type' => $type,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    public function calculateSlaDueAt(string $priority, ?Carbon $baseTime = null): ?Carbon
    {
        $base = $baseTime ? $baseTime->copy() : Carbon::now();

        $setting = SlaSetting::where('priority', $priority)->first();
        if ($setting && $setting->resolution_hours > 0) {
            return $base->addHours($setting->resolution_hours);
        }

        // Fallback default hours
        $defaultHours = match ($priority) {
            Ticket::PRIORITY_URGENT => 8,
            Ticket::PRIORITY_HIGH => 24,
            Ticket::PRIORITY_MEDIUM => 48,
            Ticket::PRIORITY_LOW => 72,
            default => 48,
        };

        return $base->addHours($defaultHours);
    }
}
