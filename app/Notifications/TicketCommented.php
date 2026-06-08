<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommented extends Notification
{
    public function __construct(public Ticket $ticket, public Comment $comment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->comment->is_internal_note ? 'Catatan internal baru' : 'Komentar baru';

        return (new MailMessage)
            ->subject("{$label}: [{$this->ticket->ticket_number}] {$this->ticket->title}")
            ->greeting("Halo, {$notifiable->name}!")
            ->line("{$label} ditambahkan oleh {$this->comment->user->name}.")
            ->line("**#{$this->ticket->ticket_number}** — {$this->ticket->title}")
            ->action('Lihat Tiket', route('tickets.show', $this->ticket));
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->comment->is_internal_note ? 'Catatan internal' : 'Komentar';

        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "{$label} baru oleh {$this->comment->user->name} pada #{$this->ticket->ticket_number}.",
            'url'           => route('tickets.show', $this->ticket, absolute: false),
        ];
    }
}
