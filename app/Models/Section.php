<?php

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'academic_year_id',
        'name',
        'code',
        'capacity',
        'room_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'grade_id' => 'integer',
            'academic_year_id' => 'integer',
            'capacity' => 'integer',
        ];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
