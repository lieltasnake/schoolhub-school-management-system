<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'student_id',
        'promoted_at',
        'notes',
    ];

    protected $dates = ['promoted_at'];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
?>
