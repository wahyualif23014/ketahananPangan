@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    
    .personel-container {
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

@php
    $currentPage = request()->get('page', 1);
    $perPage = 250; // Sesuai permintaan: maksimal data tampil 250 per halaman

    // Paginate data anggota terlebih dahulu sebelum dikelompokkan
    $paginatedPersonels = new \Illuminate\Pagination\LengthAwarePaginator(
        $personels->forPage($currentPage, $perPage),
        $personels->count(),
        $perPage,
        $currentPage,
        ['path' => url()->current(), 'query' => request()->query()]
    );

    // Lalu kelompokkan 250 data yang tampil di halaman ini berdasarkan kesatuan
    $groupedPersonels = collect($paginatedPersonels->items())->groupBy(function ($item) {
        return $item->kesatuan ?? 'PUSAT DATA POLDA JATIM';
    });

    // Menghitung total seluruh kesatuan tanpa terpengaruh paginasi (untuk dashboard statistik)
    $totalKesatuanGlobal = $personels->groupBy('kesatuan')->count();
@endphp

<div class="space-y-8 pb-24 personel-container max-w-7xl mx-auto" 
    x-data="personelApp()"
    x-on:open-modal-personel.window="openModal($event.detail.mode, $event.detail.data)">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-2 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Data Personel</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                PERSONEL <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">ANGGOTA</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Kelola pendaftaran anggota dan otorisasi role per wilayah tugas.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" 
                        id="search-personel"
                        placeholder="CARI ANGGOTA..." 
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
            <button type="button" 
                onclick="window.dispatchEvent(new CustomEvent('open-modal-personel', { detail: { mode: 'add', data: null }}))"
                class="flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-105 active:scale-95 transition-all text-xs font-black uppercase tracking-widest border border-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah
            </button>
        </div>
    </div>


    @if(session('success'))
    <div class="px-2 mb-4">
        <div class="bg-emerald-50/80 backdrop-blur-md border border-emerald-200 text-emerald-700 p-4 rounded-xl shadow-sm flex justify-between animate-in fade-in slide-in-from-top-4" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="font-bold text-sm tracking-wide">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
    @endif

    {{-- Stats Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative px-2 mt-10">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-105"></div>
        <div class="absolute inset-0 topo-pattern -z-10 rounded-[3rem]"></div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL DATA</p>
                <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-emerald-500 text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $personels->count() }}; let duration = 1000; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Personel
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-teal-500 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30 relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL POSISI KESATUAN</p>
                <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Tersebar di <span class="text-blue-500 text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalKesatuanGlobal }}; let duration = 1500; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Kesatuan
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30 relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
        </div>
    </div>


    {{-- Main Tree Accordion --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-2 mt-12 bg-clip-padding backdrop-filter backdrop-blur-3xl bg-opacity-70">
        
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR ANGGOTA (PER KESATUAN)</h3>
                @if($search)
                <span class="text-[10px] font-black text-emerald-300 bg-emerald-500/20 px-2.5 py-1 rounded-lg border border-emerald-400/30 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    "{{ $search }}"
                </span>
                <a href="{{ route('admin.anggota.index') }}" class="text-[10px] font-black text-rose-300 bg-rose-500/20 px-2 py-1 rounded-lg border border-rose-400/30 hover:bg-rose-500/40 transition-colors">
                    ✕ Hapus Filter
                </a>
                @endif
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-emerald-400 bg-emerald-400/20 px-3 py-1.5 rounded-lg border border-emerald-400/30">
                PENGELOMPOKAN WILAYAH
            </div>
        </div>

        <div class="divide-y divide-slate-100/80">
            @forelse($groupedPersonels as $unit => $members)

                <div x-data="{ expanded: {{ $search ? 'true' : 'false' }} }" 
                     class="group/unit transition-all duration-300 hover:bg-slate-50/50"
                     :class="expanded ? 'bg-slate-50/50' : ''">
                     
                    <div @click="expanded = !expanded" class="p-6 md:px-8 cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start md:items-center gap-5">
                            
                            <div :class="expanded ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/40 rotate-180' : 'bg-white text-slate-400 shadow-sm border border-slate-200 group-hover/unit:border-emerald-300 group-hover/unit:text-emerald-500'"
                                class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl md:text-2xl font-black text-slate-800 uppercase tracking-tight group-hover/unit:text-emerald-600 transition-colors">
                                        {{ $unit }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center md:justify-end gap-4 md:pl-0 pl-16">
                            <div class="flex flex-col items-center justify-center px-4 py-2 bg-blue-50/50 rounded-xl border border-blue-100/50">
                                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Total Personel</span>
                                <span class="text-lg font-black text-blue-600 leading-none">{{ $members->count() }}</span>
                            </div>
                        </div>
                    </div>

                    @if($members->isNotEmpty())
                        <div x-show="expanded" x-collapse>
                            <div class="px-4 pb-6 md:px-8 md:pl-16">
                                <div class="pl-7 relative border-l-[3px] border-emerald-200/60 pb-2">
                                    <div class="w-full h-px bg-slate-200 my-4 mb-6"></div>
                                    <div class="space-y-4">
                                        @foreach($members as $p)
                                            <div class="relative bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-emerald-300 hover:-translate-y-1 transition-all duration-300 group/p">
                                                
                                                <div class="absolute -left-9 top-1/2 -translate-y-1/2 w-5 border-t-[3px] border-emerald-200/60 z-0"></div>
                                                <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-white shadow-sm z-10 group-hover/p:scale-150 transition-transform"></div>

                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center font-black shadow-inner shadow-blue-700/50 group-hover/p:rotate-6 transition-all duration-300 border border-blue-400">
                                                            {{ strtoupper(substr($p->nama_anggota, 0, 2)) }}
                                                        </div>
                                                        <div class="space-y-1">
                                                            <h4 class="text-base font-black text-slate-800 uppercase tracking-wide group-hover/p:text-blue-600 transition-colors">
                                                                {{ $p->nama_anggota }}
                                                            </h4>
                                                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-500 tracking-wider">
                                                                <span>NRP:</span> <span class="text-slate-700 font-black">{{ $p->username }}</span>
                                                                <span class="mx-1 text-slate-300">•</span>
                                                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 004.815 4.815l.773-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
                                                                <span class="text-slate-700 font-bold">{{ $p->no_telp_anggota ?? 'BELUM DIATUR' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <div class="bg-indigo-50 border border-indigo-100 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                                                            {{ $p->jabatan->nama_jabatan ?? 'STAFF' }}
                                                        </div>
                                                        <div class="bg-blue-50 border border-blue-100 text-blue-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-sm flex items-center gap-1.5">
                                                            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></div>
                                                            {{ $p->role }}
                                                        </div>
                                                        
                                                        <div class="flex gap-2 opacity-0 group-hover/p:opacity-100 transition-opacity duration-300 delay-100">
                                                            <button 
                                                                type="button"
                                                                onclick="window.dispatchEvent(new CustomEvent('open-modal-personel', { detail: {
                                                                    mode: 'edit',
                                                                    data: {
                                                                        id_anggota: '{{ $p->id_anggota }}',
                                                                        nama_anggota: '{{ addslashes($p->nama_anggota) }}',
                                                                        username: '{{ addslashes($p->username) }}',
                                                                        id_jabatan: '{{ $p->id_jabatan }}',
                                                                        role: '{{ $p->role }}',
                                                                        id_tugas: '{{ addslashes($p->id_tugas) }}',
                                                                        no_telp_anggota: '{{ addslashes($p->no_telp_anggota) }}'
                                                                    }
                                                                }}))"
                                                                class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white shadow-sm transition-all active:scale-95">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            </button>
                                                            <button 
                                                                type="button"
                                                                onclick="window.dispatchEvent(new CustomEvent('open-modal-personel', { detail: {
                                                                    mode: 'delete',
                                                                    data: {
                                                                        id_anggota: '{{ $p->id_anggota }}',
                                                                        nama_anggota: '{{ addslashes($p->nama_anggota) }}',
                                                                        username: '{{ $p->username }}',
                                                                        id_jabatan: '{{ $p->id_jabatan }}',
                                                                        role: '{{ $p->role }}',
                                                                        id_tugas: '{{ $p->id_tugas }}',
                                                                        no_telp_anggota: '{{ $p->no_telp_anggota }}'
                                                                    }
                                                                }}))"
                                                                class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white shadow-sm transition-all active:scale-95">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                            </button>
                                                        </div>
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
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest max-w-sm mx-auto mb-6">Database personel masih kosong atau pencarian tidak cocok.</p>
                    <button @click="openModal('add')" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl shadow-md transition-colors text-sm uppercase tracking-wider inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Pendaftaran Personel Baru
                    </button>
                </div>
            @endforelse
        </div>

        @if($paginatedPersonels->hasPages())
        <div class="px-6 py-5 border-t border-slate-200/60 bg-slate-50/80 flex flex-col sm:flex-row justify-between items-center gap-4 rounded-b-[2.5rem]">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                Data ke <span class="text-blue-600">{{ $paginatedPersonels->firstItem() }}</span> - <span class="text-blue-600">{{ $paginatedPersonels->lastItem() }}</span> dari total <span class="text-slate-800">{{ $paginatedPersonels->total() }}</span>
                @if($search) &bull; <span class="text-emerald-600">Filter: "{{ $search }}"</span> @endif
            </div>
            <div class="flex items-center gap-1 sm:gap-2">
                @if ($paginatedPersonels->onFirstPage())
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Mundur</span>
                @else
                    <a href="{{ $paginatedPersonels->appends(['search' => $search])->previousPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-300 text-slate-600 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">Mundur</a>
                @endif
                <div class="hidden sm:flex items-center gap-1 mx-2">
                    @php
                        $startPage = max($paginatedPersonels->currentPage() - 2, 1);
                        $endPage   = min($startPage + 4, $paginatedPersonels->lastPage());
                        if ($endPage - $startPage < 4) $startPage = max($endPage - 4, 1);
                    @endphp
                    @for ($pg = $startPage; $pg <= $endPage; $pg++)
                        @if ($pg == $paginatedPersonels->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center bg-emerald-600 text-white rounded-xl text-xs font-black shadow-md shadow-emerald-500/30">{{ $pg }}</span>
                        @else
                            <a href="{{ $paginatedPersonels->appends(['search' => $search])->url($pg) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-xl text-xs font-black transition-all">{{ $pg }}</a>
                        @endif
                    @endfor
                </div>
                @if ($paginatedPersonels->hasMorePages())
                    <a href="{{ $paginatedPersonels->appends(['search' => $search])->nextPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-emerald-500/30 active:scale-95">Next</a>
                @else
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Next</span>
                @endif
            </div>
        </div>
        @endif
        </div>

    {{-- Universal Modal Component (Desain Baru yang Sederhana) --}}
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-modal="true">
        <div x-show="isModalOpen" x-transition.opacity.duration.300ms @click="closeModal()" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        
        <div x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative z-10 w-full max-w-lg flex flex-col bg-white rounded-xl shadow-lg overflow-hidden max-h-[95vh]">

            <form id="personel-form"
                x-bind:action="getFormAction()"
                method="POST"
                @submit.prevent="submitForm($el)">
                @csrf
                <input type="hidden" name="_method" x-bind:value="getFormMethod()">
                <input type="hidden" name="id_anggota_hidden" x-model="formData.id_anggota">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800" x-text="getModalTitle()"></h3>
                        <p class="text-sm text-slate-500" x-text="getModalSubtitle()"></p>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 overflow-y-auto max-h-[65vh]">
                    @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- DELETE MODE --}}
                    <template x-if="modalMode === 'delete'">
                        <div class="py-4 text-center">
                            <p class="text-slate-700">Kamu yakin ingin mencopot personel <strong class="text-slate-900 font-bold" x-text="formData.nama_anggota"></strong>?</p>
                            <p class="text-sm text-slate-500 mt-2">Data ini akan dihapus dari sistem secara permanen.</p>
                        </div>
                    </template>

                    {{-- ADD / EDIT MODE --}}
                    <template x-if="modalMode !== 'delete'">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">ID Anggota</label>
                                    <input type="number" name="id_anggota" x-model="formData.id_anggota"
                                        :required="modalMode === 'add'"
                                        :readonly="modalMode === 'edit'"
                                        :class="modalMode === 'edit' ? 'bg-slate-100 cursor-not-allowed' : ''"
                                        placeholder="Contoh: 1001" 
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">NRP / Username</label>
                                    <input type="text" name="username" x-model="formData.username" required placeholder="NRP Personel" 
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap & Pangkat</label>
                                <input type="text" name="nama_anggota" x-model="formData.nama_anggota" required placeholder="Contoh: IPTU BAMBANG SETIAWAN" 
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan</label>
                                    <select name="id_jabatan" x-model="formData.id_jabatan" required 
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow bg-white">
                                        <option value="" disabled selected>Pilih Jabatan</option>
                                        @foreach($jabatans as $j)
                                            <option value="{{ $j->id_jabatan }}">{{ $j->nama_jabatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Role Akses</label>
                                    <select name="role" x-model="formData.role" required 
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow bg-white">
                                        <option value="view">View</option>
                                        <option value="operator">Operator</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">ID Wilayah Tugas</label>
                                    <input type="text" name="id_tugas" x-model="formData.id_tugas" placeholder="Misal: 11.01" 
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Telepon / WA</label>
                                    <input type="text" name="no_telp_anggota" x-model="formData.no_telp_anggota" placeholder="0812xxxxxxxx" 
                                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                </div>
                            </div>

                            <template x-if="modalMode === 'add'">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-200">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Sandi</label>
                                        <input type="password" name="password" required placeholder="Minimal 8 karakter" 
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Ulangi Sandi</label>
                                        <input type="password" name="password_confirmation" required placeholder="Ketik ulang sandi" 
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                    </div>
                                </div>
                            </template>

                            <template x-if="modalMode === 'edit'">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-200">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Sandi Baru <span class="text-slate-400 font-normal text-xs">(opsional)</span></label>
                                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" 
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Sandi</label>
                                        <input type="password" name="password_confirmation" placeholder="Ulangi sandi baru" 
                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-shadow">
                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
                    <button type="button" @click="closeModal()" 
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" 
                        :class="modalMode === 'delete' ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'" 
                        x-text="getSubmitText()">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function personelApp() {
        return {
            isModalOpen: {{ $errors->any() ? 'true' : 'false' }},
            modalMode: {!! $errors->any() ? "'add'" : 'null' !!},
            formData: {
                id_anggota: '{{ old('id_anggota') }}',
                nama_anggota: '{!! addslashes(old('nama_anggota')) !!}',
                username: '{!! addslashes(old('username')) !!}',
                id_jabatan: '{{ old('id_jabatan') }}',
                role: '{{ old('role', 'view') }}',
                id_tugas: '{!! addslashes(old('id_tugas')) !!}',
                no_telp_anggota: '{!! addslashes(old('no_telp_anggota')) !!}'
            },

            openModal(mode, data = null) {
                this.modalMode = mode;
                this.isModalOpen = true;
                if (mode === 'add') {
                    this.formData = { id_anggota: '', nama_anggota: '', username: '', id_jabatan: '', role: 'view', id_tugas: '', no_telp_anggota: '' };
                } else if (data) {
                    this.formData = {
                        id_anggota: data.id_anggota,
                        nama_anggota: data.nama_anggota,
                        username: data.username,
                        id_jabatan: String(data.id_jabatan),
                        role: data.role,
                        id_tugas: data.id_tugas,
                        no_telp_anggota: data.no_telp_anggota
                    };
                }
            },

            closeModal() {
                this.isModalOpen = false;
                this.modalMode = null;
            },

            getModalTitle() {
                if (this.modalMode === 'add') return 'Pendaftaran Anggota Baru';
                if (this.modalMode === 'edit') return 'Edit Data Anggota';
                if (this.modalMode === 'delete') return 'Copot Personel';
                return '';
            },

            getModalSubtitle() {
                if (this.modalMode === 'add') return 'Isi data berikut untuk mendaftarkan personel baru.';
                if (this.modalMode === 'edit') return 'Perbarui informasi data personel yang dipilih.';
                if (this.modalMode === 'delete') return 'Tindakan ini tidak dapat dibatalkan.';
                return '';
            },

            getSubmitText() {
                if (this.modalMode === 'add') return 'Simpan Data';
                if (this.modalMode === 'edit') return 'Simpan Perubahan';
                if (this.modalMode === 'delete') return 'Ya, Copot Sekarang';
                return '';
            },

            getFormAction() {
                if (this.modalMode === 'add')    return '{{ route('admin.anggota.store') }}';
                if (this.modalMode === 'edit')   return '/admin/anggota/' + this.formData.id_anggota;
                if (this.modalMode === 'delete') return '/admin/anggota/' + this.formData.id_anggota;
                return '#';
            },

            getFormMethod() {
                if (this.modalMode === 'add')    return 'POST';
                if (this.modalMode === 'edit')   return 'PUT';
                if (this.modalMode === 'delete') return 'DELETE';
                return 'POST';
            },

            submitForm(form) {
                const targetAction = this.getFormAction();
                const targetMethod = this.getFormMethod();

                if (targetAction === '#') {
                    console.error('Form action is invalid');
                    return;
                }

                form.action = targetAction;

                const methodInput = form.querySelector('[name="_method"]');
                if (methodInput) {
                    methodInput.value = targetMethod;
                }

                form.submit();
            }
        };
    }
</script>

@endsection