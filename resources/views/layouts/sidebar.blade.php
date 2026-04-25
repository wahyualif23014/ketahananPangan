@php
$userRole = Auth::user()->role ?? 'admin';

$dashboardRoute = match ($userRole) {
'admin' => 'admin.dashboard',
'operator' => 'operator.dashboard',
'view' => 'view.dashboard',
default => 'login'
};

$roleData = match ($userRole) {
'admin' => ['label' => 'Administrator', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20'],
'operator' => ['label' => 'Operator Wilayah', 'bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20'],
'view' => ['label' => 'Viewer Data', 'bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'border' => 'border-blue-500/20'],
default => ['label' => 'Anggota Satgas', 'bg' => 'bg-slate-500/10', 'text' => 'text-slate-400', 'border' => 'border-slate-500/20']
};
@endphp

{{-- Overlay Mobile (Hidden on MD up) --}}
<div x-show="sidebarExpanded"
    x-cloak
    @click="sidebarExpanded = false"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-[#0B1220]/60 backdrop-blur-sm md:hidden"></div>

{{-- Sidebar Area --}}
<aside
    :class="{
        'translate-x-0 w-64': sidebarExpanded,
        '-translate-x-full w-64 md:translate-x-0 md:w-24': !sidebarExpanded
    }"
    class="fixed inset-y-0 left-0 z-50 flex flex-col h-full bg-[#0B1220] border-r border-[#1F2937] shadow-xl md:relative shrink-0 transition-all duration-400 ease-[cubic-bezier(0.4,0,0.2,1)] will-change-[width,transform] overflow-hidden">

    {{-- Header Content --}}
    <div class="h-[72px] flex items-center px-5 border-b border-[#1F2937] shrink-0 overflow-hidden relative"
        :class="sidebarExpanded ? 'justify-between' : 'justify-center'">

        <div class="flex items-center" :class="sidebarExpanded ? 'gap-3 w-full' : 'justify-center'">
            <div class="p-1.5 bg-gradient-to-tr from-emerald-600 to-emerald-400 rounded-lg flex-shrink-0 shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform"
                :class="sidebarExpanded ? '' : 'mx-auto'">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="flex flex-col whitespace-nowrap overflow-hidden">
                <span class="text-sm font-bold tracking-widest text-[#E5E7EB] uppercase">Sikap <span class="text-emerald-400">Presisi</span></span>
            </div>
        </div>

        {{-- Close button specific to mobile overlay --}}
        <button x-show="sidebarExpanded" @click="sidebarExpanded = false" class="md:hidden p-1.5 text-[#9CA3AF] hover:text-white hover:bg-white/5 rounded-lg transition-colors absolute right-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    {{-- User Profile Area --}}
    <div x-show="sidebarExpanded" x-cloak x-transition.opacity.duration.300ms
        class="px-5 py-6 flex flex-col items-center border-b border-[#1F2937] shrink-0 bg-[#0c1424]">

        <div class="relative mb-3">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-[#1F2937] to-[#111827] border border-[#1F2937] flex items-center justify-center text-lg font-bold text-white shadow-xl">
                {{ collect(explode(' ', Auth::user()->nama_anggota))->filter(fn($n) => !empty($n))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
            </div>
        </div>

        <h2 class="text-[13px] font-semibold text-[#E5E7EB] tracking-wide truncate max-w-full leading-tight text-center">
            {{ Auth::user()->nama_anggota }}
        </h2>

        <div class="mt-2 {{ $roleData['bg'] }} border {{ $roleData['border'] }} px-2.5 py-0.5 rounded-full">
            <p class="text-[10px] uppercase font-bold {{ $roleData['text'] }} tracking-wider">
                {{ $roleData['label'] }}
            </p>
        </div>
    </div>

    {{-- Divider for collapsed state --}}
    <div x-show="!sidebarExpanded" x-cloak class="w-8 mx-auto mt-6 mb-4 h-[1px] bg-[#1F2937]"></div>

    {{-- Menu List (Using <nav>) --}}
    <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto custom-scrollbar">

        {{-- Section 1: Dashboard --}}
        <div class="mb-2">
            <a href="{{ route($dashboardRoute) }}"
                class="group relative flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 
               {{ request()->routeIs($dashboardRoute) ? 'bg-emerald-500/10 text-emerald-400' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}"
                :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
                <div class="relative shrink-0 flex items-center justify-center">
                    <svg class="w-[20px] h-[20px] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                </div>
                <span x-show="sidebarExpanded" x-transition.opacity class="ml-3 text-[13px] font-medium tracking-wide">Beranda</span>
                @if(request()->routeIs($dashboardRoute))
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-emerald-400"></div>
                @endif
            </a>
        </div>

        {{-- Section 2: Data Utama (Admin Only) --}}
        @if($userRole === 'admin')
        <div x-show="sidebarExpanded" x-cloak class="pt-5 pb-2 px-3 text-[10px] font-semibold text-[#6B7280] uppercase tracking-wider">
            Navigasi Utama
        </div>

        <div x-show="!sidebarExpanded" class="w-6 mx-auto my-3 h-[1px] bg-[#1F2937]"></div>

        <div x-data="{ open: {{ request()->is('data-utama*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="w-full group relative flex items-center px-3 py-2.5 rounded-xl transition-all duration-200"
                :class="[
                        sidebarExpanded ? 'justify-between' : 'justify-center',
                        open && sidebarExpanded ? 'text-[#E5E7EB]' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5'
                    ]">
                <div class="flex items-center" :class="sidebarExpanded ? '' : 'justify-center'">
                    <div class="shrink-0 flex items-center justify-center" :class="open && sidebarExpanded ? 'text-emerald-400' : ''">
                        <svg class="w-[20px] h-[20px] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" class="ml-3 text-[13px] font-medium tracking-wide">Data Utama</span>
                </div>
                <svg x-show="sidebarExpanded" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open && sidebarExpanded" x-collapse.duration.300ms class="mt-1">
                <div class="pl-11 pr-2 space-y-0.5 border-l border-[#1F2937] ml-4 py-1">
                    <a href="{{ route('admin.tingkat-kesatuan.index') }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs('admin.tingkat-kesatuan.index') ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Tingkat Kesatuan</a>
                    <a href="{{ route('admin.jabatan.index') }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs('admin.jabatan.index') ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Jabatan</a>
                    <a href="{{ route('admin.wilayah.index') }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs('admin.wilayah.index') ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Wilayah</a>
                    <a href="{{ route('admin.komoditi.index') }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs('admin.komoditi.*') ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Komoditi Lahan</a>
                </div>
            </div>
        </div>

        <div x-show="sidebarExpanded" x-cloak class="pt-5 pb-2 px-3 text-[10px] font-semibold text-[#6B7280] uppercase tracking-wider">
            Konfigurasi
        </div>

        <a href="{{ route('admin.anggota.index') }}"
            class="group relative flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 
               {{ request()->routeIs('admin.anggota.*') ? 'bg-emerald-500/10 text-emerald-400' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}"
            :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
            <div class="relative shrink-0 flex items-center justify-center">
                <svg class="w-[20px] h-[20px] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <span x-show="sidebarExpanded" x-transition.opacity class="ml-3 text-[13px] font-medium tracking-wide">Data Personel</span>
            @if(request()->routeIs('admin.anggota.*'))
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-emerald-400"></div>
            @endif
        </a>
        @endif

        {{-- Section 3: Operasional --}}
        <div x-show="sidebarExpanded" x-cloak class="pt-5 pb-2 px-3 text-[10px] font-semibold text-[#6B7280] uppercase tracking-wider">
            Operasional
        </div>

        <div x-show="!sidebarExpanded" class="w-6 mx-auto my-3 h-[1px] bg-[#1F2937]"></div>

        <div x-data="{ open: {{ request()->is('*/kelola-lahan*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="w-full group relative flex items-center px-3 py-2.5 rounded-xl transition-all duration-200"
                :class="[
                    sidebarExpanded ? 'justify-between' : 'justify-center',
                    open && sidebarExpanded ? 'text-[#E5E7EB]' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5'
                ]">
                <div class="flex items-center" :class="sidebarExpanded ? '' : 'justify-center'">
                    <div class="shrink-0 flex items-center justify-center" :class="open && sidebarExpanded ? 'text-emerald-400' : ''">
                        <svg class="w-[20px] h-[20px] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V7.416a2 2 0 011.082-1.789l5.447-2.724a2 2 0 011.836 0l5.447 2.724A2 2 0 0118 7.416v7.966a2 2 0 01-1.082 1.79l-5.447 2.723a2 2 0 01-1.836 0z"></path>
                        </svg>
                    </div>
                    <span x-show="sidebarExpanded" class="ml-3 text-[13px] font-medium tracking-wide">Kelola Lahan</span>
                </div>
                <svg x-show="sidebarExpanded" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform text-[#6B7280]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open && sidebarExpanded" x-collapse.duration.300ms class="mt-1">
                <div class="pl-11 pr-2 space-y-0.5 border-l border-[#1F2937] ml-4 py-1">
                    @if($userRole === 'view')
                    <a href="{{ route('view.kelola-lahan.index') }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs('view.kelola-lahan.*') ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Data Kelola</a>
                    @else
                    @php
                    $potensiRoute = $userRole === 'operator' ? 'operator.kelola-lahan.potensi.index' : 'admin.kelola-lahan.potensi.index';
                    $daftarRoute = $userRole === 'operator' ? 'operator.kelola-lahan.daftar.index' : 'admin.kelola-lahan.daftar.index';
                    $potensiActive = $userRole === 'operator' ? 'operator.kelola-lahan.potensi.*' : 'admin.kelola-lahan.potensi.*';
                    $daftarActive = $userRole === 'operator' ? 'operator.kelola-lahan.daftar.*' : 'admin.kelola-lahan.daftar.*';
                    @endphp
                    <a href="{{ route($potensiRoute) }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs($potensiActive) ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Data Potensi</a>
                    <a href="{{ route($daftarRoute) }}" class="block py-2 pl-3 text-[12px] font-medium rounded-lg transition-colors {{ request()->routeIs($daftarActive) ? 'text-emerald-400 bg-emerald-400/5' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}">Daftar Kelola</a>
                    @endif
                </div>
            </div>
        </div>

        @php
        $rekapRoute = match($userRole) {
        'operator' => 'operator.rekapitulasi.index',
        'view' => 'view.rekapitulasi.index',
        default => 'admin.rekapitulasi.index'
        };
        $rekapActive = match($userRole) {
        'operator' => 'operator.rekapitulasi.*',
        'view' => 'view.rekapitulasi.*',
        default => 'admin.rekapitulasi.*'
        };
        @endphp

        <a href="{{ route($rekapRoute) }}"
            class="group relative flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 mt-1
           {{ request()->routeIs($rekapActive) ? 'bg-emerald-500/10 text-emerald-400' : 'text-[#9CA3AF] hover:text-[#E5E7EB] hover:bg-white/5' }}"
            :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
            <div class="relative shrink-0 flex items-center justify-center">
                <svg class="w-[20px] h-[20px] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <span x-show="sidebarExpanded" x-transition.opacity class="ml-3 text-[13px] font-medium tracking-wide">Rekapitulasi</span>
            @if(request()->routeIs($rekapActive))
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 rounded-r-full bg-emerald-400"></div>
            @endif
        </a>

    </nav>

    {{-- Footer --}}
    <div class="p-4 border-t border-[#1F2937] bg-[#0B1220] shrink-0 text-center">
        <div x-show="sidebarExpanded" x-cloak class="bg-[#111827] border border-[#1F2937] py-2 px-3 rounded-xl flex items-center justify-between">
            <p class="text-[10px] text-[#6B7280] font-semibold uppercase tracking-wider">Satgas Pangan</p>
            <p class="text-[10px] text-[#9CA3AF] font-bold">1.0.26</p>
        </div>
        <div x-show="!sidebarExpanded" class="text-[10px] text-[#6B7280] font-bold tracking-tighter">1.0</div>
    </div>
</aside>