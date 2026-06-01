<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Tiket') }}
            </h2>
            @if(Auth::user()->isCustomer())
                <a href="{{ route('tickets.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                    Buat Tiket Baru
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label for="search" class="sr-only">Cari Tiket</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nomor atau judul tiket..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        </div>
                        <div class="w-full md:w-48">
                            <select name="status" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="Open" {{ request('status') == 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="Closed" {{ request('status') == 'Closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <select name="priority_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Semua Prioritas</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded transition">
                            Filter
                        </button>
                        <a href="{{ route('tickets.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition text-center">
                            Reset
                        </a>
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiket</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori / Prioritas</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenggat SLA</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tickets as $ticket)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ $ticket->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ticket->category->name ?? '-' }}</div>
                                        <div class="text-sm" style="color: {{ $ticket->priority->color ?? '#6b7280' }}">{{ $ticket->priority->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $ticket->status }}
                                        </span>
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
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
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
