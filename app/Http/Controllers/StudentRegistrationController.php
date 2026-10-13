<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\RegistrationSetup;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentRegistration;
use App\Models\StudentType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentRegistration::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $query = StudentRegistration::query()
            ->with(['branch', 'academicYear', 'grade', 'studentType', 'convertedStudent']);

        if (! $canManageAll) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : null)
            : $user->branch_id;

        $grades = Grade::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
            ->get();

        $baseCountQuery = StudentRegistration::query()
            ->when(! $canManageAll, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when($canManageAll && $selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId));

        $totalCount = (clone $baseCountQuery)->count();
        $pendingCount = (clone $baseCountQuery)->where('status', 'pending')->count();
        $approvedCount = (clone $baseCountQuery)->where('status', 'approved')->count();
        $enrolledCount = (clone $baseCountQuery)->where('status', 'enrolled')->count();

        $registrations = $query->orderByDesc('registration_date')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('registrations.index', [
            'registrations' => $registrations,
            'branches' => $branches,
            'grades' => $grades,
            'academicYears' => $academicYears,
            'selectedBranchId' => $selectedBranchId,
            'totalCount' => $totalCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'enrolledCount' => $enrolledCount,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentRegistration::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);
        $branches = $this->availableBranches($user->branch_id, $canManageAll);

        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
            : $user->branch_id;

        $selectedBranch = $branches->firstWhere('id', $selectedBranchId);

        $grades = Grade::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->orderByDesc('start_date')
            ->get();

        $studentTypes = StudentType::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where(fn ($sub) => $sub->whereNull('branch_id')->orWhere('branch_id', $selectedBranchId)))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $setups = RegistrationSetup::query()
            ->when($selectedBranchId, fn (Builder $q) => $q->where('branch_id', $selectedBranchId))
            ->where('status', 'open')
            ->orderByDesc('start_date')
            ->get();

        $generatedRegistrationNumber = StudentRegistration::generateRegistrationNumber($selectedBranch);

        return view('registrations.create', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'grades' => $grades,
            'academicYears' => $academicYears,
            'studentTypes' => $studentTypes,
            'setups' => $setups,
            'generatedRegistrationNumber' => $generatedRegistrationNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StudentRegistration::class);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'registration_setup_id' => ['nullable', 'exists:registration_setups,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'student_type_id' => ['nullable', 'exists:student_types,id'],
            'registration_number' => ['nullable', 'string', 'max:50', 'unique:student_registrations,registration_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'registration_date' => ['required', 'date'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'previous_school' => ['nullable', 'string', 'max:200'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,approved,enrolled,rejected'],
            'application_fee_paid' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $branch = Branch::findOrFail($data['branch_id']);
        if (empty($data['registration_number'])) {
            $data['registration_number'] = StudentRegistration::generateRegistrationNumber($branch);
        }

        $data['application_fee_paid'] = (bool) ($data['application_fee_paid'] ?? false);

        $registration = StudentRegistration::create($data);

        return redirect()->route('registrations.show', $registration)
            ->with('success', "Registration application {$registration->registration_number} created successfully.");
    }

    public function show(StudentRegistration $registration): View
    {
        $this->authorize('view', $registration);

        $registration->load(['branch', 'academicYear', 'grade', 'studentType', 'setup', 'convertedStudent']);

        return view('registrations.show', [
            'registration' => $registration,
        ]);
    }

    public function edit(StudentRegistration $registration): View
    {
        $this->authorize('update', $registration);

        $registration->load(['branch', 'academicYear', 'grade', 'studentType', 'setup']);

        $grades = Grade::where('branch_id', $registration->branch_id)->orderBy('level')->orderBy('name')->get();
        $academicYears = AcademicYear::where('branch_id', $registration->branch_id)->orderByDesc('start_date')->get();
        $studentTypes = StudentType::where(fn ($sub) => $sub->whereNull('branch_id')->orWhere('branch_id', $registration->branch_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $setups = RegistrationSetup::where('branch_id', $registration->branch_id)->orderByDesc('start_date')->get();

        return view('registrations.edit', [
            'registration' => $registration,
            'grades' => $grades,
            'academicYears' => $academicYears,
            'studentTypes' => $studentTypes,
            'setups' => $setups,
        ]);
    }

    public function update(Request $request, StudentRegistration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $data = $request->validate([
            'registration_setup_id' => ['nullable', 'exists:registration_setups,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'student_type_id' => ['nullable', 'exists:student_types,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['required', 'date'],
            'registration_date' => ['required', 'date'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'previous_school' => ['nullable', 'string', 'max:200'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,approved,enrolled,rejected'],
            'application_fee_paid' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['application_fee_paid'] = (bool) ($data['application_fee_paid'] ?? false);

        $registration->update($data);

        return redirect()->route('registrations.show', $registration)
            ->with('success', 'Registration details updated successfully.');
    }

    public function approve(StudentRegistration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $registration->update(['status' => 'approved']);

        return back()->with('success', "Registration {$registration->registration_number} for {$registration->full_name} has been approved.");
    }

    public function reject(StudentRegistration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $registration->update(['status' => 'rejected']);

        return back()->with('success', "Registration {$registration->registration_number} has been rejected.");
    }

    public function destroy(StudentRegistration $registration): RedirectResponse
    {
        $this->authorize('delete', $registration);

        $registration->delete();

        return redirect()->route('registrations.index')
            ->with('success', 'Registration application removed.');
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
