<?php

namespace App\Services;

use App\Models\SlaRule;
use App\Models\Ticket;
use Carbon\Carbon;

class TicketService
{
    /**
     * Generate a unique ticket number.
     * Format: TCK-YYYY-XXXXXX (e.g. TCK-2026-000001)
     */
    public function generateTicketNumber(): string
    {
        $prefix = 'TCK-' . now()->year;

        $lastTicket = Ticket::where('ticket_number', 'LIKE', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTicket ? ((int) substr($lastTicket->ticket_number, -6)) + 1 : 1;

        return sprintf('%s-%06d', $prefix, $number);
    }

    /**
     * Calculate SLA resolution due date based on priority.
     * $from defaults to now() for new tickets; pass $ticket->created_at when recalculating on edit.
     */
    public function calculateSlaDueDate(int $priorityId, ?Carbon $from = null): ?Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            return null;
        }

        return ($from ?? Carbon::now())->copy()->addHours($slaRule->resolution_time_hours);
    }

    public function calculateResponseDueDate(int $priorityId, ?Carbon $from = null): ?Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            return null;
        }

        return ($from ?? Carbon::now())->copy()->addHours($slaRule->response_time_hours);
    }
}
