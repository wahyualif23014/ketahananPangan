<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PANGAN PRESISI - POLDA JAWA TIMUR</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        @layer utilities {
            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #10b981; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900" 
      x-data="{ sidebarOpen: false, desktopCollapsed: false }">
    
    <div class="flex min-h-screen overflow-hidden">
        
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-8 sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 md:hidden transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <div class="hidden md:flex items-center text-sm font-medium">
                        <span class="text-slate-400 uppercase tracking-wider">Beranda</span>
                        <svg class="w-4 h-4 mx-2 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg>
                        <span class="text-emerald-600 font-black uppercase tracking-widest">@yield('header', 'Halaman Utama')</span>
                    </div>
                </div>

                <div class="flex items-center gap-3" x-data="{ userOpen: false }">
                    <div class="text-right mr-2 hidden sm:block">
                        <p class="text-xs font-black text-slate-800 uppercase leading-none">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-tighter mt-1">{{ Auth::user()->roles->first()->name ?? 'User' }}</p>
                    </div>
                    <div class="relative">
                        <button @click="userOpen = !userOpen" class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black border-2 border-slate-200 shadow-sm focus:outline-none hover:bg-emerald-600 transition-all">
                            {{ substr(Auth::user()->name, 0, 2) }}
                        </button>
                        <div x-show="userOpen" x-cloak @click.outside="userOpen = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs font-black text-red-600 hover:bg-red-50 transition uppercase tracking-widest">
                                    LOGOUT SISTEM
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 custom-scrollbar bg-slate-50">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>