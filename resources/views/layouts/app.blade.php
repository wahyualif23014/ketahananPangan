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
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
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
                width: 5px;
                height: 5px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #1F2937;
                border-radius: 99px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #374151;
            }
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#f3f4f6] text-slate-900 overflow-hidden"
    x-data="{ sidebarExpanded: window.innerWidth >= 768 }"
    @resize.window="sidebarExpanded = window.innerWidth >= 768">

    <div class="flex h-screen overflow-hidden">

        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden transition-all duration-300">

            <header class="h-[72px] bg-white border-b border-gray-200 flex items-center justify-between px-6 lg:px-8 shrink-0 z-30 shadow-sm relative">
                <div class="flex items-center gap-3">
                    <button @click="sidebarExpanded = !sidebarExpanded"
                        class="p-2 -ml-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <svg x-show="!sidebarExpanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="sidebarExpanded" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </button>

                    <h1 class="text-sm font-semibold text-slate-800 tracking-tight sm:text-base">SISTEM KETAHANAN PANGAN</h1>
                </div>

                <div class="flex items-center gap-4" x-data="{ userOpen: false }">
                    <div class="text-right hidden sm:block">
                        <p class="text-[13px] font-semibold text-slate-800 leading-tight">
                            {{ Auth::user()->nama_anggota }}
                        </p>
                        <p class="text-[11px] font-medium text-slate-500 tracking-wide mt-0.5 capitalize">
                            {{ Auth::user()->role ?? 'Administrator' }}
                        </p>
                    </div>
                    <div class="relative">
                        <button @click="userOpen = !userOpen"
                            class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-500 to-emerald-400 text-white flex items-center justify-center font-bold text-sm shadow-sm hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            {{ collect(explode(' ', Auth::user()->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                        </button>

                        <div x-show="userOpen" x-cloak @click.outside="userOpen = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                            class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-50 origin-top-right">

                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Beranda
                            </a>

                            <div class="h-[1px] bg-slate-100 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Keluar Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 custom-scrollbar">
                <div class="max-w-[1600px] mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>

</html>