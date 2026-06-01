<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $query = Ticket::query()->with(['category', 'priority', 'assignedAgent']);

        // Role-based filtering
        $user = Auth::user();
        if ($user->isCustomer()) {
            $query->where('created_by', $user->id);
        } elseif ($user->isAgent()) {
            $query->where(function($q) use ($user) {
                $q->where('assigned_agent_id', $user->id)
                  ->orWhereNull('assigned_agent_id');
            });
        }
        
        // Custom search & filters can be added here
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }
        
        $tickets = $query->latest()->paginate(10)->withQueryString();
        $priorities = Priority::all();

        return view('tickets.index', compact('tickets', 'priorities'));
    }

    public function create()
    {
        $categories = Category::all();
        $priorities = Priority::orderBy('level', 'desc')->get();
        return view('tickets.create', compact('categories', 'priorities'));
    }

    public function store(StoreTicketRequest $request)
    {
        $ticketData = $request->validated();

        $ticket = Ticket::create([
            'ticket_number' => $this->ticketService->generateTicketNumber(),
            'title' => $ticketData['title'],
            'description' => $ticketData['description'],
            'category_id' => $ticketData['category_id'],
            'priority_id' => $ticketData['priority_id'],
            'created_by' => Auth::id(),
            'status' => 'Open',
            'due_at' => $this->ticketService->calculateSlaDueDate($ticketData['priority_id']),
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
        // Check authorization
        $user = Auth::user();
        if ($user->isCustomer() && $ticket->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        $ticket->load(['category', 'priority', 'creator', 'assignedAgent', 'attachments']);
        return view('tickets.show', compact('ticket'));
    }
}
