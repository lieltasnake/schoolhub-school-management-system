<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeacherAssignment::class);

        $user = $request->user();
        $query = TeacherAssignment::query()
            ->with(['teacher.user', 'teacher.branch', 'grade', 'section', 'subject', 'academicYear']);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->whereHas('teacher', fn (Builder $q) => $q->where('branch_id', $user->branch_id));
        } elseif ($request->filled('branch_id')) {
            $query->whereHas('teacher', fn (Builder $q) => $q->where('branch_id', $request->integer('branch_id')));
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        return view('teacher-assignments.index', [
            'assignments' => $query->orderByDesc('id')->paginate(15)->withQueryString(),
            'teachers' => Teacher::query()->with('user')
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $q) => $q->where('branch_id', $user->branch_id))
                ->orderByDesc('id')->get(),
            'grades' => Grade::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $q) => $q->where('branch_id', $user->branch_id))
                ->orderBy('level')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TeacherAssignment::class);

        $user = $request->user();
        $canViewAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        return view('teacher-assignments.form', [
            'teachers' => Teacher::query()->with('user')
                ->when(! $canViewAll, fn (Builder $q) => $q->where('branch_id', $user->branch_id))
                ->orderByDesc('id')->get(),
            'grades' => Grade::query()
                ->when(! $canViewAll, fn (Builder $q) => $q->where('branch_id', $user->branch_id))
                ->orderBy('level')->orderBy('name')->get(),
            'sections' => Section::query()->with('grade')
                ->when(! $canViewAll, fn (Builder $q) => $q->whereHas('grade', fn (Builder $g) => $g->where('branch_id', $user->branch_id)))
                ->orderBy('name')->get(),
            'subjects' => Subject::query()
                ->when(! $canViewAll, fn (Builder $q) => $q->where('branch_id', $user->branch_id))
                ->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()
                ->when(! $canViewAll, fn (Builder $q) => $q->where('branch_id', $user->branch_id))
                ->orderByDesc('start_date')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TeacherAssignment::class);

        $validated = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'role' => ['required', 'in:class_teacher,subject_teacher,co_teacher'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Conflict check: same teacher, section, subject in same year
        if ($validated['section_id'] && $validated['subject_id'] && $validated['academic_year_id']) {
            $exists = TeacherAssignment::where([
                'teacher_id' => $validated['teacher_id'],
                'section_id' => $validated['section_id'],
                'subject_id' => $validated['subject_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ])->exists();

            if ($exists) {
                return back()->withInput()
                    ->with('error', 'This teacher is already assigned to this section and subject for the selected academic year.');
            }
        }

        TeacherAssignment::create($validated);

        return redirect()->route('teacher-assignments.index')
            ->with('success', 'Teacher assignment created successfully.');
    }

    public function destroy(TeacherAssignment $teacherAssignment): RedirectResponse
    {
        $this->authorize('delete', $teacherAssignment);
        $teacherAssignment->delete();

        return redirect()->route('teacher-assignments.index')
            ->with('success', 'Assignment removed.');
    }
}
