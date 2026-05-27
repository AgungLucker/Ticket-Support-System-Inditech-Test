<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'supervisor_id' => null,
        ];
    }

    public function withSupervisor(): static
    {
        return $this->state(fn () => [
            'supervisor_id' => User::factory()->supervisor(),
        ]);
    }
}
