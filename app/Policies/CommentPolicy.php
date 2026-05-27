<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Comment $comment): bool
    {
        if ($comment->is_internal_note && $user->isCustomer()) {
            return false;
        }

        return (new TicketPolicy())->view($user, $comment->ticket);
    }

    public function create(User $user): bool
    {
        return $user->isCustomer()
            || $user->isAgent()
            || $user->isSupervisor();
    }

    public function createInternalNote(User $user): bool
    {
        return $user->isAgent() || $user->isSupervisor();
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}
