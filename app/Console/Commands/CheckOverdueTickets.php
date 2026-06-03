<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketSlaOverdue;
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
            ->whereNotIn('status', ['Resolved', 'Closed'])
            ->with(['creator', 'priority'])
            ->get();

        if ($overdueTickets->isEmpty()) {
            $this->info('Tidak ada tiket overdue.');
            return;
        }

        $supervisorsAndAdmins = User::whereHas('role', fn($q) => $q->whereIn('slug', ['supervisor', 'admin']))->get();

        foreach ($overdueTickets as $ticket) {
            Notification::send($supervisorsAndAdmins, new TicketSlaOverdue($ticket));
        }

        $this->info("Notifikasi dikirim untuk {$overdueTickets->count()} tiket overdue.");
    }
}
