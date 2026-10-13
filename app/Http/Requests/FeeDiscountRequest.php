<?php

namespace App\Http\Requests;

use App\Models\FeeDiscount;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $feeDiscount = $this->route('fee_discount');
        $branchId = $feeDiscount instanceof FeeDiscount
            ? $feeDiscount->branch_id
            : $this->integer('branch_id');

        return $user !== null
            && $user->canManageFinance()
            && $branchId > 0
            && $user->canAccessBranch($branchId);
    }

    public function rules(): array
    {
        $feeDiscount = $this->route('fee_discount');
        $branchId = $feeDiscount instanceof FeeDiscount
            ? $feeDiscount->branch_id
            : $this->integer('branch_id');

        $maxValue = $this->input('type') === 'percentage' ? 100 : 999999999999.99;

        return [
            'branch_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('fee_discounts', 'name')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($feeDiscount?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('fee_discounts', 'code')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($feeDiscount?->id),
            ],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric', 'min:0', "max:{$maxValue}"],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'value.max' => $this->input('type') === 'percentage'
                ? 'Percentage discount cannot exceed 100%.'
                : 'Discount amount exceeds the maximum allowed limit.',
        ];
    }
}
