<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TermRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $term = $this->route('term');
        $academicYear = $term instanceof Term
            ? $term->academicYear
            : AcademicYear::query()->find($this->integer('academic_year_id'));

        return $user !== null
            && $user->canManageAcademicCalendar()
            && $academicYear !== null
            && $user->canAccessBranch($academicYear->branch_id);
    }

    public function rules(): array
    {
        $term = $this->route('term');
        $academicYearId = $term instanceof Term
            ? $term->academic_year_id
            : $this->integer('academic_year_id');

        return [
            'academic_year_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:academic_years,id'],
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('terms', 'name')
                    ->where(fn (QueryBuilder $query) => $query->where('academic_year_id', $academicYearId))
                    ->ignore($term?->id),
            ],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'status' => ['sometimes', 'in:planned,completed'],
        ];
    }

    public function after(): array
    {
        return [function (ValidatorContract $validator): void {
            if ($validator->errors()->hasAny(['academic_year_id', 'start_date', 'end_date'])) {
                return;
            }

            $term = $this->route('term');
            $academicYear = $term instanceof Term
                ? $term->academicYear
                : AcademicYear::query()->find($this->integer('academic_year_id'));

            if ($academicYear === null) {
                return;
            }

            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            if ($startDate < $academicYear->start_date->toDateString()) {
                $validator->errors()->add('start_date', 'The term must start within its academic year.');
            }

            if ($endDate > $academicYear->end_date->toDateString()) {
                $validator->errors()->add('end_date', 'The term must end within its academic year.');
            }

            $overlappingTermExists = Term::query()
                ->where('academic_year_id', $academicYear->id)
                ->whereDate('start_date', '<=', $endDate)
                ->whereDate('end_date', '>=', $startDate)
                ->when($term instanceof Term, fn (EloquentBuilder $query) => $query->whereKeyNot($term->id))
                ->exists();

            if ($overlappingTermExists) {
                $validator->errors()->add('start_date', 'Term dates cannot overlap another term in this academic year.');
            }
        }];
    }
}
