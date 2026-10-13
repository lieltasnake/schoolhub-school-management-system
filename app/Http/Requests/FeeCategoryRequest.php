<?php

namespace App\Http\Requests;

use App\Models\FeeCategory;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $feeCategory = $this->route('fee_category');
        $branchId = $feeCategory instanceof FeeCategory
            ? $feeCategory->branch_id
            : $this->integer('branch_id');

        return $user !== null
            && $user->canManageFinance()
            && $branchId > 0
            && $user->canAccessBranch($branchId);
    }

    public function rules(): array
    {
        $feeCategory = $this->route('fee_category');
        $branchId = $feeCategory instanceof FeeCategory
            ? $feeCategory->branch_id
            : $this->integer('branch_id');

        return [
            'branch_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fee_categories', 'name')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($feeCategory?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('fee_categories', 'code')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($feeCategory?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_mandatory' => ['nullable', 'boolean'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_mandatory' => $this->boolean('is_mandatory'),
        ]);
    }
}
