<?php

namespace App\Models\Superadmin\Dashboard;

use App\Models\Superadmin\Dashboard\Schedule\Day;
use App\Models\Superadmin\Dashboard\SchoolClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'day_id',
        'subject_id',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'subjects' => 'array',
    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function day()
    {
        return $this->belongsTo(Day::class, 'day_id');
    }

    public function subject()
    {
        return $this->belongsTo(ClassSubject::class, 'subject_id');
    }

    public $timestamps = false;
}
