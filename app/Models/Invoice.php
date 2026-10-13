<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'student_id',
        'academic_year_id',
        'term_id',
        'invoice_number',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'balance',
        'due_date',
        'status',
        'notes',
        'issued_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'student_id' => 'integer',
            'academic_year_id' => 'integer',
            'term_id' => 'integer',
            'created_by' => 'integer',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'due_date' => 'date',
            'issued_at' => 'datetime',
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

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(InvoiceDiscount::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'overdue']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->where('due_date', '<', now()->toDateString());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('invoice_number', 'like', "%{$term}%")
                ->orWhereHas('student', function (Builder $studentQuery) use ($term) {
                    $studentQuery->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('admission_number', 'like', "%{$term}%");
                });
        });
    }

    public static function generateInvoiceNumber(?Branch $branch = null): string
    {
        $year = now()->year;
        $prefix = $branch && $branch->code ? strtoupper($branch->code) : 'INV';
        $pattern = "INV-{$prefix}-{$year}-";

        $latestInvoice = static::where('invoice_number', 'like', "{$pattern}%")
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($latestInvoice && preg_match('/-(\d+)$/', $latestInvoice->invoice_number, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%04d', $pattern, $sequence);
    }

    public function recalculateTotals(): void
    {
        $total = (float) $this->items()->sum('amount');
        $discount = (float) $this->discounts()->sum('computed_amount');
        $paid = (float) $this->paid_amount;
        $net = max(0, round($total - $discount, 2));
        $balance = max(0, round($net - $paid, 2));

        $status = $this->status;
        if ($status !== 'cancelled' && $status !== 'draft') {
            if ($balance <= 0 && $net > 0) {
                $status = 'paid';
            } elseif ($paid > 0 && $balance > 0) {
                $status = 'partially_paid';
            } elseif ($this->due_date && $this->due_date->isPast() && $balance > 0) {
                $status = 'overdue';
            } else {
                $status = 'issued';
            }
        }

        $this->update([
            'total_amount' => $total,
            'discount_amount' => $discount,
            'balance' => $balance,
            'status' => $status,
        ]);
    }
}
