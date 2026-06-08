<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false; // Admin only, handled by before()
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function manageRoles(User $user): bool
    {
        return false;
    }
}
