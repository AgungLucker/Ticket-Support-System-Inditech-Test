<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'is_internal_note' => false,
        ];
    }

    public function internal(): static
    {
        return $this->state(['is_internal_note' => true]);
    }
}
