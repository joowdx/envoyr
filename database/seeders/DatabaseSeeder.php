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

        // 6. Create HR office
        $hrOffice = Office::create([
            'id' => Str::ulid(),
            'name' => 'Provincial Human Resource Management Office',
            'acronym' => 'PGO - HRMO',
            'head_name' => 'Jane Smith',
            'designation' => 'Office Head',
        ]);

        // 7. Create HR section and HR account, link as section head
        $hrSection = Section::create([
            'id' => Str::ulid(),
            'name' => 'HR Section',
            'office_id' => $hrOffice->id,
        ]);

        $hrUser = User::create([
            'name' => 'HR Admin',
            'email' => 'hr@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMINISTRATOR,
            'designation' => 'HR Administrator',
            'office_id' => $hrOffice->id,
            'section_id' => $hrSection->id,
            'email_verified_at' => now(),
        ]);

        $hrSection->update([
            'user_id' => $hrUser->id,
            'head_name' => $hrUser->name,
            'designation' => $hrUser->designation,
        ]);

        // 8. Create a liaison for PICTO and assign to its section
        User::create([
            'name' => 'PICTO Liaison',
            'email' => 'liaison@test.com',
            'password' => Hash::make('password'),
            'role' => UserRole::LIAISON,
            'designation' => 'Liaison',
            'office_id' => $office->id,
            'section_id' => $section->id,
            'email_verified_at' => now(),
        ]);
    }
}
