<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::USER,
        ];
    }

    /**
     * Set root user account.
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Root',
            'email' => 'root@local.dev',
            'password' => Hash::make('$2y$12$AhM2U/ZllWkWFJA.9GI6heCOwPl/JnESmEsOlbZ57KlF0VJOIV1hK'),
            'email_verified_at' => now(),
            'approved_at' => now(),
            'role' => UserRole::ROOT,
        ]);
    }
}
