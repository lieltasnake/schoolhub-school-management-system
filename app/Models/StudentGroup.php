<?php

namespace App\Models;

use Database\Factories\StudentGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentGroup extends Model
{
    /** @use HasFactory<StudentGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'branch_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(StudentGroupMember::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_group_members')
            ->withPivot(['id', 'role', 'joined_at'])
            ->withTimestamps();
    }
}
