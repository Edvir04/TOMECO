<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Superadmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'superadmins';

    protected $fillable = [
        'fullname', 'username', 'id_number', 'password', 'gender', 'dob',
        'contact_number', 'address', 'profile_picture',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'dob' => 'date',
    ];
}
