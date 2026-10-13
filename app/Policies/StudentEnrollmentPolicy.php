<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\StudentEnrollment;
use App\Models\User;

class StudentEnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents();
    }

    public function view(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->canViewStudents()
            && $user->canAccessBranch($enrollment->student?->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageEnrollment()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->canManageEnrollment()
            && $user->canAccessBranch($enrollment->student?->branch_id);
    }

    public function delete(User $user, StudentEnrollment $enrollment): bool
    {
        return $user->canManageEnrollment()
            && $user->canAccessBranch($enrollment->student?->branch_id);
    }
}
