<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, AcademicYear $academicYear): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($academicYear->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($academicYear->branch_id);
    }
}
