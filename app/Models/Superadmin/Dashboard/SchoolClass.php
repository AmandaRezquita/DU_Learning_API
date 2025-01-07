<?php

namespace App\Models\Superadmin\Dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;
    protected $fillable = [
        'class_id',
        'class_name',
        'class_description'
    ];
    
    public function teachers()
    {
        return $this->hasMany(SubjectAddTeacher::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(StudentClass::class, 'class_id');
    }

    public function subjects()
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    public $timestamps = false;
}
