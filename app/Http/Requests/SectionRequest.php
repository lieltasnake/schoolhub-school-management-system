<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Section;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $section = $this->route('section');
        $grade = $section instanceof Section
            ? $section->grade
            : Grade::query()->find($this->integer('grade_id'));

        return $user !== null
            && $user->canManageAcademicCalendar()
            && $grade !== null
            && $user->canAccessBranch($grade->branch_id);
    }

    public function rules(): array
    {
        $section = $this->route('section');
        $gradeId = $section instanceof Section
            ? $section->grade_id
            : $this->integer('grade_id');
        $academicYearId = $this->filled('academic_year_id')
            ? $this->integer('academic_year_id')
            : null;

        return [
            'grade_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:grades,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('sections', 'name')
                    ->where(function (Builder $query) use ($gradeId, $academicYearId) {
                        return $query->where('grade_id', $gradeId)
                            ->where('academic_year_id', $academicYearId);
                    })
                    ->ignore($section?->id),
            ],
            'code' => ['nullable', 'string', 'max:30'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function after(): array
    {
        return [function (ValidatorContract $validator): void {
            if ($validator->errors()->hasAny(['grade_id', 'academic_year_id'])) {
                return;
            }

            if (! $this->filled('academic_year_id')) {
                return;
            }

            $section = $this->route('section');
            $grade = $section instanceof Section
                ? $section->grade
                : Grade::query()->find($this->integer('grade_id'));

            $academicYear = AcademicYear::query()->find($this->integer('academic_year_id'));

            if ($grade && $academicYear && $grade->branch_id !== $academicYear->branch_id) {
                $validator->errors()->add(
                    'academic_year_id',
                    'The academic year must belong to the same campus as the selected grade.',
                );
            }
        }];
    }
}
