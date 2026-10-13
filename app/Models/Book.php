<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'library_id',
        'book_type_id',
        'title',
        'author',
        'isbn',
        'publisher',
        'edition',
        'shelf_location',
        'total_copies',
        'available_copies',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'library_id' => 'integer',
            'book_type_id' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
        ];
    }

    public function library(): BelongsTo
    {
        return $this->belongsTo(Library::class);
    }

    public function bookType(): BelongsTo
    {
        return $this->belongsTo(BookType::class);
    }

    public function circulations(): HasMany
    {
        return $this->hasMany(BookCirculation::class);
    }

    public function activeCirculations(): HasMany
    {
        return $this->hasMany(BookCirculation::class)->where('status', 'issued');
    }

    public function isAvailable(): bool
    {
        return $this->available_copies > 0 && $this->status === 'available';
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('title', 'like', "%{$term}%")
                ->orWhere('author', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%");
        });
    }
}
