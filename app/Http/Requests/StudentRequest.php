<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $branchId = $this->input('branch_id')
            ? (int) $this->input('branch_id')
            : $this->route('student')?->branch_id;

        return $this->user()?->canManageStudents()
            && ($branchId === null || $this->user()?->canAccessBranch($branchId));
    }

    public function rules(): array
    {
        /** @var Student|null $student */
        $student = $this->route('student');
        $isCreate = $student === null;

        return [
            'branch_id' => [
                $isCreate ? 'required' : 'sometimes',
                'integer',
                'exists:branches,id',
            ],
            'admission_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'admission_number')->ignore($student?->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'admission_date' => ['required', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'religion' => ['nullable', 'string', 'max:100'],
            'national_id' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'suspended', 'transferred'])],

            // Academic placement
            'student_type_id' => ['nullable', 'integer', 'exists:student_types,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'roll_number' => ['nullable', 'string', 'max:30'],

            // Guardian details (for creation or updating primary guardian)
            'guardian_first_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:100'],
            'guardian_last_name' => [$isCreate ? 'required' : 'nullable', 'string', 'max:100'],
            'guardian_phone' => [$isCreate ? 'required' : 'nullable', 'string', 'max:50'],
            'guardian_email' => ['nullable', 'email', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $branchId = (int) ($this->input('branch_id') ?: $this->route('student')?->branch_id);
            $gradeId = (int) $this->input('grade_id');
            $sectionId = $this->input('section_id') ? (int) $this->input('section_id') : null;
            $academicYearId = $this->input('academic_year_id') ? (int) $this->input('academic_year_id') : null;

            if ($branchId && $gradeId) {
                $grade = Grade::find($gradeId);
                if ($grade && (int) $grade->branch_id !== $branchId) {
                    $v->errors()->add('grade_id', 'The selected grade does not belong to the selected campus.');
                }
            }

            if ($gradeId && $sectionId) {
                $section = Section::find($sectionId);
                if ($section && (int) $section->grade_id !== $gradeId) {
                    $v->errors()->add('section_id', 'The selected section does not belong to the selected grade.');
                }
            }

            if ($branchId && $academicYearId) {
                $year = AcademicYear::find($academicYearId);
                if ($year && (int) $year->branch_id !== $branchId) {
                    $v->errors()->add('academic_year_id', 'The selected academic year does not belong to the selected campus.');
                }
            }
        });
    }
}
