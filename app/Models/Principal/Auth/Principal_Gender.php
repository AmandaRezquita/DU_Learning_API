<?php

namespace App\Models\Principal\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Principal_Gender extends Model
{
    use HasFactory;
    protected $fillable = [
        'gender_id',
        'name'
    ];

    public $timestamps = false;

}
