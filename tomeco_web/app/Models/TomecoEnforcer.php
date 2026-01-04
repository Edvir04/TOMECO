<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TomecoEnforcer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'tomeco_enforcers';

    protected $fillable = [
        'fullname', 'username', 'id_number', 'password', 'gender', 'dob',
        'contact_number', 'address', 'profile_picture',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'dob' => 'date',
    ];
}
