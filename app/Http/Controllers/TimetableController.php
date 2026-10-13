<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Period;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Timetable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Timetable::class);

        $user = $request->user();
        $query = Timetable::query()->with([
            'branch',
            'academicYear',
            'term',
            'section.grade',
            'subject',
            'teacher.user',
            'classroom',
            'period',
        ]);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->integer('section_id'));
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->string('day_of_week')->toString());
        }

        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        return view('timetables.index', [
            'timetables' => $query->orderBy('day_of_week')->orderBy('period_id')->paginate(20)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('grade')->orderBy('name')->get(),
            'teachers' => Teacher::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('user')->get(),
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Timetable::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branchId = $canManageAll ? ($request->integer('branch_id') ?: $user->branch_id ?: Branch::first()?->id) : $user->branch_id;

        return view('timetables.form', [
            'timetable' => null,
            'branches' => Branch::query()
                ->when(! $canManageAll, fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderBy('name')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('grade')->orderBy('name')->get(),
            'subjects' => Subject::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'teachers' => Teacher::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('user')->get(),
            'classrooms' => Classroom::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('room_number')->get(),
            'periods' => Period::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('period_number')->get(),
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'selectedBranchId' => $branchId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Timetable::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'period_id' => ['required', 'integer', 'exists:periods,id'],
            'day_of_week' => ['required', 'string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->validateConflicts($validated);

        Timetable::create($validated);

        return redirect()->route('timetables.index')->with('success', 'Timetable slot created successfully.');
    }

    public function edit(Request $request, Timetable $timetable): View
    {
        $this->authorize('update', $timetable);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branchId = $timetable->branch_id;

        return view('timetables.form', [
            'timetable' => $timetable,
            'branches' => Branch::query()
                ->when(! $canManageAll, fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'terms' => Term::query()->orderBy('name')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('grade')->orderBy('name')->get(),
            'subjects' => Subject::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'teachers' => Teacher::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('user')->get(),
            'classrooms' => Classroom::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('room_number')->get(),
            'periods' => Period::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('period_number')->get(),
            'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'selectedBranchId' => $branchId,
        ]);
    }

    public function update(Request $request, Timetable $timetable): RedirectResponse
    {
        $this->authorize('update', $timetable);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'period_id' => ['required', 'integer', 'exists:periods,id'],
            'day_of_week' => ['required', 'string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->validateConflicts($validated, $timetable->id);

        $timetable->update($validated);

        return redirect()->route('timetables.index')->with('success', 'Timetable slot updated successfully.');
    }

    public function destroy(Timetable $timetable): RedirectResponse
    {
        $this->authorize('delete', $timetable);

        $timetable->delete();

        return redirect()->route('timetables.index')->with('success', 'Timetable slot deleted successfully.');
    }

    private function validateConflicts(array $data, ?int $ignoreId = null): void
    {
        // 1. Section conflict
        $sectionConflict = Timetable::query()
            ->where('section_id', $data['section_id'])
            ->where('period_id', $data['period_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->when($ignoreId, fn (Builder $b) => $b->where('id', '!=', $ignoreId))
            ->exists();

        if ($sectionConflict) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section already has a class scheduled for this period and day.',
            ]);
        }

        // 2. Teacher conflict
        $teacherConflict = Timetable::query()
            ->where('teacher_id', $data['teacher_id'])
            ->where('period_id', $data['period_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->when($ignoreId, fn (Builder $b) => $b->where('id', '!=', $ignoreId))
            ->exists();

        if ($teacherConflict) {
            throw ValidationException::withMessages([
                'teacher_id' => 'This teacher is already scheduled to teach another class in this period and day.',
            ]);
        }

        // 3. Classroom conflict
        if (! empty($data['classroom_id'])) {
            $classroomConflict = Timetable::query()
                ->where('classroom_id', $data['classroom_id'])
                ->where('period_id', $data['period_id'])
                ->where('day_of_week', $data['day_of_week'])
                ->when($ignoreId, fn (Builder $b) => $b->where('id', '!=', $ignoreId))
                ->exists();

            if ($classroomConflict) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'The selected classroom is already occupied for this period and day.',
                ]);
            }
        }
    }
}
