<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class AttachmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Attachment $attachment): bool
    {
        $attachable = $attachment->attachable;

        if ($attachable instanceof Ticket) {
            return (new TicketPolicy())->view($user, $attachable);
        }

        if ($attachable instanceof Comment) {
            return (new CommentPolicy())->view($user, $attachable);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer()
            || $user->isAgent()
            || $user->isSupervisor();
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $attachment->uploaded_by === $user->id;
    }
}
