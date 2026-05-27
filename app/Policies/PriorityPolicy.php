<?php

namespace App\Policies;

use App\Models\Priority;
use App\Models\User;

class PriorityPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Priority $priority): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Priority $priority): bool
    {
        return false;
    }

    public function delete(User $user, Priority $priority): bool
    {
        return false;
    }
}
