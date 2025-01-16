<?php

namespace App\Models\Teacher\Dashboard;

use Illuminate\Database\Eloquent\Model;

class AddTask extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'title',
        'description',
        'file',
        'date',
        'due_date',
    ];

    public $timestamps = false;

}
