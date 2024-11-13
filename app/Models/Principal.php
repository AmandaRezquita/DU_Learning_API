<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Principal extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'principal_id',
        'name',
        'phone_number',
        'email',
        'username',
        'password',
        'image',
        'principal_avatar_id',
    ];
    
    public $timestamps = false;

    public function avatar()
    {
        return $this->belongsTo(PrincipalAvatar::class, 'avatar_id');
    }
}
