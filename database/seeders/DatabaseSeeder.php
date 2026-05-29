<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the master admin account
        User::create([
            'first_name' => 'System',
            'last_name' => 'Admin',
            'email' => 'admin@cravecare.com',
            'password' => Hash::make('cravecare_admin'), // Change this to a secure password later
            'phone_number' => '0000000000',
            'role' => 'admin',
        ]);
    }
}