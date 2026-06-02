<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreCommentRequest;
use App\Models\Ticket;
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
                $path = $file->store('attachments', 'public');
                $comment->attachments()->create([
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name'   => basename($path),
                    'mime_type'     => $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                    'uploaded_by'   => Auth::id(),
                ]);
            }
        }

        return back()->with('success', $isInternal ? 'Catatan internal berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.');
    }
}
