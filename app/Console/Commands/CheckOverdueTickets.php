<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSlaOverdue;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckOverdueTickets extends Command
{
    protected $signature = 'tickets:check-overdue';

    protected $description = 'Kirim notifikasi untuk tiket yang melewati batas SLA';

    public function handle(): void
    {
        $overdueTickets = Ticket::whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['Resolved', 'Closed', 'Waiting for Customer'])
            ->whereDoesntHave('activityLogs', fn($q) => $q->where('action', 'sla_overdue'))
            ->with(['creator', 'priority'])
            ->get();

        if ($overdueTickets->isEmpty()) {
            $this->info('Tidak ada tiket overdue.');
            return;
        }

        $supervisorsAndAdmins = User::whereHas('role', fn($q) => $q->whereIn('slug', ['supervisor', 'admin']))->get();

        foreach ($overdueTickets as $ticket) {
            Notification::send($supervisorsAndAdmins, new TicketSlaOverdue($ticket));
            ActivityLogger::log($ticket, 'sla_overdue');
        }

        $this->info("Notifikasi dikirim untuk {$overdueTickets->count()} tiket overdue.");
    }
}
