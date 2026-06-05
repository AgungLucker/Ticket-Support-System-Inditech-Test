{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Total Tiket Tim</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalTeamTickets }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Semua tiket agent di tim</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Aktif</p>
        <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $openCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Belum Resolved / Closed</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Overdue</p>
        <p class="mt-2 text-3xl font-bold {{ $overdueCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $overdueCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Melewati batas SLA</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100">
        <p class="text-sm font-medium text-gray-500">Tiket Eskalasi</p>
        <p class="mt-2 text-3xl font-bold {{ $escalatedCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $escalatedCount }} <span class="text-base font-normal text-gray-400">tiket</span></p>
        <p class="mt-1 text-xs text-gray-400">Status Escalated</p>
    </div>
</div>

{{-- Beban Kerja Agent --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Beban Kerja Agent</h3>
    @if($agents->isEmpty())
        <p class="text-sm text-gray-400 text-center py-6">Tidak ada agent di tim Anda.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="pb-2 pr-4">Nama Agent</th>
                        <th class="pb-2 pr-4 text-center">Jumlah Tiket Aktif</th>
                        <th class="pb-2 pr-4 text-center">Jumlah Tiket Resolved</th>
                        <th class="pb-2 text-right">Rata-rata Waktu Resolusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($agents as $agent)
                        @php
                            $avgH   = $agentResolution[$agent->id] ?? null;
                            $avgH   = $avgH !== null ? (int) round($avgH) : null;
                            $avgFmt = $avgH === null
                                ? '-'
                                : ($avgH === 0
                                    ? '< 1 jam'
                                    : ($avgH < 24
                                        ? $avgH . ' jam'
                                        : floor($avgH / 24) . ' hari' . ($avgH % 24 > 0 ? ' ' . ($avgH % 24) . ' jam' : '')));
                        @endphp
                        <tr>
                            <td class="py-3 pr-4">
                                <div class="font-medium text-gray-800">{{ $agent->name }}</div>
                                <div class="text-xs text-gray-400">{{ $agent->email }}</div>
                            </td>
                            <td class="py-3 pr-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                    {{ $agent->open_count }} tiket
                                </span>
                            </td>
                            <td class="py-3 pr-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                    {{ $agent->resolved_count }} tiket
                                </span>
                            </td>
                            <td class="py-3 text-right text-gray-600">{{ $avgFmt }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
