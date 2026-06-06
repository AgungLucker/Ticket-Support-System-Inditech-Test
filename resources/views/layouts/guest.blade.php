<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ticket Support System') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">

            {{-- Left branding panel --}}
            <div class="hidden lg:flex lg:w-1/2 bg-indigo-900 flex-col justify-between p-12 relative overflow-hidden">

                {{-- Decorative circles --}}
                <div class="absolute -top-20 -right-20 w-80 h-80 bg-indigo-800 rounded-full opacity-60"></div>
                <div class="absolute -bottom-28 -left-12 w-72 h-72 bg-indigo-800 rounded-full opacity-50"></div>
                <div class="absolute top-1/2 right-8 w-40 h-40 bg-indigo-700 rounded-full opacity-30"></div>

                {{-- Logo --}}
                <div class="relative z-10 flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow">
                        <svg class="w-6 h-6 text-indigo-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold text-lg tracking-tight">{{ config('app.name') }}</span>
                </div>

                {{-- Hero text --}}
                <div class="relative z-10">
                    <h2 class="text-white text-3xl font-bold leading-snug mb-3">
                        Kelola tiket support<br>dalam satu platform
                    </h2>
                    <p class="text-indigo-300 text-sm leading-relaxed mb-8">
                        Platform terpusat untuk membuat, melacak, dan menyelesaikan permintaan support secara efisien.
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-indigo-200 text-sm">Pantau status tiket secara real-time</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-indigo-200 text-sm">Notifikasi & Reminder SLA otomatis</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-indigo-200 text-sm">Riwayat aktivitas yang terstruktur</span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 text-indigo-500 text-xs">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </div>
            </div>

            {{-- Right form panel --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-6 py-12 sm:px-12">
                <div class="w-full max-w-md">

                    {{-- Mobile logo --}}
                    <div class="flex items-center gap-2 mb-8 lg:hidden justify-center">
                        <div class="w-9 h-9 bg-indigo-900 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                        </div>
                        <span class="text-indigo-900 font-semibold text-base tracking-tight">{{ config('app.name') }}</span>
                    </div>

                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>
