@extends('layouts.app')

@section('header', 'Kelola Hierarki Data Wilayah')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

    .wilayah-container {
        font-family: 'Outfit', sans-serif;
    }

    [x-cloak] {
        display: none !important;
    }

    .topo-pattern {
        background-color: transparent;
        background-image: radial-gradient(#3b82f6 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.1;
    }
</style>

<div class="space-y-8 pb-24 wilayah-container max-w-7xl mx-auto" x-data="{ searchQuery: '' }">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-blue-600 drop-shadow-sm border-b-2 border-blue-600 pb-0.5">Jaringan Wilayah</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                HIERARKI <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-indigo-500">WILAYAH</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Visualisasi pohon data dari tingkat Kabupaten/Kota hingga Desa/Kelurahan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" x-model="searchQuery" placeholder="CARI WILAYAH..." 
                    class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none uppercase shadow-sm">
            </div>
            <button onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-slate-900 text-blue-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    @php
        // Optimize: Use SQL LIKE operators instead of pulling everything into PHP memory
        // Kabupaten: format XX.XX (1 dot)
        $totalKabupaten = DB::table('wilayah')
            ->where('id_wilayah', 'like', '%.%')
            ->whereNot('id_wilayah', 'like', '%.%.%')
            ->count();
            
        // Kecamatan: format XX.XX.XX (2 dots)
        $totalKecamatan = DB::table('wilayah')
            ->where('id_wilayah', 'like', '%.%.%')
            ->whereNot('id_wilayah', 'like', '%.%.%.%')
            ->count();
            
        // Desa: format XX.XX.XX.XXXX (3 dots)
        $totalDesa = DB::table('wilayah')
            ->where('id_wilayah', 'like', '%.%.%.%')
            ->whereNot('id_wilayah', 'like', '%.%.%.%.%')
            ->count();

        // Server-side database pagination for Kabupaten (Root Nodes)
        $perPage = 20;
        $kabupatenList = DB::table('wilayah')
            ->where('id_wilayah', 'like', '%.%')
            ->whereNot('id_wilayah', 'like', '%.%.%')
            ->orderBy('id_wilayah')
            ->paginate($perPage)
            ->withQueryString();

        // To support the tree rendering efficiently in Blade, we fetch ONLY the child nodes
        // (Kecamatan and Desa) that belong strictly to the 5 Kabupaten currently displayed on this page.
        $kabupatenIds = collect($kabupatenList->items())->pluck('id_wilayah')->toArray();
        
        if (!empty($kabupatenIds)) {
            // Get Kecamatans belonging to these Kabupatens
            $kecamatanQuery = DB::table('wilayah')->where('id_wilayah', 'like', '%.%.%')->whereNot('id_wilayah', 'like', '%.%.%.%');
            $kecamatanQuery->where(function($q) use ($kabupatenIds) {
                foreach($kabupatenIds as $id) {
                    $q->orWhere('id_wilayah', 'like', $id . '.%');
                }
            });
            $kecamatans = $kecamatanQuery->get();

            // Get Desas belonging to these Kabupatens
            $desaQuery = DB::table('wilayah')->where('id_wilayah', 'like', '%.%.%.%')->whereNot('id_wilayah', 'like', '%.%.%.%.%');
            $desaQuery->where(function($q) use ($kabupatenIds) {
                foreach($kabupatenIds as $id) {
                    $q->orWhere('id_wilayah', 'like', $id . '.%');
                }
            });
            $desas = $desaQuery->get();

            // Store the scoped collection here so Blade's $allWilayah->filter() still works flawlessly
            $allWilayah = collect($kabupatenList->items())->merge($kecamatans)->merge($desas);
        } else {
            $allWilayah = collect();
        }
    @endphp

    {{-- Stats Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative px-2">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-105"></div>
        <div class="absolute inset-0 topo-pattern -z-10 rounded-[3rem]"></div>

        <!-- Kabupaten Stats -->
        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TINGKAT 1</p>
                <h3 class="text-2xl lg:text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-blue-500 text-3xl lg:text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalKabupaten }}; let duration = 1000; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Kab/Kota
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30 relative z-10 flex-shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>

        <!-- Kecamatan Stats -->
        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-indigo-100 shadow-xl shadow-indigo-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-indigo-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TINGKAT 2</p>
                <h3 class="text-2xl lg:text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-indigo-500 text-3xl lg:text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalKecamatan }}; let duration = 1500; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Kecamatan
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-indigo-600 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-indigo-500/30 relative z-10 flex-shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Desa Stats -->
        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TINGKAT 3</p>
                <h3 class="text-2xl lg:text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-emerald-500 text-3xl lg:text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalDesa }}; let duration = 2000; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Desa
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-emerald-600 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30 relative z-10 flex-shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>
            </div>
        </div>
    </div>


    {{-- Main Tree Accordion --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-2 mt-12 bg-clip-padding backdrop-filter backdrop-blur-3xl bg-opacity-70">
        
        <!-- Header Panel -->
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-1.5 h-8 bg-blue-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR HIERARKI WILAYAH</h3>
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-blue-400 bg-blue-400/20 px-3 py-1.5 rounded-lg border border-blue-400/30">
                LOKASI DAN KOORDINAT
            </div>
        </div>

        <!-- Accordion Loop -->
        <div class="divide-y divide-slate-100/80">
            @forelse($kabupatenList as $kab)
                @php
                    $kecamatanList = $allWilayah->filter(function ($item) use ($kab) {
                        $parts = explode('.', $item->id_wilayah);
                        return count($parts) == 3 && str_starts_with($item->id_wilayah, $kab->id_wilayah . '.');
                    })->sortBy('id_wilayah');
                @endphp

                <div x-data="{ expandedKab: false }" 
                     x-show="searchQuery === '' || '{{ strtolower($kab->nama_wilayah) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($kab->id_wilayah) }}'.includes(searchQuery.toLowerCase())"
                     class="group/kab transition-all duration-300 hover:bg-slate-50/50"
                     :class="expandedKab ? 'bg-slate-50/50' : ''">
                     
                    <!-- Level 1: Kabupaten Item -->
                    <div @click="expandedKab = !expandedKab" class="p-6 md:px-8 cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start md:items-center gap-5">
                            
                            <!-- Toggle Button -->
                            <div :class="expandedKab ? 'bg-blue-600 text-white shadow-md shadow-blue-500/40 rotate-180' : 'bg-white text-slate-400 shadow-sm border border-slate-200 group-hover/kab:border-blue-300 group-hover/kab:text-blue-500'"
                                class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl md:text-2xl font-black text-slate-800 uppercase tracking-tight group-hover/kab:text-blue-600 transition-colors">
                                        {{ $kab->nama_wilayah }}
                                    </h3>
                                    <span class="inline-flex items-center justify-center bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                                        KABUPATEN
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Stats/ID -->
                        <div class="flex items-center md:justify-end gap-4 md:pl-0 pl-16">
                            <div class="flex flex-col items-center justify-center px-4 py-2 bg-indigo-50/50 rounded-xl border border-indigo-100/50">
                                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Kecamatan</span>
                                <span class="text-lg font-black text-indigo-600 leading-none">{{ $kecamatanList->count() }}</span>
                            </div>
                            <div class="flex flex-col items-end justify-center px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl shadow-inner">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ID Wilayah</span>
                                <span class="text-sm font-black text-slate-700 leading-none">{{ $kab->id_wilayah }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Level 2: Kecamatan Sub-List Dropdown -->
                    @if($kecamatanList->isNotEmpty())
                        <div x-show="expandedKab" x-collapse>
                            <div class="px-4 pb-6 md:px-8 md:pl-16">
                                <div class="pl-7 relative border-l-[3px] border-blue-200/60 pb-2">
                                    <div class="w-full h-px bg-slate-200 my-4 mb-6"></div>
                                    <div class="space-y-4">
                                        @foreach($kecamatanList as $kec)
                                            @php
                                                $desaList = $allWilayah->filter(function ($item) use ($kec) {
                                                    $parts = explode('.', $item->id_wilayah);
                                                    return count($parts) == 4 && str_starts_with($item->id_wilayah, $kec->id_wilayah . '.');
                                                })->sortBy('id_wilayah');
                                            @endphp

                                            <div x-data="{ expandedKec: false }" class="relative bg-white p-4 sm:p-5 rounded-[1.5rem] border border-slate-200 shadow-sm hover:shadow-lg transition-all duration-300 group/kec">
                                                
                                                <!-- Level 2 Connector -->
                                                <div class="absolute -left-9 top-8 w-5 border-t-[3px] border-blue-200/60 z-0"></div>
                                                <div class="absolute -left-4 top-8 w-2.5 h-2.5 rounded-full bg-blue-400 border-2 border-white shadow-sm z-10 group-hover/kec:scale-150 transition-transform -translate-y-1/2"></div>

                                                <!-- Kecamatan Header Area -->
                                                <div @click="expandedKec = !expandedKec" class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 cursor-pointer relative z-10">
                                                    <div class="flex items-center gap-4">
                                                        <div :class="expandedKec ? 'bg-indigo-600 text-white shadow-md rotate-180' : 'bg-indigo-50 border border-indigo-100 text-indigo-500 shadow-inner group-hover/kec:bg-indigo-500 group-hover/kec:text-white'" 
                                                             class="w-10 h-10 rounded-xl flex items-center justify-center font-bold transition-all duration-300">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-500 tracking-wider">
                                                                <span class="text-indigo-400 bg-indigo-50 px-2 rounded-md">KECAMATAN</span>
                                                            </div>
                                                            <h4 class="text-base font-black text-slate-800 uppercase tracking-wide group-hover/kec:text-indigo-600 transition-colors">
                                                                {{ $kec->nama_wilayah }}
                                                            </h4>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-[10px] font-bold uppercase text-emerald-500 bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-lg flex items-center gap-1.5">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                            {{ $desaList->count() }} Desa
                                                        </div>
                                                        <div class="bg-slate-50 border border-slate-100 text-slate-600 px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-[0.2em] flex-shrink-0">
                                                            {{ $kec->id_wilayah }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Level 3: Desa Sub-List Dropdown -->
                                                @if($desaList->isNotEmpty())
                                                    <div x-show="expandedKec" x-collapse>
                                                        <div class="relative mt-6 pt-5 pl-4 sm:pl-10 border-t border-slate-100">
                                                            <!-- Vertical line for Desa -->
                                                            <div class="absolute left-6 sm:left-12 top-5 bottom-4 w-px border-l-2 border-dashed border-indigo-200/50"></div>
                                                            
                                                            <div class="space-y-4">
                                                                @foreach($desaList as $desa)
                                                                    <div x-data="{ showMap: false }" class="relative bg-slate-50 hover:bg-emerald-50/30 p-4 rounded-xl border border-slate-100 hover:border-emerald-200 transition-all ml-6 group/desa">
                                                                        <!-- Connector Desa -->
                                                                        <div class="absolute -left-6 sm:-left-10 top-1/2 -translate-y-1/2 w-6 sm:w-10 border-t-2 border-dashed border-indigo-200/50 z-0 group-hover/desa:border-emerald-300 transition-colors"></div>
                                                                        <div class="absolute -left-1 sm:-left-1.5 top-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-indigo-300 z-10 group-hover/desa:bg-emerald-400 group-hover/desa:scale-150 transition-all border border-white"></div>

                                                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                                                                            <div class="flex items-center gap-3">
                                                                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm font-bold flex-shrink-0">
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                                                                </div>
                                                                                <div>
                                                                                    <h5 class="text-sm font-black text-slate-800 uppercase tracking-wide group-hover/desa:text-emerald-700 transition-colors">{{ $desa->nama_wilayah }}</h5>
                                                                                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em] bg-white px-2 py-0.5 rounded border border-slate-100 mt-1 inline-block">{{ $desa->id_wilayah }}</span>
                                                                                </div>
                                                                            </div>

                                                                            <div class="flex items-center gap-2">
                                                                                @if($desa->Latitude && $desa->longitude)
                                                                                    <button @click="showMap = !showMap" 
                                                                                        class="text-[10px] font-bold px-3 py-2 rounded-lg border shadow-sm transition-all flex items-center gap-1.5"
                                                                                        :class="showMap ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-emerald-600 border-emerald-200 hover:bg-emerald-50'">
                                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                                                                        Lihat Peta
                                                                                    </button>
                                                                                @else
                                                                                    <span class="text-[10px] font-bold text-amber-500 bg-amber-50 px-2 py-2 rounded-lg border border-amber-200 shadow-sm">Blm Disetel</span>
                                                                                @endif

                                                                                <button onclick="openMapModal('{{ $desa->id_wilayah }}', '{{ addslashes($desa->nama_wilayah) }}', '{{ $desa->Latitude }}', '{{ $desa->longitude }}')" 
                                                                                    class="text-[10px] font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 shadow-sm transition-all flex items-center gap-1.5">
                                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                                                    Edit Ordinat
                                                                                </button>
                                                                            </div>
                                                                        </div>

                                                                        <!-- Interactive Map Container -->
                                                                        @if($desa->Latitude && $desa->longitude)
                                                                            <div x-show="showMap" x-collapse x-cloak>
                                                                                <div class="mt-4 p-2 bg-white border border-slate-200 rounded-xl relative z-10 shadow-sm">
                                                                                    <iframe width="100%" height="220" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                                                                                            src="https://maps.google.com/maps?q={{ $desa->Latitude }},{{ $desa->longitude }}&hl=id&z=15&output=embed"
                                                                                            class="rounded-lg shadow-inner border border-slate-100 w-full">
                                                                                    </iframe>
                                                                                    <div class="flex justify-between items-center mt-2 px-2 pb-1">
                                                                                        <span class="text-[10px] font-black text-slate-400 tracking-wider">MAPS PREVIEW</span>
                                                                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $desa->Latitude }},{{ $desa->longitude }}" target="_blank" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1 transition-colors">
                                                                                            Buka di Tab Baru 
                                                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif

                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-20 px-4">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-100 rounded-full mb-6">
                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 uppercase mb-2">Data Kosong</h3>
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest max-w-sm mx-auto">Tidak ada hierarki wilayah yang ditemukan.</p>
                </div>
            @endforelse
        </div>

        @if($kabupatenList->hasPages())
        <div class="px-6 py-5 border-t border-slate-200/60 bg-slate-50/80 flex flex-col sm:flex-row justify-between items-center gap-4 rounded-b-[2.5rem]">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                Data ke <span class="text-blue-600">{{ $kabupatenList->firstItem() }}</span> - <span class="text-blue-600">{{ $kabupatenList->lastItem() }}</span> dari total <span class="text-slate-800">{{ $kabupatenList->total() }}</span>
            </div>
            
            <div class="flex items-center gap-1 sm:gap-2">
                {{-- Previous --}}
                @if ($kabupatenList->onFirstPage())
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Mundur</span>
                @else
                    <a href="{{ $kabupatenList->previousPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 text-slate-600 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">Mundur</a>
                @endif

                {{-- Pages --}}
                <div class="hidden sm:flex items-center gap-1 mx-2">
                    @php
                        // Membangun array halaman yang wajar untuk paginasi kustom (Hindari error links()->elements)
                        $startPage = max($kabupatenList->currentPage() - 2, 1);
                        $endPage = min($startPage + 4, $kabupatenList->lastPage());
                        if ($endPage - $startPage < 4) {
                            $startPage = max($endPage - 4, 1);
                        }
                    @endphp

                    @if($startPage > 1)
                        <a href="{{ $kabupatenList->url(1) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-black transition-all">1</a>
                        @if($startPage > 2)
                            <span class="w-9 h-9 flex items-center justify-center text-slate-400 text-xs font-black">...</span>
                        @endif
                    @endif

                    @for ($page = $startPage; $page <= $endPage; $page++)
                        @if ($page == $kabupatenList->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center bg-blue-600 text-white rounded-xl text-xs font-black shadow-md shadow-blue-500/30">{{ $page }}</span>
                        @else
                            <a href="{{ $kabupatenList->url($page) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 rounded-xl text-xs font-black transition-all">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($endPage < $kabupatenList->lastPage())
                        @if($endPage < $kabupatenList->lastPage() - 1)
                            <span class="w-9 h-9 flex items-center justify-center text-slate-400 text-xs font-black">...</span>
                        @endif
                        <a href="{{ $kabupatenList->url($kabupatenList->lastPage()) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-xl text-xs font-black transition-all">{{ $kabupatenList->lastPage() }}</a>
                    @endif
                </div>

                {{-- Next --}}
                @if ($kabupatenList->hasMorePages())
                    <a href="{{ $kabupatenList->nextPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-blue-500/30 active:scale-95">Next</a>
                @else
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Edit Maps Modal -->
<div id="mapModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300 border border-slate-100" id="mapModalContent">
        <div class="px-8 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full opacity-10 text-white transform scale-150" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <h3 class="text-lg font-black text-white uppercase tracking-wider relative z-10 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Perbarui Lokasi
            </h3>
            <button onclick="closeMapModal()" class="relative z-10 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all hover:rotate-90">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form action="{{ route('admin.wilayah.update-lokasi') }}" method="POST" class="p-8">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="id_wilayah" id="modal_id_wilayah">
            
            <div class="mb-6 bg-blue-50/50 border border-blue-100/50 rounded-2xl p-5 text-center">
                <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.3em] mb-1">WILAYAH TERPILIH</p>
                <h4 id="modal_nama_desa" class="text-xl font-black text-blue-900 uppercase">NAMA DESA</h4>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Latitude <span class="text-rose-500">*</span></label>
                    <input type="text" name="latitude" id="modal_latitude" required placeholder="-7.250445" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder:font-normal">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-widest mb-2">Longitude <span class="text-rose-500">*</span></label>
                    <input type="text" name="longitude" id="modal_longitude" required placeholder="112.768845" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder:font-normal">
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeMapModal()" class="flex-1 bg-white border border-slate-200 hover:bg-slate-100 text-slate-600 uppercase tracking-widest text-xs font-bold py-3.5 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white uppercase tracking-widest text-xs font-bold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('mapModal');
    const modalContent = document.getElementById('mapModalContent');
    
    function openMapModal(id, nama, lat, lng) {
        document.getElementById('modal_id_wilayah').value = id;
        document.getElementById('modal_nama_desa').innerText = nama;
        document.getElementById('modal_latitude').value = lat || '';
        document.getElementById('modal_longitude').value = lng || '';
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeMapModal() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
</script>

<!-- Alpine Plugins -->
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection
