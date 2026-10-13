<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'description',
        'is_mandatory',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'is_mandatory' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function feeStructures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FeeStructure::class);
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
            $builder->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }
}
