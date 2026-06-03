<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.index') }}" class="text-gray-500 hover:text-gray-700">&larr; Kembali</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Tiket #{{ $ticket->ticket_number }}
                </h2>
            </div>
            @can('update', $ticket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold py-2 px-4 rounded-md transition">
                    Edit Tiket
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

            <div class="w-full md:w-2/3 space-y-6">

                {{-- Flash message --}}
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-md px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-md px-4 py-3">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Detail tiket --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                            <span class="px-3 py-1 text-sm font-semibold rounded-full {{ $statusClass }}">
                                {{ $ticket->status }}
                            </span>
                        </div>

                        <div class="prose max-w-none mb-6">
                            {!! nl2br(e($ticket->description)) !!}
                        </div>

                        {{-- Lampiran tiket --}}
                        @if($ticket->attachments->count() > 0)
                            <div class="mt-4 pt-4 border-t">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Lampiran:</h3>
                                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($ticket->attachments as $attachment)
                                        <li class="flex items-center p-3 border rounded-md">
                                            <svg class="w-5 h-5 text-gray-400 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            <div class="flex-1 min-w-0">
                                                <a href="{{ route('attachments.show', $attachment) }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 truncate block">
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

                <!-- Komentar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Komentar ({{ $ticket->comments->where('is_internal_note', false)->count() }})
                            @if(!Auth::user()->isCustomer())
                                <span class="text-xs font-normal text-gray-400 ml-1">
                                    + {{ $ticket->comments->where('is_internal_note', true)->count() }} catatan internal
                                </span>
                            @endif
                        </h3>

                        <!-- Daftar Komentar -->
                        <div class="space-y-4 mb-6">
                            @forelse($ticket->comments as $comment)
                                {{-- Sembunyikan internal note dari Customer --}}
                                @if($comment->is_internal_note && Auth::user()->isCustomer())
                                    @continue
                                @endif

                                <div class="flex gap-3 {{ $comment->is_internal_note ? 'bg-amber-50 border border-amber-200 rounded-lg p-4' : '' }}">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                            @if($comment->is_internal_note)
                                                <span class="text-xs bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full font-medium">Catatan Internal</span>
                                            @endif
                                            <span class="text-xs text-gray-400">{{ $comment->created_at->format('d M Y, H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>

                                        @if($comment->attachments->count() > 0)
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($comment->attachments as $att)
                                                    <a href="{{ route('attachments.show', $att) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded px-2 py-1">
                                                        📎 {{ $att->original_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 italic">Belum ada komentar.</p>
                            @endforelse
                        </div>

                        <!-- Tambah Komentar -->
                        @can('addComment', $ticket)
                            <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" enctype="multipart/form-data" class="border-t pt-4">
                                @csrf
                                <div class="mb-3">
                                    <textarea name="content" rows="3" placeholder="Tulis komentar..." class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>{{ old('content') }}</textarea>
                                </div>
                                <div class="mb-3" x-data="attachmentValidator()">
                                    <input type="file" name="attachments[]" multiple
                                        class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        @change="validate($event)">
                                    <template x-for="error in errors" :key="error">
                                        <p x-text="error" class="text-sm text-red-600 mt-1"></p>
                                    </template>
                                    <p class="text-xs text-gray-400 mt-1">Maks 5 file, 2MB per file (jpg, png, pdf, doc, xls)</p>
                                </div>
                                <div class="flex items-center justify-between">
                                    @cannot('addInternalNote', $ticket)
                                        <span></span>
                                    @else
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                            <input type="checkbox" name="is_internal_note" value="1" {{ old('is_internal_note') ? 'checked' : '' }} class="rounded border-gray-300 text-amber-500">
                                            <span>Kirim sebagai catatan internal (tidak terlihat Customer)</span>
                                        </label>
                                    @endcannot
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-md transition">
                                        Kirim
                                    </button>
                                </div>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="w-full md:w-1/3 space-y-4">

                {{-- Update Status --}}
                @can('updateStatus', $ticket)
                    @if(count($allowedStatuses) > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-5">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Ubah Status</h3>
                                <form method="POST" action="{{ route('tickets.updateStatus', $ticket) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        @foreach($allowedStatuses as $s)
                                            <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-3 rounded-md transition">
                                        Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endcan

                {{-- Assign / Reassign --}}
                @can('assign', $ticket)
                    @if($assignableAgents && $assignableAgents->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-5">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                    {{ $ticket->assigned_agent_id ? 'Reassign Agent' : 'Assign ke Agent' }}
                                </h3>
                                <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex gap-2">
                                    @csrf
                                    <select name="agent_id" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        @foreach($assignableAgents as $agent)
                                            <option value="{{ $agent->id }}" {{ $ticket->assigned_agent_id == $agent->id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold py-2 px-3 rounded-md transition">
                                        Assign
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endcan

                {{-- Informasi Tiket --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-5">
                        <h3 class="text-sm font-semibold text-gray-700 border-b pb-2 mb-4">Informasi Tiket</h3>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase">Kategori</dt>
                                <dd class="mt-1 text-gray-900">{{ $ticket->category->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase">Prioritas</dt>
                                <dd class="mt-1 font-semibold" style="color: {{ $ticket->priority->color ?? '#6b7280' }}">
                                    {{ $ticket->priority->name ?? '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase">Agen</dt>
                                <dd class="mt-1 text-gray-900">
                                    {{ $ticket->assignedAgent->name ?? '—' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase">Tenggat Respons</dt>
                                <dd class="mt-1">
                                    @if($ticket->response_due_at)
                                        @php $responseBreached = $ticket->response_due_at->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']); @endphp
                                        <span class="{{ $responseBreached ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                            {{ $ticket->response_due_at->format('d M Y, H:i') }}
                                            @if($responseBreached) ⚠️ @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 uppercase">Tenggat Resolusi</dt>
                                <dd class="mt-1">
                                    @if($ticket->due_at)
                                        @php $resolutionBreached = $ticket->due_at->isPast() && !in_array($ticket->status, ['Resolved', 'Closed']); @endphp
                                        <span class="{{ $resolutionBreached ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                            {{ $ticket->due_at->format('d M Y, H:i') }}
                                            @if($resolutionBreached) ⚠️ @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @can('reopen', $ticket)
                    @if(in_array('Reopened', $allowedStatuses))
                        <form method="POST" action="{{ route('tickets.updateStatus', $ticket) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="Reopened">
                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold py-2 px-4 rounded-md transition">
                                Buka Kembali Tiket
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
