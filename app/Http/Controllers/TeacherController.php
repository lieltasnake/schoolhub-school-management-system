<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Teacher::class);

        $user = $request->user();
        $query = Teacher::query()->with(['user', 'branch']);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('teachers.index', [
            'teachers' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Teacher::class);

        $user = $request->user();
        $canViewAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        return view('teachers.form', [
            'teacher' => null,
            'branches' => Branch::query()
                ->when(! $canViewAll, fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'availableUsers' => User::query()
                ->when(! $canViewAll, fn (Builder $b) => $b->where('branch_id', $user->branch_id))
                ->whereDoesntHave('teacher')
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Teacher::class);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('teachers', 'user_id')],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_id')],
            'qualification' => ['nullable', 'string', 'max:200'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $teacher = Teacher::create($validated);

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher): View
    {
        $this->authorize('view', $teacher);
        $teacher->load(['user', 'branch', 'assignments.grade', 'assignments.section', 'assignments.subject', 'assignments.academicYear']);

        return view('teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher): View
    {
        $this->authorize('update', $teacher);
        $teacher->load('user', 'branch');

        return view('teachers.form', [
            'teacher' => $teacher,
            'branches' => collect([$teacher->branch]),
            'availableUsers' => User::query()
                ->where(fn (Builder $q) => $q->where('id', $teacher->user_id)->orWhereDoesntHave('teacher'))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorize('update', $teacher);

        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_id')->ignore($teacher->id)],
            'qualification' => ['nullable', 'string', 'max:200'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $teacher->update($validated);

        return redirect()->route('teachers.show', $teacher)
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);
        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Teacher removed successfully.');
    }
}
