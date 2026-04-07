@php
// Tentukan rute default jika role tidak cocok (fallback)
$dashboardRoute = 'login';

if (Auth::check()) {
if (Auth::user()->role === 'admin') {
$dashboardRoute = 'admin.dashboard';
} elseif (Auth::user()->role === 'operator') {
$dashboardRoute = 'operator.dashboard';
} elseif (Auth::user()->role === 'view') {
$dashboardRoute = 'view.dashboard';
}
}

// Logika Label Status Role
$roleLabel = 'ANGGOTA SATGAS';
$roleColor = 'text-slate-400';

if (Auth::user()->role === 'admin') {
$roleLabel = 'ADMINISTRATOR';
$roleColor = 'text-emerald-400';
} elseif (Auth::user()->role === 'operator') {
$roleLabel = 'OPERATOR WILAYAH';
$roleColor = 'text-amber-400';
} elseif (Auth::user()->role === 'view') {
$roleLabel = 'VIEWER DATA';
$roleColor = 'text-blue-400';
}
@endphp

<div x-show="sidebarOpen" x-cloak
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden"></div>

<aside
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'md:w-72': !desktopCollapsed,
        'md:w-20': desktopCollapsed
    }"
    class="fixed inset-y-0 left-0 bg-slate-900 text-white z-50 transition-all duration-300 ease-in-out transform md:relative md:translate-x-0 flex flex-col border-r border-slate-800 shadow-2xl h-full shrink-0">

    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800/50 bg-slate-900 shrink-0">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="p-1.5 bg-emerald-500 rounded-lg flex-shrink-0 shadow-lg shadow-emerald-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h1 x-show="!desktopCollapsed" x-transition:enter="transition ease-out duration-300"
                class="text-lg font-black tracking-tighter uppercase whitespace-nowrap italic">
                SIKAP <span class="text-emerald-400">PRESISI</span>
            </h1>
        </div>
        <button @click="desktopCollapsed = !desktopCollapsed"
            class="hidden md:block text-slate-500 hover:text-white transition-colors">
            <svg :class="desktopCollapsed ? 'rotate-180' : ''" class="w-5 h-5 transition-transform duration-300"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7">
                </path>
            </svg>
        </button>
    </div>

    {{-- Profil User --}}
    <div x-show="!desktopCollapsed" x-cloak x-transition
        class="px-6 py-8 text-center border-b border-slate-800/50 bg-gradient-to-b from-slate-900 to-slate-800/30 shrink-0">
        <div class="relative inline-block mb-4">
            <div class="w-16 h-16 rounded-2xl bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-xl font-black text-white shadow-xl rotate-3">
                <span class="-rotate-3">
                    {{-- Perbaikan inisial menggunakan nama_anggota dan filter string kosong --}}
                    {{ collect(explode(' ', Auth::user()->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                </span>
            </div>
        </div>
        {{-- Menggunakan nama_anggota dari tabel anggota --}}
        <h2 class="text-sm font-black text-white uppercase tracking-wide truncate">{{ Auth::user()->nama_anggota }}</h2>
        {{-- Menggunakan username sebagai NRP --}}
        <p class="text-[10px] text-slate-500 mt-1 font-mono uppercase">NRP : {{ Auth::user()->username }}</p>
        <div class="mt-2 inline-block px-3 py-1 bg-slate-800/50 border border-slate-700 rounded-full">
            <p class="text-[9px] {{ $roleColor }} font-black tracking-[0.2em] uppercase">{{ $roleLabel }}</p>
        </div>
        <p class="text-[9px] text-slate-500 font-bold mt-2 tracking-widest uppercase italic opacity-50">POLDA JAWA TIMUR
        </p>
    </div>

    {{-- Navigasi Utama --}}
    <nav class="flex-1 px-3 space-y-1 overflow-y-auto custom-scrollbar py-4">

        {{-- Beranda --}}
        <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)" icon="home">
            <span x-show="!desktopCollapsed">BERANDA</span>
        </x-nav-link>

        <div x-data="{ open: {{ request()->is('data-utama*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                :class="open ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white'"
                class="w-full group flex items-center px-4 py-3 text-[11px] font-black transition-all rounded-xl uppercase tracking-wider">
                <div :class="!desktopCollapsed ? 'mr-3' : 'mx-auto'" class="text-slate-500 group-hover:text-emerald-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                    </svg>
                </div>
                <span x-show="!desktopCollapsed" class="flex-1 text-left">Data Utama</span>
                <svg x-show="!desktopCollapsed" :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open && !desktopCollapsed" x-cloak x-transition class="pl-12 space-y-1 pr-2 py-1">
                <a href="{{ route('admin.tingkat-kesatuan.index') }}"
                    class="block py-2 text-[10px] font-bold {{ request()->routeIs('admin.tingkat-kesatuan.index') ? 'text-emerald-400' : 'text-slate-500' }} hover:text-emerald-400 uppercase tracking-widest transition">
                    Tingkat Kesatuan
                </a>
                <a href="{{ route('admin.jabatan.index') }}"
                    class="block py-2 text-[10px] font-bold {{ request()->routeIs('admin.jabatan.index') ? 'text-emerald-400' : 'text-slate-500' }} hover:text-emerald-400 uppercase tracking-widest transition">
                    Jabatan
                </a>
                <a href="{{ route('admin.wilayah.index') }}"
                    class="block py-2 text-[10px] font-bold {{ request()->routeIs('admin.wilayah.index') ? 'text-emerald-400' : 'text-slate-500' }} hover:text-emerald-400 uppercase tracking-widest transition">
                    Wilayah
                </a>
                <a href="{{ route('admin.komoditas.index') }}"
                    class="block py-2 text-[10px] font-bold {{ request()->routeIs('admin.komoditas.*') ? 'text-emerald-400' : 'text-slate-500' }} hover:text-emerald-400 uppercase tracking-widest transition">
                    Komoditas Lahan
                </a>

            </div>
        </div>

        {{-- Menggunakan pengecekan role manual untuk menggantikan @hasrole --}}
        @if(Auth::user()->role === 'admin')
        <div x-show="!desktopCollapsed" class="pt-6 pb-2 px-4 text-[9px] font-black text-slate-600 uppercase tracking-[0.3em]">Master Data</div>
        <x-nav-link href="/anggota" :active="request()->is('anggota*')" icon="users">
            <span x-show="!desktopCollapsed">DATA PERSONEL</span>
        </x-nav-link>
        @endif

        {{-- Section: Operasional --}}
        <div x-show="!desktopCollapsed"
            class="pt-6 pb-2 px-4 text-[9px] font-black text-slate-600 uppercase tracking-[0.3em]">
            Operasional
        </div>

        {{-- DROPDOWN: KELOLA LAHAN --}}
        <div x-data="{ open: {{ request()->is('admin/kelola-lahan*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                :class="open ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800/50 hover:text-white'"
                class="w-full group flex items-center px-4 py-3 text-[11px] font-black transition-all rounded-xl uppercase tracking-wider">
                <div :class="!desktopCollapsed ? 'mr-3' : 'mx-auto'"
                    class="text-slate-500 group-hover:text-emerald-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A2 2 0 013 15.382V7.416a2 2 0 011.082-1.789l5.447-2.724a2 2 0 011.836 0l5.447 2.724A2 2 0 0118 7.416v7.966a2 2 0 01-1.082 1.79l-5.447 2.723a2 2 0 01-1.836 0z">
                        </path>
                    </svg>
                </div>
                <span x-show="!desktopCollapsed" class="flex-1 text-left">Kelola Lahan</span>
                <svg x-show="!desktopCollapsed" :class="open ? 'rotate-180' : ''"
                    class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open && !desktopCollapsed" x-cloak x-transition class="pl-12 space-y-1 pr-2 py-1">
                <a href="{{ route('admin.kelola-lahan.potensi.index') }}"
                    class="block py-2 text-[10px] font-bold {{ request()->routeIs('admin.kelola-lahan.potensi.*') ? 'text-emerald-400' : 'text-slate-500' }} hover:text-emerald-400 uppercase tracking-widest transition">
                    Data Potensi
                </a>
                <a href="{{ route('admin.kelola-lahan.daftar.index') }}"
                    class="block py-2 text-[10px] font-bold {{ request()->routeIs('admin.kelola-lahan.daftar.*') ? 'text-emerald-400' : 'text-slate-500' }} hover:text-emerald-400 uppercase tracking-widest transition">
                    Daftar Kelola
                </a>
            </div>
        </div>

        {{-- Single Link: REKAPITULASI --}}
        <x-nav-link :href="route('admin.rekapitulasi.index')" :active="request()->routeIs('admin.rekapitulasi.*')">
            <div :class="!desktopCollapsed ? 'mr-3' : 'mx-auto'"
                class="{{ request()->routeIs('admin.rekapitulasi.*') ? 'text-emerald-400' : 'text-slate-500' }} group-hover:text-emerald-400 transition-colors">
                {{-- Ikon Chart Bar Modern --}}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
            </div>
            <span x-show="!desktopCollapsed" class="font-black">REKAPITULASI</span>
        </x-nav-link>

    </nav>

    {{-- Footer Sidebar --}}
    <div class="p-6 border-t border-slate-800/50 bg-slate-900 shrink-0">
        <div x-show="!desktopCollapsed" x-cloak class="bg-slate-800/30 p-3 rounded-xl border border-slate-800/50">
            <p class="text-[9px] text-slate-500 text-center font-black tracking-widest uppercase italic">
                v1.0.26 © SATGAS PANGAN JATIM
            </p>
        </div>
        <div x-show="desktopCollapsed" class="text-center text-[8px] text-slate-600 font-bold uppercase">V1.0</div>
    </div>
</aside>
