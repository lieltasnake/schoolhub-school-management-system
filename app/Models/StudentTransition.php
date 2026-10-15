<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransition extends Model
{
    protected $fillable = [
        'branch_id',
        'student_id',
        'transition_type',
        'from_academic_year_id',
        'to_academic_year_id',
        'from_grade_id',
        'to_grade_id',
        'from_section_id',
        'to_section_id',
        'transition_date',
        'reason',
        'certificate_number',
        'notes',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'transition_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeWithdrawals(Builder $query): Builder
    {
        return $query->where('transition_type', 'withdrawal');
    }

    public function scopeGraduations(Builder $query): Builder
    {
        return $query->where('transition_type', 'graduation');
    }

    public function scopeRepeats(Builder $query): Builder
    {
        return $query->where('transition_type', 'repeat');
    }
}
