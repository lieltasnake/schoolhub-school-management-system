<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookCirculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'library_member_id',
        'issued_by',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'fine_amount',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'library_member_id' => 'integer',
            'issued_by' => 'integer',
            'issue_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_amount' => 'decimal:2',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function libraryMember(): BelongsTo
    {
        return $this->belongsTo(LibraryMember::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'issued' && $this->due_date->isPast();
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'issued')->where('due_date', '<', now()->toDateString());
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', 'issued');
    }
}
