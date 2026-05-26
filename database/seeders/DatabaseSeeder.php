<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Priority;
use App\Models\Role;
use App\Models\SlaRule;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system administrator'],
            ['name' => 'Supervisor', 'slug' => 'supervisor', 'description' => 'Manages agents and ticket assignments'],
            ['name' => 'Agent', 'slug' => 'agent', 'description' => 'Handles assigned support tickets'],
            ['name' => 'Customer', 'slug' => 'customer', 'description' => 'Creates and tracks support tickets'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $priorities = [
            ['name' => 'Low', 'slug' => 'low', 'color' => '#22c55e', 'level' => 1, 'response_time_hours' => 24, 'resolution_time_hours' => 120],
            ['name' => 'Medium', 'slug' => 'medium', 'color' => '#eab308', 'level' => 2, 'response_time_hours' => 8, 'resolution_time_hours' => 72],
            ['name' => 'High', 'slug' => 'high', 'color' => '#f97316', 'level' => 3, 'response_time_hours' => 4, 'resolution_time_hours' => 24],
            ['name' => 'Critical', 'slug' => 'critical', 'color' => '#ef4444', 'level' => 4, 'response_time_hours' => 1, 'resolution_time_hours' => 8],
        ];

        foreach ($priorities as $priorityData) {
            $priority = Priority::updateOrCreate(
                ['slug' => $priorityData['slug']],
                [
                    'name' => $priorityData['name'],
                    'color' => $priorityData['color'],
                    'level' => $priorityData['level'],
                ],
            );

            SlaRule::updateOrCreate(
                ['priority_id' => $priority->id],
                [
                    'response_time_hours' => $priorityData['response_time_hours'],
                    'resolution_time_hours' => $priorityData['resolution_time_hours'],
                ],
            );
        }

        $categories = [
            ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'Technical issues and troubleshooting'],
            ['name' => 'Billing', 'slug' => 'billing', 'description' => 'Payment, invoice, and subscription issues'],
            ['name' => 'Account', 'slug' => 'account', 'description' => 'Login, profile, and access issues'],
            ['name' => 'General Inquiry', 'slug' => 'general-inquiry', 'description' => 'General questions and requests'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }

        $supportTeam = Team::updateOrCreate(
            ['name' => 'Support Team'],
            ['description' => 'Default support operations team'],
        );

        $password = Hash::make('password');

        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => $password,
                'role_id' => Role::where('slug', 'admin')->value('id'),
                'team_id' => null,
            ],
        );

        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@admin.com'],
            [
                'name' => 'Supervisor',
                'password' => $password,
                'role_id' => Role::where('slug', 'supervisor')->value('id'),
                'team_id' => $supportTeam->id,
            ],
        );

        $supportTeam->update(['supervisor_id' => $supervisor->id]);

        User::updateOrCreate(
            ['email' => 'agent@admin.com'],
            [
                'name' => 'Agent',
                'password' => $password,
                'role_id' => Role::where('slug', 'agent')->value('id'),
                'team_id' => $supportTeam->id,
            ],
        );

        User::updateOrCreate(
            ['email' => 'customer@demo.com'],
            [
                'name' => 'Customer',
                'password' => $password,
                'role_id' => Role::where('slug', 'customer')->value('id'),
                'team_id' => null,
            ],
        );
    }
}
