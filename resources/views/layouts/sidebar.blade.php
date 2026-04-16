@php
    $dashboardRoute = 'login';
    if (Auth::check()) {
        $dashboardRoute = match (Auth::user()->role) {
            'admin' => 'admin.dashboard',
            'operator' => 'operator.dashboard',
            'view' => 'view.dashboard',
            default => 'login'
        };
    }

    $roleData = match (Auth::user()->role) {
        'admin' => ['label' => 'Administrator', 'color' => 'text-emerald-400'],
        'operator' => ['label' => 'Operator Wilayah', 'color' => 'text-amber-400'],
        'view' => ['label' => 'Viewer Data', 'color' => 'text-blue-400'],
        default => ['label' => 'Anggota Satgas', 'color' => 'text-slate-400']
    };
@endphp

{{-- Overlay Mobile --}}
<div x-show="sidebarOpen" x-cloak 
    x-transition:enter="transition-opacity ease-linear duration-300" 
    x-transition:enter-start="opacity-0" 
    x-transition:enter-end="opacity-100" 
    x-transition:leave="transition-opacity ease-linear duration-300" 
    x-transition:leave-start="opacity-100" 
    x-transition:leave-end="opacity-0" 
    @click="sidebarOpen = false" 
    class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"></div>

{{-- Sidebar Container --}}
<aside 
    :class="{
        'w-72': !desktopCollapsed,
        'w-20': desktopCollapsed,
        '-translate-x-full': !sidebarOpen,
        'md:translate-x-0': true
    }"
    class="fixed inset-y-0 left-0 z-50 flex flex-col h-full transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] bg-slate-900 text-white border-r border-white/5 shadow-2xl md:relative shrink-0 overflow-hidden">
    
    {{-- Sidebar Header --}}
    <div class="h-16 flex items-center justify-between px-6 border-b border-white/5 bg-slate-900 shrink-0 overflow-hidden">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="p-1.5 bg-emerald-500 rounded-lg flex-shrink-0 shadow-lg shadow-emerald-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h1 x-show="!desktopCollapsed" x-transition.opacity.duration.400ms
                class="text-sm font-bold tracking-widest uppercase whitespace-nowrap italic">
                Sikap <span class="text-emerald-400">Presisi</span>
            </h1>
        </div>

        {{-- Toggle Button (Bisa di klik di Laptop maupun HP) --}}
        <button @click="window.innerWidth >= 768 ? desktopCollapsed = !desktopCollapsed : sidebarOpen = false" 
            class="flex items-center justify-center p-1.5 rounded-lg text-slate-500 hover:bg-white/5 hover:text-emerald-400 transition-all duration-200 focus:outline-none">
            <svg :class="desktopCollapsed ? 'rotate-180' : ''" class="w-5 h-5 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>
    </div>

    {{-- Profil User --}}
    <div x-show="!desktopCollapsed" x-cloak x-transition.opacity
        class="px-6 py-8 text-center border-b border-white/5 bg-gradient-to-b from-slate-900 to-slate-800/20 shrink-0">
        <div class="relative inline-block mb-4 group">
            <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center text-xl font-bold text-white shadow-xl transition-transform group-hover:rotate-3 duration-300">
                <span class="-rotate-3 uppercase">
                    {{ collect(explode(' ', Auth::user()->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                </span>
            </div>
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-4 border-slate-900 rounded-full shadow-md"></div>
        </div>
        <h2 class="text-sm font-semibold text-white tracking-wide truncate uppercase">{{ Auth::user()->nama_anggota }}</h2>
        <p class="text-[10px] text-slate-500 mt-1 font-mono tracking-widest uppercase">NRP: {{ Auth::user()->username }}</p>
        <div class="mt-3 inline-block px-3 py-1 bg-white/5 border border-white/5 rounded-full">
            <p class="text-[9px] {{ $roleData['color'] }} font-bold tracking-widest uppercase">{{ $roleData['label'] }}</p>
        </div>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 px-3 space-y-1 overflow-y-auto custom-scrollbar py-4">
        
        <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)" icon="home">
            <span x-show="!desktopCollapsed" class="text-xs font-medium tracking-wide uppercase">Beranda</span>
        </x-nav-link>

        <div x-show="!desktopCollapsed" class="pt-4 pb-2 px-4 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Navigasi Utama</div>

        <div x-data="{ open: {{ request()->is('data-utama*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                :class="(open && !desktopCollapsed) ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                class="w-full group flex items-center px-4 py-3 text-xs font-medium transition-all rounded-xl uppercase">
                <div :class="!desktopCollapsed ? 'mr-3' : 'mx-auto'" class="text-slate-500 group-hover:text-emerald-400 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <span x-show="!desktopCollapsed" class="flex-1 text-left tracking-wide">Data Utama</span>
                <svg x-show="!desktopCollapsed" :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="open && !desktopCollapsed" x-cloak x-transition class="pl-12 space-y-1 pr-2 py-1 border-l border-white/5 ml-6">
                <a href="{{ route('admin.tingkat-kesatuan.index') }}" class="block py-2 text-[11px] font-medium transition-colors {{ request()->routeIs('admin.tingkat-kesatuan.index') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-200' }}">Tingkat Kesatuan</a>
                <a href="{{ route('admin.jabatan.index') }}" class="block py-2 text-[11px] font-medium transition-colors {{ request()->routeIs('admin.jabatan.index') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-200' }}">Jabatan</a>
                <a href="{{ route('admin.wilayah.index') }}" class="block py-2 text-[11px] font-medium transition-colors {{ request()->routeIs('admin.wilayah.index') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-200' }}">Wilayah</a>
                <a href="{{ route('admin.komoditi.index') }}" class="block py-2 text-[11px] font-medium transition-colors {{ request()->routeIs('admin.komoditi.*') ? 'text-emerald-400' : 'text-slate-500 hover:text-emerald-400 transition' }}">Komoditi Lahan</a>
            </div>
        </div>

        @if(Auth::user()->role === 'admin')
            <div x-show="!desktopCollapsed" class="pt-6 pb-2 px-4 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Konfigurasi</div>
            <x-nav-link :href="route('admin.anggota.index')" :active="request()->routeIs('admin.anggota.*')" icon="users">
                <span x-show="!desktopCollapsed" class="text-xs font-medium tracking-wide uppercase">Data Personel</span>
            </x-nav-link>
        @endif

        <div x-show="!desktopCollapsed" class="pt-6 pb-2 px-4 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Operasional</div>

        <div x-data="{ open: {{ request()->is('admin/kelola-lahan*') ? 'true' : 'false' }} }">
            <button @click="open = !open" 
                :class="(open && !desktopCollapsed) ? 'bg-white/5 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                class="w-full group flex items-center px-4 py-3 text-xs font-medium transition-all rounded-xl uppercase">
                <div :class="!desktopCollapsed ? 'mr-3' : 'mx-auto'" class="text-slate-500 group-hover:text-emerald-400 transition-colors">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V7.416a2 2 0 011.082-1.789l5.447-2.724a2 2 0 011.836 0l5.447 2.724A2 2 0 0118 7.416v7.966a2 2 0 01-1.082 1.79l-5.447 2.723a2 2 0 01-1.836 0z"></path></svg>
                </div>
                <span x-show="!desktopCollapsed" class="flex-1 text-left tracking-wide">Kelola Lahan</span>
                <svg x-show="!desktopCollapsed" :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="open && !desktopCollapsed" x-cloak x-transition class="pl-12 space-y-1 pr-2 py-1 border-l border-white/5 ml-6">
                <a href="{{ route('admin.kelola-lahan.potensi.index') }}" class="block py-2 text-[11px] font-medium transition-colors {{ request()->routeIs('admin.kelola-lahan.potensi.*') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-200' }}">Data Potensi</a>
                <a href="{{ route('admin.kelola-lahan.daftar.index') }}" class="block py-2 text-[11px] font-medium transition-colors {{ request()->routeIs('admin.kelola-lahan.daftar.*') ? 'text-emerald-400' : 'text-slate-500 hover:text-slate-200' }}">Daftar Kelola</a>
            </div>
        </div>

        <x-nav-link :href="route('admin.rekapitulasi.index')" :active="request()->routeIs('admin.rekapitulasi.*')">
            <div :class="!desktopCollapsed ? 'mr-3' : 'mx-auto'" class="{{ request()->routeIs('admin.rekapitulasi.*') ? 'text-emerald-400' : 'text-slate-500' }} group-hover:text-emerald-400 transition-colors">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <span x-show="!desktopCollapsed" class="text-xs font-medium tracking-wide uppercase">Rekapitulasi</span>
        </x-nav-link>

    </nav>

    {{-- Footer Sidebar --}}
    <div class="p-6 border-t border-white/5 bg-slate-900 shrink-0 text-center">
        <div x-show="!desktopCollapsed" x-cloak class="opacity-60">
            <p class="text-[9px] text-slate-500 font-bold tracking-widest uppercase italic">v1.0.26 © Satgas Pangan Jatim</p>
        </div>
        <div x-show="desktopCollapsed" class="text-[10px] text-slate-600 font-bold uppercase tracking-tighter">V1</div>
    </div>
</aside>