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
        // 1. Create the initial office first
        $office = Office::create([
            'id' => Str::ulid(),
            'name' => fake()->company() . ' Office',
            'acronym' => strtoupper(fake()->lexify('???')),
            'head_name' => fake()->name(),
            'designation' => fake()->jobTitle(),
        ]);

        // 2. Now create users and assign office_id
        User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ROOT,
            'designation' => 'System Administrator',
            'office_id' => $office->id,
            'email_verified_at' => now(),
        ]);

        Office::create([
            'id' => Str::ulid(),
            'name' => 'Provincial Information and Communication Technology Office',
            'acronym' => 'PGO - PICTO',
            'head_name' => 'John Doe',
            'designation' => 'Office Head',
        ]);

        User::create([
            'name' => 'Test User',
            'office_id' => $office->id,
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::USER,
            'designation' => 'Officer',

        ]);

        User::create([
            'name' => 'Test Liaison',
            'office_id' => $office->id,
            'email' => 'liaison@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LIAISON,
            'designation' => 'Officer',
            'email_verified_at' => now(),
        ]);
    }
}
