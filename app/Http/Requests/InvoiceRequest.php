<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $invoice = $this->route('invoice');
        $branchId = $invoice instanceof Invoice
            ? $invoice->branch_id
            : $this->integer('branch_id');

        return $user !== null
            && $user->canManageFinance()
            && $branchId > 0
            && $user->canAccessBranch($branchId);
    }

    public function rules(): array
    {
        $branchId = $this->integer('branch_id');
        $isBatch = $this->boolean('is_batch');

        if ($isBatch) {
            return [
                'branch_id' => ['required', 'integer', 'exists:branches,id'],
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
                'section_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('sections', 'id')->where(fn (Builder $query) => $query->where('grade_id', $this->integer('grade_id'))),
                ],
                'term_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('terms', 'id')->where(fn (Builder $query) => $query->where('academic_year_id', $this->integer('academic_year_id'))),
                ],
                'due_date' => ['required', 'date'],
                'is_batch' => ['required', 'boolean'],
            ];
        }

        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
            ],
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
            ],
            'term_id' => [
                'nullable',
                'integer',
                Rule::exists('terms', 'id')->where(fn (Builder $query) => $query->where('academic_year_id', $this->integer('academic_year_id'))),
            ],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_category_id' => [
                'required',
                'integer',
                Rule::exists('fee_categories', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
            ],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'discounts' => ['nullable', 'array'],
            'discounts.*.fee_discount_id' => [
                'nullable',
                'integer',
                Rule::exists('fee_discounts', 'id')->where(fn (Builder $query) => $query->where('branch_id', $branchId)),
            ],
            'discounts.*.description' => ['nullable', 'string', 'max:255'],
            'discounts.*.type' => ['required_with:discounts.*.value', 'in:percentage,fixed'],
            'discounts.*.value' => ['required_with:discounts.*.type', 'numeric', 'min:0'],
        ];
    }
}
