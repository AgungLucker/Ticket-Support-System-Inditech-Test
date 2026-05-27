<?php

namespace Database\Factories;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlaRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'priority_id' => Priority::factory(),
            'response_time_hours' => fake()->randomElement([1, 4, 8, 24]),
            'resolution_time_hours' => fake()->randomElement([8, 24, 72, 120]),
        ];
    }
}
