<?php

namespace App\Models\Student\Dashboard;

use Illuminate\Database\Eloquent\Model;

class StudentTask extends Model
{
    protected $fillable = [
        'task_id',
        'student_id',
        'file',
        'status',
        'score',
        'submitted_at'
    ];

    public $timestamps = false;
}
