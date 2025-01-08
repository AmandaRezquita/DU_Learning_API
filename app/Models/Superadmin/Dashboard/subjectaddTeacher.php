<?php

namespace App\Models\Superadmin\Dashboard;

use App\Models\Teacher\Auth\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class subjectaddTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
    ];
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }


    public function Subject() 
    {
        return $this->belongsTo(ClassSubject::class, 'subject_id');
    }

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    
    public $timestamps = false;
}
