<?php

namespace App\Models\Superadmin\Dashboard;

use App\Models\Student\Auth\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'student_id'
    ];

    public $timestamps = false;

    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
