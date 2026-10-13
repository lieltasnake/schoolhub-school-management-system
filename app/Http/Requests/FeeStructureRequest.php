<?php

namespace App\Http\Requests;

use App\Models\FeeStructure;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $feeStructure = $this->route('fee_structure');
        $branchId = $feeStructure instanceof FeeStructure
            ? $feeStructure->branch_id
            : $this->integer('branch_id');

        return $user !== null
            && $user->canManageFinance()
            && $branchId > 0
            && $user->canAccessBranch($branchId);
    }

    public function rules(): array
    {
        $feeStructure = $this->route('fee_structure');
        $branchId = $feeStructure instanceof FeeStructure
            ? $feeStructure->branch_id
            : $this->integer('branch_id');

        $academicYearId = $this->integer('academic_year_id') ?: ($feeStructure?->academic_year_id);
        $gradeId = $this->integer('grade_id') ?: ($feeStructure?->grade_id);
        $termId = $this->filled('term_id') ? $this->integer('term_id') : null;

        return [
            'branch_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:branches,id'],
            'fee_category_id' => [
                'required',
                'integer',
                Rule::exists('fee_categories', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
                Rule::unique('fee_structures', 'fee_category_id')
                    ->where(function (Builder $query) use ($academicYearId, $gradeId, $termId) {
                        $query->where('academic_year_id', $academicYearId)
                            ->where('grade_id', $gradeId);

                        if ($termId !== null) {
                            $query->where('term_id', $termId);
                        } else {
                            $query->whereNull('term_id');
                        }
                    })
                    ->ignore($feeStructure?->id),
            ],
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
            ],
            'grade_id' => [
                'required',
                'integer',
                Rule::exists('grades', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where(fn (Builder $query) => $query->where('academic_year_id', $academicYearId)),
            ],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'fee_category_id.unique' => 'A fee structure already exists for this fee category, academic year, grade, and term combination.',
            'term_id.exists' => 'The selected term must belong to the chosen academic year.',
        ];
    }
}
