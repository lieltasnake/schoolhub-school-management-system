<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'member_number',
        'member_type',
        'student_id',
        'user_id',
        'max_borrow_limit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'student_id' => 'integer',
            'user_id' => 'integer',
            'max_borrow_limit' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function circulations(): HasMany
    {
        return $this->hasMany(BookCirculation::class);
    }

    public function activeCirculations(): HasMany
    {
        return $this->hasMany(BookCirculation::class)->where('status', 'issued');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->member_type === 'student' && $this->student) {
            return $this->student->full_name;
        }

        return $this->user?->name ?? 'Unknown';
    }

    public function canBorrowMore(): bool
    {
        return $this->activeCirculations()->count() < $this->max_borrow_limit;
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('member_number', 'like', "%{$term}%")
                ->orWhereHas('student', fn (Builder $q) => $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%"))
                ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$term}%"));
        });
    }

    public static function generateMemberNumber(?Branch $branch = null): string
    {
        $prefix = $branch && $branch->code ? strtoupper($branch->code) : 'LIB';
        $pattern = "{$prefix}-";

        $latest = static::where('member_number', 'like', "{$pattern}%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($latest && preg_match('/(\d+)$/', $latest->member_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $pattern, $sequence);
    }
}
