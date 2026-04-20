@extends('layouts.app')

@section('header', 'Kelola Komoditi Lahan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    
    .komoditi-container {
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

<div class="space-y-8 pb-24 komoditi-container max-w-7xl mx-auto" x-data="komoditiApp()">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-2 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Daftar Komoditi</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                KATALOG <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">KOMODITI</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Klasifikasi dan pendataan jenis tanaman komoditas untuk pengelolaan potensi lahan.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" x-model="searchQuery" placeholder="CARI KOMODITI..." 
                    class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase shadow-sm">
            </div>

            <button onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-slate-900 text-emerald-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <button @click="openModal('add')" 
                class="flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-105 active:scale-95 transition-all text-xs font-black uppercase tracking-widest border-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah
            </button>
        </div>
    </div>

    {{-- Error / Success Alerts --}}
    @if(session('success'))
    <div class="px-2">
        <div class="bg-emerald-50/80 backdrop-blur-md border border-emerald-200 text-emerald-700 p-4 rounded-xl shadow-sm mb-2 flex justify-between animate-in fade-in slide-in-from-top-4" x-data="{ show: true }" x-show="show">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="font-bold text-sm tracking-wide">{{ session('success') }}</p>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-800"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    </div>
    @endif

    @php
        // Fetch and group Komoditi logic
        $allKomoditi = DB::table('komoditi')
            ->where('deletestatus', '!=', '0')
            ->get();
            
        $totalItems = $allKomoditi->count();
            
        $groupedKomoditiTotal = $allKomoditi->groupBy('jenis_komoditi');

        $currentPage = request()->get('page', 1);
        $perPage = 6;

        $groupedKomoditi = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedKomoditiTotal->forPage($currentPage, $perPage),
            $groupedKomoditiTotal->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current(), 'query' => request()->query()]
        );
    @endphp

    {{-- Stats Dashboard --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative px-2">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-105"></div>
        <div class="absolute inset-0 topo-pattern -z-10 rounded-[3rem]"></div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL DATA</p>
                <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-emerald-500 text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ $totalItems }}; let duration = 1000; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Tanaman
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-teal-500 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30 relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
            </div>
        </div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL KATEGORI</p>
                <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-blue-500 text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ count($groupedKomoditiTotal) }}; let duration = 1500; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Jenis komoditi
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30 relative z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
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
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR HIERARKI KOMODITI</h3>
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-emerald-400 bg-emerald-400/20 px-3 py-1.5 rounded-lg border border-emerald-400/30">
                PENGELOMPOKAN TANAMAN
            </div>
        </div>

        <!-- Accordion Loop -->
        <div class="divide-y divide-slate-100/80">
            @forelse($groupedKomoditi as $jenis => $items)

                <div x-data="{ expandedJenis: false }" 
                     x-show="searchQuery === '' || '{{ strtolower($jenis) }}'.includes(searchQuery.toLowerCase()) || JSON.stringify({{ json_encode($items) }}).toLowerCase().includes(searchQuery.toLowerCase())"
                     class="group/jenis transition-all duration-300 hover:bg-slate-50/50"
                     :class="expandedJenis ? 'bg-slate-50/50' : ''">
                     
                    <!-- Root Item Header (Jenis Komoditi) -->
                    <div @click="expandedJenis = !expandedJenis" class="p-6 md:px-8 cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start md:items-center gap-5">
                            
                            <!-- Toggle Button -->
                            <div :class="expandedJenis ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/40 rotate-180' : 'bg-white text-slate-400 shadow-sm border border-slate-200 group-hover/jenis:border-emerald-300 group-hover/jenis:text-emerald-500'"
                                class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl md:text-2xl font-black text-slate-800 uppercase tracking-tight group-hover/jenis:text-emerald-600 transition-colors">
                                        {{ $jenis }}
                                    </h3>
                                    <span class="inline-flex items-center justify-center bg-gradient-to-r from-emerald-400 to-teal-500 text-white px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                                        KATEGORI POKOK
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Stats & Actions -->
                        <div class="flex items-center md:justify-end gap-4 md:pl-0 pl-16">
                            
                            <button @click.stop="openModal('add_tanaman', '{{ addslashes($jenis) }}')" title="Tambah tanaman ke dalam kategori {{ $jenis }}" class="flex items-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl border border-emerald-200 transition-all shadow-sm text-[10px] font-black uppercase tracking-wider group/addbtn active:scale-95 z-20">
                                <svg class="w-4 h-4 group-hover/addbtn:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span class="hidden sm:inline">Tambah Tanaman</span>
                                <span class="sm:hidden">Tambah</span>
                            </button>

                            <div class="flex flex-col items-center justify-center px-4 py-2 bg-blue-50/50 rounded-xl border border-blue-100/50">
                                <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Sub-Item</span>
                                <span class="text-lg font-black text-blue-600 leading-none">{{ $items->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Child Items (Nama Komoditi) -->
                    @if($items->isNotEmpty())
                        <div x-show="expandedJenis" x-collapse>
                            <div class="px-4 pb-6 md:px-8 md:pl-16">
                                <div class="pl-7 relative border-l-[3px] border-emerald-200/60 pb-2">
                                    <div class="w-full h-px bg-slate-200 my-4 mb-6"></div>
                                    <div class="space-y-4">
                                        @foreach($items as $item)
                                            <div class="relative bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-emerald-300 hover:-translate-y-1 transition-all duration-300 group/item"
                                                 x-show="searchQuery === '' || '{{ strtolower($item->nama_komoditi) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($item->id_komoditi) }}'.includes(searchQuery.toLowerCase())">
                                                
                                                <!-- Branch node connector -->
                                                <div class="absolute -left-9 top-1/2 -translate-y-1/2 w-5 border-t-[3px] border-emerald-200/60 z-0"></div>
                                                <div class="absolute -left-4 top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-white shadow-sm z-10 group-hover/item:scale-150 transition-transform"></div>

                                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-emerald-500 flex items-center justify-center font-black shadow-inner group-hover/item:bg-emerald-500 group-hover/item:text-white transition-colors">
                                                            {{ strtoupper(substr($item->nama_komoditi, 0, 2)) }}
                                                        </div>
                                                        <div class="space-y-1">
                                                            <h4 class="text-base font-black text-slate-800 uppercase tracking-wide group-hover/item:text-emerald-600 transition-colors">
                                                                {{ $item->nama_komoditi }}
                                                            </h4>
                                                            <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-500 tracking-wider">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                TGL INPUT: <span class="text-slate-700">{{ date('d M Y', strtotime($item->datetransaction)) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-3 self-start sm:self-center">
                                                        <div class="bg-slate-50 border border-slate-100 text-slate-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-[0.2em] shadow-inner">
                                                            #{{ $item->id_komoditi }}
                                                        </div>
                                                        
                                                        <div class="flex gap-2">
                                                            <button @click="openModal('edit', {{ json_encode($item) }})" class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white shadow-sm transition-all active:scale-95">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                            </button>
                                                            <button @click="openModal('delete', {{ json_encode($item) }})" class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white shadow-sm transition-all active:scale-95">
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
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest max-w-sm mx-auto mb-6">Database komoditi masih kosong atau pencarian tidak cocok.</p>
                    <button @click="openModal('add')" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl shadow-md transition-colors text-sm uppercase tracking-wider inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Buat Data Pertama
                    </button>
                </div>
            @endforelse
        </div>

        @if($groupedKomoditi->hasPages())
        <div class="px-8 py-5 border-t border-slate-200/60 bg-slate-50/50">
            {{ $groupedKomoditi->links() }}
        </div>
        @endif
    </div>

    {{-- Universal Modal Component --}}
    <div x-show="modalMode !== null" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="modalMode !== null" x-transition.opacity.duration.300ms @click="closeModal()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        
        <!-- Modal Panel -->
        <div x-show="modalMode !== null" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-[2rem] shadow-2xl shadow-emerald-900/20 w-full max-w-lg relative z-10 flex flex-col overflow-hidden border border-slate-100">
            
            <form :action="getFormAction()" method="POST">
                @csrf
                <input type="hidden" name="_method" x-bind:value="getFormMethod()">
                <input type="hidden" name="id_komoditi" x-model="formData.id_komoditi" x-if="modalMode === 'edit' || modalMode === 'delete'">
                
                <!-- Dynamic Header -->
                <div class="px-8 py-5 border-b border-slate-100" :class="modalMode === 'delete' ? 'bg-rose-50' : 'bg-slate-50'">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-inner"
                                 :class="modalMode === 'delete' ? 'bg-rose-500' : (modalMode === 'edit' ? 'bg-blue-500' : 'bg-emerald-500')">
                                <template x-if="modalMode === 'add'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg></template>
                                <template x-if="modalMode === 'edit'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></template>
                                <template x-if="modalMode === 'delete'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></template>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold tracking-widest uppercase text-slate-400">Pusat Aksi</p>
                                <h3 class="text-xl font-black text-slate-800 uppercase" x-text="getModalTitle()"></h3>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 bg-white hover:bg-slate-200 p-2 rounded-xl transition-colors border border-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Form Body -->
                <div class="px-8 py-6 space-y-5">
                    
                    <template x-if="modalMode === 'delete'">
                        <div class="bg-white border-2 border-rose-200 rounded-2xl p-5 text-center shadow-sm">
                            <h4 class="text-lg font-bold text-slate-800">Menghapus Komoditi?</h4>
                            <p class="text-sm text-slate-500 font-medium mt-1 mb-3">Tindakan ini akan menghapus komoditi (<span class="text-rose-600 font-bold" x-text="formData.nama_komoditi"></span>) dari peredaran data. Apakah Anda yakin?</p>
                        </div>
                    </template>

                    <template x-if="modalMode !== 'delete'">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                                Jenis Komoditi <span class="text-rose-500">*</span>
                                <span x-show="isJenisLocked" class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded ml-2 border border-emerald-200">TERKUNCI</span>
                            </label>
                            <input type="text" name="jenis_komoditi" list="kategori-list" x-model="formData.jenis_komoditi" required placeholder="Pilih kategori dari daftar atau ketik kategori baru..." 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 font-bold focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all placeholder:font-normal uppercase tracking-wide"
                                :class="isJenisLocked ? 'cursor-not-allowed opacity-80' : ''"
                                :readonly="isJenisLocked">
                            
                            <datalist id="kategori-list">
                                @foreach($groupedKomoditiTotal->keys() as $jk)
                                    <option value="{{ $jk }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </template>
                    
                    <template x-if="modalMode !== 'delete'">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Nama Spesifik Komoditi <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_komoditi" x-model="formData.nama_komoditi" required placeholder="Contoh: Padi Ketan Putih" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 font-bold focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all placeholder:font-normal uppercase tracking-wide">
                        </div>
                    </template>

                </div>

                <!-- Form Footer -->
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button type="button" @click="closeModal()" class="flex-1 bg-white hover:bg-slate-100 text-slate-600 font-bold py-3.5 rounded-xl transition-colors border border-slate-200 uppercase tracking-widest text-xs">
                        Batal
                    </button>
                    <button type="submit" 
                        class="flex-1 text-white font-bold py-3.5 rounded-xl shadow-md transition-colors uppercase tracking-widest text-xs"
                        :class="modalMode === 'delete' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' : (modalMode === 'edit' ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/30' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30')"
                        x-text="getSubmitText()">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function komoditiApp() {
        return {
            searchQuery: '',
            modalMode: null, // 'add', 'edit', 'delete'
            isJenisLocked: false,
            formData: {
                id_komoditi: '',
                jenis_komoditi: '',
                nama_komoditi: ''
            },
            
            openModal(mode, data = null) {
                if (mode === 'add') {
                    this.modalMode = 'add';
                    this.isJenisLocked = false;
                    this.formData = { id_komoditi: '', jenis_komoditi: '', nama_komoditi: '' };
                } else if (mode === 'add_tanaman') {
                    this.modalMode = 'add'; // Re-use the Add form route and title
                    this.isJenisLocked = true;
                    this.formData = { id_komoditi: '', jenis_komoditi: data, nama_komoditi: '' };
                } else if (data) {
                    this.modalMode = mode;
                    this.isJenisLocked = false;
                    this.formData = { 
                        id_komoditi: data.id_komoditi, 
                        jenis_komoditi: data.jenis_komoditi, 
                        nama_komoditi: data.nama_komoditi 
                    };
                }
            },
            
            closeModal() {
                this.modalMode = null;
            },
            
            getModalTitle() {
                if (this.modalMode === 'add') return 'Tambah Komoditi';
                if (this.modalMode === 'edit') return 'Edit Komoditi';
                if (this.modalMode === 'delete') return 'Hapus Komoditi';
                return '';
            },

            getSubmitText() {
                if (this.modalMode === 'add') return 'Simpan Baru';
                if (this.modalMode === 'edit') return 'Simpan Perubahan';
                if (this.modalMode === 'delete') return 'Konfirmasi Hapus';
                return '';
            },
            
            getFormAction() {
                if (this.modalMode === 'add') return "{{ route('admin.komoditi.store') }}";
                if (this.modalMode === 'edit') return "{{ route('admin.komoditi.update') }}";
                if (this.modalMode === 'delete') return "{{ route('admin.komoditi.destroy') }}";
                return "#";
            },
            
            getFormMethod() {
                if (this.modalMode === 'add') return "POST";
                if (this.modalMode === 'edit') return "PUT";
                if (this.modalMode === 'delete') return "DELETE";
                return "POST";
            }
        }
    }
</script>

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection