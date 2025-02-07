<?php

namespace App\Models\Teacher\Dashboard;

use App\Models\Superadmin\Dashboard\ClassSubject;
use App\Models\Superadmin\Dashboard\StudentClass;
use Illuminate\Database\Eloquent\Model;

class AddMaterials extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'title',
        'description',
        'date',
        'file',
        'link'
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(ClassSubject::class, 'subject_id');
    }

    public $timestamps = false;
}
