<?php

namespace App\Models;

use Database\Factories\GradePromotionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradePromotion extends Model
{
    /** @use HasFactory<GradePromotionFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'from_academic_year_id',
        'to_academic_year_id',
        'from_grade_id',
        'to_grade_id',
        'processed_by',
        'total_students',
        'promoted_count',
        'repeated_count',
        'graduated_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'from_academic_year_id' => 'integer',
            'to_academic_year_id' => 'integer',
            'from_grade_id' => 'integer',
            'to_grade_id' => 'integer',
            'processed_by' => 'integer',
            'total_students' => 'integer',
            'promoted_count' => 'integer',
            'repeated_count' => 'integer',
            'graduated_count' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function fromAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    public function fromGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'from_grade_id');
    }

    public function toGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'to_grade_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
