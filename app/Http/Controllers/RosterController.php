<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Period;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentGrade;
use App\Models\Teacher;
use App\Models\Timetable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function students(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        $query = StudentEnrollment::query()->with([
            'student.branch',
            'grade',
            'section',
            'academicYear',
        ])->where('status', 'active');

        if ($branchId) {
            $query->whereHas('student', fn (Builder $b) => $b->where('branch_id', $branchId));
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->integer('section_id'));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('student', function (Builder $b) use ($search) {
                $b->where('admission_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return view('roster.students', [
            'enrollments' => $query->orderBy('grade_id')->orderBy('section_id')->orderBy('roll_number')->paginate(20)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'grades' => Grade::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('level')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'selectedBranchId' => $branchId,
        ]);
    }

    public function teachers(Request $request): View
    {
        $this->authorize('viewAny', Teacher::class);

        $user = $request->user();
        $query = Teacher::query()->with(['user', 'branch', 'assignments.grade', 'assignments.subject', 'assignments.section']);

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

        return view('roster.teachers', [
            'teachers' => $query->orderBy('id')->paginate(15)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function classTimetable(Request $request): View
    {
        $this->authorize('viewAny', Timetable::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        $sections = Section::query()
            ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
            ->with('grade')
            ->orderBy('name')
            ->get();

        $selectedSectionId = $request->integer('section_id') ?: $sections->first()?->id;

        $periods = Period::query()
            ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
            ->orderBy('period_number')
            ->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $schedule = [];
        if ($selectedSectionId) {
            $slots = Timetable::query()
                ->where('section_id', $selectedSectionId)
                ->with(['subject', 'teacher.user', 'classroom'])
                ->get();

            foreach ($slots as $slot) {
                $schedule[$slot->day_of_week][$slot->period_id] = $slot;
            }
        }

        return view('roster.timetable', [
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'sections' => $sections,
            'selectedSectionId' => $selectedSectionId,
            'periods' => $periods,
            'days' => $days,
            'schedule' => $schedule,
        ]);
    }

    public function studentGrades(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        $query = StudentGrade::query()->with([
            'student.branch',
            'assessment.grade',
            'assessment.section',
        ]);

        if ($branchId) {
            $query->whereHas('student', fn (Builder $b) => $b->where('branch_id', $branchId));
        }

        if ($request->filled('section_id')) {
            $query->whereHas('assessment', fn (Builder $b) => $b->where('section_id', $request->integer('section_id')));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('student', function (Builder $b) use ($search) {
                $b->where('admission_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        return view('roster.grades', [
            'grades' => $query->orderByDesc('id')->paginate(20)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('grade')->orderBy('name')->get(),
        ]);
    }
}
