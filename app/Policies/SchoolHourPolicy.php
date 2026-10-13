<?php

namespace App\Policies;

use App\Models\SchoolHour;
use App\Models\User;

class SchoolHourPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, SchoolHour $schoolHour): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($schoolHour->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function update(User $user, SchoolHour $schoolHour): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($schoolHour->branch_id);
    }

    public function delete(User $user, SchoolHour $schoolHour): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($schoolHour->branch_id);
    }
}
