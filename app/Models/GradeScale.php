<?php

namespace App\Models;

use Database\Factories\GradeScaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScale extends Model
{
    /** @use HasFactory<GradeScaleFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'description',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GradeScaleItem::class)->orderByDesc('min_percentage');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function calculateGrade(float $percentage): ?GradeScaleItem
    {
        return $this->items()
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();
    }
}
