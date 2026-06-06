<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Ticket Lost in the Void | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-screen flex flex-col items-center justify-center text-white px-6" style="background-color: #030712;">

    <div class="text-center max-w-md">
        <p class="text-indigo-500 text-lg font-bold tracking-widest uppercase mb-4">Error 404</p>

        <h1 class="text-xl sm:text-5xl font-extrabold text-white mb-4 leading-tight">
            Ticket Lost<br>in the Void
        </h1>

        <p class="text-gray-500 text-sm leading-relaxed mb-8">
            Halaman yang dicari tidak ditemukan, mungkin sudah tersedot ke dalam void.
        </p>
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-lg transition duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

</body>
</html>
