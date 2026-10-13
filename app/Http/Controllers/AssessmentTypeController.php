<?php

namespace App\Http\Controllers;

use App\Models\AssessmentType;
use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssessmentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssessmentType::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = AssessmentType::query()
            ->with('branch')
            ->withCount('assessments');

        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
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

        $types = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('assessment-types.index', [
            'types' => $types,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AssessmentType::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        return view('assessment-types.form', [
            'type' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AssessmentType::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $type = AssessmentType::create($data);

        return redirect()->route('assessment-types.index')
            ->with('success', "Assessment type '{$type->name}' created successfully.");
    }

    public function edit(AssessmentType $assessmentType): View
    {
        $this->authorize('update', $assessmentType);

        $branches = collect([$assessmentType->branch]);

        return view('assessment-types.form', [
            'type' => $assessmentType,
            'branches' => $branches,
            'selectedBranchId' => $assessmentType->branch_id,
        ]);
    }

    public function update(Request $request, AssessmentType $assessmentType): RedirectResponse
    {
        $this->authorize('update', $assessmentType);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $assessmentType->update($data);

        return redirect()->route('assessment-types.index')
            ->with('success', "Assessment type '{$assessmentType->name}' updated successfully.");
    }

    public function destroy(AssessmentType $assessmentType): RedirectResponse
    {
        $this->authorize('delete', $assessmentType);

        if ($assessmentType->assessments()->exists()) {
            return back()->with('error', 'Cannot delete assessment type because existing assessments use it.');
        }

        $assessmentType->delete();

        return redirect()->route('assessment-types.index')
            ->with('success', 'Assessment type deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
