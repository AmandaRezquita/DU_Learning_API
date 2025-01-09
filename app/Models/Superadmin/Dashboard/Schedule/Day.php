<?php

namespace App\Models\Superadmin\Dashboard\Schedule;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $fillable = [
        'day_id',
        'day',
    ];

    public $timestamps = false;
}
