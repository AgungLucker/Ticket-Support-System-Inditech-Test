<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private function hourDiffExpr(string $from, string $to): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "AVG((julianday({$to}) - julianday({$from})) * 24)"
            : "AVG(TIMESTAMPDIFF(HOUR, {$from}, {$to}))";
    }

    public function adminData(): array
    {
        $totalTickets = Ticket::count();

        $statusCounts = Ticket::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $priorityCounts = DB::table('priorities')
            ->leftJoin('tickets', 'priorities.id', '=', 'tickets.priority_id')
            ->whereNull('priorities.deleted_at')
            ->selectRaw('priorities.name, priorities.color, COUNT(tickets.id) as count')
            ->groupBy('priorities.id', 'priorities.name', 'priorities.color')
            ->orderBy('priorities.id')
            ->get();

        $categoryCounts = DB::table('categories')
            ->leftJoin('tickets', 'categories.id', '=', 'tickets.category_id')
            ->whereNull('categories.deleted_at')
            ->selectRaw('categories.name, COUNT(tickets.id) as count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->get();

        $overdueCount = Ticket::whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['Resolved', 'Closed', 'Waiting for Customer'])
            ->count();

        $unassignedCount = Ticket::whereNull('assigned_agent_id')
            ->whereNotIn('status', ['Resolved', 'Closed'])
            ->count();

        $thisWeekCount = Ticket::where('created_at', '>=', now()->subDays(7))->count();
        $lastWeekCount = Ticket::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        $avgResolutionHours = Ticket::whereNotNull('resolved_at')
            ->selectRaw($this->hourDiffExpr('created_at', 'resolved_at') . ' as avg_hours')
            ->value('avg_hours');

        $topAgents = User::whereHas('role', fn($q) => $q->where('slug', 'agent'))
            ->withCount(['assignedTickets as resolved_count' => fn($q) => $q->where('status', 'Resolved')])
            ->orderByDesc('resolved_count')
            ->limit(5)
            ->get();

        return compact(
            'totalTickets', 'statusCounts', 'priorityCounts', 'categoryCounts',
            'overdueCount', 'unassignedCount', 'thisWeekCount', 'lastWeekCount',
            'avgResolutionHours', 'topAgents'
        );
    }

    public function supervisorData(User $user): array
    {
        $teamIds  = $user->supervisedTeams()->pluck('id');
        $agentIds = User::whereIn('team_id', $teamIds)
            ->whereHas('role', fn($q) => $q->where('slug', 'agent'))
            ->pluck('id');

        $base = Ticket::whereIn('assigned_agent_id', $agentIds);

        $totalTeamTickets = (clone $base)->count();
        $openCount        = (clone $base)->whereNotIn('status', ['Resolved', 'Closed'])->count();
        $overdueCount     = (clone $base)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['Resolved', 'Closed', 'Waiting for Customer'])
            ->count();
        $escalatedCount = (clone $base)->where('status', 'Escalated')->count();

        $agents = User::whereIn('id', $agentIds)
            ->withCount([
                'assignedTickets as open_count'     => fn($q) => $q->whereNotIn('status', ['Resolved', 'Closed']),
                'assignedTickets as resolved_count' => fn($q) => $q->where('status', 'Resolved'),
            ])
            ->get();

        $agentResolution = Ticket::whereIn('assigned_agent_id', $agentIds)
            ->whereNotNull('resolved_at')
            ->selectRaw('assigned_agent_id, ' . $this->hourDiffExpr('created_at', 'resolved_at') . ' as avg_hours')
            ->groupBy('assigned_agent_id')
            ->pluck('avg_hours', 'assigned_agent_id');

        return compact(
            'totalTeamTickets', 'openCount', 'overdueCount', 'escalatedCount',
            'agents', 'agentResolution'
        );
    }

    public function agentData(User $user): array
    {
        $base = Ticket::where('assigned_agent_id', $user->id);

        $totalAssigned = (clone $base)->count();
        $overdueCount  = (clone $base)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', ['Resolved', 'Closed', 'Waiting for Customer'])
            ->count();

        $byStatus = Ticket::where('assigned_agent_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentTickets = Ticket::where('assigned_agent_id', $user->id)
            ->with(['priority', 'category'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return compact('totalAssigned', 'overdueCount', 'byStatus', 'recentTickets');
    }

    public function customerData(User $user): array
    {
        $base = Ticket::where('created_by', $user->id);

        $totalTickets    = (clone $base)->count();
        $openCount       = (clone $base)->whereNotIn('status', ['Resolved', 'Closed'])->count();
        $unassignedCount = (clone $base)->where('status', 'Open')->count();
        $resolvedCount   = (clone $base)->whereIn('status', ['Resolved', 'Closed'])->count();

        $recentTickets = Ticket::where('created_by', $user->id)
            ->with(['priority', 'category'])
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return compact('totalTickets', 'openCount', 'unassignedCount', 'resolvedCount', 'recentTickets');
    }
}
