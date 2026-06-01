<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Label;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

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
            // Supervisor: hanya tiket dari agent dalam timnya
            $query->whereHas('assignedAgent', fn($q) => $q->where('team_id', $user->team_id));
        }
        // Admin: tidak ada filter tambahan, lihat semua tiket

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
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
                  ->whereNotIn('status', ['Resolved', 'Closed']);
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

        // Sorting
        $sortField = in_array($request->sort, ['created_at', 'due_at', 'status', 'priority_id'])
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

        // Admin perlu daftar customer untuk dipilih sebagai requester
        $customers = null;
        if (Auth::user()->isAdmin()) {
            $customers = User::whereHas('role', fn($q) => $q->where('slug', 'customer'))
                ->orderBy('name')
                ->get();
        }

        return view('tickets.create', compact('categories', 'priorities', 'customers'));
    }

    public function store(StoreTicketRequest $request)
    {
        Gate::authorize('create', Ticket::class);

        $ticketData = $request->validated();

        $createdBy = Auth::user()->isAdmin()
            ? $ticketData['created_by']
            : Auth::id();

        $ticket = Ticket::create([
            'ticket_number' => $this->ticketService->generateTicketNumber(),
            'title'         => $ticketData['title'],
            'description'   => $ticketData['description'],
            'category_id'   => $ticketData['category_id'],
            'priority_id'   => $ticketData['priority_id'],
            'created_by'    => $createdBy,
            'status'        => 'Open',
            'due_at'        => $this->ticketService->calculateSlaDueDate($ticketData['priority_id']),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) {
                    continue; // Skip file yang gagal terunggah
                }
                $path = $file->store('attachments', 'public');
                $ticket->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => basename($path),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['category', 'priority', 'creator', 'assignedAgent', 'attachments']);
        return view('tickets.show', compact('ticket'));
    }
}
