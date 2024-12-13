<?php

namespace App\Models\Superadmin\Dashboard;

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
}
