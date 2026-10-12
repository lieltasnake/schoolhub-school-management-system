<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-administrator')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageBranches() || $user->canManageUsers();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->canAccessBranch((int) $branch->id);
    }

    public function create(User $user): bool
    {
        return $user->canManageBranches();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->canManageBranches() && $user->canAccessBranch((int) $branch->id);
    }
}
