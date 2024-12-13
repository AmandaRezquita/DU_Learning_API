<?php

namespace App\Models\Teacher\Auth;

use Illuminate\Database\Eloquent\Model;

class TeacherImage extends Model
{
    protected $fillable = [
        'teacher_image_id',
        'gender_id',
        'image'
    ];

    public function gender()
    {
        return $this->belongsTo(TeacherGender::class);
    }

    public $timestamps = false;
}
