<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username'   => 'admin',
            'email'      => 'admin@gmail.com',
            'full_name'  => 'Admin Artiv',
            'phone'      => '081234567890',
            'avatar_url' => null,
            'role'       => User::ROLE_ADMIN,
            'password'   => Hash::make('password'),
        ]);

        User::create([
            'username'   => 'designer',
            'email'      => 'designer@gmail.com',
            'full_name'  => 'Designer Artiv',
            'phone'      => '081234567891',
            'avatar_url' => null,
            'role'       => User::ROLE_DESIGNER,
            'password'   => Hash::make('password'),
        ]);

        User::create([
            'username'   => 'customer',
            'email'      => 'customer@gmail.com',
            'full_name'  => 'Customer Artiv',
            'phone'      => '081234567892',
            'avatar_url' => null,
            'role'       => User::ROLE_CUSTOMER,
            'password'   => Hash::make('password'),
        ]);
    }
}