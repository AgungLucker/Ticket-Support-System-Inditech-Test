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

    public function run(): void
    {
        // ── Roles 
        foreach ([
            ['name' => 'Admin',      'slug' => 'admin',      'description' => 'Full system administrator'],
            ['name' => 'Supervisor', 'slug' => 'supervisor',  'description' => 'Manages agents and ticket assignments'],
            ['name' => 'Agent',      'slug' => 'agent',       'description' => 'Handles assigned support tickets'],
            ['name' => 'Customer',   'slug' => 'customer',    'description' => 'Creates and tracks support tickets'],
        ] as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $adminRole      = Role::where('slug', 'admin')->value('id');
        $supervisorRole = Role::where('slug', 'supervisor')->value('id');
        $agentRole      = Role::where('slug', 'agent')->value('id');
        $customerRole   = Role::where('slug', 'customer')->value('id');

        // ── Priorities + SLA Rules 
        foreach ([
            ['name' => 'Low',      'slug' => 'low',      'color' => '#22c55e', 'level' => 1, 'response_time_hours' => 24, 'resolution_time_hours' => 120],
            ['name' => 'Medium',   'slug' => 'medium',   'color' => '#eab308', 'level' => 2, 'response_time_hours' => 8,  'resolution_time_hours' => 72],
            ['name' => 'High',     'slug' => 'high',     'color' => '#f97316', 'level' => 3, 'response_time_hours' => 4,  'resolution_time_hours' => 24],
            ['name' => 'Critical', 'slug' => 'critical', 'color' => '#ef4444', 'level' => 4, 'response_time_hours' => 1,  'resolution_time_hours' => 8],
        ] as $p) {
            $priority = Priority::updateOrCreate(['slug' => $p['slug']], [
                'name'  => $p['name'],
                'color' => $p['color'],
                'level' => $p['level'],
            ]);
            SlaRule::updateOrCreate(['priority_id' => $priority->id], [
                'response_time_hours'    => $p['response_time_hours'],
                'resolution_time_hours'  => $p['resolution_time_hours'],
            ]);
        }

        // ── Categories 
        foreach ([
            ['name' => 'Technical Support', 'slug' => 'technical-support', 'description' => 'Technical issues and troubleshooting'],
            ['name' => 'Billing',           'slug' => 'billing',           'description' => 'Payment, invoice, and subscription issues'],
            ['name' => 'Account',           'slug' => 'account',           'description' => 'Login, profile, and access issues'],
            ['name' => 'General Inquiry',   'slug' => 'general-inquiry',   'description' => 'General questions and requests'],
        ] as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }

        // ── Labels 
        foreach ([
            ['name' => 'Urgent',           'slug' => 'urgent',           'color' => '#ef4444'],
            ['name' => 'Backend',          'slug' => 'backend',          'color' => '#3b82f6'],
            ['name' => 'Frontend',         'slug' => 'frontend',         'color' => '#8b5cf6'],
            ['name' => 'Database',         'slug' => 'database',         'color' => '#14b8a6'],
            ['name' => 'Security',         'slug' => 'security',         'color' => '#f97316'],
            ['name' => 'Needs Follow Up',  'slug' => 'needs-follow-up',  'color' => '#eab308'],
            ['name' => 'Customer Waiting', 'slug' => 'customer-waiting', 'color' => '#64748b'],
        ] as $label) {
            Label::updateOrCreate(['slug' => $label['slug']], $label);
        }

        // ── Teams
        $teamA = Team::updateOrCreate(
            ['name' => 'Support Team A'],
            ['description' => 'Tier 1 — general inquiries and billing']
        );
        $teamB = Team::updateOrCreate(
            ['name' => 'Support Team B'],
            ['description' => 'Tier 2 — technical and escalated issues']
        );
        $teamC = Team::updateOrCreate(
            ['name' => 'Support Team C'],
            ['description' => 'Tier 3 — account and security issues']
        );

        // ── Admins (3 usesrs)
        $adminPwd = Hash::make('admin1234');
        foreach (range(1, 3) as $n) {
            User::updateOrCreate(
                ['email' => "admin{$n}@ticket.com"],
                ['name' => "Admin {$n}", 'password' => $adminPwd, 'role_id' => $adminRole, 'team_id' => null]
            );
        }

        // ── Supervisors (3 users) — masing-masing punya tim
        $supervisorPwd = Hash::make('supervisor1234');

        $sup1 = User::updateOrCreate(
            ['email' => 'supervisor1@ticket.com'],
            ['name' => 'Supervisor 1', 'password' => $supervisorPwd, 'role_id' => $supervisorRole, 'team_id' => $teamA->id]
        );
        $sup2 = User::updateOrCreate(
            ['email' => 'supervisor2@ticket.com'],
            ['name' => 'Supervisor 2', 'password' => $supervisorPwd, 'role_id' => $supervisorRole, 'team_id' => $teamB->id]
        );
        $sup3 = User::updateOrCreate(
            ['email' => 'supervisor3@ticket.com'],
            ['name' => 'Supervisor 3', 'password' => $supervisorPwd, 'role_id' => $supervisorRole, 'team_id' => $teamC->id]
        );

        $teamA->update(['supervisor_id' => $sup1->id]);
        $teamB->update(['supervisor_id' => $sup2->id]);
        $teamC->update(['supervisor_id' => $sup3->id]);

        // ── Agents (3 users)
        $agentPwd = Hash::make('agent1234');

        User::updateOrCreate(
            ['email' => 'agent1@ticket.com'],
            ['name' => 'Agent 1', 'password' => $agentPwd, 'role_id' => $agentRole, 'team_id' => $teamA->id]
        );
        User::updateOrCreate(
            ['email' => 'agent2@ticket.com'],
            ['name' => 'Agent 2', 'password' => $agentPwd, 'role_id' => $agentRole, 'team_id' => $teamA->id]
        );
        User::updateOrCreate(
            ['email' => 'agent3@ticket.com'],
            ['name' => 'Agent 3', 'password' => $agentPwd, 'role_id' => $agentRole, 'team_id' => $teamC->id]
        );

        // ── Customers (3) ───────────────────────────────────────────────────
        $customerPwd = Hash::make('customer1234');
        foreach (range(1, 3) as $n) {
            User::updateOrCreate(
                ['email' => "customer{$n}@test.com"],
                ['name' => "Customer {$n}", 'password' => $customerPwd, 'role_id' => $customerRole, 'team_id' => null]
            );
        }

        // ── Additional dummy users via factory ──────────────────────────────
        User::factory(3)->agent()->create(['team_id' => $teamA->id]);
        User::factory(3)->agent()->create(['team_id' => $teamB->id]);
        User::factory(3)->agent()->create(['team_id' => $teamC->id]);
        User::factory(10)->customer()->create();

        // ── Dummy Tickets ───────────────────────────────────────────────────
        $admin      = User::where('email', 'admin1@ticket.com')->first();
        $customers  = User::where('role_id', $customerRole)->get();
        $agents     = User::where('role_id', $agentRole)->get();
        $allUsers   = $customers->merge($agents);
        $priorities = Priority::all();
        $categories = Category::all();

        \App\Models\Ticket::factory(50)
            ->recycle($customers)
            ->recycle($priorities)
            ->recycle($categories)
            ->create()
            ->each(function ($ticket) use ($agents, $allUsers, $admin) {

                if (rand(1, 10) > 4) {
                    $assignedAgent = $agents->random();
                    $ticket->update([
                        'status'            => rand(1, 10) > 5 ? 'In Progress' : 'Assigned',
                        'assigned_agent_id' => $assignedAgent->id,
                    ]);
                }

                $ticket->labels()->attach(
                    \App\Models\Label::inRandomOrder()->take(rand(1, 3))->pluck('id')
                );

                \App\Models\Comment::factory(rand(1, 4))
                    ->recycle($ticket)
                    ->recycle($allUsers)
                    ->create();

                $ticket->refresh();

                ActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $ticket->created_by,
                    'action'    => 'ticket_created',
                ]);

                if ($ticket->assigned_agent_id) {
                    ActivityLog::create([
                        'ticket_id' => $ticket->id,
                        'user_id'   => $admin->id,
                        'action'    => 'ticket_assigned',
                        'old_value' => null,
                        'new_value' => $ticket->assignedAgent?->name,
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
