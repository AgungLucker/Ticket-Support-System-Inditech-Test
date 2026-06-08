<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketEscalated extends Notification
{
    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket Dieskalasi: [{$this->ticket->ticket_number}] {$this->ticket->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket berikut telah dieskalasi dan membutuhkan perhatian Anda.")
            ->line("**#{$this->ticket->ticket_number}** — {$this->ticket->title}")
            ->line("Prioritas: {$this->ticket->priority->name} | Customer: {$this->ticket->creator->name}")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Tiket #{$this->ticket->ticket_number} telah dieskalasi.",
            'url'           => route('tickets.show', $this->ticket, absolute: false),
        ];
    }
}
