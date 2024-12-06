<?php

namespace App\Models\Superadmin\Dashboard;

use App\Models\Superadmin\Dashboard\SchoolClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'day',
        'subjects',
    ];

    protected $casts = [
        'subjects' => 'array',
    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id'); 
    }

    public $timestamps = false;
}
