<?php

namespace App\Models\Principal\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Principal_Image extends Model
{
    use HasFactory;
    protected $fillable = [
        'principal_image_id',
        'gender_id',
        'image',
    ];
}
