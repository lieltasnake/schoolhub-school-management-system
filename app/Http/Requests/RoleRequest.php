<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $role = $this->route('role');

        if ($user === null) {
            return false;
        }

        return $role instanceof Role
            ? $user->can('update', $role)
            : $user->can('create', Role::class);
    }

    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : null;

        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')->ignore($roleId)],
            'slug' => [
                'required',
                'string',
                'max:191',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(array_keys(Role::DEFAULT_ROLES)),
                Rule::unique('roles', 'slug')->ignore($roleId),
            ],
            'description' => ['nullable', 'string', 'max:191'],
            'permission_keys' => ['required', 'array', 'min:1'],
            'permission_keys.*' => ['required', 'string', 'distinct', Rule::in(array_keys(Role::CUSTOM_PERMISSION_LABELS))],
        ];
    }
}
