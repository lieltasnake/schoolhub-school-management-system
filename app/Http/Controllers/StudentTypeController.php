<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\StudentType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentType::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = StudentType::query()
            ->with('branch')
            ->withCount('students');

        if (! $canManageAll) {
            $query->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%"));
        }

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $user->branch_id;

        $studentTypes = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('student-types.index', [
            'studentTypes' => $studentTypes,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentType::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        return view('student-types.form', [
            'studentType' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StudentType::class);

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $type = StudentType::create($data);

        return redirect()->route('student-types.index')
            ->with('success', "Student type '{$type->name}' created successfully.");
    }

    public function edit(StudentType $studentType): View
    {
        $this->authorize('update', $studentType);

        $branches = Branch::orderBy('name')->get();

        return view('student-types.form', [
            'studentType' => $studentType,
            'branches' => $branches,
            'selectedBranchId' => $studentType->branch_id,
        ]);
    }

    public function update(Request $request, StudentType $studentType): RedirectResponse
    {
        $this->authorize('update', $studentType);

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $studentType->update($data);

        return redirect()->route('student-types.index')
            ->with('success', "Student type '{$studentType->name}' updated successfully.");
    }

    public function destroy(StudentType $studentType): RedirectResponse
    {
        $this->authorize('delete', $studentType);

        if ($studentType->students()->exists()) {
            return back()->with('error', 'Cannot delete student type because students are currently assigned to it.');
        }

        $studentType->delete();

        return redirect()->route('student-types.index')
            ->with('success', 'Student type deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
