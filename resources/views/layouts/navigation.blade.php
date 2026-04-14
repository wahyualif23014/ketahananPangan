<div x-data="{ sidebarCollapsed: false, mobileMenuOpen: false }" 
     class="flex min-h-screen bg-slate-50 font-sans antialiased text-slate-900">

    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-40 md:hidden" x-cloak>
    </div>

    <aside 
        :class="{
            'w-72': !sidebarCollapsed,
            'w-20': sidebarCollapsed,
            'translate-x-0': mobileMenuOpen,
            '-translate-x-full': !mobileMenuOpen
        }"
        class="fixed inset-y-0 left-0 bg-slate-900 text-slate-300 z-50 flex flex-col border-r border-white/5 shadow-2xl transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] md:relative md:translate-x-0 shrink-0">

        <div class="h-20 flex items-center justify-between px-6 border-b border-white/5 shrink-0 overflow-hidden">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-500 rounded-xl flex-shrink-0 shadow-lg shadow-emerald-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div x-show="!sidebarCollapsed" x-transition.opacity.duration.400ms class="flex flex-col">
                    <span class="text-sm font-bold text-white tracking-widest italic">SIKAP</span>
                    <span class="text-[10px] text-emerald-400 font-bold tracking-[0.2em]">PRESISI</span>
                </div>
            </div>
            
            <button @click="sidebarCollapsed = !sidebarCollapsed" 
                    class="hidden md:flex p-1.5 rounded-lg hover:bg-white/5 text-slate-500 hover:text-white transition-all">
                <svg :class="sidebarCollapsed ? 'rotate-180' : ''" class="w-5 h-5 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <div x-show="!sidebarCollapsed" x-transition.opacity.duration.500ms
             class="px-6 py-8 text-center border-b border-white/5 bg-gradient-to-b from-transparent to-white/[0.02] shrink-0">
            <div class="relative inline-block mb-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-800 border border-white/10 flex items-center justify-center text-xl font-semibold text-white shadow-xl">
                    {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-slate-900 rounded-full"></div>
            </div>
            <h2 class="text-sm font-semibold text-white tracking-tight truncate">{{ Auth::user()->name }}</h2>
            <p class="text-[10px] text-slate-500 mt-1 font-mono">NRP : {{ Auth::user()->nrp }}</p>
            <div class="mt-3 inline-block px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                <p class="text-[9px] text-emerald-400 font-bold tracking-widest uppercase">POLDA JAWA TIMUR</p>
            </div>
        </div>

        <nav class="flex-1 px-3 mt-6 space-y-1.5 overflow-y-auto custom-scrollbar">
            <div x-show="!sidebarCollapsed" class="px-4 pb-2 text-[10px] font-bold text-slate-600 uppercase tracking-widest">Utama</div>

            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                <span x-show="!sidebarCollapsed" class="text-xs font-semibold">BERANDA</span>
            </x-nav-link>

            <div x-data="{ open: {{ request()->is('data-utama*') ? 'true' : 'false' }} }">
                <button @click="open = !open" 
                    :class="open ? 'text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white'" 
                    class="w-full group flex items-center px-4 py-3 rounded-xl transition-all duration-200">
                    <div :class="!sidebarCollapsed ? 'mr-3' : 'mx-auto'" class="text-slate-500 group-hover:text-emerald-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <span x-show="!sidebarCollapsed" class="flex-1 text-left text-xs font-semibold tracking-wide">DATA UTAMA</span>
                    <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-180' : ''" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="open && !sidebarCollapsed" x-transition.opacity.duration.300ms class="mt-1 ml-12 space-y-2 border-l border-white/5 py-1">
                    <a href="#" class="block pl-4 py-1.5 text-[11px] font-medium text-slate-500 hover:text-emerald-400 transition">Tingkat Kesatuan</a>
                    <a href="#" class="block pl-4 py-1.5 text-[11px] font-medium text-slate-500 hover:text-emerald-400 transition">Jabatan</a>
                </div>
            </div>
        </nav>

        <div class="p-6 border-t border-white/5 bg-slate-900 shrink-0">
            <div x-show="!sidebarCollapsed" class="bg-white/[0.02] p-3 rounded-xl border border-white/5 text-center">
                <p class="text-[9px] text-slate-500 font-bold tracking-widest uppercase">v1.0.26</p>
                <p class="text-[8px] text-slate-600 mt-1 uppercase">SATGAS PANGAN JATIM</p>
            </div>
            <div x-show="sidebarCollapsed" class="text-center text-[9px] text-slate-600 font-bold">V1.0</div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 px-6 flex items-center justify-between md:hidden shrink-0">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <span class="font-bold text-slate-800 tracking-tight">SIKAP PRESISI</span>
            <div class="w-10"></div> </header>
        
        <main class="flex-1 p-6 md:p-10 overflow-x-hidden">
            {{ $slot }}
        </main>
    </div>
</div>