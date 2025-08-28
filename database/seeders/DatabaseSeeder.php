<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Office;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test admin user with all required fields
        User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ROOT,
            'designation' => 'System Administrator',
            'email_verified_at' => now(),
        ]);

        Office::create([
            'id' => Str::ulid(),
            'name' => 'Provincial Information and Communication Technology Office',
            'acronym' => 'PICTO',
            'head_name' => 'John Doe',
            'designation' => 'Office Head',
        ]);

        User::create([
            'name' => 'Test User',
            'office_id' => Office::first()->id,
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::USER,
            'designation' => 'Officer',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test Liaison',
            'office_id' => Office::first()->id,
            'email' => 'liaison@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LIAISON,
            'designation' => 'Officer',
            'email_verified_at' => now(),
        ]);

        for ($i = 1; $i <= 10; $i++) {
            Office::create([
                'id' => Str::ulid(),
                'name' => fake()->company() . ' Office',
                'acronym' => strtoupper(fake()->lexify('???')),
                'head_name' => fake()->name(),
                'designation' => fake()->jobTitle(),
            ]);
        }
      
    }
}
