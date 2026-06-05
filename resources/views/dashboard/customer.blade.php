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
@endphp

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Semua Tiket Saya</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalTickets }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Total tiket yang pernah dibuat</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Sedang Diproses</p>
        <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $openCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <div class="mt-2 flex items-center gap-1.5">
            @if($unassignedCount > 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                    <span class="font-bold">{{ $unassignedCount }}</span> belum di-assign
                </span>
            @else
                <p class="text-xs text-gray-400">Semua sudah ditangani agent</p>
            @endif
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Selesai</p>
        <p class="mt-2 text-3xl font-bold text-green-600">{{ $resolvedCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Resolved atau Closed</p>
    </div>
</div>

{{-- Aktivitas Terkini --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-1">
        <h3 class="text-sm font-semibold text-gray-700">Aktivitas Terkini</h3>
        <a href="{{ route('tickets.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
            Lihat semua →
        </a>
    </div>
    <p class="text-xs text-gray-400 mb-4">5 tiket yang paling baru ada perubahan</p>
    @if($recentTickets->isEmpty())
        <div class="text-center py-10">
            <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm text-gray-400">Anda belum membuat tiket.</p>
            <a href="{{ route('tickets.create') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Buat tiket pertama →</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="pb-2 pr-4">Nomor Tiket</th>
                        <th class="pb-2 pr-4">Judul</th>
                        <th class="pb-2 pr-4">Status</th>
                        <th class="pb-2 pr-4">Prioritas</th>
                        <th class="pb-2 text-right">Terakhir Diperbarui</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentTickets as $ticket)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 pr-4">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-xs text-indigo-600 hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td class="py-3 pr-4 max-w-xs">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-gray-800 hover:text-indigo-600 truncate block">
                                    {{ $ticket->title }}
                                </a>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[$ticket->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td class="py-3 pr-4">
                                <span class="text-sm" style="color: {{ $ticket->priority->color ?? '#6b7280' }}">
                                    {{ $ticket->priority->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 text-right text-xs text-gray-400">
                                {{ $ticket->updated_at->diffForHumans() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
