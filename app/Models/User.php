<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_AGENT = 'agent';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'phone',
        'job_title',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_AGENT, self::ROLE_SUPERVISOR, self::ROLE_ADMIN], true);
    }

    public function isRequester(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function getRoleBadgeClass(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'bg-purple-100 text-purple-800 border-purple-200',
            self::ROLE_SUPERVISOR => 'bg-blue-100 text-blue-800 border-blue-200',
            self::ROLE_AGENT => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getRoleDisplayName(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_SUPERVISOR => 'Supervisor',
            self::ROLE_AGENT => 'Support Agent',
            default => 'Requester / User',
        };
    }
}
