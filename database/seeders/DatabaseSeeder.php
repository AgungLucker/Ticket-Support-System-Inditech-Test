<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Label;
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

        $labels = [
            ['name' => 'Urgent', 'slug' => 'urgent', 'color' => '#ef4444'],
            ['name' => 'Backend', 'slug' => 'backend', 'color' => '#3b82f6'],
            ['name' => 'Frontend', 'slug' => 'frontend', 'color' => '#8b5cf6'],
            ['name' => 'Database', 'slug' => 'database', 'color' => '#14b8a6'],
            ['name' => 'Security', 'slug' => 'security', 'color' => '#f97316'],
            ['name' => 'Needs Follow Up', 'slug' => 'needs-follow-up', 'color' => '#eab308'],
            ['name' => 'Customer Waiting', 'slug' => 'customer-waiting', 'color' => '#64748b'],
        ];

        foreach ($labels as $label) {
            Label::updateOrCreate(['slug' => $label['slug']], $label);
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

        // ==========================================
        // DUMMY DATA GENERATOR 
        // ==========================================
        
        // Buat tambahan 10 customer dan 5 agent agar data bervariasi
        User::factory(10)->customer()->create();
        User::factory(5)->agent()->create(['team_id' => $supportTeam->id]);

        $admin     = User::where('email', 'admin@admin.com')->first();
        $customers = User::where('role_id', Role::where('slug', 'customer')->value('id'))->get();
        $agents    = User::where('role_id', Role::where('slug', 'agent')->value('id'))->get();
        $allUsers  = $customers->merge($agents);
        
        $priorities = Priority::all();
        $categories = Category::all();

        // Buat 50 Tiket Dummy
        \App\Models\Ticket::factory(50)
            ->recycle($customers) // Memastikan created_by adalah customer
            ->recycle($priorities) // Menghindari duplikasi master priority
            ->recycle($categories) // Menghindari duplikasi master category
            ->create()
            ->each(function ($ticket) use ($agents, $allUsers, $admin) {
                
                // Secara acak, berikan (*assign*) tiket ke agent tertentu
                $assignedAgent = null;
                if (rand(1, 10) > 4) {
                    $assignedAgent = $agents->random();
                    $ticket->update([
                        'status'            => rand(1, 10) > 5 ? 'In Progress' : 'Assigned',
                        'assigned_agent_id' => $assignedAgent->id,
                    ]);
                }

                // Berikan label acak pada tiket
                $ticket->labels()->attach(
                    \App\Models\Label::inRandomOrder()->take(rand(1, 3))->pluck('id')
                );

                // Tambahkan 1-4 komentar acak untuk tiap tiket
                \App\Models\Comment::factory(rand(1, 4))
                    ->recycle($ticket)
                    ->recycle($allUsers)
                    ->create();

                // Activity logs berdasarkan state tiket
                $ticket->refresh();

                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $ticket->created_by,
                    'action'    => 'ticket_created',
                ]);

                if ($assignedAgent) {
                    ActivityLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => $admin->id,
                        'action'    => 'ticket_assigned',
                        'old_value' => null,
                        'new_value' => $assignedAgent->name,
                    ]);
                }

                if ($ticket->status !== 'Open') {
                    ActivityLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => $ticket->assigned_agent_id ?? $admin->id,
                        'action'    => 'status_changed',
                        'old_value' => 'Open',
                        'new_value' => $ticket->status,
                    ]);
                }
            });
    }
}
