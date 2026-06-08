<?php

namespace App\Services;

use App\Models\SlaRule;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketCreated;
use App\Notifications\TicketEscalated;
use App\Notifications\TicketResolved;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketService
{
    public function __construct(
        protected AttachmentService $attachmentService,
        protected TicketStatusService $statusService,
    ) {}

    public function generateTicketNumber(): string
    {
        $prefix = 'TCK-' . now()->year;

        $lastTicket = Ticket::where('ticket_number', 'LIKE', "{$prefix}-%")
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastTicket ? ((int) substr($lastTicket->ticket_number, -6)) + 1 : 1;

        return sprintf('%s-%06d', $prefix, $number);
    }

    public function calculateSlaDueDate(int $priorityId, ?Carbon $from = null): ?Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            return null;
        }

        return ($from ?? Carbon::now())->copy()->addHours($slaRule->resolution_time_hours);
    }

    public function calculateResponseDueDate(int $priorityId, ?Carbon $from = null): ?Carbon
    {
        $slaRule = SlaRule::where('priority_id', $priorityId)->first();

        if (! $slaRule) {
            return null;
        }

        return ($from ?? Carbon::now())->copy()->addHours($slaRule->response_time_hours);
    }

    public function createTicket(array $data, int $createdBy, ?array $files, int $uploadedBy): Ticket
    {
        return DB::transaction(function () use ($data, $createdBy, $files, $uploadedBy) {
            $ticket = Ticket::create([
                'ticket_number'   => $this->generateTicketNumber(),
                'title'           => $data['title'],
                'description'     => $data['description'],
                'category_id'     => $data['category_id'],
                'priority_id'     => $data['priority_id'],
                'created_by'      => $createdBy,
                'status'          => 'Open',
                'due_at'          => $this->calculateSlaDueDate($data['priority_id']),
                'response_due_at' => $this->calculateResponseDueDate($data['priority_id']),
            ]);

            if (! empty($data['label_ids'])) {
                $ticket->labels()->sync($data['label_ids']);
            }

            if ($files) {
                $this->attachmentService->storeMany($files, $ticket, $uploadedBy, $ticket, 'ticket');
            }

            ActivityLogger::log($ticket, 'ticket_created');

            return $ticket;
        });
    }

    public function assignTicket(Ticket $ticket, User $agent): void
    {
        DB::transaction(function () use ($ticket, $agent) {
            $oldAgentName = $ticket->assignedAgent?->name;

            $ticket->update([
                'assigned_agent_id' => $agent->id,
                'status'            => $this->statusService->statusAfterAssign($ticket->status),
            ]);

            ActivityLogger::log($ticket, 'ticket_assigned', $oldAgentName, $agent->name);
        });
    }

    public function updateStatus(Ticket $ticket, string $newStatus): void
    {
        DB::transaction(function () use ($ticket, $newStatus) {
            $oldStatus = $ticket->status;

            $updates = array_merge(
                ['status' => $newStatus],
                $this->statusService->timestampUpdates($newStatus)
            );

            if ($newStatus === 'Reopened') {
                $updates['due_at']          = $this->calculateSlaDueDate($ticket->priority_id);
                $updates['response_due_at'] = $this->calculateResponseDueDate($ticket->priority_id);
            }

            $ticket->update($updates);

            ActivityLogger::log($ticket, 'status_changed', $oldStatus, $newStatus);
        });
    }

    public function notifyCreated(Ticket $ticket): void
    {
        $admins = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->get();
        Notification::send($admins, new TicketCreated($ticket));
    }

    public function notifyAssigned(Ticket $ticket, ?User $agent): void
    {
        $agent?->notify(new TicketAssigned($ticket->fresh()));
    }

    public function notifyStatusChanged(Ticket $ticket, string $newStatus): void
    {
        if ($newStatus === 'Resolved') {
            $ticket->creator->notify(new TicketResolved($ticket));
        } elseif ($newStatus === 'Escalated') {
            $supervisorsAndAdmins = User::whereHas('role', fn($q) => $q->whereIn('slug', ['supervisor', 'admin']))->get();
            Notification::send($supervisorsAndAdmins, new TicketEscalated($ticket));
        }
    }
}
