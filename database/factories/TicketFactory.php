<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Priority;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_number' => 'TCK-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['Open', 'Assigned', 'In Progress', 'Waiting for Customer', 'Resolved', 'Closed', 'Reopened', 'Escalated']),
            'priority_id' => Priority::factory(),
            'category_id' => Category::factory(),
            'created_by' => User::factory()->customer(),
            'assigned_agent_id' => null,
            'due_at' => now()->addHours(fake()->numberBetween(8, 120)),
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }

    public function assigned(?User $agent = null): static
    {
        return $this->state(fn () => [
            'status' => 'Assigned',
            'assigned_agent_id' => $agent?->id ?? User::factory()->agent(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => 'Resolved',
            'resolved_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state([
            'due_at' => now()->subHour(),
        ]);
    }
}
