<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'fee_discount_id',
        'description',
        'type',
        'value',
        'computed_amount',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_id' => 'integer',
            'fee_discount_id' => 'integer',
            'approved_by' => 'integer',
            'value' => 'decimal:2',
            'computed_amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeDiscount(): BelongsTo
    {
        return $this->belongsTo(FeeDiscount::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
