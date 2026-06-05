{{-- Sidebar logo --}}
<div class="flex h-14 shrink-0 items-center border-b border-indigo-800 px-4">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
        <x-application-logo class="h-8 w-auto shrink-0 fill-current text-white" />
        <span class="text-sm font-semibold tracking-wide text-white">Support Desk</span>
    </a>
</div>

{{-- Sidebar navigation --}}
<nav class="flex-1 overflow-y-auto p-3">
    @php
        $active   = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium bg-indigo-800 text-white';
        $inactive = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-indigo-200 hover:bg-indigo-800 hover:text-white transition duration-150';
    @endphp

    <div class="space-y-0.5">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $active : $inactive }}">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        {{-- Tickets --}}
        <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? $active : $inactive }}">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
            {{ Auth::user()->isCustomer() ? 'Tiket Saya' : 'Semua Tiket' }}
        </a>

        @if(!Auth::user()->isAgent())
        <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? $active : $inactive }}">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Activity Log
        </a>
        @endif
    </div>

    {{-- Master Data (Admin only) --}}
    @can('access-admin')
        <div class="mt-6 mb-2 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-400">Master Data</p>
        </div>
        <div class="space-y-0.5">
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? $active : $inactive }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Users
            </a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? $active : $inactive }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                Categories
            </a>
            <a href="{{ route('admin.labels.index') }}" class="{{ request()->routeIs('admin.labels.*') ? $active : $inactive }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10V5a2 2 0 012-2z" />
                </svg>
                Labels
            </a>
            <a href="{{ route('admin.priorities.index') }}" class="{{ request()->routeIs('admin.priorities.*') ? $active : $inactive }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
                Priorities
            </a>
            <a href="{{ route('admin.sla-rules.index') }}" class="{{ request()->routeIs('admin.sla-rules.*') ? $active : $inactive }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                SLA Rules
            </a>
        </div>
    @endcan
</nav>

{{-- User info --}}
<div class="shrink-0 border-t border-indigo-800 p-3">
    <div class="flex items-center gap-3 rounded-lg px-2 py-1.5">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-700 text-xs font-semibold text-white ring-2 ring-indigo-500">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
            <p class="truncate text-xs capitalize text-indigo-400">{{ Auth::user()->role?->name ?? '' }}</p>
        </div>
    </div>
</div>
