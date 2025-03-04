<?php

namespace App\Models\Superadmin\Dashboard;

use Illuminate\Database\Eloquent\Model;

class TimeSchedule extends Model
{
    protected $fillable = [
        'time',
    ];

    public $timestamps = false;

}
