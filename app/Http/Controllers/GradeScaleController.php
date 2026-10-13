<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GradeScale;
use App\Models\GradeScaleItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeScaleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GradeScale::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = GradeScale::query()
            ->with(['branch', 'items'])
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

        $scales = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('grade-scales.index', [
            'scales' => $scales,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', GradeScale::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        return view('grade-scales.form', [
            'scale' => null,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GradeScale::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.letter_grade' => ['required', 'string', 'max:10'],
            'items.*.min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.max_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'items.*.remarks' => ['nullable', 'string', 'max:100'],
            'items.*.is_pass' => ['nullable', 'boolean'],
        ]);

        $scale = DB::transaction(function () use ($data) {
            $scale = GradeScale::create([
                'branch_id' => $data['branch_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'is_default' => (bool) ($data['is_default'] ?? false),
                'status' => $data['status'],
            ]);

            foreach ($data['items'] as $item) {
                $scale->items()->create([
                    'letter_grade' => $item['letter_grade'],
                    'min_percentage' => $item['min_percentage'],
                    'max_percentage' => $item['max_percentage'],
                    'grade_point' => $item['grade_point'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                    'is_pass' => (bool) ($item['is_pass'] ?? true),
                ]);
            }

            return $scale;
        });

        return redirect()->route('grade-scales.index')
            ->with('success', "Grade scale '{$scale->name}' created successfully.");
    }

    public function edit(GradeScale $gradeScale): View
    {
        $this->authorize('update', $gradeScale);

        $gradeScale->load(['branch', 'items']);
        $branches = collect([$gradeScale->branch]);

        return view('grade-scales.form', [
            'scale' => $gradeScale,
            'branches' => $branches,
            'selectedBranchId' => $gradeScale->branch_id,
        ]);
    }

    public function update(Request $request, GradeScale $gradeScale): RedirectResponse
    {
        $this->authorize('update', $gradeScale);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.letter_grade' => ['required', 'string', 'max:10'],
            'items.*.min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.max_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'items.*.remarks' => ['nullable', 'string', 'max:100'],
            'items.*.is_pass' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($gradeScale, $data) {
            $gradeScale->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'is_default' => (bool) ($data['is_default'] ?? false),
                'status' => $data['status'],
            ]);

            $gradeScale->items()->delete();

            foreach ($data['items'] as $item) {
                $gradeScale->items()->create([
                    'letter_grade' => $item['letter_grade'],
                    'min_percentage' => $item['min_percentage'],
                    'max_percentage' => $item['max_percentage'],
                    'grade_point' => $item['grade_point'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                    'is_pass' => (bool) ($item['is_pass'] ?? true),
                ]);
            }
        });

        return redirect()->route('grade-scales.index')
            ->with('success', "Grade scale '{$gradeScale->name}' updated successfully.");
    }

    public function destroy(GradeScale $gradeScale): RedirectResponse
    {
        $this->authorize('delete', $gradeScale);

        if ($gradeScale->assessments()->exists()) {
            return back()->with('error', 'Cannot delete grade scale because assessments are currently using it.');
        }

        $gradeScale->delete();

        return redirect()->route('grade-scales.index')
            ->with('success', 'Grade scale deleted successfully.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
