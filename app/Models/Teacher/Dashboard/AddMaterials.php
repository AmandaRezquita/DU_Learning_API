<?php

namespace App\Models\Teacher\Dashboard;

use Illuminate\Database\Eloquent\Model;

class AddMaterials extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'title',
        'description',
        'file',
        'link'
    ];

    public $timestamps = false;
}
