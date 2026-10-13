<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\RegistrationSetup;
use App\Models\User;

class RegistrationSetupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageRegistration();
    }

    public function view(User $user, RegistrationSetup $setup): bool
    {
        return $user->canManageRegistration()
            && $user->canAccessBranch($setup->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageRegistration()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, RegistrationSetup $setup): bool
    {
        return $user->canManageRegistration()
            && $user->canAccessBranch($setup->branch_id);
    }

    public function delete(User $user, RegistrationSetup $setup): bool
    {
        return $user->canManageRegistration()
            && $user->canAccessBranch($setup->branch_id);
    }
}
