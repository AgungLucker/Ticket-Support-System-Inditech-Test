<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketResolved extends Notification
{
    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket Diselesaikan: [{$this->ticket->ticket_number}] {$this->ticket->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket Anda telah diselesaikan.")
            ->line("**#{$this->ticket->ticket_number}** — {$this->ticket->title}")
            ->line("Jika masalah belum teratasi, Anda dapat membuka kembali tiket ini.")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Tiket #{$this->ticket->ticket_number} telah diselesaikan.",
            'url'           => route('tickets.show', $this->ticket),
        ];
    }
}
