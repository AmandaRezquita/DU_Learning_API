<?php

namespace App\Models\Superadmin\Dashboard;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    protected $fillable = [
        'subject_id',
        'subject_name',
        'class_id'
    ];

    public function SchoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public $timestamps = false;

}
