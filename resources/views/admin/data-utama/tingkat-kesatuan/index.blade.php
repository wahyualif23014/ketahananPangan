@extends('layouts.app')

@section('header', 'Tingkat Kesatuan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    
    .kesatuan-container {
        font-family: 'Outfit', sans-serif;
    }
    
    [x-cloak] {
        display: none !important;
    }

    .topo-pattern {
        background-color: transparent;
        background-image: radial-gradient(#10b981 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.1;
    }
</style>

<div class="space-y-8 pb-24 kesatuan-container max-w-7xl mx-auto">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Tingkat Kesatuan</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                KESATUAN <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-indigo-500">WILAYAH</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Hierarki kepolisian berdasarkan klasifikasi struktur penanggung jawab wilayah.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                    id="search-kesatuan"
                    placeholder="CARI KESATUAN..." 
                    value="{{ $search ?? '' }}"
                    @input.debounce.400ms="
                        const q = $event.target.value;
                        const url = new URL(window.location.href);
                        if (q) url.searchParams.set('search', q);
                        else url.searchParams.delete('search');
                        url.searchParams.delete('page');
                        window.location.href = url.toString();
                    "
                    class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase shadow-sm">
            </div>
            <button onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-slate-900 text-emerald-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    @php
        // Stats Counters (dari data keseluruhan)
        $totalPolres = $allTingkatFull->filter(fn($i) => count(explode('.', $i->id_tingkat)) == 2)->count();
        $totalPolsek = $allTingkatFull->filter(fn($i) => count(explode('.', $i->id_tingkat)) == 3)->count();
    @php

    {{-- Stats Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative px-2">
        <!-- Decor pattern behind cards -->
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-105"></div>
        <div class="absolute inset-0 topo-pattern -z-10 rounded-[3rem]"></div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TINGKAT MENEGAH</p>
                <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-emerald-500 text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalPolres }}; let duration = 1000; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Polres
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-teal-500 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30 relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
        </div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TINGKAT DASAR</p>
                <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-blue-500 text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalPolsek }}; let duration = 1500; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Polsek
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30 relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
    </div>

    {{-- Main Tree Accordion --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-2 mt-12 bg-clip-padding backdrop-filter backdrop-blur-3xl bg-opacity-70">
        
        <!-- Header Panel -->
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR KESATUAN (POLDA &amp; POLRES)</h3>
                @if($search)
                <span class="text-[10px] font-black text-emerald-300 bg-emerald-500/20 px-2.5 py-1 rounded-lg border border-emerald-400/30 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    "{{ $search }}"
                </span>
                <a href="{{ route('admin.tingkat-kesatuan.index') }}" class="text-[10px] font-black text-rose-300 bg-rose-500/20 px-2 py-1 rounded-lg border border-rose-400/30 hover:bg-rose-500/40 transition-colors">
                    ✕ Hapus Filter
                </a>
                @endif
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-emerald-400 bg-emerald-400/20 px-3 py-1.5 rounded-lg border border-emerald-400/30">
                PENGELOMPOKAN WILAYAH
            </div>
        </div>

        <!-- Accordion Loop -->
        <div class="divide-y divide-slate-100/80">
            @forelse($kategoriList as $kategori)
                @php
                    $isPolda = count(explode('.', $kategori->id_tingkat)) == 1;
                    
                    // Ambil Polsek dibawahnya hanya jika dia Polres (level 2)
                    $polsekList = collect();
                    if (!$isPolda) {
                        $polsekList = $allTingkatFull->filter(function ($item) use ($kategori) {
                            $parts = explode('.', $item->id_tingkat);
                            return count($parts) == 3 && str_starts_with($item->id_tingkat, $kategori->id_tingkat . '.');
                        })->sortBy('id_tingkat');
                    }
                    
                    $pj = $tingkatWilayah->get($kategori->id_tingkat);
                @endphp

                <div x-data="{ expanded: {{ $search ? 'true' : 'false' }} }" 
                     class="group/parent transition-all duration-300 hover:bg-slate-50/50"
                     :class="expanded ? 'bg-slate-50/50' : ''">
                     
                    <!-- Root Item Header -->
                    <div @click="expanded = !expanded" class="p-6 md:px-8 cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start md:items-center gap-5">
                            
                            <!-- Toggle Button -->
                            <div :class="expanded ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/40 rotate-180' : 'bg-white text-slate-400 shadow-sm border border-slate-200 group-hover/parent:border-emerald-300 group-hover/parent:text-emerald-500'"
                                class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl md:text-2xl font-black text-slate-800 uppercase tracking-tight group-hover/parent:text-emerald-600 transition-colors">
                                        {{ $kategori->nama_tingkat }}
                                    </h3>
                                    
                                    @if($isPolda)
                                        <span class="inline-flex items-center justify-center bg-gradient-to-r from-amber-400 to-orange-500 text-white px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                                            POLDA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center bg-gradient-to-r from-indigo-500 to-blue-500 text-white px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                                            POLRES
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-2 md:gap-4 text-xs font-semibold uppercase tracking-wider text-slate-500 mt-1">
                                    <div class="flex items-center gap-2 bg-white/80 px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
                                        <span class="text-slate-400">PJ:</span>
                                        <span class="{{ isset($pj) && $pj->nama_anggota ? 'text-slate-800' : 'text-slate-400 italic' }} font-bold">
                                            {{ $pj->nama_anggota ?? 'BELUM DITENTUKAN' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 004.815 4.815l.773-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
                                        {{ $pj->no_telp_anggota ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Stats/ID -->
                        <div class="flex items-center md:justify-end gap-4 md:pl-0 pl-16">
                            @if(!$isPolda)
                                <div class="flex flex-col items-center justify-center px-4 py-2 bg-blue-50/50 rounded-xl border border-blue-100/50">
                                    <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Polsek</span>
                                    <span class="text-lg font-black text-blue-600 leading-none">{{ $polsekList->count() }}</span>
                                </div>
                            @endif
                            <div class="flex flex-col items-end justify-center px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl shadow-inner">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ID Registry</span>
                                <span class="text-sm font-black text-slate-700 leading-none">{{ $kategori->id_tingkat }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Polsek Sub-List Dropsdown -->
                    @if(!$isPolda && $polsekList->isNotEmpty())
                        <div x-show="expanded" x-collapse>
                            <div class="px-4 pb-6 md:px-8 md:pl-16">
                                <div class="pl-7 relative border-l-[3px] border-emerald-200/60 pb-2">
                                    <div class="w-full h-px bg-slate-200 my-4 mb-6"></div>
                                    <div class="space-y-4">
                                        @foreach($polsekList as $polsek)
                                            @php
                                                $pjPolsek = $tingkatWilayah->get($polsek->id_tingkat);
                                            @endphp
                                            <div class="relative bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-blue-300 hover:-translate-y-1 transition-all duration-300 group/polsek">
                                                <!-- Branch node connector -->
                                                <div class="absolute -left-9 top-1/2 -translate-y-1/2 w-5 border-t-[3px] border-emerald-200/60 z-0"></div>
                                                <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-white shadow-sm z-10 group-hover/polsek:scale-150 transition-transform"></div>

                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-500 flex items-center justify-center font-bold shadow-inner group-hover/polsek:bg-blue-500 group-hover/polsek:text-white transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>
                                                        </div>
                                                        <div class="space-y-1">
                                                            <h4 class="text-base font-black text-slate-800 uppercase tracking-wide group-hover/polsek:text-blue-600 transition-colors">
                                                                {{ $polsek->nama_tingkat }}
                                                            </h4>
                                                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-500 tracking-wider">
                                                                <span>PENANGGUNG JAWAB:</span>
                                                                <span class="{{ isset($pjPolsek) && $pjPolsek->nama_anggota ? 'text-slate-800 font-black' : 'text-slate-400 italic font-semibold' }}">
                                                                    {{ $pjPolsek->nama_anggota ?? 'BELUM ADA PJ' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="bg-slate-50 border border-slate-100 text-slate-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-[0.2em] flex-shrink-0 self-start sm:self-center">
                                                        {{ $polsek->id_tingkat }}
                                                    </div>
                                                </div>
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
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest max-w-sm mx-auto">Tidak ada data tingkat kesatuan yang ditemukan.</p>
                </div>
            @endforelse
        </div>

        @if($kategoriList->hasPages())
        <div class="px-8 py-5 border-t border-slate-200/60 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                Halaman <span class="text-blue-600">{{ $kategoriList->currentPage() }}</span> dari <span class="text-slate-800">{{ $kategoriList->lastPage() }}</span>
                @if($search) &bull; <span class="text-emerald-600">Filter: "{{ $search }}"</span> @endif
            </div>
            <div class="flex items-center gap-1 sm:gap-2">
                @if ($kategoriList->onFirstPage())
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Mundur</span>
                @else
                    <a href="{{ $kategoriList->appends(['search' => $search])->previousPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-300 text-slate-600 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">Mundur</a>
                @endif
                <div class="hidden sm:flex items-center gap-1 mx-2">
                    @php
                        $startPage = max($kategoriList->currentPage() - 2, 1);
                        $endPage   = min($startPage + 4, $kategoriList->lastPage());
                        if ($endPage - $startPage < 4) $startPage = max($endPage - 4, 1);
                    @endphp
                    @for ($pg = $startPage; $pg <= $endPage; $pg++)
                        @if ($pg == $kategoriList->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center bg-emerald-600 text-white rounded-xl text-xs font-black shadow-md shadow-emerald-500/30">{{ $pg }}</span>
                        @else
                            <a href="{{ $kategoriList->appends(['search' => $search])->url($pg) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-xl text-xs font-black transition-all">{{ $pg }}</a>
                        @endif
                    @endfor
                </div>
                @if ($kategoriList->hasMorePages())
                    <a href="{{ $kategoriList->appends(['search' => $search])->nextPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-emerald-500/30 active:scale-95">Next</a>
                @else
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection