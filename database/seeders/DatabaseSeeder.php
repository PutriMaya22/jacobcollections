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
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Create or update default users
        User::updateOrCreate(
            ['email' => 'admin@jacob.com'],
            [
                'name' => 'Admin Jacob',
                'password' => Hash::make('admin01'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@jacob.com'],
            [
                'name' => 'Owner Jacob',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
