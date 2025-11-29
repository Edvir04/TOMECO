<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DefaultUserSeeder extends Seeder
{
    public function run()
    {
        // Create a default user
        User::create([
            'username' => 'admin',   // You can change the username
            'password' => Hash::make('password123'),   // Change the password here
        ]);
    }
}
