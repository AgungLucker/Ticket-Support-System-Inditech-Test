<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Tiket Baru: [{$this->ticket->ticket_number}] {$this->ticket->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("Tiket baru telah dibuat oleh {$this->ticket->creator->name}.")
            ->line("**#{$this->ticket->ticket_number}** — {$this->ticket->title}")
            ->line("Prioritas: {$this->ticket->priority->name} | Kategori: {$this->ticket->category->name}")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Tiket baru dibuat oleh {$this->ticket->creator->name}.",
            'url'           => route('tickets.show', $this->ticket),
        ];
    }
}
