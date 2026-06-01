<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tickets.index') }}" class="text-gray-500 hover:text-gray-700">
                &larr; Kembali
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Tiket') }} #{{ $ticket->ticket_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            <!-- Left Content: Ticket Details -->
            <div class="w-full md:w-2/3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-start mb-6 pb-4 border-b">
                            <div>
                                <h1 class="text-2xl font-bold mb-2">{{ $ticket->title }}</h1>
                                <div class="flex items-center gap-3 text-sm text-gray-500">
                                    <span>Oleh: <span class="font-medium text-gray-700">{{ $ticket->creator->name }}</span></span>
                                    <span>&bull;</span>
                                    <span>{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $ticket->status }}
                            </span>
                        </div>

                        <div class="prose max-w-none mb-8">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>

                        @if($ticket->attachments->count() > 0)
                            <div class="mt-8 pt-4 border-t">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Lampiran:</h3>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($ticket->attachments as $attachment)
                                        <li class="flex items-center p-3 border rounded-md">
                                            <svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ Storage::url($attachment->path) }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 truncate block">
                                                    {{ $attachment->original_name }}
                                                </a>
                                                <p class="text-xs text-gray-500">{{ number_format($attachment->size / 1024, 2) }} KB</p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Content: Meta Sidebar -->
            <div class="w-full md:w-1/3 space-y-6">
                <!-- Status & Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium border-b pb-2 mb-4">Informasi Tiket</h3>
                        
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase">Kategori</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->category->name ?? '-' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase">Prioritas</dt>
                                <dd class="mt-1 text-sm font-semibold" style="color: {{ $ticket->priority->color ?? '#6b7280' }}">
                                    {{ $ticket->priority->name ?? '-' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase">Agen Penanggung Jawab</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($ticket->assignedAgent)
                                        {{ $ticket->assignedAgent->name }}
                                    @else
                                        <span class="text-gray-400 italic">Belum di-assign</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-gray-500 uppercase">Tenggat Waktu (SLA)</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($ticket->due_at)
                                        <span class="{{ $ticket->due_at->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']) ? 'text-red-600 font-bold' : '' }}">
                                            {{ $ticket->due_at->format('d M Y, H:i') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
