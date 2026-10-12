<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $academicYear = $this->route('academic_year');
        $branchId = $academicYear instanceof AcademicYear
            ? $academicYear->branch_id
            : $this->integer('branch_id');

        return $user !== null
            && $user->canManageAcademicCalendar()
            && $branchId > 0
            && $user->canAccessBranch($branchId);
    }

    public function rules(): array
    {
        $academicYear = $this->route('academic_year');
        $branchId = $academicYear instanceof AcademicYear
            ? $academicYear->branch_id
            : $this->integer('branch_id');

        return [
            'branch_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('academic_years', 'name')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($academicYear?->id),
            ],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['sometimes', 'in:planned,completed'],
        ];
    }

    public function after(): array
    {
        return [function (ValidatorContract $validator): void {
            if ($validator->errors()->hasAny(['branch_id', 'start_date', 'end_date'])) {
                return;
            }

            $academicYear = $this->route('academic_year');
            $branchId = $academicYear instanceof AcademicYear
                ? $academicYear->branch_id
                : $this->integer('branch_id');

            $overlappingYearExists = AcademicYear::query()
                ->where('branch_id', $branchId)
                ->whereDate('start_date', '<=', $this->input('end_date'))
                ->whereDate('end_date', '>=', $this->input('start_date'))
                ->when($academicYear instanceof AcademicYear, fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereKeyNot($academicYear->id))
                ->exists();

            if ($overlappingYearExists) {
                $validator->errors()->add('start_date', 'Academic year dates cannot overlap another year at this branch.');
            }
        }];
    }
}
