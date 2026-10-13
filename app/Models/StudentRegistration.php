<?php

namespace App\Models;

use Database\Factories\StudentRegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRegistration extends Model
{
    /** @use HasFactory<StudentRegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_setup_id',
        'branch_id',
        'academic_year_id',
        'grade_id',
        'student_type_id',
        'registration_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'registration_date',
        'email',
        'phone',
        'address',
        'previous_school',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_relationship',
        'status',
        'application_fee_paid',
        'converted_student_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_setup_id' => 'integer',
            'branch_id' => 'integer',
            'academic_year_id' => 'integer',
            'grade_id' => 'integer',
            'student_type_id' => 'integer',
            'converted_student_id' => 'integer',
            'date_of_birth' => 'date',
            'registration_date' => 'date',
            'application_fee_paid' => 'boolean',
        ];
    }

    public function setup(): BelongsTo
    {
        return $this->belongsTo(RegistrationSetup::class, 'registration_setup_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function studentType(): BelongsTo
    {
        return $this->belongsTo(StudentType::class);
    }

    public function convertedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name])));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('registration_number', 'like', "%{$term}%")
                ->orWhere('guardian_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public static function generateRegistrationNumber(?Branch $branch = null): string
    {
        $year = now()->year;
        $prefix = $branch && $branch->code ? strtoupper($branch->code) : 'REG';
        $pattern = "REG-{$prefix}-{$year}-";

        $latest = static::query()
            ->where('registration_number', 'like', "{$pattern}%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->registration_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $pattern, $sequence);
    }
}
