<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public const TYPE_TICKET_CREATED = 'ticket_created';
    public const TYPE_TICKET_ASSIGNED = 'ticket_assigned';
    public const TYPE_REPLY_ADDED = 'reply_added';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_PRIORITY_CHANGED = 'priority_changed';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'url',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            return $this->update(['read_at' => Carbon::now()]);
        }
        return true;
    }

    public function getIconColorClass(): string
    {
        return match ($this->type) {
            self::TYPE_TICKET_ASSIGNED => 'bg-blue-100 text-blue-600',
            self::TYPE_STATUS_CHANGED => 'bg-amber-100 text-amber-600',
            self::TYPE_PRIORITY_CHANGED => 'bg-rose-100 text-rose-600',
            self::TYPE_REPLY_ADDED => 'bg-purple-100 text-purple-600',
            default => 'bg-emerald-100 text-emerald-600',
        };
    }
}
