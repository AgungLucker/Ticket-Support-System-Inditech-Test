<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;

class TicketStatusService
{
    /**
     * Valid status transitions map.
     * Key = current status, Value = list of allowed next statuses.
     */
    private array $transitions = [
        'Open'                 => ['Assigned', 'Closed'],
        'Assigned'             => ['In Progress', 'Escalated'],
        'In Progress'          => ['Waiting for Customer', 'Resolved', 'Escalated'],
        'Waiting for Customer' => ['In Progress', 'Resolved', 'Closed'],
        'Resolved'             => ['Closed', 'Reopened'],
        'Closed'               => ['Reopened'],
        'Reopened'             => ['Assigned', 'In Progress'],
        'Escalated'            => ['Assigned', 'In Progress'],
    ];

    /**
     * All valid statuses in the system.
     */
    public static function allStatuses(): array
    {
        return [
            'Open',
            'Assigned',
            'In Progress',
            'Waiting for Customer',
            'Resolved',
            'Closed',
            'Reopened',
            'Escalated',
        ];
    }

    /**
     * Returns the allowed next statuses from a given current status.
     */
    public function allowedTransitions(string $currentStatus): array
    {
        return $this->transitions[$currentStatus] ?? [];
    }

    /**
     * Checks whether transitioning from one status to another is valid.
     */
    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->allowedTransitions($from), true);
    }

    /**
     * Returns the timestamp columns that must be updated alongside the status change.
     * Resolved sets resolved_at, Closed sets closed_at, Reopened clears both.
     */
    public function timestampUpdates(string $newStatus): array
    {
        return match ($newStatus) {
            'Resolved' => ['resolved_at' => now()],
            'Closed'   => ['closed_at'   => now()],
            'Reopened' => ['resolved_at' => null, 'closed_at' => null],
            default    => [],
        };
    }

    /**
     * - Customer: can only Reopen (Resolved/Closed → Reopened)
     * - Agent: can update status of their assigned ticket (except Assign/Reassign)
     * - Supervisor/Admin: unrestricted (handled by Policy)
     */
    public function canUserTransition(User $user, Ticket $ticket, string $newStatus): bool
    {
        if (! $this->canTransition($ticket->status, $newStatus)) {
            return false;
        }

        if ($user->isCustomer()) {
            return $newStatus === 'Reopened' && $ticket->created_by === $user->id;
        }

        return true;
    }
}
