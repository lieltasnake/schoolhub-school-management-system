<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'student_type_id',
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'admission_date',
        'blood_group',
        'religion',
        'national_id',
        'email',
        'phone',
        'address',
        'city',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'student_type_id' => 'integer',
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function studentType(): BelongsTo
    {
        return $this->belongsTo(StudentType::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroup::class, 'student_group_members')
            ->withPivot(['id', 'role', 'joined_at'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(StudentRegistration::class, 'converted_student_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(StudentGrade::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary', 'is_emergency_contact'])
            ->withTimestamps();
    }

    public function primaryGuardian(): BelongsToMany
    {
        return $this->guardians()->wherePivot('is_primary', true);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class)->orderByDesc('enrolled_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderByDesc('id');
    }

    public function latestEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)->latestOfMany('enrolled_at');
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);

        return implode(' ', $parts);
    }

    public function getPrimaryGuardianAttribute(): ?Guardian
    {
        return $this->guardians->first(fn ($g) => (bool) $g->pivot->is_primary) ?? $this->guardians->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('middle_name', 'like', "%{$term}%")
                ->orWhere('admission_number', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public static function generateAdmissionNumber(?Branch $branch = null): string
    {
        $year = now()->year;
        $prefix = $branch && $branch->code ? strtoupper($branch->code) : 'ADM';
        $pattern = "{$prefix}-{$year}-";

        $latestStudent = static::withTrashed()
            ->where('admission_number', 'like', "{$pattern}%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($latestStudent && preg_match('/-(\d+)$/', $latestStudent->admission_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $pattern, $sequence);
    }
}
