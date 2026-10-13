<?php

namespace App\Models;

use Database\Factories\GradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    /** @use HasFactory<GradeFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'level',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'level' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }
}
