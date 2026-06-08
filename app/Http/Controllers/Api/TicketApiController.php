<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;
use App\Services\CommentService;
use App\Services\TicketService;
use App\Services\TicketStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TicketApiController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected TicketStatusService $statusService,
        protected CommentService $commentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Ticket::class);

        $user  = Auth::user();
        $query = Ticket::query()->with(['category', 'priority', 'creator', 'assignedAgent']);

        // Role-based scope (same rules as web controller)
        if ($user->isCustomer()) {
            $query->where('created_by', $user->id);
        } elseif ($user->isAgent()) {
            $query->where('assigned_agent_id', $user->id);
        } elseif ($user->isSupervisor()) {
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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $sortField = in_array($request->sort, ['created_at', 'due_at', 'status', 'priority_id'])
            ? $request->sort : 'created_at';
        $sortDir = $request->direction === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $tickets = $query->paginate(15);

        return response()->json([
            'data' => $tickets->map(fn($t) => $this->formatTicket($t)),
            'meta' => [
                'total'        => $tickets->total(),
                'per_page'     => $tickets->perPage(),
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
            ],
        ]);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['category', 'priority', 'creator', 'assignedAgent',
            'comments' => fn($q) => $q->with('user')->oldest(),
        ]);

        $user = Auth::user();

        // Filter internal notes for Customer
        $comments = $ticket->comments->filter(function ($comment) use ($user) {
            return $user->isCustomer() ? ! $comment->is_internal_note : true;
        })->values()->map(fn($c) => [
            'id'          => $c->id,
            'body'        => $c->content,
            'is_internal' => $c->is_internal_note,
            'user'        => ['id' => $c->user->id, 'name' => $c->user->name],
            'created_at'  => $c->created_at->toIso8601String(),
        ]);

        $data              = $this->formatTicket($ticket);
        $data['comments']  = $comments;

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Ticket::class);

        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
        ];

        // Admin must specify a customer as requester
        if (Auth::user()->isAdmin()) {
            $rules['created_by'] = ['required', 'exists:users,id'];
        }

        $data = $request->validate($rules);

        $createdBy = Auth::user()->isAdmin() ? $data['created_by'] : Auth::id();

        $ticket = $this->ticketService->createTicket($data, $createdBy, null, Auth::id());

        $this->ticketService->notifyCreated($ticket);

        return response()->json([
            'data'    => $this->formatTicket($ticket->load(['category', 'priority', 'creator'])),
            'message' => 'Tiket berhasil dibuat.',
        ], 201);
    }

    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $data      = $request->validate(['status' => ['required', 'string']]);
        $newStatus = $data['status'];

        if ($newStatus === 'Reopened' && Auth::user()->isCustomer()) {
            Gate::authorize('reopen', $ticket);
        } else {
            Gate::authorize('updateStatus', $ticket);
        }

        if (! $this->statusService->canUserTransition(Auth::user(), $ticket, $newStatus)) {
            return response()->json(['message' => 'Transisi status tidak diizinkan.'], 422);
        }

        $this->ticketService->updateStatus($ticket, $newStatus);
        $this->ticketService->notifyStatusChanged($ticket, $newStatus);

        return response()->json([
            'data'    => $this->formatTicket($ticket->fresh(['category', 'priority', 'creator', 'assignedAgent'])),
            'message' => 'Status tiket berhasil diperbarui.',
        ]);
    }

    public function assign(Request $request, Ticket $ticket): JsonResponse
    {
        Gate::authorize('assign', $ticket);

        $request->validate(['agent_id' => ['required', 'exists:users,id']]);

        $agent = User::find($request->agent_id);
        if (! $agent || ! $agent->isAgent()) {
            return response()->json(['message' => 'User yang dipilih bukan Agent.'], 422);
        }

        $this->ticketService->assignTicket($ticket, $agent);
        $this->ticketService->notifyAssigned($ticket, $agent);

        return response()->json([
            'data'    => $this->formatTicket($ticket->fresh(['category', 'priority', 'creator', 'assignedAgent'])),
            'message' => 'Tiket berhasil di-assign.',
        ]);
    }

    public function addComment(Request $request, Ticket $ticket): JsonResponse
    {
        Gate::authorize('addComment', $ticket);

        $data = $request->validate([
            'body'        => ['required', 'string'],
            'is_internal' => ['boolean'],
        ]);

        // Only agents/admin/supervisor can post internal notes
        $isInternal = ($data['is_internal'] ?? false)
            && ! Auth::user()->isCustomer();

        if ($isInternal) {
            Gate::authorize('addInternalNote', $ticket);
        }

        $comment = $this->commentService->addComment(
            $ticket, Auth::id(), $data['body'], $isInternal, null
        );

        return response()->json([
            'data' => [
                'id'          => $comment->id,
                'body'        => $comment->content,
                'is_internal' => $comment->is_internal_note,
                'user'        => ['id' => Auth::id(), 'name' => Auth::user()->name],
                'created_at'  => $comment->created_at->toIso8601String(),
            ],
            'message' => 'Komentar berhasil ditambahkan.',
        ], 201);
    }

    /** Format a single ticket for JSON response */
    private function formatTicket(Ticket $ticket): array
    {
        return [
            'id'             => $ticket->id,
            'ticket_number'  => $ticket->ticket_number,
            'title'          => $ticket->title,
            'description'    => $ticket->description,
            'status'         => $ticket->status,
            'category'       => $ticket->category ? ['id' => $ticket->category->id, 'name' => $ticket->category->name] : null,
            'priority'       => $ticket->priority ? ['id' => $ticket->priority->id, 'name' => $ticket->priority->name] : null,
            'creator'        => $ticket->creator  ? ['id' => $ticket->creator->id,  'name' => $ticket->creator->name]  : null,
            'assigned_agent' => $ticket->assignedAgent ? ['id' => $ticket->assignedAgent->id, 'name' => $ticket->assignedAgent->name] : null,
            'due_at'         => $ticket->due_at?->toIso8601String(),
            'created_at'     => $ticket->created_at->toIso8601String(),
            'updated_at'     => $ticket->updated_at->toIso8601String(),
        ];
    }
}
