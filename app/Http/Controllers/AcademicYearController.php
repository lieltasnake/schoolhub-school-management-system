<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Term;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicYearController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AcademicYear::class);

        $user = $request->user();
        $query = AcademicYear::query()->with('branch');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('academic-years.index', [
            'academicYears' => $query->orderByDesc('start_date')->paginate(10)->withQueryString(),
            'branches' => $this->availableBranches($user->branch_id, $user->hasAnyRole(['super-administrator', 'school-administrator'])),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AcademicYear::class);

        return view('academic-years.form', [
            'academicYear' => null,
            'branches' => $this->availableBranches(
                $request->user()->branch_id,
                $request->user()->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function store(AcademicYearRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $academicYear = DB::transaction(function () use ($validated): AcademicYear {
            Branch::query()->whereKey($validated['branch_id'])->lockForUpdate()->firstOrFail();
            $this->assertNoOverlappingYear(
                $validated['branch_id'],
                $validated['start_date'],
                $validated['end_date'],
            );

            return AcademicYear::create($validated);
        });

        return redirect()->route('academic-years.show', $academicYear)
            ->with('success', 'Academic year created successfully.');
    }

    public function show(AcademicYear $academicYear): View
    {
        $this->authorize('view', $academicYear);
        $academicYear->load(['branch', 'terms' => fn ($query) => $query->orderBy('start_date')]);

        return view('academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear): View
    {
        $this->authorize('update', $academicYear);

        return view('academic-years.form', [
            'academicYear' => $academicYear,
            'branches' => collect([$academicYear->branch]),
        ]);
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('update', $academicYear);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $academicYear): void {
            Branch::query()->whereKey($academicYear->branch_id)->lockForUpdate()->firstOrFail();
            $this->assertNoOverlappingYear(
                $academicYear->branch_id,
                $validated['start_date'],
                $validated['end_date'],
                $academicYear->id,
            );
            $academicYear->update($validated);
        });

        return redirect()->route('academic-years.show', $academicYear)
            ->with('success', 'Academic year updated successfully.');
    }

    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('update', $academicYear);

        DB::transaction(function () use ($academicYear): void {
            Branch::query()->whereKey($academicYear->branch_id)->lockForUpdate()->firstOrFail();

            $previousYearIds = AcademicYear::query()
                ->where('branch_id', $academicYear->branch_id)
                ->where('id', '!=', $academicYear->id)
                ->where('status', 'active')
                ->pluck('id');

            Term::query()->whereIn('academic_year_id', $previousYearIds)
                ->where('status', 'active')
                ->update(['status' => 'completed']);

            AcademicYear::query()->whereIn('id', $previousYearIds)
                ->update(['status' => 'completed']);

            $academicYear->update(['status' => 'active']);
        });

        return redirect()->route('academic-years.show', $academicYear)
            ->with('success', 'Academic year is now active for this branch.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }

    private function assertNoOverlappingYear(int $branchId, string $startDate, string $endDate, ?int $exceptId = null): void
    {
        $overlapExists = AcademicYear::query()
            ->where('branch_id', $branchId)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_date' => 'Academic year dates cannot overlap another year at this branch.',
            ]);
        }
    }
}
