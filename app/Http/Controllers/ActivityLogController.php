<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAgent()) {
            abort(403);
        }

        $query = ActivityLog::with(['user.role', 'ticket'])->latest();

        if ($user->isCustomer()) {
            $query->whereHas('ticket', fn($q) => $q->where('created_by', $user->id))
                  ->whereNotIn('action', ['internal_note_added']);
        } elseif ($user->isSupervisor()) {
            if ($user->team_id === null) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('ticket', fn($q) =>
                    $q->whereHas('assignedAgent', fn($q2) => $q2->where('team_id', $user->team_id))
                );
            }
        }

        if ($request->filled('search')) {
            $query->whereHas('ticket', fn($q) =>
                $q->where('ticket_number', 'like', '%'.$request->search.'%')
                  ->orWhere('title', 'like', '%'.$request->search.'%')
            );
        }

        if ($request->filled('action') && !$user->isCustomer()) {
            $query->where('action', $request->action);
        }

        $logs       = $query->paginate(20)->withQueryString();
        $isCustomer = $user->isCustomer();

        return view('activity-logs.index', compact('logs', 'isCustomer'));
    }
}
