<?php

namespace App\Http\Controllers;

use App\Http\Requests\TermRequest;
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

class TermController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Term::class);

        $user = $request->user();
        $query = Term::query()->with('academicYear.branch');

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->whereHas('academicYear', fn (Builder $builder) => $builder->where('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('academicYear', fn (Builder $builder) => $builder->where('branch_id', $request->integer('branch_id')));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('terms.index', [
            'terms' => $query->orderByDesc('start_date')->paginate(10)->withQueryString(),
            'academicYears' => $this->availableAcademicYears($user->branch_id, $user->hasAnyRole(['super-administrator', 'school-administrator'])),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $builder) => $builder->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Term::class);

        return view('terms.form', [
            'term' => null,
            'academicYears' => $this->availableAcademicYears(
                $request->user()->branch_id,
                $request->user()->hasAnyRole(['super-administrator', 'school-administrator']),
            ),
        ]);
    }

    public function store(TermRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $term = DB::transaction(function () use ($validated): Term {
            $academicYear = AcademicYear::query()->findOrFail($validated['academic_year_id']);
            Branch::query()->whereKey($academicYear->branch_id)->lockForUpdate()->firstOrFail();
            $academicYear->refresh();
            $this->authorize('create', [Term::class, $academicYear]);
            $this->assertTermFitsAcademicYear($academicYear, $validated['start_date'], $validated['end_date']);
            $this->assertNoOverlappingTerm(
                $academicYear->id,
                $validated['start_date'],
                $validated['end_date'],
            );

            return $academicYear->terms()->create($validated);
        });

        return redirect()->route('terms.show', $term)->with('success', 'Term created successfully.');
    }

    public function show(Term $term): View
    {
        $this->authorize('view', $term);
        $term->load('academicYear.branch');

        return view('terms.show', compact('term'));
    }

    public function edit(Term $term): View
    {
        $this->authorize('update', $term);
        $term->load('academicYear');

        return view('terms.form', [
            'term' => $term,
            'academicYears' => collect([$term->academicYear]),
        ]);
    }

    public function update(TermRequest $request, Term $term): RedirectResponse
    {
        $this->authorize('update', $term);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $term): void {
            $academicYear = $term->academicYear;
            Branch::query()->whereKey($academicYear->branch_id)->lockForUpdate()->firstOrFail();
            $academicYear->refresh();
            $this->assertTermFitsAcademicYear($academicYear, $validated['start_date'], $validated['end_date']);
            $this->assertNoOverlappingTerm(
                $academicYear->id,
                $validated['start_date'],
                $validated['end_date'],
                $term->id,
            );
            $term->update($validated);
        });

        return redirect()->route('terms.show', $term)->with('success', 'Term updated successfully.');
    }

    public function activate(Term $term): RedirectResponse
    {
        $this->authorize('update', $term);

        $activated = DB::transaction(function () use ($term): bool {
            $academicYear = $term->academicYear;
            Branch::query()->whereKey($academicYear->branch_id)->lockForUpdate()->firstOrFail();

            if ($academicYear->refresh()->status !== 'active') {
                return false;
            }

            Term::query()->where('academic_year_id', $academicYear->id)
                ->where('id', '!=', $term->id)
                ->where('status', 'active')
                ->update(['status' => 'completed']);

            $term->update(['status' => 'active']);

            return true;
        });

        if (! $activated) {
            return redirect()->route('terms.show', $term)
                ->with('error', 'Activate this term only after its academic year is active.');
        }

        return redirect()->route('terms.show', $term)->with('success', 'Term is now active.');
    }

    private function availableAcademicYears(?int $branchId, bool $canViewAll): Collection
    {
        return AcademicYear::query()
            ->with('branch')
            ->when(! $canViewAll, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->orderByDesc('start_date')
            ->get();
    }

    private function assertTermFitsAcademicYear(AcademicYear $academicYear, string $startDate, string $endDate): void
    {
        if (
            $startDate < $academicYear->start_date->toDateString()
            || $endDate > $academicYear->end_date->toDateString()
        ) {
            throw ValidationException::withMessages([
                'start_date' => 'Term dates must fall within the selected academic year.',
            ]);
        }
    }

    private function assertNoOverlappingTerm(
        int $academicYearId,
        string $startDate,
        string $endDate,
        ?int $exceptId = null,
    ): void {
        $overlapExists = Term::query()
            ->where('academic_year_id', $academicYearId)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'start_date' => 'Term dates cannot overlap another term in this academic year.',
            ]);
        }
    }
}
