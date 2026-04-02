<aside class="w-64 bg-slate-900 text-white flex-shrink-0 min-h-screen shadow-xl">
    <div class="p-6 text-center border-b border-slate-800">
        <h1 class="text-xl font-bold tracking-widest uppercase text-emerald-400">PANGAN PRASISI</h1>
        <p class="text-xs text-slate-400 mt-1">IAM Managed System</p>
    </div>

    <nav class="mt-6 px-4 space-y-2">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
            Dashboard
        </x-nav-link>

        @hasrole('admin')
        <div class="pt-4 pb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Master Data</div>
        <x-nav-link href="/anggota" :active="request()->is('anggota*')" icon="users">
            Kelola Anggota
        </x-nav-link>
        @endhasrole

        <div class="pt-4 pb-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Operasional</div>
        <x-nav-link href="/lahan" :active="request()->is('lahan*')" icon="map">
            Data Lahan
        </x-nav-link>
    </nav>
</aside>