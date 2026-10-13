<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, Grade $grade): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($grade->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageAcademicCalendar()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($grade->branch_id);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($grade->branch_id);
    }
}
