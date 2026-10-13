<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\StudentRegistration;
use App\Models\User;

class StudentRegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageRegistration();
    }

    public function view(User $user, StudentRegistration $registration): bool
    {
        return $user->canManageRegistration()
            && $user->canAccessBranch($registration->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageRegistration()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, StudentRegistration $registration): bool
    {
        return $user->canManageRegistration()
            && $user->canAccessBranch($registration->branch_id);
    }

    public function delete(User $user, StudentRegistration $registration): bool
    {
        return $user->canManageRegistration()
            && $user->canAccessBranch($registration->branch_id);
    }
}
