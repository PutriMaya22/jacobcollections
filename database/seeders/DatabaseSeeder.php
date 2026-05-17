<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or update default users dengan role
        User::updateOrCreate(
            ['email' => 'admin@jacob.com'],
            [
                'name' => 'Admin Jacob',
                'password' => Hash::make('admin01'),
                'role' => 'admin',  
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@jacob.com'],
            [
                'name' => 'Owner Jacob',
                'password' => Hash::make('password123'),
                'role' => 'owner',  
                'email_verified_at' => now(),
            ]
        );
    }
}