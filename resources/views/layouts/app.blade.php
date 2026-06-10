<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: false }">

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-20 bg-black/50 lg:hidden"
             style="display: none;"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-indigo-900 transform transition-transform duration-200 ease-in-out lg:translate-x-0">
            @include('layouts.navigation')
        </aside>

        {{-- Main wrapper --}}
        <div class="flex min-h-screen flex-col lg:pl-64">

            {{-- Topbar --}}
            <header class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4">

                {{-- Hamburger (mobile) --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none transition">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1"></div>

                {{-- Bell notification --}}
                @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="relative flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-500 text-[10px] font-bold leading-none text-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-96 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                         style="display: none;">

                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">Notifikasi</span>
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                        {{ $unreadCount }} belum dibaca
                                    </span>
                                @endif
                            </div>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.readAll') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                                        Tandai semua dibaca
                                    </button>
                                </form>
                            @endif
                        </div>

                        @php $notifications = Auth::user()->notifications()->latest()->take(8)->get(); @endphp
                        <div class="max-h-96 divide-y divide-gray-50 overflow-y-auto">
                            @forelse($notifications as $notif)
                                <a href="{{ route('notifications.read', $notif->id) }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition {{ $notif->read_at ? '' : 'bg-indigo-50/40' }}">
                                    <div class="mt-1.5 shrink-0">
                                        @if(!$notif->read_at)
                                            <span class="block h-2 w-2 rounded-full bg-indigo-500"></span>
                                        @else
                                            <span class="block h-2 w-2 rounded-full bg-transparent"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-800 {{ $notif->read_at ? '' : 'font-medium' }} line-clamp-2">
                                            {{ $notif->data['message'] ?? '-' }}
                                        </p>
                                        @if(!empty($notif->data['ticket_number']))
                                            <p class="mt-0.5 text-xs text-gray-400">#{{ $notif->data['ticket_number'] }}</p>
                                        @endif
                                        <p class="mt-0.5 text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <svg class="mx-auto mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <p class="text-sm text-gray-400">Belum ada notifikasi</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-full px-2 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none transition">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden lg:block w-28 truncate">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 fill-current text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="border-b border-gray-100 px-4 py-2">
                            <p class="text-xs text-gray-400">Masuk sebagai</p>
                            <p class="truncate text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs text-gray-400">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </header>

            {{-- Page Heading --}}
            @isset($header)
                <div class="border-b border-gray-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            {{-- Page Content --}}
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
