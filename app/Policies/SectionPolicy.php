<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageAcademicCalendar();
    }

    public function view(User $user, Section $section): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($section->grade->branch_id);
    }

    public function create(User $user, ?Grade $grade = null): bool
    {
        return $user->canManageAcademicCalendar()
            && ($grade === null || $user->canAccessBranch($grade->branch_id));
    }

    public function update(User $user, Section $section): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($section->grade->branch_id);
    }

    public function delete(User $user, Section $section): bool
    {
        return $user->canManageAcademicCalendar()
            && $user->canAccessBranch($section->grade->branch_id);
    }
}
