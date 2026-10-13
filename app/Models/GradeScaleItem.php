<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScaleItem extends Model
{
    protected $fillable = [
        'grade_scale_id',
        'letter_grade',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'remarks',
        'is_pass',
    ];

    protected function casts(): array
    {
        return [
            'grade_scale_id' => 'integer',
            'min_percentage' => 'decimal:2',
            'max_percentage' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'is_pass' => 'boolean',
        ];
    }

    public function gradeScale(): BelongsTo
    {
        return $this->belongsTo(GradeScale::class);
    }
}
