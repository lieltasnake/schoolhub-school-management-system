<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    public const SINGLETON_ID = 1;

    public $incrementing = false;

    protected $fillable = [
        // Original profile fields
        'school_name',
        'school_code',
        'official_email',
        'official_phone',
        'address',
        'timezone',
        // Extended profile fields
        'website',
        'motto',
        'logo_path',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'school_type',
        'established_year',
        // Notification flags
        'notify_new_registration',
        'notify_payment_received',
        'notify_attendance_alert',
        'notify_exam_results',
        'notify_fee_due',
        'notification_email',
    ];

    protected function casts(): array
    {
        return [
            'notify_new_registration'  => 'boolean',
            'notify_payment_received'  => 'boolean',
            'notify_attendance_alert'  => 'boolean',
            'notify_exam_results'      => 'boolean',
            'notify_fee_due'           => 'boolean',
            'established_year'         => 'integer',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->find(self::SINGLETON_ID);
    }
}
