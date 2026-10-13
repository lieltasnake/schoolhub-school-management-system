<?php

namespace App\Models;

use Database\Factories\RegistrationSetupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationSetup extends Model
{
    /** @use HasFactory<RegistrationSetupFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'academic_year_id',
        'title',
        'start_date',
        'end_date',
        'application_fee',
        'capacity',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'academic_year_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'application_fee' => 'decimal:2',
            'capacity' => 'integer',
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

    public function registrations(): HasMany
    {
        return $this->hasMany(StudentRegistration::class);
    }
}
