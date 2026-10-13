<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewStudents();
    }

    public function view(User $user, Student $student): bool
    {
        return $user->canViewStudents()
            && $user->canAccessBranch($student->branch_id);
    }

    public function create(User $user, ?Branch $branch = null): bool
    {
        return $user->canManageStudents()
            && ($branch === null || $user->canAccessBranch($branch->id));
    }

    public function update(User $user, Student $student): bool
    {
        return $user->canManageStudents()
            && $user->canAccessBranch($student->branch_id);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->canManageStudents()
            && $user->canAccessBranch($student->branch_id);
    }
}
