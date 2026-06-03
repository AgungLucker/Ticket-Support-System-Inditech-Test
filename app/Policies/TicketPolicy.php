<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isCustomer()
            || $user->isAgent()
            || $user->isSupervisor();
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $this->ownsTicket($user, $ticket)
            || $this->isAssignedAgent($user, $ticket)
            || $this->isSupervisorForTicket($user, $ticket);
    }

    public function create(User $user): bool
    {
        // Admin sudah di-handle oleh before(), Customer juga boleh
        return $user->isCustomer();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return false; // hanya Admin, di-handle oleh before()
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return false;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->isSupervisor()
            && ($ticket->assigned_agent_id === null || $this->isSupervisorForTicket($user, $ticket));
    }

    public function addComment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($user, $ticket)
            || $this->isSupervisorForTicket($user, $ticket);
    }

    public function viewInternalNotes(User $user, Ticket $ticket): bool
    {
        return $this->addInternalNote($user, $ticket);
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($user, $ticket)
            || $this->isSupervisorForTicket($user, $ticket);
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return $this->ownsTicket($user, $ticket)
            && in_array($ticket->status, ['Resolved', 'Closed'], true);
    }

    public function export(User $user): bool
    {
        return $user->isSupervisor();
    }

    private function ownsTicket(User $user, Ticket $ticket): bool
    {
        return $user->isCustomer() && $ticket->created_by === $user->id;
    }

    private function isAssignedAgent(User $user, Ticket $ticket): bool
    {
        return $user->isAgent() && $ticket->assigned_agent_id === $user->id;
    }

    private function isSupervisorForTicket(User $user, Ticket $ticket): bool
    {
        if (! $user->isSupervisor() || $user->team_id === null) {
            return false;
        }

        return $ticket->assignedAgent?->team_id === $user->team_id;
    }
}
