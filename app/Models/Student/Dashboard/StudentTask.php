<?php

namespace App\Models\Student\Dashboard;

use App\Models\Student\Auth\Student;
use App\Models\Teacher\Dashboard\AddTask;
use Illuminate\Database\Eloquent\Model;

class StudentTask extends Model
{
    protected $fillable = [
        'task_id',
        'student_id',
        'file',
        'link',
        'status',
        'score',
        'submitted_at'
    ];

    public function student()
    {
        return $this->hasMany(Student::class, 'id'); 
    }

    public function task()
    {
        return $this->belongsTo(AddTask::class, 'task_id'); 
    }
    

    public $timestamps = false;
}
