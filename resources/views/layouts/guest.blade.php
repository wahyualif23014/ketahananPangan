<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PANGAN PRESISI - POLDA JATIM') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Mencegah flickering saat elemen Alpine.js dimuat */
            [x-cloak] { display: none !important; }
            
            /* Haluskan scrollbar untuk vibe modern */
            ::-webkit-scrollbar { width: 5px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased overflow-x-hidden bg-[#f8fafc]">
        
        <div class="min-h-screen selection:bg-emerald-500 selection:text-white">
            {{ $slot }}
        </div>

    </body>
</html>