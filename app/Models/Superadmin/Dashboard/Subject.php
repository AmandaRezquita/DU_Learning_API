<?php

namespace App\Models\Superadmin\Dashboard;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'subject_name'
    ];

    public $timestamps = false;
}
