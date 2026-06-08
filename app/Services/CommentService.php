<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(protected AttachmentService $attachmentService) {}

    public function addComment(Ticket $ticket, int $userId, string $content, bool $isInternal, ?array $files): Comment
    {
        return DB::transaction(function () use ($ticket, $userId, $content, $isInternal, $files) {
            $comment = $ticket->comments()->create([
                'user_id'          => $userId,
                'content'          => $content,
                'is_internal_note' => $isInternal,
            ]);

            if ($files) {
                $this->attachmentService->storeMany(
                    $files, $comment, $userId, $ticket,
                    $isInternal ? 'internal note' : 'comment'
                );
            }

            ActivityLogger::log($ticket, $isInternal ? 'internal_note_added' : 'comment_added');

            return $comment;
        });
    }
}
