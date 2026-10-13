<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'academic_year_id',
        'term_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'classroom_id',
        'period_id',
        'day_of_week',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'academic_year_id' => 'integer',
            'term_id' => 'integer',
            'section_id' => 'integer',
            'subject_id' => 'integer',
            'teacher_id' => 'integer',
            'classroom_id' => 'integer',
            'period_id' => 'integer',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }
}
