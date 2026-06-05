<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        Ticket $ticket,
        string $action,
        ?string $oldValue = null,
        ?string $newValue = null
    ): void {
        ActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }
}
