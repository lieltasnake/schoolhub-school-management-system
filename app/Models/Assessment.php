<?php

namespace App\Models;

use Database\Factories\AssessmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    /** @use HasFactory<AssessmentFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'academic_year_id',
        'term_id',
        'grade_id',
        'section_id',
        'assessment_type_id',
        'grade_scale_id',
        'title',
        'subject_name',
        'max_score',
        'pass_mark',
        'weight_percentage',
        'assessment_date',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'academic_year_id' => 'integer',
            'term_id' => 'integer',
            'grade_id' => 'integer',
            'section_id' => 'integer',
            'assessment_type_id' => 'integer',
            'grade_scale_id' => 'integer',
            'max_score' => 'decimal:2',
            'pass_mark' => 'decimal:2',
            'weight_percentage' => 'decimal:2',
            'assessment_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function gradeScale(): BelongsTo
    {
        return $this->belongsTo(GradeScale::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('subject_name', 'like', "%{$term}%");
        });
    }
}
