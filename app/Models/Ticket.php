<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PENDING = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'department_id',
        'category_id',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'status',
        'opened_at',
        'first_replied_at',
        'resolved_at',
        'closed_at',
        'sla_due_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'first_replied_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'sla_due_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function publicReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->where('is_internal', false);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->latest();
    }

    public function isResolvedOrClosed(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED], true);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'bg-blue-50 text-blue-700 border-blue-200 ring-1 ring-blue-600/10',
            self::STATUS_IN_PROGRESS => 'bg-amber-50 text-amber-700 border-amber-200 ring-1 ring-amber-600/10',
            self::STATUS_PENDING => 'bg-purple-50 text-purple-700 border-purple-200 ring-1 ring-purple-600/10',
            self::STATUS_RESOLVED => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-1 ring-emerald-600/10',
            self::STATUS_CLOSED => 'bg-slate-100 text-slate-700 border-slate-300 ring-1 ring-slate-600/10',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'bg-slate-100 text-slate-700 border-slate-200',
            self::PRIORITY_MEDIUM => 'bg-blue-100 text-blue-700 border-blue-200',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800 border-orange-200',
            self::PRIORITY_URGENT => 'bg-rose-100 text-rose-800 border-rose-200 font-semibold',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    /**
     * Get SLA status info: state, label, style class, remaining human readable time.
     */
    public function getSlaInfo(): array
    {
        if ($this->isResolvedOrClosed()) {
            return [
                'state' => 'completed',
                'label' => 'Resolved',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'check',
                'text' => 'Resolved within SLA',
            ];
        }

        if (!$this->sla_due_at) {
            return [
                'state' => 'none',
                'label' => 'No SLA',
                'badge' => 'bg-slate-50 text-slate-600 border-slate-200',
                'icon' => 'dash',
                'text' => 'Not configured',
            ];
        }

        $now = Carbon::now();
        $diffMinutes = $now->diffInMinutes($this->sla_due_at, false);

        if ($diffMinutes < 0) {
            return [
                'state' => 'overdue',
                'label' => 'Overdue',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-300 animate-pulse',
                'icon' => 'exclamation',
                'text' => 'Overdue by ' . $this->sla_due_at->diffForHumans(null, true),
            ];
        }

        // Near deadline if less than 4 hours remaining
        if ($diffMinutes <= 240) {
            return [
                'state' => 'near_deadline',
                'label' => 'Near Deadline',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-300',
                'icon' => 'clock',
                'text' => $this->sla_due_at->diffForHumans(null, true) . ' left',
            ];
        }

        return [
            'state' => 'safe',
            'label' => 'Safe',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'icon' => 'shield-check',
            'text' => $this->sla_due_at->diffForHumans(null, true) . ' left',
        ];
    }
}
