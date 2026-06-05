@php
    $statusBadge = [
        'Open'                 => 'bg-blue-100 text-blue-800',
        'Assigned'             => 'bg-yellow-100 text-yellow-800',
        'In Progress'          => 'bg-indigo-100 text-indigo-800',
        'Waiting for Customer' => 'bg-orange-100 text-orange-800',
        'Resolved'             => 'bg-green-100 text-green-800',
        'Closed'               => 'bg-gray-100 text-gray-800',
        'Reopened'             => 'bg-purple-100 text-purple-800',
        'Escalated'            => 'bg-red-100 text-red-800',
    ];
    $activeCount = $totalAssigned - ($byStatus['Resolved'] ?? 0) - ($byStatus['Closed'] ?? 0);
@endphp

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Ditugaskan ke Saya</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalAssigned }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Semua status</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Overdue</p>
        <p class="mt-2 text-3xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $overdueCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Melewati batas SLA</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Belum Selesai</p>
        <p class="mt-2 text-3xl font-bold text-indigo-600">{{ max(0, $activeCount) }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Belum Resolved / Closed</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Jumlah Tiket per Status --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Jumlah Tiket per Status</h3>
        <div class="flex text-xs font-medium text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2 mb-1">
            <span class="flex-1">Status</span>
            <span>Jumlah Tiket</span>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($statusBadge as $status => $classes)
                @php $count = $byStatus[$status] ?? 0; @endphp
                <div class="flex items-center justify-between py-2 {{ $count === 0 ? 'opacity-40' : '' }}">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
                        {{ $status }}
                    </span>
                    <span class="text-sm font-semibold text-gray-800">{{ $count }} tiket</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Aktivitas Terkini --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Aktivitas Terkini</h3>
            <p class="text-xs text-gray-400 mt-0.5">5 tiket yang paling baru ada perubahan (status, komentar, atau edit)</p>
        </div>
        @if($recentTickets->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Belum ada tiket yang ditugaskan.</p>
        @else
            <div class="space-y-3">
                @foreach($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="flex items-start gap-3 group">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs text-gray-400 font-mono">{{ $ticket->ticket_number }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $ticket->status }}
                                </span>
                                @if($ticket->due_at && $ticket->due_at->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']))
                                    <span class="text-xs font-medium text-red-600">Overdue</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-sm font-medium text-gray-800 truncate group-hover:text-indigo-600">
                                {{ $ticket->title }}
                            </p>
                            <p class="text-xs text-gray-400">Diperbarui {{ $ticket->updated_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
