<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PANGAN PRESISI - POLDA JAWA TIMUR</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ramabhadra&display=swap" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        .font-tactical {
            font-family: 'Ramabhadra', 'Figtree', sans-serif;
        }

        @layer utilities {
            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #334155;
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #10b981;
            }
        }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-slate-100 to-slate-200 text-slate-900 overflow-hidden"
    x-data="{ sidebarOpen: false, desktopCollapsed: false }">

    <div class="flex h-screen overflow-hidden">

        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 shrink-0 z-30 shadow-sm">
                <div class="flex items-center gap-2 md:gap-4">

                    <button @click="sidebarOpen = true"
                        class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 md:hidden transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <button @click="desktopCollapsed = !desktopCollapsed"
                        class="hidden md:flex p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors">
                        <svg :class="desktopCollapsed ? 'rotate-180' : ''"
                            class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <h1 class="text-md md:text-xl font-bold text-[#577C8E] tracking-tight uppercase truncate">SISTEM
                        KETAHANAN PANGAN PRESISI</h1>
                </div>

                <div class="flex items-center gap-3" x-data="{ userOpen: false }">
                    <div class="text-right hidden sm:block">
                        <p class="text-[11px] font-black text-[#577C8E] uppercase leading-none">
                            {{ Auth::user()->nama_anggota }}
                        </p>
                        <p class="text-[10px] font-bold text-[#7D9AA8] uppercase tracking-tighter mt-0.5">
                            {{ Auth::user()->role ?? 'ADMINISTRATOR' }}
                        </p>
                    </div>
                    <div class="relative">
                        <button @click="userOpen = !userOpen"
                            class="w-10 h-10 rounded-full bg-[#7D9AA8] text-white flex items-center justify-center font-black border border-slate-200 shadow-sm focus:outline-none transition-all">
                            {{ collect(explode(' ', Auth::user()->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                        </button>

                        <div x-show="userOpen" x-cloak @click.outside="userOpen = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-2xl border border-slate-200 py-1 z-50">

                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center gap-3 px-4 py-3 text-[11px] font-bold text-slate-500 hover:bg-slate-50 hover:text-blue-600 transition-all uppercase tracking-widest">
                                <div
                                    class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                        </path>
                                    </svg>
                                </div>
                                Beranda
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-[11px] font-bold text-slate-500 hover:bg-slate-50 hover:text-orange-600 transition-all uppercase tracking-widest">
                                    <div
                                        class="w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                    </div>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar bg-transparent">
                <div class="max-w-[1600px] mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>

</html>