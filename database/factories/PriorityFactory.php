<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PriorityFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Low', 'Medium', 'High', 'Critical']).' '.fake()->unique()->numberBetween(1000, 9999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->hexColor(),
            'level' => fake()->numberBetween(1, 4),
        ];
    }

    public function low(): static
    {
        return $this->state(['name' => 'Low', 'slug' => 'low', 'level' => 1, 'color' => '#22c55e']);
    }

    public function medium(): static
    {
        return $this->state(['name' => 'Medium', 'slug' => 'medium', 'level' => 2, 'color' => '#eab308']);
    }

    public function high(): static
    {
        return $this->state(['name' => 'High', 'slug' => 'high', 'level' => 3, 'color' => '#f97316']);
    }

    public function critical(): static
    {
        return $this->state(['name' => 'Critical', 'slug' => 'critical', 'level' => 4, 'color' => '#ef4444']);
    }
}
