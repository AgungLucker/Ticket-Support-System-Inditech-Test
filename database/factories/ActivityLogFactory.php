<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'assigned', 'status_changed', 'comment_added']),
            'description' => fake()->sentence(),
            'old_value' => null,
            'new_value' => null,
        ];
    }
}
