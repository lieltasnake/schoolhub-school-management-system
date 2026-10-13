<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Subject::class);

        $user = $request->user();
        $query = Subject::query()->with('branch');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('department', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('subjects.index', [
            'subjects' => $query->orderBy('name')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Subject::class);

        $user = $request->user();

        return view('subjects.form', [
            'subject' => null,
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Subject::class);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', Rule::unique('subjects')->where(fn ($q) => $q->where('branch_id', $request->integer('branch_id')))],
            'department' => ['nullable', 'string', 'max:120'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $subject = Subject::create($validated);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject): View
    {
        $this->authorize('view', $subject);
        $subject->load(['branch', 'assignments.teacher.user', 'assignments.grade', 'assignments.section']);

        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        return view('subjects.form', [
            'subject' => $subject,
            'branches' => collect([$subject->branch]),
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', Rule::unique('subjects')->where(fn ($q) => $q->where('branch_id', $subject->branch_id))->ignore($subject->id)],
            'department' => ['nullable', 'string', 'max:120'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.show', $subject)
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('delete', $subject);
        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
