<?php

namespace App\Models\Student\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGender extends Model
{
    use HasFactory;
    protected $fillable = [
        'gender_id',
        'name'
    ];

    public function students()
    {
        return $this->hasMany(Student::class); 
    }

    public function studentImages()
    {
        return $this->hasMany(StudentImage::class); 
    }

    public $timestamps = false;
}
