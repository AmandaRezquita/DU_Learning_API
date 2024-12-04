<?php

namespace App\Models\Student\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_image_id',
        'gender_id',
        'image',
    ];
    
    public function gender()
    {
        return $this->belongsTo(StudentGender::class);
    }

    public $timestamps = false;
}
