<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;

class TermPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, Term $term): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($term->academicYear->branch_id);
    }

    public function create(User $user, ?AcademicYear $academicYear = null): bool
    {
        return $user->canManageAcademicCalendar()
            && ($academicYear === null || $user->canAccessBranch($academicYear->branch_id));
    }

    public function update(User $user, Term $term): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($term->academicYear->branch_id);
    }
}
