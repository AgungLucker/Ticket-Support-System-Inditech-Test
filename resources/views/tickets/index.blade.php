<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Tiket') }}
            </h2>
            @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                    Buat Tiket Baru
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('tickets.index') }}">
                        {{-- Primary filters --}}
                        <div class="flex flex-col md:flex-row gap-3">
                            <div class="flex-1">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor, judul, deskripsi, nama/email customer..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            </div>
                            <div class="w-full md:w-52">
                                <select name="status" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua Status</option>
                                    @foreach(\App\Services\TicketStatusService::allStatuses() as $s)
                                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-40">
                                <select name="priority_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    <option value="">Semua Prioritas</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold py-2 px-4 rounded-md transition">Filter</button>
                                <a href="{{ route('tickets.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2 px-4 rounded-md transition">Reset</a>
                            </div>
                        </div>

                        {{-- Advanced filters (collapsible) --}}
                        @php
                            $hasAdvanced = request()->hasAny(['category_id','label_id','assigned_agent_id','from_date','to_date','due_from','due_to','overdue']);
                        @endphp
                        <details class="mt-3" {{ $hasAdvanced ? 'open' : '' }}>
                            <summary class="text-sm text-indigo-600 hover:text-indigo-800 cursor-pointer select-none w-fit">
                                Filter lanjutan {{ $hasAdvanced ? '(aktif)' : '' }}
                            </summary>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                                    <select name="category_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua Kategori</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Label</label>
                                    <select name="label_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua Label</option>
                                        @foreach($labels as $label)
                                            <option value="{{ $label->id }}" {{ request('label_id') == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($agents !== null)
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Agent</label>
                                    <select name="assigned_agent_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">Semua Agent</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}" {{ request('assigned_agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="sm:col-span-2 lg:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Rentang Tanggal Dibuat</label>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="min-w-0 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <span class="text-gray-400 text-xs shrink-0">–</span>
                                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="min-w-0 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>
                                </div>
                                <div class="sm:col-span-2 lg:col-span-2">
                                    <label class="block text-xs text-gray-500 mb-1">Rentang Tenggat SLA</label>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="date" name="due_from" value="{{ request('due_from') }}" class="min-w-0 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <span class="text-gray-400 text-xs shrink-0">–</span>
                                        <input type="date" name="due_to" value="{{ request('due_to') }}" class="min-w-0 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>
                                </div>
                                <div class="sm:col-span-2 lg:col-span-4 flex items-center">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                        <input type="checkbox" name="overdue" value="1" {{ request('overdue') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                        Hanya Overdue
                                    </label>
                                </div>
                            </div>
                        </details>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-900">Tiket</a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori / Prioritas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-900">Status</a>
                                </th>
                                @if(!Auth::user()->isCustomer())
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-900">Tanggal Dibuat</a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'due_at', 'direction' => request('sort') == 'due_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-900">Tenggat SLA</a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tickets as $ticket)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ $ticket->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ticket->category->name ?? '-' }}</div>
                                        <div class="text-sm" style="color: {{ $ticket->priority->color ?? '#6b7280' }}">{{ $ticket->priority->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = [
                                                'Open'                 => 'bg-blue-100 text-blue-800',
                                                'Assigned'             => 'bg-yellow-100 text-yellow-800',
                                                'In Progress'          => 'bg-indigo-100 text-indigo-800',
                                                'Waiting for Customer' => 'bg-orange-100 text-orange-800',
                                                'Resolved'             => 'bg-green-100 text-green-800',
                                                'Closed'               => 'bg-gray-100 text-gray-800',
                                                'Reopened'             => 'bg-purple-100 text-purple-800',
                                                'Escalated'            => 'bg-red-100 text-red-800',
                                            ][$ticket->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ $ticket->status }}
                                        </span>
                                    </td>
                                    @if(!Auth::user()->isCustomer())
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $ticket->creator->name ?? '-' }}
                                    </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($ticket->due_at)
                                            <span class="{{ $ticket->due_at->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']) ? 'text-red-600 font-bold' : '' }}">
                                                {{ $ticket->due_at->format('d M Y, H:i') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->isCustomer() ? 6 : 7 }}" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada tiket yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $tickets->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
