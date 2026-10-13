<?php

namespace App\Models;

use Database\Factories\StudentEnrollmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    /** @use HasFactory<StudentEnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'grade_id',
        'section_id',
        'roll_number',
        'enrolled_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'academic_year_id' => 'integer',
            'grade_id' => 'integer',
            'section_id' => 'integer',
            'enrolled_at' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
