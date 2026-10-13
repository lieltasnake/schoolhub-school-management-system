<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'shift_name',
        'opening_time',
        'closing_time',
        'operating_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'is_active' => 'boolean',
            'operating_days' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getOperatingDaysLabelAttribute(): string
    {
        $days = $this->operating_days ?? [];

        return implode(', ', $days);
    }
}
