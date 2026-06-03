<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketSlaOverdue extends Notification
{
    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("SLA Overdue: [{$this->ticket->ticket_number}] {$this->ticket->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket berikut telah melewati batas SLA dan belum diselesaikan.")
            ->line("**#{$this->ticket->ticket_number}** — {$this->ticket->title}")
            ->line("Due: {$this->ticket->due_at->format('d M Y H:i')} | Status: {$this->ticket->status}")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Tiket #{$this->ticket->ticket_number} melewati batas SLA.",
            'url'           => route('tickets.show', $this->ticket),
        ];
    }
}
