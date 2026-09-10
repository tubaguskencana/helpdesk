<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'activity_type',
        'description',
        'old_value',
        'new_value',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getIconColor(): string
    {
        return match ($this->activity_type) {
            'created' => 'bg-blue-500 text-white',
            'status_changed' => 'bg-amber-500 text-white',
            'priority_changed' => 'bg-rose-500 text-white',
            'assigned' => 'bg-purple-500 text-white',
            'reply_added' => 'bg-teal-500 text-white',
            'internal_note_added' => 'bg-amber-600 text-white',
            'resolved' => 'bg-emerald-600 text-white',
            'closed' => 'bg-slate-600 text-white',
            'reopened' => 'bg-orange-500 text-white',
            default => 'bg-slate-400 text-white',
        };
    }
}
