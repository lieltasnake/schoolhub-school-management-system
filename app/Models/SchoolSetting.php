<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    public const SINGLETON_ID = 1;

    public $incrementing = false;

    protected $fillable = [
        'school_name',
        'school_code',
        'official_email',
        'official_phone',
        'address',
        'timezone',
    ];

    public static function current(): ?self
    {
        return static::query()->find(self::SINGLETON_ID);
    }
}
