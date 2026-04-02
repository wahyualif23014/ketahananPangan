<div x-data="{ sidebarOpen: true, mobileMenu: false }" class="flex min-h-screen bg-slate-100">
    <aside 
        :class="sidebarOpen ? 'w-72' : 'w-20'" 
        class="bg-slate-800 text-white flex-shrink-0 min-h-screen shadow-2xl flex flex-col border-r border-slate-700 transition-all duration-300 ease-in-out fixed md:relative z-40"
        x-show="window.innerWidth > 768 || mobileMenu"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
    >
        <div class="p-6 flex items-center justify-between border-b border-slate-700">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="p-2 bg-emerald-500 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h1 x-show="sidebarOpen" class="text-lg font-black tracking-tighter uppercase whitespace-nowrap">SIKAP <span class="text-emerald-400">PRESISI</span></h1>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="hidden md:block text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <div x-show="sidebarOpen" class="px-6 py-8 text-center border-b border-slate-700 bg-slate-800/50">
            <div class="relative inline-block mb-4">
                <div class="w-16 h-16 rounded-full bg-slate-600 border-2 border-slate-500 flex items-center justify-center text-xl font-bold shadow-lg uppercase">
                    {{ collect(explode(' ', Auth::user()->name))->map(fn($n) => $n[0])->take(2)->implode('') }}
                </div>
                <div class="absolute bottom-0 right-0 w-4 h-4 bg-emerald-500 border-2 border-slate-800 rounded-full"></div>
            </div>
            <h2 class="text-sm font-bold truncate uppercase">{{ Auth::user()->name }}</h2>
            <p class="text-[10px] text-slate-400 mt-1 font-mono">NRP : {{ Auth::user()->nrp }}</p>
            <p class="text-[10px] text-emerald-400 font-bold mt-1 tracking-widest uppercase">POLDA JAWA TIMUR</p>
        </div>

        <nav class="flex-1 mt-4 px-2 space-y-1 overflow-y-auto">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                <span x-show="sidebarOpen">BERANDA</span>
            </x-nav-link>

            <div x-data="{ open: false }">
                <button @click="open = !open" :class="open ? 'bg-slate-700 text-white' : 'text-slate-400'" class="w-full group flex items-center px-4 py-3 text-[13px] font-medium hover:bg-slate-700/50 hover:text-white transition-all rounded-md">
                    <div class="mr-3 text-slate-500 group-hover:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <span x-show="sidebarOpen" class="flex-1 text-left">DATA UTAMA</span>
                    <svg x-show="sidebarOpen" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="open && sidebarOpen" x-cloak class="mt-1 space-y-1 bg-slate-900/30 py-2 rounded-lg">
                    <a href="#" class="block pl-12 pr-4 py-2 text-[12px] text-slate-400 hover:text-white hover:bg-slate-700/30">TINGKAT KESATUAN</a>
                    <a href="#" class="block pl-12 pr-4 py-2 text-[12px] text-slate-400 hover:text-white hover:bg-slate-700/30">JABATAN</a>
                    <a href="#" class="block pl-12 pr-4 py-2 text-[12px] text-slate-400 hover:text-white hover:bg-slate-700/30">WILAYAH</a>
                </div>
            </div>

            @hasrole('admin')
            <div x-show="sidebarOpen" class="pt-4 pb-2 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Master Data</div>
            <x-nav-link href="/anggota" :active="request()->is('anggota*')" icon="users">
                <span x-show="sidebarOpen">DATA PERSONEL</span>
            </x-nav-link>
            @endhasrole

            <div x-show="sidebarOpen" class="pt-4 pb-2 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Operasional</div>
            <x-nav-link href="/lahan" :active="request()->is('lahan*')" icon="map">
                <span x-show="sidebarOpen">DATA KELOLA LAHAN</span>
            </x-nav-link>
        </nav>

        <div x-show="sidebarOpen" class="p-4 border-t border-slate-700 text-[10px] text-slate-500 text-center uppercase tracking-tighter">
            v1.0.26 &copy; SATGAS PANGAN JATIM
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="bg-white shadow-sm py-4 px-6 flex items-center md:hidden">
            <button @click="mobileMenu = !mobileMenu" class="text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <span class="ml-4 font-bold text-slate-800">SIKAP PRESISI</span>
        </header>
        
        <main class="p-6">
            {{ $slot }}
        </main>
    </div>
</div>