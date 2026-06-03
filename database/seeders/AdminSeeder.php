<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cravecare.com'],
            [
                'first_name'   => 'Super',
                'last_name'    => 'Admin',
                'email'        => 'admin@cravecare.com',
                'password'     => Hash::make('password123'),
                'phone_number' => '09171234567',
                'role'         => 'admin',
                'prc_id'       => null,
            ]
        );
    }
}