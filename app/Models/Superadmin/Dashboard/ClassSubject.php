<?php

namespace App\Models\Superadmin\Dashboard;

use App\Models\Teacher\Auth\Teacher;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id'
    ];

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id');
    }

    public $timestamps = false;

}
