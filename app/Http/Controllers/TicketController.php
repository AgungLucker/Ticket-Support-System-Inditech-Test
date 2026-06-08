<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketStatusRequest;
use App\Models\Category;
use App\Models\Label;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\TicketService;
use App\Services\TicketStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected TicketStatusService $statusService,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = Auth::user();
        $query = Ticket::query()->with(['category', 'priority', 'creator', 'assignedAgent']);

        // Listing sesuai role secara ketat
        if ($user->isCustomer()) {
            // Customer: hanya tiket milik sendiri
            $query->where('created_by', $user->id);
        } elseif ($user->isAgent()) {
            // Agent: hanya tiket yang ditugaskan ke dia saja
            $query->where('assigned_agent_id', $user->id);
        } elseif ($user->isSupervisor()) {
            if ($user->team_id === null) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('assignedAgent', fn($q) => $q->where('team_id', $user->team_id));
            }
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('creator', fn($q2) => $q2->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter prioritas
        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter overdue
        if ($request->filled('overdue') && $request->overdue === '1') {
            $query->whereNotNull('due_at')
                  ->where('due_at', '<', now())
                  ->whereNotIn('status', ['Resolved', 'Closed', 'Waiting for Customer']);
        }

        // Filter label
        if ($request->filled('label_id')) {
            $query->whereHas('labels', fn($q) => $q->where('labels.id', $request->label_id));
        }

        // Filter assigned agent (hanya Admin & Supervisor)
        if ($request->filled('assigned_agent_id') && ($user->isAdmin() || $user->isSupervisor())) {
            $query->where('assigned_agent_id', $request->assigned_agent_id);
        }

        // Filter rentang tanggal dibuat
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter rentang due date
        if ($request->filled('due_from')) {
            $query->whereDate('due_at', '>=', $request->due_from);
        }
        if ($request->filled('due_to')) {
            $query->whereDate('due_at', '<=', $request->due_to);
        }

        // Sorting
        $sortField = in_array($request->sort, ['created_at', 'updated_at', 'due_at', 'status', 'priority_id'])
            ? $request->sort : 'created_at';
        $sortDir = $request->direction === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $tickets    = $query->paginate(10)->withQueryString();
        $priorities = Priority::orderBy('level', 'desc')->get();
        $categories = Category::orderBy('name')->get();
        $labels     = Label::orderBy('name')->get();

        // Daftar agent untuk filter (Admin lihat semua, Supervisor hanya agent di timnya)
        $agents = null;
        if ($user->isAdmin()) {
            $agents = User::whereHas('role', fn($q) => $q->where('slug', 'agent'))->orderBy('name')->get();
        } elseif ($user->isSupervisor()) {
            $agents = User::whereHas('role', fn($q) => $q->where('slug', 'agent'))
                ->where('team_id', $user->team_id)
                ->orderBy('name')
                ->get();
        }

        return view('tickets.index', compact('tickets', 'priorities', 'categories', 'labels', 'agents'));
    }

    public function create()
    {
        Gate::authorize('create', Ticket::class);

        $categories = Category::orderBy('name')->get();
        $priorities = Priority::orderBy('level', 'desc')->get();
        $labels     = Label::orderBy('name')->get();

        // Admin perlu daftar customer untuk dipilih sebagai requester
        $customers = null;
        if (Auth::user()->isAdmin()) {
            $customers = User::whereHas('role', fn($q) => $q->where('slug', 'customer'))
                ->orderBy('name')
                ->get();
        }

        return view('tickets.create', compact('categories', 'priorities', 'labels', 'customers'));
    }

    public function store(StoreTicketRequest $request)
    {
        Gate::authorize('create', Ticket::class);

        $ticketData = $request->validated();

        $createdBy = Auth::user()->isAdmin()
            ? $ticketData['created_by']
            : Auth::id();

        $files  = $request->hasFile('attachments') ? $request->file('attachments') : null;
        $ticket = $this->ticketService->createTicket($ticketData, $createdBy, $files, Auth::id());

        $this->ticketService->notifyCreated($ticket);

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['category', 'priority', 'creator', 'assignedAgent', 'attachments',
            'comments' => fn($q) => $q->with(['user', 'attachments'])->oldest(),
        ]);

        $allowedStatuses  = $this->statusService->allowedTransitions($ticket->status);
        $assignableAgents = null;

        if (Auth::user()->isAdmin()) {
            $assignableAgents = User::whereHas('role', fn($q) => $q->where('slug', 'agent'))
                ->orderBy('name')->get();
        } elseif (Auth::user()->isSupervisor()) {
            $assignableAgents = User::whereHas('role', fn($q) => $q->where('slug', 'agent'))
                ->where('team_id', Auth::user()->team_id)
                ->orderBy('name')->get();
        }

        return view('tickets.show', compact('ticket', 'allowedStatuses', 'assignableAgents'));
    }

    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $ticket->load('labels');
        $categories = Category::orderBy('name')->get();
        $priorities = Priority::orderBy('level', 'desc')->get();
        $labels     = Label::orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'categories', 'priorities', 'labels'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        $data = $request->validated();

        $priorityChanged = $ticket->priority_id != $data['priority_id'];
        $oldPriority     = $ticket->priority->name;

        $ticket->update([
            'title'           => $data['title'],
            'description'     => $data['description'],
            'category_id'     => $data['category_id'],
            'priority_id'     => $data['priority_id'],
            'due_at'          => $this->ticketService->calculateSlaDueDate($data['priority_id'], $ticket->created_at),
            'response_due_at' => $this->ticketService->calculateResponseDueDate($data['priority_id'], $ticket->created_at),
        ]);

        $ticket->labels()->sync($data['label_ids'] ?? []);

        if ($priorityChanged) {
            ActivityLogger::log($ticket, 'priority_changed', $oldPriority, Priority::find($data['priority_id'])->name);
        } else {
            ActivityLogger::log($ticket, 'ticket_updated');
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Tiket berhasil diperbarui.');
    }

    public function updateStatus(UpdateTicketStatusRequest $request, Ticket $ticket)
    {
        $newStatus = $request->validated()['status'];

        // Customer reopen pakai policy khusus; Agent/Supervisor/Admin pakai updateStatus biasa
        if ($newStatus === 'Reopened' && Auth::user()->isCustomer()) {
            Gate::authorize('reopen', $ticket);
        } else {
            Gate::authorize('updateStatus', $ticket);
        }

        if (! $this->statusService->canUserTransition(Auth::user(), $ticket, $newStatus)) {
            return back()->withErrors(['status' => 'Transisi status tidak diizinkan.']);
        }

        $this->ticketService->updateStatus($ticket, $newStatus);
        $this->ticketService->notifyStatusChanged($ticket, $newStatus);

        return back()->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function export(Request $request)
    {
        Gate::authorize('export-ticket-reports');

        $user  = Auth::user();
        $query = Ticket::query()->with(['category', 'priority', 'creator', 'assignedAgent']);

        if ($user->isSupervisor()) {
            if ($user->team_id === null) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereHas('assignedAgent', fn($q) => $q->where('team_id', $user->team_id));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('assigned_agent_id')) {
            $query->where('assigned_agent_id', $request->assigned_agent_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $tickets  = $query->orderBy('created_at', 'desc')->lazy();
        $filename = 'tickets-' . now()->format('Ymd-His') . '.csv';

        $callback = function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'Ticket Number', 'Title', 'Status', 'Priority', 'Category',
                'Requester', 'Assigned Agent', 'Created At', 'Due At', 'Resolved At', 'Closed At',
            ]);
            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->ticket_number,
                    $ticket->title,
                    $ticket->status,
                    $ticket->priority?->name ?? '-',
                    $ticket->category?->name ?? '-',
                    $ticket->creator?->name ?? '-',
                    $ticket->assignedAgent?->name ?? '-',
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->due_at?->format('Y-m-d H:i:s') ?? '-',
                    $ticket->resolved_at?->format('Y-m-d H:i:s') ?? '-',
                    $ticket->closed_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        Gate::authorize('assign', $ticket);

        $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
        ]);

        $agent = User::find($request->agent_id);

        if (! $agent?->isAgent()) {
            return back()->withErrors(['agent_id' => 'User yang dipilih bukan Agent.']);
        }

        $this->ticketService->assignTicket($ticket, $agent);
        $this->ticketService->notifyAssigned($ticket, $agent);

        return back()->with('success', 'Tiket berhasil di-assign ke agent.');
    }
}
