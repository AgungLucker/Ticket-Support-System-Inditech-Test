<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreCommentRequest;
use App\Models\Ticket;
use App\Notifications\TicketCommented;
use App\Services\CommentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService) {}

    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        Gate::authorize('addComment', $ticket);

        $isInternal = false;

        if (! Auth::user()->isCustomer() && $request->boolean('is_internal_note')) {
            Gate::authorize('addInternalNote', $ticket);
            $isInternal = true;
        }

        $files   = $request->hasFile('attachments') ? $request->file('attachments') : null;
        $comment = $this->commentService->addComment(
            $ticket, Auth::id(), $request->validated()['content'], $isInternal, $files
        );

        $recipients = collect([$ticket->creator, $ticket->assignedAgent])
            ->filter()
            ->unique('id')
            ->filter(fn($u) => $u->id !== Auth::id())
            ->filter(fn($u) => ! ($isInternal && $u->isCustomer()));

        foreach ($recipients as $recipient) {
            $recipient->notify(new TicketCommented($ticket, $comment));
        }

        return back()->with('success', $isInternal ? 'Catatan internal berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.');
    }
}
