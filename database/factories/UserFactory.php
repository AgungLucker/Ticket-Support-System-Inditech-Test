<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'role_id' => Role::firstOrCreate(
                ['slug' => 'customer'],
                ['name' => 'Customer', 'description' => 'Creates and tracks support tickets'],
            )->id,
            'team_id' => null,
            'phone' => fake()->optional()->phoneNumber(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['slug' => 'admin'],
                ['name' => 'Admin', 'description' => 'Full system administrator'],
            )->id,
        ]);
    }

    public function supervisor(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['slug' => 'supervisor'],
                ['name' => 'Supervisor', 'description' => 'Manages agents and ticket assignments'],
            )->id,
        ]);
    }

    public function agent(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['slug' => 'agent'],
                ['name' => 'Agent', 'description' => 'Handles assigned support tickets'],
            )->id,
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(
                ['slug' => 'customer'],
                ['name' => 'Customer', 'description' => 'Creates and tracks support tickets'],
            )->id,
        ]);
    }
}
