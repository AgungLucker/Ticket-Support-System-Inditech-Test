<?php

namespace App\Services;

use App\Models\SlaRule;
use App\Models\Ticket;
use Carbon\Carbon;

class TicketService
{
    /**
     * Generate a unique ticket number.
     * Format: TKT-YYYYMMDD-XXXX (e.g. TKT-20240528-0001)
     */
    public function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = Carbon::now()->format('Ymd');
        
        $lastTicket = Ticket::where('ticket_number', 'LIKE', "{$prefix}-{$date}-%")->orderBy('id', 'desc')->first();

        if (!$lastTicket) {
            $number = 1;
        } else {
            $lastNumber = intval(substr($lastTicket->ticket_number, -4));
            $number = $lastNumber + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $number);
    }

    /**
     * Calculate SLA due date based on priority.
     */
    public function calculateSlaDueDate(int $priorityId): ?Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            return null;
        }

        return Carbon::now()->addHours($slaRule->resolution_time_hours);
    }

    public function calculateResponseDueDate(int $priorityId): ?Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            return null;
        }

        return Carbon::now()->addHours($slaRule->response_time_hours);
    }
}
