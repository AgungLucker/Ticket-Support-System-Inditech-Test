<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Activity Log</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if(!$isCustomer)
            {{-- Filters --}}
            <form method="GET" action="{{ route('activity-logs.index') }}" class="mb-4 flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by ticket number or title..."
                    class="w-full sm:w-72 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">

                <select name="action" class="w-full sm:w-52 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option value="">All Actions</option>
                    @foreach([
                        'ticket_created'      => 'Created',
                        'ticket_updated'      => 'Updated',
                        'priority_changed'    => 'Priority Changed',
                        'status_changed'      => 'Status Changed',
                        'ticket_assigned'     => 'Agent Assigned',
                        'comment_added'       => 'Comment Added',
                        'internal_note_added' => 'Internal Note',
                        'attachment_uploaded' => 'Attachment Uploaded',
                        'sla_overdue'         => 'SLA Overdue',
                    ] as $value => $label)
                        <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'action']))
                    <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                        Clear
                    </a>
                @endif
            </form>
            @endif

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                @if($logs->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-sm text-gray-400">No activity logs found.</p>
                    </div>
                @else
                    @php
                    $actionMap = [
                        'ticket_created'      => ['label' => 'Created',         'class' => 'bg-green-100 text-green-700'],
                        'ticket_updated'      => ['label' => 'Updated',          'class' => 'bg-blue-100 text-blue-700'],
                        'priority_changed'    => ['label' => 'Priority Changed', 'class' => 'bg-yellow-100 text-yellow-800'],
                        'status_changed'      => ['label' => 'Status Changed',   'class' => 'bg-indigo-100 text-indigo-700'],
                        'ticket_assigned'     => ['label' => 'Agent Assigned',    'class' => 'bg-purple-100 text-purple-700'],
                        'comment_added'       => ['label' => 'Comment',          'class' => 'bg-gray-100 text-gray-600'],
                        'internal_note_added' => ['label' => 'Internal Note',       'class' => 'bg-amber-100 text-amber-700'],
                        'attachment_uploaded' => ['label' => 'Attachment Uploaded','class' => 'bg-teal-100 text-teal-700'],
                        'sla_overdue'         => ['label' => 'SLA Overdue',         'class' => 'bg-red-100 text-red-700'],
                    ];

                    $roleMap = [
                        'admin'      => 'bg-red-100 text-red-700',
                        'supervisor' => 'bg-blue-100 text-blue-700',
                        'agent'      => 'bg-indigo-100 text-indigo-700',
                        'customer'   => 'bg-gray-100 text-gray-600',
                    ];

                    $changeLabel = [
                        'status_changed'      => 'Status',
                        'priority_changed'    => 'Priority',
                        'ticket_assigned'     => 'Agent',
                        'attachment_uploaded' => 'File',
                    ];
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Time</th>
                                    @if(!$isCustomer)
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actor</th>
                                    @endif
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Ticket</th>
                                    @if(!$isCustomer)
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Change</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($logs as $log)
                                    @php
                                        $badge     = $actionMap[$log->action] ?? ['label' => $log->action, 'class' => 'bg-gray-100 text-gray-600'];
                                        $roleSlug  = $log->user?->role?->slug;
                                        $roleCss   = $roleMap[$roleSlug] ?? 'bg-gray-100 text-gray-500';
                                        $roleName  = $log->user?->role?->name ?? 'System';
                                        $ctxLabel  = $changeLabel[$log->action] ?? null;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500">
                                            {{ $log->created_at->format('d M Y, H:i') }}
                                        </td>

                                        @if(!$isCustomer)
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-gray-700">{{ $log->user?->name ?? 'System' }}</span>
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $roleCss }}">
                                                        {{ $roleName }}
                                                    </span>
                                                </div>
                                            </td>
                                        @endif

                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            @if($log->ticket)
                                                <a href="{{ route('tickets.show', $log->ticket) }}"
                                                   class="font-mono text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                                                    {{ $log->ticket->ticket_number }}
                                                </a>
                                                <p class="truncate max-w-xs text-xs text-gray-400">{{ $log->ticket->title }}</p>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>

                                        @if(!$isCustomer)
                                            <td class="px-4 py-3 text-sm">
                                                @if($log->action === 'attachment_uploaded')
                                                    <span class="text-xs text-gray-500">File on {{ $log->old_value }}:</span>
                                                    <span class="text-xs font-medium text-gray-700">{{ $log->new_value }}</span>
                                                @elseif($ctxLabel && ($log->old_value || $log->new_value))
                                                    <span class="text-xs text-gray-500">{{ $ctxLabel }}:</span>
                                                    <span class="text-xs text-gray-400 line-through">{{ $log->old_value ?? '—' }}</span>
                                                    <span class="mx-0.5 text-gray-300">→</span>
                                                    <span class="text-xs font-medium text-gray-700">{{ $log->new_value ?? '—' }}</span>
                                                @else
                                                    <span class="text-xs text-gray-400">—</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isCustomer ? 3 : 5 }}" class="px-6 py-12 text-center">
                                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <p class="mt-2 text-sm font-medium text-gray-900">Belum ada aktivitas</p>
                                            <p class="mt-1 text-xs text-gray-500">Log akan muncul saat ada perubahan pada tiket.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($logs->hasPages())
                        <div class="border-t border-gray-100 px-4 py-3">
                            {{ $logs->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
