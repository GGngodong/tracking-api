<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@testmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'ADMIN',
            'token' => 'admin_token'
        ]);

        User::create([
            'username' => 'user',
            'email' => 'user@testmail.com',
            'password' => Hash::make('User@123'),
            'role' => 'USER',
            'token' => 'user_token'
        ]);
    }
}
