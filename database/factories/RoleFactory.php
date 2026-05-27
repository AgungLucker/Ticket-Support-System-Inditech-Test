<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Admin', 'Supervisor', 'Agent', 'Customer']).' '.fake()->unique()->numberBetween(100, 999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
        ];
    }

    public function admin(): static
    {
        return $this->state(['name' => 'Admin', 'slug' => 'admin']);
    }

    public function supervisor(): static
    {
        return $this->state(['name' => 'Supervisor', 'slug' => 'supervisor']);
    }

    public function agent(): static
    {
        return $this->state(['name' => 'Agent', 'slug' => 'agent']);
    }

    public function customer(): static
    {
        return $this->state(['name' => 'Customer', 'slug' => 'customer']);
    }
}
