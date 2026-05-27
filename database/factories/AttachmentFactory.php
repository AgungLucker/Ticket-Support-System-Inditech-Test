<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        $filename = fake()->uuid().'.pdf';

        return [
            'attachable_type' => Ticket::class,
            'attachable_id' => Ticket::factory(),
            'uploaded_by' => User::factory(),
            'original_name' => fake()->word().'.pdf',
            'stored_name' => $filename,
            'path' => 'attachments/'.$filename,
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 2048 * 1024),
        ];
    }
}
