<?php

namespace App\Services;

use App\Jobs\SendWebPushNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    public function notifyTicketCreated(Ticket $ticket): void
    {
        $requester = $ticket->user;
        if ($requester) {
            $this->dispatchMultiChannel(
                user: $requester,
                event: 'ticket_created',
                type: Notification::TYPE_TICKET_CREATED,
                title: 'Ticket Created: #' . $ticket->ticket_number,
                message: "Your ticket '{$ticket->subject}' has been created and logged in the system.",
                url: route('tickets.show', $ticket),
                ticket: $ticket
            );
        }

        // If assigned right away upon creation
        if ($ticket->assignedAgent && $ticket->assignedAgent->id !== $requester?->id) {
            $this->dispatchMultiChannel(
                user: $ticket->assignedAgent,
                event: 'ticket_assigned',
                type: Notification::TYPE_TICKET_ASSIGNED,
                title: 'New Ticket Assigned: #' . $ticket->ticket_number,
                message: "A new ticket '{$ticket->subject}' has been assigned to you.",
                url: route('tickets.show', $ticket),
                ticket: $ticket
            );
        }
    }

    public function notifyTicketAssigned(Ticket $ticket, ?User $agent, User $assigner): void
    {
        if (!$agent || $agent->id === $assigner->id) {
            return;
        }

        $this->dispatchMultiChannel(
            user: $agent,
            event: 'ticket_assigned',
            type: Notification::TYPE_TICKET_ASSIGNED,
            title: 'Ticket Assigned: #' . $ticket->ticket_number,
            message: "Ticket '{$ticket->subject}' was assigned to you by {$assigner->name}.",
            url: route('tickets.show', $ticket),
            ticket: $ticket
        );
    }

    public function notifyReplyAdded(Ticket $ticket, TicketReply $reply, User $author): void
    {
        $snippet = mb_strimwidth(strip_tags($reply->message), 0, 90, '...');

        // CRITICAL RULE: If internal note, NEVER notify requester! Only notify assigned staff/supervisor.
        if ($reply->is_internal) {
            if ($ticket->assignedAgent && $ticket->assignedAgent->id !== $author->id) {
                $this->dispatchMultiChannel(
                    user: $ticket->assignedAgent,
                    event: 'reply_added',
                    type: Notification::TYPE_REPLY_ADDED,
                    title: 'Internal Note on #' . $ticket->ticket_number,
                    message: "{$author->name} added an internal note: \"{$snippet}\"",
                    url: route('tickets.show', $ticket),
                    ticket: $ticket,
                    allowWhatsApp: false // Internal notes not sent via WhatsApp
                );
            }
            return;
        }

        // Public Reply:
        // 1. If author is staff/agent, notify requester
        if ($author->isStaff() && $ticket->user && $ticket->user->id !== $author->id) {
            $this->dispatchMultiChannel(
                user: $ticket->user,
                event: 'reply_added',
                type: Notification::TYPE_REPLY_ADDED,
                title: 'New Reply on #' . $ticket->ticket_number,
                message: "{$author->name} replied: \"{$snippet}\"",
                url: route('tickets.show', $ticket),
                ticket: $ticket,
                extraWhatsAppParams: [
                    'author_name' => $author->name,
                    'snippet' => $snippet,
                ]
            );
        }

        // 2. If author is requester, notify assigned agent
        if ($ticket->assignedAgent && $ticket->assignedAgent->id !== $author->id) {
            $this->dispatchMultiChannel(
                user: $ticket->assignedAgent,
                event: 'reply_added',
                type: Notification::TYPE_REPLY_ADDED,
                title: 'Requester Replied on #' . $ticket->ticket_number,
                message: "{$author->name} replied: \"{$snippet}\"",
                url: route('tickets.show', $ticket),
                ticket: $ticket,
                extraWhatsAppParams: [
                    'author_name' => $author->name,
                    'snippet' => $snippet,
                ]
            );
        }
    }

    public function notifyStatusChanged(Ticket $ticket, string $oldStatus, string $newStatus, User $actor): void
    {
        $readableStatus = strtoupper(str_replace('_', ' ', $newStatus));

        // Notify ticket creator if actor is not creator
        if ($ticket->user && $ticket->user->id !== $actor->id) {
            $this->dispatchMultiChannel(
                user: $ticket->user,
                event: 'status_changed',
                type: Notification::TYPE_STATUS_CHANGED,
                title: "Ticket #{$ticket->ticket_number} is now {$readableStatus}",
                message: "Status changed from {$oldStatus} to {$newStatus} by {$actor->name}.",
                url: route('tickets.show', $ticket),
                ticket: $ticket
            );
        }

        // If ticket was reopened by requester, notify assigned agent
        if (in_array($newStatus, [Ticket::STATUS_OPEN, Ticket::STATUS_IN_PROGRESS]) &&
            in_array($oldStatus, [Ticket::STATUS_RESOLVED, Ticket::STATUS_CLOSED]) &&
            $ticket->assignedAgent && $ticket->assignedAgent->id !== $actor->id) {
            $this->dispatchMultiChannel(
                user: $ticket->assignedAgent,
                event: 'status_changed',
                type: Notification::TYPE_STATUS_CHANGED,
                title: "Ticket #{$ticket->ticket_number} Reopened",
                message: "Ticket was reopened by {$actor->name}.",
                url: route('tickets.show', $ticket),
                ticket: $ticket
            );
        }
    }

    public function notifyPriorityChanged(Ticket $ticket, string $oldPriority, string $newPriority, User $actor): void
    {
        $readablePriority = ucfirst($newPriority);

        if ($ticket->assignedAgent && $ticket->assignedAgent->id !== $actor->id) {
            $this->dispatchMultiChannel(
                user: $ticket->assignedAgent,
                event: 'priority_changed',
                type: Notification::TYPE_PRIORITY_CHANGED,
                title: "Priority Updated: #{$ticket->ticket_number}",
                message: "Priority changed to {$readablePriority} by {$actor->name}.",
                url: route('tickets.show', $ticket),
                ticket: $ticket
            );
        }
    }

    protected function dispatchMultiChannel(
        User $user,
        string $event,
        string $type,
        string $title,
        string $message,
        string $url,
        ?Ticket $ticket = null,
        bool $allowWhatsApp = true,
        array $extraWhatsAppParams = []
    ): void {
        try {
            // 1. Web In-App Notification (Channel: Web)
            if (NotificationSetting::isChannelEnabled($event, 'web')) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                ]);
            }

            // 2. Web Push Notification (Channel: Push)
            if (NotificationSetting::isChannelEnabled($event, 'push')) {
                SendWebPushNotification::dispatch(
                    userId: $user->id,
                    title: $title,
                    body: $message,
                    url: $url,
                    ticketId: $ticket?->id
                );
            }

            // 3. WhatsApp Notification (Channel: WhatsApp)
            if ($allowWhatsApp &&
                NotificationSetting::isChannelEnabled($event, 'whatsapp') &&
                $this->whatsAppService->isEnabled() &&
                $ticket !== null) {
                
                $phone = $this->whatsAppService->normalizePhoneNumber($user->phone);

                if ($phone && $this->whatsAppService->isConnected()) {
                    $waMessage = $this->whatsAppService->formatTicketMessage($event, $ticket, $extraWhatsAppParams);

                    SendWhatsAppNotification::dispatch(
                        phone: $phone,
                        message: $waMessage,
                        ticketId: $ticket->id,
                        userId: $user->id,
                        type: $event
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error("NotificationService dispatch failure: " . $e->getMessage());
        }
    }
}
