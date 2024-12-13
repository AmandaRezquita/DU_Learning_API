<?php

namespace App\Models\Superadmin\Dashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;
    protected $fillable = [
        'class_id',
        'class_name',
        'class_description'
    ];

    public $timestamps = false;
}
