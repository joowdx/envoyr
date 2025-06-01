<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvent;

use App\Models\Office;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::where('role', 'root')->doesntExist()) {
            User::factory()->root()->create();
        }

        Office::factory()
            ->count(5)
            ->has(Section::factory()->count(3))
            ->create();
    }
}
