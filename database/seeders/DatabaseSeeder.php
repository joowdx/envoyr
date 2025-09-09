<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Office;
use App\Models\Section;
use App\Models\Classification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create the office
        $office = Office::create([
            'id' => Str::ulid(),
            'name' => 'Provincial Information and Communication Technology Office',
            'acronym' => 'PGO - PICTO',
            'head_name' => 'John Doe',
            'designation' => 'Office Head',
        ]);

        // 2. Create one section under the office first
        $section = Section::create([
            'id' => Str::ulid(),
            'name' => 'Administrative Section',
            'office_id' => $office->id,
        ]);

        // 3. Create the admin user and assign to section
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ROOT,
            'designation' => 'System Administrator',
            'office_id' => $office->id,
            'section_id' => $section->id,
            'email_verified_at' => now(),
        ]);

        // 4. Update the section to assign the admin as the head
        $section->update([
            'user_id' => $admin->id,
            'head_name' => $admin->name,
            'designation' => $admin->designation,
        ]);

        // 5. Create one classification
        Classification::create([
            'id' => Str::ulid(),
            'name' => 'Memo',
            'description' => 'Memorandum classification for official documents',
        ]);
    }
}
