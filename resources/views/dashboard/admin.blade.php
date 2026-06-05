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

    $avgHours     = $avgResolutionHours !== null ? (int) round($avgResolutionHours) : null;
    $avgFormatted = $avgResolutionHours === null
        ? '-'
        : ($avgHours === 0
            ? (int) round($avgResolutionHours * 60) . ' menit'
            : ($avgHours < 24
                ? $avgHours . ' jam'
                : floor($avgHours / 24) . ' hari' . ($avgHours % 24 > 0 ? ' ' . ($avgHours % 24) . ' jam' : '')));
@endphp

{{-- Baris 1: Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Total Tiket</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalTickets }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Semua tiket di sistem</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Overdue</p>
        <p class="mt-2 text-3xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $overdueCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Melewati batas SLA</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Belum Ditugaskan</p>
        <p class="mt-2 text-3xl font-bold {{ $unassignedCount > 0 ? 'text-yellow-600' : 'text-gray-900' }}">{{ $unassignedCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Aktif tanpa agent</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Rata-rata Waktu Resolusi</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $avgFormatted }}</p>
        <p class="mt-1 text-xs text-gray-400">Dihitung dari tiket resolved</p>
    </div>
</div>

{{-- Baris 2: Jumlah Tiket per Status + Tiket Dibuat Minggu Ini --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Jumlah Tiket per Status</h3>
        @if($statusCounts->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">Belum ada tiket.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($statusBadge as $status => $classes)
                    @php $count = $statusCounts[$status] ?? 0; @endphp
                    <div class="flex flex-col items-center justify-center p-3 rounded-lg border {{ $count > 0 ? 'border-gray-100' : 'border-gray-50 opacity-40' }}">
                        <span class="text-2xl font-bold text-gray-800">{{ $count }}</span>
                        <span class="text-xs text-gray-400 mb-1">tiket</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
                            {{ $status }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-1">Tiket Dibuat</h3>
        <p class="text-xs text-gray-400 mb-4">Perbandingan minggu ini vs minggu lalu</p>
        @php $maxW = max($thisWeekCount, $lastWeekCount, 1); @endphp
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Minggu ini</span>
                    <span class="font-semibold text-gray-800">{{ $thisWeekCount }} tiket</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ min(100, $thisWeekCount / $maxW * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Minggu lalu</span>
                    <span class="font-semibold text-gray-800">{{ $lastWeekCount }} tiket</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-gray-400 h-2 rounded-full" style="width: {{ min(100, $lastWeekCount / $maxW * 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Baris 3: Jumlah Tiket per Prioritas + Jumlah Tiket per Kategori --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5"
         x-data="listPager({{ json_encode($priorityCounts->values()) }})">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Jumlah Tiket per Prioritas</h3>
            </div>
            <div class="flex items-center gap-1" x-show="totalPages > 1">
                <button @click="prev()" :disabled="page === 0"
                    class="flex items-center justify-center w-6 h-6 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="text-xs text-gray-400" x-text="(page + 1) + ' / ' + totalPages"></span>
                <button @click="next()" :disabled="page === totalPages - 1"
                    class="flex items-center justify-center w-6 h-6 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div class="flex text-xs font-medium text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2 mb-2">
            <span class="flex-1">Prioritas</span>
            <span>Jumlah Tiket</span>
        </div>
        <template x-for="row in visible" :key="row.name">
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0" :style="'background-color: ' + (row.color ?? '#6b7280')"></span>
                    <span class="text-sm text-gray-700" x-text="row.name"></span>
                </div>
                <span class="text-sm font-semibold text-gray-800" x-text="row.count + ' tiket'"></span>
            </div>
        </template>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5"
         x-data="listPager({{ json_encode($categoryCounts->values()) }})">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Jumlah Tiket per Kategori</h3>
            </div>
            <div class="flex items-center gap-1" x-show="totalPages > 1">
                <button @click="prev()" :disabled="page === 0"
                    class="flex items-center justify-center w-6 h-6 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="text-xs text-gray-400" x-text="(page + 1) + ' / ' + totalPages"></span>
                <button @click="next()" :disabled="page === totalPages - 1"
                    class="flex items-center justify-center w-6 h-6 rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div class="flex text-xs font-medium text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2 mb-2">
            <span class="flex-1">Kategori</span>
            <span>Jumlah Tiket</span>
        </div>
        <template x-for="row in visible" :key="row.name">
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-700" x-text="row.name"></span>
                <span class="text-sm font-semibold text-gray-800" x-text="row.count + ' tiket'"></span>
            </div>
        </template>
    </div>
</div>

{{-- Baris 4: Top 5 Agent --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Top 5 Agent berdasarkan Tiket Resolved</h3>
    @if($topAgents->isEmpty())
        <p class="text-sm text-gray-400 text-center py-4">Belum ada agent.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="pb-2 pr-4">#</th>
                        <th class="pb-2 pr-4">Nama Agent</th>
                        <th class="pb-2 pr-4">Email</th>
                        <th class="pb-2 text-right">Jumlah Tiket Resolved</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($topAgents as $i => $agent)
                        <tr>
                            <td class="py-2.5 pr-4 text-gray-400 font-medium">{{ $i + 1 }}</td>
                            <td class="py-2.5 pr-4 font-medium text-gray-800">{{ $agent->name }}</td>
                            <td class="py-2.5 pr-4 text-gray-500">{{ $agent->email }}</td>
                            <td class="py-2.5 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    {{ $agent->resolved_count }} tiket
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('listPager', (items) => ({
        items,
        page: 0,
        perPage: 4,
        get totalPages() { return Math.ceil(this.items.length / this.perPage); },
        get visible() { return this.items.slice(this.page * this.perPage, (this.page + 1) * this.perPage); },
        prev() { if (this.page > 0) this.page--; },
        next() { if (this.page < this.totalPages - 1) this.page++; },
    }));
});
</script>
