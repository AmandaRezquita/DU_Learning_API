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
        'fullname',
        'nickname',
        'birth_date',
        'principal_number',
        'phone_number',
        'email',
        'username',
        'password',
        'principal_image_id',
        'gender_id',
        'role_id',
    ];
    
    public $timestamps = false;

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
