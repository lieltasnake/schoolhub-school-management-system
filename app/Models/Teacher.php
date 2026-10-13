<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'employee_id',
        'qualification',
        'specialization',
        'phone',
        'joining_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'branch_id' => 'integer',
            'joining_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('employee_id', 'like', "%{$term}%")
                ->orWhere('specialization', 'like', "%{$term}%")
                ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
        });
    }
}
