<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'attendance_date',
        'status',
        'check_in_time',
        'check_out_time',
        'remarks',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'branch_id' => 'integer',
            'recorded_by' => 'integer',
            'attendance_date' => 'date',
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
