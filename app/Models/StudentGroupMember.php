<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGroupMember extends Model
{
    protected $fillable = [
        'student_group_id',
        'student_id',
        'role',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'student_group_id' => 'integer',
            'student_id' => 'integer',
            'joined_at' => 'date',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'student_group_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
