<?php

namespace App\Models\Teacher\Auth;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Teacher extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guard = 'teacher';
    protected $table = 'teachers';
    protected $fillable = [
        'teacher_id',
        'fullname',
        'nickname',
        'birth_date',
        'teacher_number',
        'phone_number',
        'email',
        'username',
        'password',
        'teacher_image_id',
        'gender_id',
        'role_id',
    ];

    public $timestamps = false;

    public function avatar()
    {
        return $this->belongsTo(TeacherAvatar::class, 'avatar_id');
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
