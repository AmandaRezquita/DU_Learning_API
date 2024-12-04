<?php

namespace App\Models\Principal\Auth;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Principal extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $guard = 'principal';
    protected $table = 'principals';
    protected $fillable = [
        'principal_id',
        'name',
        'phone_number',
        'email',
        'username',
        'password',
        'image',
        'principal_avatar_id',
        'role_id',
    ];
    
    public $timestamps = false;

    public function avatar()
    {
        return $this->belongsTo(PrincipalAvatar::class, 'avatar_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
