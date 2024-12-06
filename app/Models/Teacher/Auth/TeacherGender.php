<?php

namespace App\Models\Teacher\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherGender extends Model
{
    use HasFactory;

    protected $fillable = [
        'gender_id',
        'name'
    ];

    
    public function teachers()
    {
        return $this->hasMany(Teacher::class); 
    }

    public function teacherImages()
    {
        return $this->hasMany(TeacherImage::class); 
    }

    public $timestamps = false;
}
