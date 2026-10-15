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
            // Original profile
            'school_name'              => ['required', 'string', 'max:191'],
            'school_code'              => ['nullable', 'string', 'max:50'],
            'official_email'           => ['nullable', 'email', 'max:191'],
            'official_phone'           => ['nullable', 'string', 'max:50'],
            'address'                  => ['nullable', 'string', 'max:2000'],
            'timezone'                 => ['required', 'timezone'],
            // Extended profile
            'website'                  => ['nullable', 'url', 'max:191'],
            'motto'                    => ['nullable', 'string', 'max:255'],
            'logo'                     => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,svg', 'max:2048'],
            'facebook_url'             => ['nullable', 'url', 'max:255'],
            'twitter_url'              => ['nullable', 'url', 'max:255'],
            'instagram_url'            => ['nullable', 'url', 'max:255'],
            'youtube_url'              => ['nullable', 'url', 'max:255'],
            'school_type'              => ['nullable', 'string', 'max:50'],
            'established_year'         => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            // Notifications
            'notify_new_registration'  => ['boolean'],
            'notify_payment_received'  => ['boolean'],
            'notify_attendance_alert'  => ['boolean'],
            'notify_exam_results'      => ['boolean'],
            'notify_fee_due'           => ['boolean'],
            'notification_email'       => ['nullable', 'email', 'max:191'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Checkboxes that are unchecked are not submitted; default them to false
        $this->merge([
            'notify_new_registration' => $this->boolean('notify_new_registration'),
            'notify_payment_received' => $this->boolean('notify_payment_received'),
            'notify_attendance_alert' => $this->boolean('notify_attendance_alert'),
            'notify_exam_results'     => $this->boolean('notify_exam_results'),
            'notify_fee_due'          => $this->boolean('notify_fee_due'),
        ]);
    }
}
