<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification
{
    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket Di-assign: [{$this->ticket->ticket_number}] {$this->ticket->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket berikut telah di-assign kepada Anda.")
            ->line("**#{$this->ticket->ticket_number}** — {$this->ticket->title}")
            ->line("Prioritas: {$this->ticket->priority->name} | Status: {$this->ticket->status}")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Tiket #{$this->ticket->ticket_number} telah di-assign kepada Anda.",
            'url'           => route('tickets.show', $this->ticket, absolute: false),
        ];
    }
}
