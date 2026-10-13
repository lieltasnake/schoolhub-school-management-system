<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StaffAttendance::class);

        $user = $request->user();
        $query = StaffAttendance::query()->with(['user', 'branch', 'recordedBy']);

        if (! $user->hasAnyRole(['super-administrator', 'school-administrator'])) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('attendance_date')) {
            $query->whereDate('attendance_date', $request->date('attendance_date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('attendance.staff.index', [
            'attendances' => $query->orderByDesc('attendance_date')->paginate(20)->withQueryString(),
            'branches' => Branch::query()
                ->when(! $user->hasAnyRole(['super-administrator', 'school-administrator']), fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StaffAttendance::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branchId = $canManageAll ? ($request->integer('branch_id') ?: $user->branch_id ?: Branch::first()?->id) : $user->branch_id;
        $attendanceDate = $request->input('attendance_date', now()->toDateString());

        $staffMembers = User::query()
            ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $existingRecords = StaffAttendance::query()
            ->when($branchId, fn (Builder $b) => $b->where('branch_id', $branchId))
            ->whereDate('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('user_id');

        return view('attendance.staff.take', [
            'branches' => Branch::query()
                ->when(! $canManageAll, fn (Builder $b) => $b->whereKey($user->branch_id))
                ->orderBy('name')->get(),
            'selectedBranchId' => $branchId,
            'attendanceDate' => $attendanceDate,
            'staffMembers' => $staffMembers,
            'existingRecords' => $existingRecords,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StaffAttendance::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $validated = $request->validate([
            'branch_id' => [
                'required',
                'integer',
                'exists:branches,id',
                Rule::when(! $canManageAll, Rule::in([$user->branch_id])),
            ],
            'attendance_date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'on_leave', 'half_day'])],
            'attendances.*.check_in_time' => ['nullable', 'string'],
            'attendances.*.check_out_time' => ['nullable', 'string'],
            'attendances.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $recordedBy = $user->id;

        foreach ($validated['attendances'] as $record) {
            StaffAttendance::updateOrCreate(
                [
                    'user_id' => $record['user_id'],
                    'attendance_date' => $validated['attendance_date'],
                ],
                [
                    'branch_id' => $validated['branch_id'],
                    'status' => $record['status'],
                    'check_in_time' => $record['check_in_time'] ?? null,
                    'check_out_time' => $record['check_out_time'] ?? null,
                    'remarks' => $record['remarks'] ?? null,
                    'recorded_by' => $recordedBy,
                ]
            );
        }

        return redirect()->route('staff-attendance.index', [
            'branch_id' => $validated['branch_id'],
            'attendance_date' => $validated['attendance_date'],
        ])->with('success', 'Staff attendance recorded successfully.');
    }

    public function destroy(StaffAttendance $staffAttendance): RedirectResponse
    {
        $this->authorize('delete', $staffAttendance);

        $staffAttendance->delete();

        return redirect()->route('staff-attendance.index')->with('success', 'Staff attendance record deleted.');
    }
}
