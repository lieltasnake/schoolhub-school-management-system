<?php

namespace App\Models;

use Database\Factories\StudentGradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGrade extends Model
{
    /** @use HasFactory<StudentGradeFactory> */
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'score',
        'percentage',
        'letter_grade',
        'grade_point',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'assessment_id' => 'integer',
            'student_id' => 'integer',
            'score' => 'decimal:2',
            'percentage' => 'decimal:2',
            'grade_point' => 'decimal:2',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
