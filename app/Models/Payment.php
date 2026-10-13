<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'receipt_number',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
        'status',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'recorded_by' => 'integer',
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('receipt_number', 'like', "%{$term}%")
                ->orWhere('reference_number', 'like', "%{$term}%")
                ->orWhereHas('invoice', function (Builder $invoiceQuery) use ($term) {
                    $invoiceQuery->where('invoice_number', 'like', "%{$term}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($term) {
                            $studentQuery->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%")
                                ->orWhere('admission_number', 'like', "%{$term}%");
                        });
                });
        });
    }

    public static function generateReceiptNumber(?Branch $branch = null): string
    {
        $year = now()->year;
        $prefix = $branch && $branch->code ? strtoupper($branch->code) : 'REC';
        $pattern = "REC-{$prefix}-{$year}-";

        $latestPayment = static::where('receipt_number', 'like', "{$pattern}%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($latestPayment && preg_match('/-(\d+)$/', $latestPayment->receipt_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $pattern, $sequence);
    }
}
