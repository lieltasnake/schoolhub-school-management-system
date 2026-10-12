<?php

namespace App\Http\Requests;

use App\Models\SchoolSetting;
use Illuminate\Foundation\Http\FormRequest;

class SchoolSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', new SchoolSetting) ?? false;
    }

    public function rules(): array
    {
        return [
            'school_name' => ['required', 'string', 'max:191'],
            'school_code' => ['nullable', 'string', 'max:50'],
            'official_email' => ['nullable', 'email', 'max:191'],
            'official_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'timezone' => ['required', 'timezone'],
        ];
    }
}
