<?php

namespace App\Models\Student\Auth;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guard = 'student';
    protected $table = 'students';
    protected $fillable = [
        'student_id',
        'name',
        'phone_number',
        'email',
        'username',
        'password',
        'image',
        'student_avatar_id',
        'role_id',
    ];

    public $timestamps = false;

    public function avatar()
    {
        return $this->belongsTo(StudentAvatar::class, 'avatar_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

