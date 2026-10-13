<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Student;
use App\Models\StudentGroup;
use App\Models\StudentGroupMember;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentGroupController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentGroup::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = StudentGroup::query()
            ->with('branch')
            ->withCount('students');

        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $user->branch_id;

        $groups = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('student-groups.index', [
            'groups' => $groups,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentGroup::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        return view('student-groups.form', [
            'group' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StudentGroup::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $group = StudentGroup::create($data);

        return redirect()->route('student-groups.show', $group)
            ->with('success', "Student group '{$group->name}' created successfully.");
    }

    public function show(StudentGroup $studentGroup): View
    {
        $this->authorize('view', $studentGroup);

        $studentGroup->load(['branch', 'students' => function ($q) {
            $q->with('latestEnrollment.grade');
        }]);

        // Available students to add
        $enrolledStudents = Student::where('branch_id', $studentGroup->branch_id)
            ->where('status', 'active')
            ->whereNotIn('id', $studentGroup->students->pluck('id'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('student-groups.show', [
            'group' => $studentGroup,
            'availableStudents' => $enrolledStudents,
        ]);
    }

    public function edit(StudentGroup $studentGroup): View
    {
        $this->authorize('update', $studentGroup);

        $branches = collect([$studentGroup->branch]);

        return view('student-groups.form', [
            'group' => $studentGroup,
            'branches' => $branches,
            'selectedBranchId' => $studentGroup->branch_id,
        ]);
    }

    public function update(Request $request, StudentGroup $studentGroup): RedirectResponse
    {
        $this->authorize('update', $studentGroup);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $studentGroup->update($data);

        return redirect()->route('student-groups.show', $studentGroup)
            ->with('success', "Student group '{$studentGroup->name}' updated successfully.");
    }

    public function addMember(Request $request, StudentGroup $studentGroup): RedirectResponse
    {
        $this->authorize('update', $studentGroup);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        $studentGroup->students()->syncWithoutDetaching([
            $data['student_id'] => [
                'role' => $data['role'] ?? 'member',
                'joined_at' => now()->toDateString(),
            ],
        ]);

        return back()->with('success', 'Student added to group.');
    }

    public function removeMember(StudentGroup $studentGroup, Student $student): RedirectResponse
    {
        $this->authorize('update', $studentGroup);

        $studentGroup->students()->detach($student->id);

        return back()->with('success', 'Student removed from group.');
    }

    public function destroy(StudentGroup $studentGroup): RedirectResponse
    {
        $this->authorize('delete', $studentGroup);

        $studentGroup->delete();

        return redirect()->route('student-groups.index')
            ->with('success', 'Student group deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
