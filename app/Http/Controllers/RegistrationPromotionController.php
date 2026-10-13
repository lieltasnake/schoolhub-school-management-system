<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentRegistration;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationPromotionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentRegistration::class);

        $user = $request->user();
        $canManageAll = $user->hasAnyRole(['super-administrator', 'school-administrator']);

        $branches = $this->availableBranches($user->branch_id, $canManageAll);
        $selectedBranchId = $canManageAll
            ? ($request->filled('branch_id') ? $request->integer('branch_id') : $branches->first()?->id)
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

        $sections = Section::query()
            ->whereIn('grade_id', $grades->pluck('id'))
            ->orderBy('name')
            ->get();

        $query = StudentRegistration::query()
            ->with(['branch', 'academicYear', 'grade', 'studentType'])
            ->where('status', 'approved');

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->integer('grade_id'));
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        $candidates = $query->orderBy('first_name')->get();

        return view('registrations.promotion', [
            'candidates' => $candidates,
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
            'grades' => $grades,
            'academicYears' => $academicYears,
            'sections' => $sections,
        ]);
    }

    public function promote(Request $request): RedirectResponse
    {
        $this->authorize('create', Student::class);

        $data = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['required', 'exists:student_registrations,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
        ]);

        $registrations = StudentRegistration::whereIn('id', $data['registration_ids'])
            ->where('status', 'approved')
            ->get();

        if ($registrations->isEmpty()) {
            return back()->with('error', 'No approved registrations selected for promotion.');
        }

        $promotedCount = DB::transaction(function () use ($registrations, $data) {
            $count = 0;
            foreach ($registrations as $reg) {
                $branch = $reg->branch;
                $admissionNumber = Student::generateAdmissionNumber($branch);

                $student = Student::create([
                    'branch_id' => $reg->branch_id,
                    'student_type_id' => $reg->student_type_id,
                    'admission_number' => $admissionNumber,
                    'first_name' => $reg->first_name,
                    'middle_name' => $reg->middle_name,
                    'last_name' => $reg->last_name,
                    'gender' => $reg->gender,
                    'date_of_birth' => $reg->date_of_birth,
                    'admission_date' => now()->toDateString(),
                    'email' => $reg->email,
                    'phone' => $reg->phone,
                    'address' => $reg->address,
                    'emergency_contact_name' => $reg->guardian_name,
                    'emergency_contact_phone' => $reg->guardian_phone,
                    'status' => 'active',
                ]);

                // Create Guardian if guardian info provided
                if (! empty($reg->guardian_name) && ! empty($reg->guardian_phone)) {
                    $guardianParts = explode(' ', trim($reg->guardian_name), 2);
                    $guardian = Guardian::create([
                        'first_name' => $guardianParts[0],
                        'last_name' => $guardianParts[1] ?? '',
                        'phone' => $reg->guardian_phone,
                        'email' => $reg->guardian_email,
                        'address' => $reg->address,
                    ]);

                    $student->guardians()->attach($guardian->id, [
                        'relationship' => $reg->guardian_relationship ?? 'guardian',
                        'is_primary' => true,
                        'is_emergency_contact' => true,
                    ]);
                }

                // Create Enrollment
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $reg->academic_year_id,
                    'grade_id' => $reg->grade_id,
                    'section_id' => $data['section_id'] ?? null,
                    'enrolled_at' => now()->toDateString(),
                    'status' => 'active',
                ]);

                // Update registration
                $reg->update([
                    'status' => 'enrolled',
                    'converted_student_id' => $student->id,
                ]);

                $count++;
            }

            return $count;
        });

        return redirect()->route('students.index')
            ->with('success', "Successfully promoted {$promotedCount} registered applicants to enrolled students.");
    }

    private function availableBranches(?int $branchId, bool $canViewAll): Collection
    {
        return Branch::query()
            ->when(! $canViewAll, fn (Builder $query) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get();
    }
}
