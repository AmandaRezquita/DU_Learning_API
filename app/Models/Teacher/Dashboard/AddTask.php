<?php

namespace App\Models\Teacher\Dashboard;

use App\Models\Student\Dashboard\StudentTask;
use App\Models\Superadmin\Dashboard\StudentClass;
use Illuminate\Database\Eloquent\Model;

class AddTask extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'title',
        'description',
        'file',
        'date',
        'due_date',
    ];

    public function studentTasks()
    {
        return $this->hasMany(StudentTask::class, 'student_id'); 
    }
    public $timestamps = false;

}
