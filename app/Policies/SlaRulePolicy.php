<?php

namespace App\Policies;

use App\Models\SlaRule;
use App\Models\User;

class SlaRulePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, SlaRule $slaRule): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, SlaRule $slaRule): bool
    {
        return false;
    }

    public function delete(User $user, SlaRule $slaRule): bool
    {
        return false;
    }
}
