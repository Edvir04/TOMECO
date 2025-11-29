<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['username', 'password']; // Add the relevant fields
    protected $hidden = ['password', 'remember_token'];

    // Other relationships and methods
}
