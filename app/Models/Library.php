<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'location',
        'contact_email',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
