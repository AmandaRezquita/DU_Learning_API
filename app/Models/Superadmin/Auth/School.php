<?php

namespace App\Models\Superadmin\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class School extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        "name",
        "email",
        "username",
        "password",
        "phone",
        "address",
        "jenjang",
        "principal_name",
        "logo",
        "role_id"
    ];

    public $timestamps = false;

    protected $hidden = [
        "password",
        'remember_token',
    ];
}
