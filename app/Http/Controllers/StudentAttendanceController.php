<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Section;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentAttendance::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        $query = StudentAttendance::query()->with(['student', 'section.grade', 'recordedBy']);

        if ($branchId) {
            $query->whereHas('section', fn (Builder $b) => $b->where('branch_id', $branchId));
        }

        if ($request->filled('attendance_date')) {
            $query->whereDate('attendance_date', $request->date('attendance_date'));
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->integer('section_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('attendance.students.index', [
            'attendances' => $query->orderByDesc('attendance_date')->paginate(20)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('grade')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentAttendance::class);

        $user = $request->user();
        $branchId = $user->hasAnyRole(['super-administrator', 'school-administrator'])
            ? $request->integer('branch_id', $user->branch_id ?? 1)
            : $user->branch_id;

        $sectionId = $request->integer('section_id');
        $attendanceDate = $request->input('attendance_date', now()->toDateString());

        $students = collect();
        $existingRecords = collect();

        if ($sectionId) {
            $students = StudentEnrollment::query()
                ->where('section_id', $sectionId)
                ->where('status', 'active')
                ->with('student')
                ->get()
                ->pluck('student')
                ->filter();

            $existingRecords = StudentAttendance::query()
                ->where('section_id', $sectionId)
                ->whereDate('attendance_date', $attendanceDate)
                ->get()
                ->keyBy('student_id');
        }

        return view('attendance.students.take', [
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'sections' => Section::query()
                ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
                ->with('grade')->orderBy('name')->get(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'selectedSectionId' => $sectionId,
            'attendanceDate' => $attendanceDate,
            'students' => $students,
            'existingRecords' => $existingRecords,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StudentAttendance::class);

        $validated = $request->validate([
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'attendance_date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
            'attendances.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;

        foreach ($validated['attendances'] as $record) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'section_id' => $validated['section_id'],
                    'attendance_date' => $validated['attendance_date'],
                ],
                [
                    'academic_year_id' => $validated['academic_year_id'] ?? null,
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'recorded_by' => $userId,
                ]
            );
        }

        return redirect()->route('student-attendance.index', [
            'section_id' => $validated['section_id'],
            'attendance_date' => $validated['attendance_date'],
        ])->with('success', 'Student attendance recorded successfully.');
    }

    public function destroy(StudentAttendance $studentAttendance): RedirectResponse
    {
        $this->authorize('delete', $studentAttendance);

        $studentAttendance->delete();

        return redirect()->route('student-attendance.index')->with('success', 'Attendance record deleted.');
    }
}
