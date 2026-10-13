<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'fee_category_id',
        'academic_year_id',
        'grade_id',
        'term_id',
        'amount',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'fee_category_id' => 'integer',
            'academic_year_id' => 'integer',
            'grade_id' => 'integer',
            'term_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForGrade(Builder $query, int $gradeId): Builder
    {
        return $query->where('grade_id', $gradeId);
    }
}
