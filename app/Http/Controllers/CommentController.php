<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreCommentRequest;
use App\Models\Ticket;
use App\Notifications\TicketCommented;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        Gate::authorize('addComment', $ticket);

        $isInternal = false;

        // Hanya staff yang boleh kirim internal note
        if (! Auth::user()->isCustomer() && $request->boolean('is_internal_note')) {
            Gate::authorize('addInternalNote', $ticket);
            $isInternal = true;
        }

        $comment = $ticket->comments()->create([
            'user_id'          => Auth::id(),
            'content'          => $request->validated()['content'],
            'is_internal_note' => $isInternal,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (! $file->isValid()) {
                    continue;
                }
                $path = $file->store('attachments');
                $comment->attachments()->create([
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => basename($path),
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                    'uploaded_by'   => Auth::id(),
                ]);
                ActivityLogger::log($ticket, 'attachment_uploaded', $isInternal ? 'internal note' : 'comment', $file->getClientOriginalName());
            }
        }

        // Kirim notifikasi ke creator + assigned agent, kecuali yang menulis komentar
        // Customer tidak dapat notifikasi internal note
        $recipients = collect();
        $recipients->push($ticket->creator);
        if ($ticket->assignedAgent) {
            $recipients->push($ticket->assignedAgent);
        }
        $recipients = $recipients
            ->unique('id')
            ->filter(fn($u) => $u->id !== Auth::id())
            ->filter(fn($u) => ! ($isInternal && $u->isCustomer()));

        foreach ($recipients as $recipient) {
            $recipient->notify(new TicketCommented($ticket, $comment));
        }

        ActivityLogger::log($ticket, $isInternal ? 'internal_note_added' : 'comment_added');

        return back()->with('success', $isInternal ? 'Catatan internal berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.');
    }
}
