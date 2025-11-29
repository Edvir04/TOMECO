<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'e_ticket_employees';  // your database table name
    protected $primaryKey = 'id';  // your primary key field
    protected $fillable = [
        'username', 'password', 'username', 'birthdate', 'gender', 'address', 'phone', 'devicetoken', 'username_verified_at'
    ];

    protected $dates = [
        'birthdate', 'created_at', 'updated_at', 'username_verified_at',
    ];
}
