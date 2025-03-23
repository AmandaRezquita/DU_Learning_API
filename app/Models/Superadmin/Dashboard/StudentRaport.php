<?php

namespace App\Models\Superadmin\Dashboard;

use Illuminate\Database\Eloquent\Model;

class StudentRaport extends Model
{
    protected $fillable = [
        'student_id',
        'smt',
        'file',
    ];

    public $timestamps = false;
    
}
