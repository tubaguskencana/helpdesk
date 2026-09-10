<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            return $user->department_id === null || $ticket->department_id === $user->department_id;
        }

        if ($user->isAgent()) {
            // Can view if assigned or in the same department
            if ($ticket->assigned_to === $user->id) {
                return true;
            }
            if ($user->department_id && $ticket->department_id === $user->department_id) {
                return true;
            }
            // Or unassigned tickets in their department
            return $ticket->assigned_to === null && $ticket->department_id === $user->department_id;
        }

        // Regular user can only view their own tickets
        return $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            return $user->department_id === null || $ticket->department_id === $user->department_id;
        }

        if ($user->isAgent()) {
            return $ticket->assigned_to === $user->id;
        }

        return false;
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket) && !$ticket->isResolvedOrClosed();
    }

    public function viewInternalNotes(User $user, Ticket $ticket): bool
    {
        return $user->isStaff();
    }

    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() && $this->view($user, $ticket);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSupervisor()) {
            return $user->department_id === null || $ticket->department_id === $user->department_id;
        }

        // Agent can self-assign if currently unassigned
        if ($user->isAgent() && $ticket->assigned_to === null && $ticket->department_id === $user->department_id) {
            return true;
        }

        return false;
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        if ($user->isStaff()) {
            return $this->view($user, $ticket);
        }

        // Regular user can close or reopen their own ticket
        return $ticket->user_id === $user->id;
    }

    public function updatePriority(User $user, Ticket $ticket): bool
    {
        return $user->isStaff() && $this->view($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }
}
