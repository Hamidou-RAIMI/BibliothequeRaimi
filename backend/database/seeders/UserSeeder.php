<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
    User::create([
        
        'first_name' => 'Admin',
        'last_name' => 'Admin',
        'email' => 'admin@example.com',
        'phone' => '1234567890',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    User::create([
        
        'first_name' => 'Responsable RH',
        'last_name' => 'RH',
        'email' => 'rh@example.com',
        'phone' => '0987654321',
        'password' => bcrypt('password'),
        'role' => 'responsable_rh',
        'status' => 'active',
    ]);

        User::create([
        
        'first_name' => 'Responsable Demande',
        'last_name' => 'RD',
        'email' => 'rd@example.com',
        'phone' => '0987654321',
        'password' => bcrypt('password'),
        'role' => 'responsable_rh',
        'status' => 'active',
    ]);

            User::create([
        
        'first_name' => 'User',
        'last_name' => 'utilisateur',
        'email' => 'user@example.com',
        'phone' => '0987654321',
        'password' => bcrypt('password'),
        'role' => 'user',
        'status' => 'active',
    ]);
    }
}
