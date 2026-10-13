<?php

namespace App\Http\Requests;

use App\Models\Grade;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $grade = $this->route('grade');
        $branchId = $grade instanceof Grade
            ? $grade->branch_id
            : $this->integer('branch_id');

        return $user !== null
            && $user->canManageAcademicCalendar()
            && $branchId > 0
            && $user->canAccessBranch($branchId);
    }

    public function rules(): array
    {
        $grade = $this->route('grade');
        $branchId = $grade instanceof Grade
            ? $grade->branch_id
            : $this->integer('branch_id');

        return [
            'branch_id' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('grades', 'name')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($grade?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('grades', 'code')
                    ->where(fn (Builder $query) => $query->where('branch_id', $branchId))
                    ->ignore($grade?->id),
            ],
            'level' => ['nullable', 'integer', 'min:1', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:active,inactive'],
        ];
    }
}
