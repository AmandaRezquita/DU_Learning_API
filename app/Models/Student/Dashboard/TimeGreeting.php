<?php

namespace App\Models\Student\Dashboard;

use Illuminate\Database\Eloquent\Model;

class TimeGreeting extends Model
{
    protected $fillable = [
        'name',
        'time',
    ];

    public $timestamps = false;
}
