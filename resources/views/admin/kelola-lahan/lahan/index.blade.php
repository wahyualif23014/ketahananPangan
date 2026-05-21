@extends('layouts.app')

@section('header', 'Data Produksi Lahan')

@section('content')
@php
$routePrefix = auth()->user()->role === 'admin' ? 'admin' : 'operator';
$isPolres = auth()->user()->role === 'admin' ||
(auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1);
@endphp
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

    .kelola-container {
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

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(241, 245, 249, 0.5);
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
</style>

<div class="space-y-8 pb-24 kelola-container max-w-[1600px] mx-auto" x-data="kelolaLahan()">

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Produksi Lahan</span>
            </nav>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                    KELOLA <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">LAHAN</span>
                </h2>
                @if(collect($filters)->filter()->isNotEmpty())
                <a href="{{ route('admin.kelola-lahan.daftar.index') }}" class="text-[10px] font-black text-rose-500 hover:text-rose-700 bg-white border border-slate-200 px-2.5 py-1.5 rounded-xl transition-all shadow-sm">
                    RESET FILTER
                </a>
                @endif
            </div>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Monitoring statistik produksi, tanam, dan panen lahan di seluruh wilayah operasional.</p>
        </div>

        <div class="flex flex-row items-center gap-3 w-full md:w-auto mt-4 md:mt-0">
            <div class="relative group flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" x-model="searchQuery"
                    @keydown.enter="submitFilters()"
                    placeholder="CARI WILAYAH / RESOR..."
                    class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase shadow-sm">
            </div>
            <button onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-slate-900 text-emerald-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <button type="button" @click="modalJadwalPanen = true" title="Jadwal Panen Mendatang"
                class="flex items-center gap-2 px-4 py-3.5 bg-amber-500/20 text-amber-600 rounded-2xl shadow-xl border border-amber-300/40 hover:bg-amber-500 hover:text-white transition-all duration-300 active:scale-95 text-[10px] font-black uppercase tracking-widest flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="hidden sm:inline">Jadwal Panen</span>
                @if(isset($upcomingHarvests) && $upcomingHarvests->count() > 0)
                <span class="w-5 h-5 bg-amber-500 text-white rounded-full text-[9px] font-black flex items-center justify-center">{{ min($upcomingHarvests->count(), 9) }}</span>
                @endif
            </button>
        </div>
    </div>

    <div class="mx-4 mb-6 glass-card border border-slate-200/60 rounded-[2rem] shadow-xl shadow-slate-200/40 p-6 animate-in fade-in zoom-in duration-500">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">RESOR</label>
                    <div class="relative">
                        <select x-model="selectedResor" @change="selectedSektor = ''; submitFilters()" class="appearance-none bg-none w-full h-12 text-[11px] font-bold px-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 uppercase tracking-wider cursor-pointer">
                            <option value="">SEMUA RESOR</option>
                            @foreach($polresList as $resor)
                            <option value="{{ $resor->id_tingkat }}">{{ $resor->id_tingkat }} - {{ $resor->nama_tingkat }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">SEKTOR</label>
                    <div class="relative">
                        <select x-model="selectedSektor" @change="submitFilters()"
                            class="appearance-none bg-none w-full h-12 text-[11px] font-bold px-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 uppercase tracking-wider cursor-pointer"
                            :disabled="!selectedResor">
                            <option value="">SEMUA SEKTOR</option>
                            @foreach($polsekList as $p)
                            @if(empty($filters['resor']) || str_starts_with($p->id_tingkat, $filters['resor'] . '.'))
                            <option value="{{ $p->id_tingkat }}">{{ $p->id_tingkat }} - {{ $p->nama_tingkat }}</option>
                            @endif
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">JENIS LAHAN</label>
                    <div class="relative">
                        <select x-model="selectedJenis" @change="submitFilters()" class="appearance-none bg-none w-full h-12 text-[11px] font-bold px-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 uppercase tracking-wider cursor-pointer">
                            <option value="">SEMUA JENIS</option>
                            <option value="1">1. PRODUKTIF (POKTAN BINAAN POLRI)</option>
                            <option value="2">2. HUTAN (PERHUTANAN SOSIAL)</option>
                            <option value="3">3. LUAS BAKU SAWAH (LBS)</option>
                            <option value="4">4. PESANTREN</option>
                            <option value="5">5. MILIK POLRI</option>
                            <option value="6">6. PRODUKTIF (MASYARAKAT BINAAN POLRI)</option>
                            <option value="7">7. PRODUKTIF (TUMPANG SARI)</option>
                            <option value="8">8. HUTAN (PERHUTANI/INHUTANI)</option>
                            <option value="9">9. LAHAN LAINNYA</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">KOMODITI</label>
                    <div class="relative">
                        <select x-model="selectedKomoditi" @change="submitFilters()" class="appearance-none bg-none w-full h-12 text-[11px] font-bold px-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 uppercase tracking-wider cursor-pointer">
                            <option value="">SEMUA KOMODITI</option>
                            @foreach($komoditiList as $km)
                            <option value="{{ $km->id_komoditi }}">{{ $km->jenis_komoditi }} - {{ $km->nama_komoditi }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-6 border-t border-slate-100">
                <div class="flex flex-col md:flex-row md:items-center gap-6 w-full lg:w-auto">
                    <div class="space-y-2 w-full md:w-auto">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">PERIODE WAKTU</label>
                        <div class="flex items-center h-12 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60 w-full sm:w-fit">
                            <button type="button" @click="periodMode = 'semua'; submitFilters()"
                                :class="periodMode === 'semua' ? 'bg-white shadow-md text-emerald-600 border border-emerald-100' : 'text-slate-400 hover:text-slate-600'"
                                class="flex-1 sm:flex-none px-5 h-full text-[10px] font-black uppercase tracking-widest rounded-lg transition-all duration-300">
                                SEMUA
                            </button>
                            <button type="button" @click="periodMode = 'tanggal'"
                                :class="periodMode === 'tanggal' ? 'bg-white shadow-md text-emerald-600 border border-emerald-100' : 'text-slate-400 hover:text-slate-600'"
                                class="flex-1 sm:flex-none px-5 h-full text-[10px] font-black uppercase tracking-widest rounded-lg transition-all duration-300">
                                TANGGAL
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
                        <div class="space-y-2 w-full sm:w-auto">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] transition-opacity ml-1" :class="periodMode === 'semua' ? 'opacity-30' : ''">MULAI</label>
                            <input type="date" id="start_date" value="{{ $filters['start'] ?? '' }}"
                                @change="submitFilters()"
                                :disabled="periodMode === 'semua'"
                                :class="periodMode === 'semua' ? 'bg-slate-50/50 text-slate-300 border-slate-100 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 cursor-text'"
                                class="w-full sm:w-36 h-12 text-[11px] font-bold px-4 border rounded-xl outline-none transition-all">
                        </div>
                        <div class="hidden sm:block pt-5 text-slate-300 font-black text-[10px]">SAMPAI</div>
                        <div class="space-y-2 w-full sm:w-auto">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] transition-opacity ml-1" :class="periodMode === 'semua' ? 'opacity-30' : ''">SELESAI</label>
                            <input type="date" id="end_date" value="{{ $filters['end'] ?? '' }}"
                                @change="submitFilters()"
                                :disabled="periodMode === 'semua'"
                                :class="periodMode === 'semua' ? 'bg-slate-50/50 text-slate-300 border-slate-100 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 cursor-text'"
                                class="w-full sm:w-36 h-12 text-[11px] font-bold px-4 border rounded-xl outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center gap-4 w-full lg:w-auto mt-4 lg:mt-0">
                    <div class="h-10 w-px bg-slate-100 hidden lg:block mx-2"></div>
                    <div class="space-y-2 w-full sm:w-auto">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">KATEGORI PRODUKSI</label>
                        <select x-model="kategoriProduksi" @change="submitFilters()" class="w-full sm:w-48 h-12 text-[11px] font-black px-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all uppercase tracking-widest cursor-pointer shadow-sm">
                            <option value="semua">SEMUA / POTENSI</option>
                            <option value="tanam">PROSES TANAM</option>
                            <option value="panen">HASIL PANEN</option>
                            <option value="serapan">SERAPAN</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 relative">
        <div class="absolute inset-0 bg-slate-50/50 rounded-[3rem] -z-10 transform scale-y-110 scale-x-[1.02]"></div>
        <div class="absolute inset-0 topo-pattern -z-10"></div>

        @php
        $statsCards = [
        [
        'label' => 'TOTAL POTENSI',
        'val' => $stats['potensi'],
        'unit' => 'HA',
        'desc' => 'Luas Lahan Terdata',
        'color' => 'bg-emerald-500',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
        </svg>',
        'details' => collect($stats['jenis_lahan_list'] ?? [])->map(function($name, $id) use ($stats) {
        $detail = $stats['potensi_details']->get($id);
        if ($detail && $detail->total_luas > 0) {
        return [
        'id' => $id,
        'name' => $name,
        'luas' => number_format($detail->total_luas, 2),
        'lokasi' => $detail->total_lokasi
        ];
        }
        return null;
        })->filter()->values()
        ],
        [
        'label' => 'PROSES TANAM',
        'val' => $stats['tanam'],
        'unit' => 'HA',
        'desc' => 'Tahap Pertumbuhan',
        'color' => 'bg-blue-500',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
        </svg>',
        'details' => collect($stats['jenis_lahan_list'] ?? [])->map(function($name, $id) use ($stats) {
        $detail = $stats['tanam_details']->get($id);
        if ($detail && $detail->total_luas > 0) {
        return [
        'id' => $id,
        'name' => $name,
        'luas' => number_format($detail->total_luas, 2),
        'lokasi' => $detail->total_lokasi
        ];
        }
        return null;
        })->filter()->values()
        ],
        [
        'label' => 'LUAS PANEN',
        'val' => $stats['panen'],
        'unit' => 'HA',
        'val2' => $stats['panen_ton'] ?? 0,
        'unit2' => 'TON',
        'desc' => 'Tervalidasi Panen',
        'color' => 'bg-amber-500',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
        </svg>',
        'details' => collect($stats['jenis_lahan_list'] ?? [])->map(function($name, $id) use ($stats) {
        $detail = $stats['panen_details']->get($id);
        if ($detail && $detail->total_luas > 0) {
        return [
        'id' => $id,
        'name' => $name,
        'luas' => number_format($detail->total_luas, 2),
        'lokasi' => $detail->total_lokasi
        ];
        }
        return null;
        })->filter()->values()
        ],
        [
        'label' => 'SERAPAN HASIL',
        'val' => $stats['serapan'],
        'unit' => 'TON',
        'desc' => 'Output Produksi',
        'color' => 'bg-indigo-500',
        'icon' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
        </svg>',
        'details' => collect($stats['distribusi_list'] ?? [])->map(function($name, $id) use ($stats) {
        $detail = $stats['serapan_details']->get($id);
        if ($detail && $detail->total_luas > 0) {
        return [
        'id' => $id,
        'name' => $name,
        'luas' => number_format($detail->total_luas, 2),
        'lokasi' => $detail->total_lokasi
        ];
        }
        return [
        'id' => $id,
        'name' => $name,
        'luas' => '0.00',
        'lokasi' => 0
        ];
        })->filter()->values()
        ]
        ];
        @endphp

        @foreach($statsCards as $card)
        <div class="group relative bg-white p-6 rounded-[2.5rem] border border-slate-200/60 shadow-lg shadow-slate-200/30 hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col justify-between min-h-[140px]">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-40"></div>

            <div class="relative z-10 flex items-start justify-between">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">{{ $card['label'] }}</p>
                    <div class="flex flex-wrap items-baseline gap-1">
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight" x-data="{ count: 0 }" x-init="setTimeout(() => { let end = {{ (float)str_replace(',', '', $card['val']) }}; let duration = 1500; let startTime = null; function step(timestamp) { if(!startTime) startTime = timestamp; let progress = Math.min((timestamp - startTime) / duration, 1); count = (progress * end).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); if(progress < 1) requestAnimationFrame(step); } requestAnimationFrame(step); }, 200)" x-text="count">0.00</h4>
                        <span class="text-[10px] font-black text-emerald-500">{{ $card['unit'] }}</span>
                        @if(isset($card['val2']))
                        <span class="text-slate-300 mx-1">|</span>
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight" x-data="{ count: 0 }" x-init="setTimeout(() => { let end = {{ (float)str_replace(',', '', $card['val2']) }}; let duration = 1500; let startTime = null; function step(timestamp) { if(!startTime) startTime = timestamp; let progress = Math.min((timestamp - startTime) / duration, 1); count = (progress * end).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); if(progress < 1) requestAnimationFrame(step); } requestAnimationFrame(step); }, 200)" x-text="count">0.00</h4>
                        <span class="text-[10px] font-black text-amber-500">{{ $card['unit2'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="w-10 h-10 {{ $card['color'] }} text-white rounded-xl shadow-lg flex items-center justify-center transform group-hover:rotate-12 transition-transform duration-500 shadow-{{ explode('-', $card['color'])[1] }}-500/20">
                    {!! $card['icon'] !!}
                </div>
            </div>

            <div class="relative z-10 mt-4 flex items-center gap-2">
                <div class="w-6 h-1 bg-emerald-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 w-2/3"></div>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ $card['desc'] }}</p>
            </div>

            @if(!empty($card['details']) && count($card['details']) > 0)
            <div class="relative z-10 mt-4 pt-4 border-t border-slate-100 space-y-1.5 max-h-36 overflow-y-auto custom-scrollbar pr-1">
                @foreach($card['details'] as $detail)
                <div class="flex justify-between items-center text-[9px] font-bold text-slate-500 hover:text-slate-700 transition-colors bg-slate-50/50 p-1.5 rounded-lg border border-slate-100/50">
                    <span class="uppercase flex items-center gap-1.5 overflow-hidden">
                        <span class="w-1 h-1 rounded-full bg-{{ explode('-', $card['color'])[1] }}-400 flex-shrink-0"></span>
                        <span class="truncate" title="{{ $detail['name'] }}">{{ $detail['id'] }}. {{ $detail['name'] }}</span>
                    </span>
                    <span class="text-right whitespace-nowrap ml-2">
                        <span class="text-slate-800 font-black">{{ $detail['luas'] }}</span> {{ $card['unit'] }} / <span class="text-slate-800 font-black">{{ $detail['lokasi'] }}</span> LOKASI
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mx-4 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-300/30 overflow-hidden relative z-20 mt-8">

        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path>
            </svg>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <div>
                    <h3 class="text-xs font-black text-white uppercase tracking-[0.2em] leading-none mb-1">RINCIAN PRODUKSI WILAYAH</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Monitoring Real-time &bullet; Updated Today</p>
                </div>
            </div>
            <div class="flex items-center gap-3 relative z-10">
                <button onclick="window.location.reload()" title="Refresh Data"
                    class="flex items-center gap-2 px-3 py-1.5 bg-white/10 hover:bg-emerald-500 text-emerald-400 hover:text-white border border-white/10 hover:border-emerald-500 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-sm group">
                    <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                <button type="button" @click="modalJadwalPanen = true" title="Jadwal Panen Mendatang"
                    class="flex items-center gap-2 px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500 text-amber-400 hover:text-white border border-amber-500/30 hover:border-amber-500 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-sm group">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Jadwal Panen
                </button>
                <span class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-400/10 text-emerald-400 border border-emerald-400/20 rounded-xl text-[10px] font-black uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                    SYNC ACTIVE
                </span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar p-6">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-white border-b border-slate-100/50">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-1/4">WILAYAH & LOKASI</th>
                        <th class="px-4 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-1/5">PROSES TANAM</th>
                        <th class="px-4 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-1/5">HASIL PANEN</th>
                        <th class="px-4 py-5 text-left text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-1/5">SERAPAN</th>
                        <th class="px-4 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">ALUR</th>
                        <th class="px-4 py-5 text-right text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] w-24">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($data as $resor)
                    @php $resorId = str_replace('.', '_', $resor->id_tingkat); @endphp
                    {{-- Resor Header Row with Accordion Toggle --}}
                    <tr class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-lg sticky top-0 z-10 cursor-pointer group"
                        @click="toggleResor('{{ $resorId }}')">
                        <td colspan="6" class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 flex items-center justify-center text-emerald-400 border border-emerald-500/30 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                                    <svg class="w-6 h-6 transition-transform duration-300" :class="isResorOpen('{{ $resorId }}') ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[9px] font-black uppercase tracking-[0.2em] rounded-md border border-emerald-500/20">KESATUAN WILAYAH</span>
                                        <span class="w-1 h-1 bg-slate-600 rounded-full"></span>
                                        <span class="text-[10px] font-black text-slate-400 tracking-widest uppercase">{{ $resor->id_tingkat }}</span>
                                    </div>
                                    <h4 class="text-lg font-black text-white uppercase tracking-wider leading-none">{{ $resor->nama_tingkat }}</h4>
                                </div>
                                <div class="ml-auto flex items-center gap-4">
                                    <div class="hidden sm:flex flex-col items-end">
                                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">TOTAL PRODUKSI</span>
                                        <span class="text-sm font-black text-emerald-400 uppercase tracking-tighter">
                                            {{ $resor->sektors->sum(fn($s) => $s->lahans->count()) }} DATA LAHAN
                                        </span>
                                    </div>
                                    <div class="w-px h-8 bg-white/10"></div>
                                    <button class="p-2 bg-white/5 border border-white/10 rounded-xl text-emerald-400 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all shadow-xl">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($resor->sektors as $sektor)
                    @foreach($sektor->lahans as $row)
                    <tr x-show="isResorOpen('{{ $resorId }}')"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 -translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="group hover:bg-emerald-50/50 transition-all duration-300 border-l-4 border-slate-100 hover:border-emerald-500">
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></div>
                                    <span class="text-sm font-black text-slate-900 uppercase tracking-tight group-hover:text-emerald-700 transition-colors">
                                        {{ $row->nama_wilayah }}
                                    </span>
                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded-md text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        {{ $row->nama_kecamatan }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="flex items-center gap-1.5 px-2 py-0.5 bg-white border border-slate-200 rounded-lg text-[10px] font-bold text-slate-600 shadow-sm">
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">SEKTOR</span>
                                        <span class="uppercase">{{ str_replace('POLSEK ', '', $sektor->nama_tingkat) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 px-2 py-0.5 bg-emerald-50/50 border border-emerald-100 rounded-lg text-[10px] font-bold text-emerald-700 shadow-sm">
                                        <span class="text-[8px] font-black text-emerald-400 uppercase tracking-tighter">DUSUN/POKTAN</span>
                                        <span class="uppercase tracking-tight">{{ $row->poktan ?? 'No Data' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-6 border-x border-slate-50 align-top">
                            @if($row->id_tanam)
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 w-fit px-2 py-0.5 rounded-lg border border-emerald-100">{{ number_format($row->luas_tanam, 2) }} HA</span>
                                <span class="text-[9px] font-bold text-slate-500 tracking-tight">Est. Panen:<br>{{ \Carbon\Carbon::parse($row->est_awal_panen)->format('d M') }} - {{ \Carbon\Carbon::parse($row->est_akhir_panen)->format('d M Y') }}</span>
                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                    <button @click='openDetailModal("tanam", @json($row))' class="px-2 py-1 bg-white border border-emerald-200 text-emerald-600 rounded text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-colors shadow-sm">Detail</button>
                                    @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                    <button @click='editTanam("{{ $row->id_tanam }}", @json($row))' class="px-2 py-1 bg-white border border-emerald-200 text-emerald-600 rounded text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-colors shadow-sm">Edit</button>
                                    @endif
                                </div>
                                @if(!$row->tanam_valid_oleh)
                                @if($row->tanam_alasan_tolak)
                                <div class="flex flex-col gap-1 group relative mt-1">
                                    <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[9px] font-black uppercase shadow-sm flex items-center justify-center cursor-help">❌ Ditolak</span>
                                    <div class="absolute bottom-full left-0 mb-2 w-56 p-2 bg-white rounded-lg shadow-xl border border-rose-100 text-[9px] text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 font-bold whitespace-pre-wrap">{{ $row->tanam_alasan_tolak }}<br><span class="text-slate-400 mt-1 block">Perbaiki data lalu edit untuk mengajukan ulang.</span></div>
                                </div>
                                @elseif(in_array(auth()->user()->role, ['admin']) || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1))
                                <div class="flex flex-col gap-1 w-full mt-1">
                                    <form action="{{ route($routePrefix.'.kelola-lahan.tanam.validasi', $row->id_tanam) }}" method="POST" class="m-0">
                                        @csrf @method('PUT')
                                        <button class="px-2 py-1 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-colors shadow-sm w-full text-center">Validasi</button>
                                    </form>
                                    <button @click="submitTolakDirect('{{ $row->id_tanam }}', 'tanam', '{{ addslashes($row->nama_wilayah ?? $row->alamat_lahan ?? '') }}')" type="button" class="px-2 py-1 bg-rose-50 border border-rose-100 text-rose-600 rounded text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors shadow-sm w-full text-center">Tolak</button>
                                </div>
                                @else
                                <span class="text-[9px] font-black text-slate-400 tracking-tight mt-1 text-center">⏳ Menunggu Validasi</span>
                                @endif
                                @else
                                <span class="text-[9px] font-black text-emerald-500 tracking-tight mt-1">✅ Tervalidasi</span>
                                @endif
                            </div>
                            @else
                            <span class="text-[10px] font-bold text-slate-400 italic">Belum Input</span>
                            @endif
                        </td>
                        <td class="px-4 py-6 border-r border-slate-50 align-top">
                            @if($row->id_panen)
                            <div class="flex flex-col gap-1.5">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-black text-amber-600 bg-amber-50 w-fit px-2 py-0.5 rounded-lg border border-amber-100">{{ number_format($row->luas_panen, 2) }} HA</span>
                                    <span class="text-[10px] font-bold text-amber-700 w-fit px-2 py-0.5 bg-amber-100 rounded-md">{{ number_format($row->total_panen, 2) }} TON</span>
                                </div>
                                <span class="text-[9px] font-bold text-slate-500 tracking-tight mt-1">Tgl: {{ \Carbon\Carbon::parse($row->tgl_panen)->format('d M Y') }}</span>
                                @php
                                $stsPanen = $row->status_panen == 1 ? 'Normal' : ($row->status_panen == 2 ? 'Gagal' : ($row->status_panen == 3 ? 'Dini' : 'Tebasan'));
                                @endphp
                                <span class="text-[9px] font-bold text-slate-500 tracking-tight">Jenis: {{ $stsPanen }}</span>
                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                    <button @click='openDetailModal("panen", @json($row))' class="px-2 py-1 bg-white border border-amber-200 text-amber-600 rounded text-[9px] font-black uppercase hover:bg-amber-500 hover:text-white transition-colors shadow-sm">Detail</button>
                                    @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                    <button @click='editPanen("{{ $row->id_panen }}", @json($row))' class="px-2 py-1 bg-white border border-amber-200 text-amber-600 rounded text-[9px] font-black uppercase hover:bg-amber-500 hover:text-white transition-colors shadow-sm">Edit</button>
                                    @endif
                                </div>
                                @if(!$row->panen_valid_oleh)
                                @if($row->panen_alasan_tolak)
                                <div class="flex flex-col gap-1 group relative mt-1">
                                    <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[9px] font-black uppercase shadow-sm flex items-center justify-center cursor-help">❌ Ditolak</span>
                                    <div class="absolute bottom-full left-0 mb-2 w-56 p-2 bg-white rounded-lg shadow-xl border border-rose-100 text-[9px] text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 font-bold whitespace-pre-wrap">{{ $row->panen_alasan_tolak }}<br><span class="text-slate-400 mt-1 block">Perbaiki data lalu edit untuk mengajukan ulang.</span></div>
                                </div>
                                @elseif(in_array(auth()->user()->role, ['admin']) || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1))
                                <div class="flex flex-col gap-1 w-full mt-1">
                                    <form action="{{ route($routePrefix.'.kelola-lahan.panen.validasi', $row->id_panen) }}" method="POST" class="m-0">
                                        @csrf @method('PUT')
                                        <button class="px-2 py-1 bg-amber-50 border border-amber-100 text-amber-600 rounded text-[9px] font-black uppercase hover:bg-amber-500 hover:text-white transition-colors shadow-sm w-full text-center">Validasi</button>
                                    </form>
                                    <button @click="submitTolakDirect('{{ $row->id_panen }}', 'panen', '{{ addslashes($row->nama_wilayah ?? $row->alamat_lahan ?? '') }}')" type="button" class="px-2 py-1 bg-rose-50 border border-rose-100 text-rose-600 rounded text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors shadow-sm w-full text-center">Tolak</button>
                                </div>
                                @else
                                <span class="text-[9px] font-black text-slate-400 tracking-tight mt-1 text-center">⏳ Menunggu Validasi</span>
                                @endif
                                @else
                                <span class="text-[9px] font-black text-amber-500 tracking-tight mt-1">✅ Tervalidasi</span>
                                @endif
                            </div>
                            @else
                            <span class="text-[10px] font-bold text-slate-400 italic">Belum Input</span>
                            @endif
                        </td>
                        <td class="px-4 py-6 border-r border-slate-50 align-top">
                            @if($row->id_distribusi)
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-black text-blue-600 bg-blue-50 w-fit px-2 py-0.5 rounded-lg border border-blue-100">{{ number_format($row->total_distribusi, 2) }} TON</span>
                                <span class="text-[9px] font-bold text-slate-500 tracking-tight">Tgl: {{ \Carbon\Carbon::parse($row->tgl_distribusi)->format('d M Y') }}</span>
                                @php
                                $dstKe = $row->distribusi_ke == 1 ? 'Bulog' : ($row->distribusi_ke == 2 ? 'Pabrik Pakan' : ($row->distribusi_ke == 3 ? 'Tengkulak' : 'Konsumsi Sendiri'));
                                @endphp
                                <span class="text-[9px] font-bold text-slate-500 tracking-tight">Tujuan: {{ $dstKe }}</span>
                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                    <button @click='openDetailModal("serapan", @json($row))' class="px-2 py-1 bg-white border border-blue-200 text-blue-600 rounded text-[9px] font-black uppercase hover:bg-blue-500 hover:text-white transition-colors shadow-sm">Detail</button>
                                    @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                    <button @click='editSerapan("{{ $row->id_distribusi }}", @json($row))' class="px-2 py-1 bg-white border border-blue-200 text-blue-600 rounded text-[9px] font-black uppercase hover:bg-blue-500 hover:text-white transition-colors shadow-sm">Edit</button>
                                    @endif
                                </div>
                                @if(!$row->serapan_valid_oleh)
                                @if($row->serapan_alasan_tolak)
                                <div class="flex flex-col gap-1 group relative mt-1">
                                    <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[9px] font-black uppercase shadow-sm flex items-center justify-center cursor-help">❌ Ditolak</span>
                                    <div class="absolute bottom-full left-0 mb-2 w-56 p-2 bg-white rounded-lg shadow-xl border border-rose-100 text-[9px] text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 font-bold whitespace-pre-wrap">{{ $row->serapan_alasan_tolak }}<br><span class="text-slate-400 mt-1 block">Perbaiki data lalu edit untuk mengajukan ulang.</span></div>
                                </div>
                                @elseif(in_array(auth()->user()->role, ['admin']) || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1))
                                <div class="flex flex-col gap-1 w-full mt-1">
                                    <form action="{{ route($routePrefix.'.kelola-lahan.serapan.validasi', $row->id_distribusi) }}" method="POST" class="m-0">
                                        @csrf @method('PUT')
                                        <button class="px-2 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded text-[9px] font-black uppercase hover:bg-blue-500 hover:text-white transition-colors shadow-sm w-full text-center">Validasi</button>
                                    </form>
                                    <button @click="submitTolakDirect('{{ $row->id_distribusi }}', 'serapan', '{{ addslashes($row->nama_wilayah ?? $row->alamat_lahan ?? '') }}')" type="button" class="px-2 py-1 bg-rose-50 border border-rose-100 text-rose-600 rounded text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors shadow-sm w-full text-center">Tolak</button>
                                </div>
                                @else
                                <span class="text-[9px] font-black text-slate-400 tracking-tight mt-1 text-center">⏳ Menunggu Validasi</span>
                                @endif
                                @else
                                <span class="text-[9px] font-black text-indigo-500 tracking-tight mt-1">✅ Tervalidasi</span>
                                @endif
                            </div>
                            @else
                            <span class="text-[10px] font-bold text-slate-400 italic">Belum Input</span>
                            @endif
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex flex-col gap-3 min-w-[160px]">

                                {{-- STEP 1: TANAM --}}
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[9px] font-black border-2 transition-all"
                                        :class="lahanStages['{{ $row->id_lahan }}'] >= 1
                                                            ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-200'
                                                            : (lahanStages['{{ $row->id_lahan }}'] === 0 ? 'bg-emerald-50 border-emerald-400 text-emerald-600 animate-pulse' : 'bg-slate-100 border-slate-200 text-slate-400')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[7px] font-black uppercase tracking-wider"
                                        :class="lahanStages['{{ $row->id_lahan }}'] >= 1 ? 'text-emerald-600' : (lahanStages['{{ $row->id_lahan }}'] === 0 ? 'text-emerald-500' : 'text-slate-400')">
                                        Tanam
                                    </span>
                                </div>

                                {{-- Connector 1-2 --}}
                                <div class="flex-1 h-0.5 mb-4 rounded-full transition-colors"
                                    :class="lahanStages['{{ $row->id_lahan }}'] >= 2 ? 'bg-amber-400' : 'bg-slate-200'">
                                </div>

                                {{-- STEP 2: PANEN --}}
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[9px] font-black border-2 transition-all"
                                        :class="lahanStages['{{ $row->id_lahan }}'] >= 2
                                                            ? 'bg-amber-500 border-amber-500 text-white shadow-md shadow-amber-200'
                                                            : (lahanStages['{{ $row->id_lahan }}'] === 1 ? 'bg-amber-50 border-amber-400 text-amber-600 animate-pulse' : 'bg-slate-100 border-slate-200 text-slate-400')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[7px] font-black uppercase tracking-wider"
                                        :class="lahanStages['{{ $row->id_lahan }}'] >= 2 ? 'text-amber-600' : (lahanStages['{{ $row->id_lahan }}'] === 1 ? 'text-amber-500' : 'text-slate-400')">
                                        Panen
                                    </span>
                                </div>

                                {{-- Connector 2-3 --}}
                                <div class="flex-1 h-0.5 mb-4 rounded-full transition-colors"
                                    :class="lahanStages['{{ $row->id_lahan }}'] >= 3 ? 'bg-blue-400' : 'bg-slate-200'">
                                </div>

                                {{-- STEP 3: SERAPAN --}}
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-7 h-7 rounded-xl flex items-center justify-center text-[9px] font-black border-2 transition-all"
                                        :class="lahanStages['{{ $row->id_lahan }}'] >= 3
                                                            ? 'bg-blue-500 border-blue-500 text-white shadow-md shadow-blue-200'
                                                            : (lahanStages['{{ $row->id_lahan }}'] === 2 ? 'bg-blue-50 border-blue-400 text-blue-600 animate-pulse' : 'bg-slate-100 border-slate-200 text-slate-400')">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[7px] font-black uppercase tracking-wider"
                                        :class="lahanStages['{{ $row->id_lahan }}'] >= 3 ? 'text-blue-600' : (lahanStages['{{ $row->id_lahan }}'] === 2 ? 'text-blue-500' : 'text-slate-400')">
                                        Serapan
                                    </span>
                                </div>
                            </div>

                        </td>
                        <td class="px-4 py-6 text-right align-top">
                            <div class="flex flex-col items-end gap-2">
                                <!-- Detail Lahan Button Removed -->
                                @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                <button onclick="window.location.href='{{ route('admin.kelola-lahan.potensi.index') }}?search={{ $row->id_lahan }}&action=edit'" title="Edit Lahan" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white rounded-lg transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                @endif
                                <button @click="toggleHistory('{{ $row->id_lahan }}')" title="Riwayat & Kelola Siklus" class="p-2 rounded-lg transition-all shadow-sm flex items-center justify-center" :class="activeHistory === '{{ $row->id_lahan }}' ? 'bg-slate-800 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                                    <svg class="w-4 h-4 transition-transform duration-300" :class="activeHistory === '{{ $row->id_lahan }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr x-show="activeHistory === '{{ $row->id_lahan }}'" x-cloak x-transition.opacity.duration.200ms>
                        <td colspan="6" class="px-6 pb-6 bg-slate-50/40">
                            <div class="rounded-[2rem] border border-slate-200 bg-white shadow-lg shadow-slate-200/30 p-6">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-100">
                                    <h4 class="flex items-center gap-2 text-sm font-black text-slate-800 uppercase tracking-widest">
                                        Kelola Lahan Aktif
                                        <span class="text-[9px] font-black text-emerald-700 bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded-full uppercase tracking-wide">Siklus Berjalan</span>
                                    </h4>
                                    @if(in_array(auth()->user()->role, ['admin', 'operator_polsek']))
                                    <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 0)' class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase hover:bg-emerald-600 transition-all shadow-md shadow-emerald-500/20 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Tambah Tanam Baru
                                    </button>
                                    @endif
                                </div>

                                @php
                                $activeTanamList = $row->history_tanam->filter(fn($t) => ($t->is_active ?? 1) == 1);
                                @endphp
                                @if($activeTanamList->isEmpty())
                                <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                    <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">Semua Siklus Sudah Selesai</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1">Tidak ada siklus aktif. Data telah dipindah ke Riwayat Lahan.</p>
                                </div>
                                @else
                                <div class="space-y-6">
                                    @foreach($activeTanamList as $tanam)
                                    <div class="relative pl-6 border-l-2 border-emerald-200 pb-2">
                                        <div class="absolute w-4 h-4 bg-emerald-500 rounded-full -left-[9px] top-0 border-4 border-white shadow-sm"></div>
                                        <div class="bg-slate-50/50 rounded-xl border border-slate-100 p-5 hover:border-emerald-200 transition-colors">
                                            <div class="flex justify-between items-start mb-4 pb-4 border-b border-slate-100">
                                                <div>
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-[9px] font-black text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded uppercase tracking-wider">Tanam</span>
                                                        <span class="text-xs font-bold text-slate-500">{{ \Carbon\Carbon::parse($tanam->tgl_tanam)->format('d M Y') }}</span>
                                                    </div>
                                                    <p class="text-sm font-black text-slate-800">{{ number_format($tanam->luas_tanam, 2) }} HA <span class="text-[10px] font-bold text-slate-400 ml-2 uppercase">Est Panen: {{ \Carbon\Carbon::parse($tanam->est_awal_panen)->format('M Y') }}</span></p>
                                                </div>
                                                <div class="flex gap-2">
                                                    @php
                                                    $canSelesai = !is_null($tanam->valid_oleh) &&
                                                    $tanam->panens->count() > 0 &&
                                                    $tanam->panens->every(fn($p) => !is_null($p->valid_oleh)) &&
                                                    $tanam->panens->every(fn($p) =>
                                                    isset($p->distribusis) && $p->distribusis->count() > 0 &&
                                                    $p->distribusis->every(fn($s) => !is_null($s->valid_oleh))
                                                    );
                                                    @endphp
                                                    @if(auth()->user()->role === 'admin' && $canSelesai)
                                                    <form action="{{ route('admin.kelola-lahan.tanam.selesai', $tanam->id_tanam) }}" method="POST" class="m-0" onsubmit="return confirm('Selesaikan siklus ini? Data akan diarsipkan ke Riwayat Lahan.');">
                                                        @csrf @method('PUT')
                                                        <button type="submit" class="px-2.5 py-1.5 bg-indigo-600 text-white rounded-lg text-[9px] font-black uppercase hover:bg-indigo-700 transition-all shadow-sm flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg> Selesai Siklus</button>
                                                    </form>
                                                    @endif
                                                    @if(auth()->user()->role === 'admin' && !is_null($tanam->id_tanam))
                                                    <button @click="submitTolakDirect('{{ $tanam->id_tanam }}', 'tanam', '{{ addslashes($row->nama_wilayah ?? $row->alamat_lahan ?? '') }}')" type="button" class="px-2.5 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-all shadow-sm flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg> Tolak Siklus</button>
                                                    @endif
                                                    @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1))
                                                    @if(is_null($tanam->valid_oleh))
                                                    @if($tanam->alasan_tolak)
                                                    {{-- DITOLAK: badge + info hover saja, tidak ada tombol validasi/tolak --}}
                                                    <div class="flex flex-col gap-1 group relative">
                                                        <span class="px-2.5 py-1.5 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg text-[9px] font-black uppercase shadow-sm flex items-center cursor-help">❌ Ditolak</span>
                                                        <div class="absolute bottom-full left-0 mb-2 w-56 p-2 bg-white rounded-lg shadow-xl border border-rose-100 text-[9px] text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 font-bold whitespace-pre-wrap">{{ $tanam->alasan_tolak }}<br><span class="text-slate-400 mt-1 block">Perbaiki data lalu edit untuk mengajukan ulang.</span></div>
                                                    </div>
                                                    @else
                                                    {{-- BELUM VALIDASI: tombol Validasi + Tolak --}}
                                                    <form action="{{ route($routePrefix.'.kelola-lahan.tanam.validasi', $tanam->id_tanam) }}" method="POST" class="m-0">@csrf @method('PUT')<button type="submit" class="px-2.5 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-all shadow-sm">Validasi</button></form>

                                                    @endif
                                                    @else
                                                    {{-- TERVALIDASI: tombol Unvalidasi saja, tidak ada Tolak --}}
                                                    <form action="{{ route($routePrefix.'.kelola-lahan.tanam.unvalidasi', $tanam->id_tanam) }}" method="POST" class="m-0">@csrf @method('PUT')<button type="submit" class="px-2.5 py-1.5 bg-emerald-500 text-white rounded-lg text-[9px] font-black uppercase hover:bg-emerald-600 transition-all shadow-sm flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                            </svg> Unvalidasi</button></form>
                                                    @endif
                                                    @endif
                                                    @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                                    <button @click='editTanam("{{ $tanam->id_tanam }}", @json(array_merge((array)$row, (array)$tanam)))' class="px-2.5 py-1.5 bg-white border border-emerald-200 text-emerald-600 rounded-lg text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-all shadow-sm">Edit</button>
                                                    <button @click='deleteTanam("{{ $tanam->id_tanam }}")' class="px-2.5 py-1.5 bg-white border border-rose-200 text-rose-600 rounded-lg text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-all shadow-sm">Hapus</button>
                                                    @if(!is_null($tanam->valid_oleh))
                                                    <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 1, "{{ $tanam->id_tanam }}")' class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-amber-600 transition-all shadow-sm shadow-amber-500/20 ml-2">+ Panen</button>
                                                    @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-6">
                                                <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Riwayat Panen & Serapan
                                                </h5>
                                                @if($tanam->panens->isEmpty())
                                                <div class="p-3 bg-white rounded-lg border border-dashed border-slate-200 text-center text-[10px] font-bold text-slate-400 italic">Belum ada panen</div>
                                                @else
                                                <div class="space-y-4">
                                                    @foreach($tanam->panens as $panen)
                                                    <div class="bg-white rounded-xl border border-amber-100 shadow-sm overflow-hidden">
                                                        <div class="p-4 bg-amber-50/30 flex justify-between items-start border-b border-amber-100">
                                                            <div>
                                                                <div class="flex items-center gap-1.5 mb-1">
                                                                    <span class="text-[10px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($panen->tgl_panen)->format('d M Y') }}</span>
                                                                    <span class="text-[8px] font-black text-white bg-amber-500 px-1.5 py-0.5 rounded">{{ $panen->status_panen == 1 ? 'NORMAL' : ($panen->status_panen == 2 ? 'GAGAL' : ($panen->status_panen == 3 ? 'DINI' : 'TEBASAN')) }}</span>
                                                                </div>
                                                                <p class="text-sm font-black text-slate-800">{{ number_format($panen->luas_panen, 2) }} HA <span class="text-[10px] font-bold text-slate-400 ml-1">dari {{ number_format($panen->total_panen, 2) }} TON</span></p>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1))
                                                                @if(is_null($panen->valid_oleh))
                                                                @if($panen->alasan_tolak)
                                                                <div class="group relative">
                                                                    <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[9px] font-black uppercase flex items-center cursor-help">❌ Ditolak</span>
                                                                    <div class="absolute bottom-full right-0 mb-2 w-56 p-2 bg-white rounded-lg shadow-xl border border-rose-100 text-[9px] text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 font-bold whitespace-pre-wrap">{{ $panen->alasan_tolak }}<br><span class="text-slate-400 mt-1 block">Perbaiki data lalu edit kembali.</span></div>
                                                                </div>
                                                                @else
                                                                <form action="{{ route($routePrefix.'.kelola-lahan.panen.validasi', $panen->id_panen) }}" method="POST" class="m-0">@csrf @method('PUT')<button type="submit" class="px-2 py-1 bg-amber-50 border border-amber-100 text-amber-600 rounded text-[9px] font-black uppercase hover:bg-amber-500 hover:text-white transition-colors">Validasi</button></form>
                                                                <button @click="submitTolakDirect('{{ $panen->id_panen }}', 'panen', '{{ addslashes($row->nama_wilayah ?? '') }}')" type="button" class="px-2 py-1 bg-rose-50 border border-rose-100 text-rose-600 rounded text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors">Tolak</button>
                                                                @endif
                                                                @else
                                                                <form action="{{ route($routePrefix.'.kelola-lahan.panen.unvalidasi', $panen->id_panen) }}" method="POST" class="m-0">@csrf @method('PUT')<button type="submit" class="px-2 py-1 bg-amber-500 text-white rounded text-[9px] font-black uppercase hover:bg-amber-600 transition-colors flex items-center gap-1">Unvalidasi</button></form>
                                                                @endif
                                                                @endif
                                                                @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                                                <button @click='editPanen("{{ $panen->id_panen }}", @json(array_merge((array)$row, (array)$tanam, (array)$panen)))' class="px-2 py-1 bg-white text-amber-600 border border-amber-200 rounded text-[9px] font-black uppercase hover:bg-amber-50 transition-colors">Edit</button>
                                                                <button @click='deletePanen("{{ $panen->id_panen }}")' class="px-2 py-1 bg-white text-rose-600 border border-rose-200 rounded text-[9px] font-black uppercase hover:bg-rose-50 transition-colors">Hapus</button>
                                                                @if(!is_null($panen->valid_oleh))
                                                                <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 2, "{{ $tanam->id_tanam }}", "{{ $panen->id_panen }}")' class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-[9px] font-black uppercase hover:bg-blue-600 shadow-sm ml-2">+ Serapan</button>
                                                                @endif
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Daftar Serapan --}}
                                                        <div class="p-4 bg-white">
                                                            @if($panen->distribusis->isEmpty())
                                                            <div class="text-center text-[10px] font-bold text-slate-400 italic">Belum ada serapan untuk panen ini</div>
                                                            @else
                                                            <div class="space-y-2">
                                                                @foreach($panen->distribusis as $distribusi)
                                                                <div class="bg-slate-50 rounded-lg border border-slate-100 p-3 flex justify-between items-center">
                                                                    <div>
                                                                        <div class="flex items-center gap-1.5 mb-1">
                                                                            <span class="text-[10px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($distribusi->tgl_distribusi)->format('d M Y') }}</span>
                                                                            <span class="text-[8px] font-black text-blue-600 bg-blue-100 px-1.5 py-0.5 rounded">{{ $distribusi->distribusi_ke == 1 ? 'BULOG' : ($distribusi->distribusi_ke == 2 ? 'PABRIK' : ($distribusi->distribusi_ke == 3 ? 'TENGKULAK' : 'KONSUMSI')) }}</span>
                                                                        </div>
                                                                        <p class="text-[11px] font-black text-slate-800">{{ number_format($distribusi->total_distribusi, 2) }} TON</p>
                                                                    </div>
                                                                    <div class="flex items-center gap-2">
                                                                        @if(auth()->user()->role === 'admin' || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1))
                                                                        @if(is_null($distribusi->valid_oleh))
                                                                        @if($distribusi->alasan_tolak)
                                                                        <div class="group relative">
                                                                            <span class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[8px] font-black uppercase flex items-center cursor-help">❌ Ditolak</span>
                                                                            <div class="absolute bottom-full right-0 mb-2 w-56 p-2 bg-white rounded-lg shadow-xl border border-rose-100 text-[8px] text-rose-600 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 font-bold whitespace-pre-wrap">{{ $distribusi->alasan_tolak }}<br><span class="text-slate-400 mt-1 block">Perbaiki data lalu edit kembali.</span></div>
                                                                        </div>
                                                                        @else
                                                                        <form action="{{ route($routePrefix.'.kelola-lahan.serapan.validasi', $distribusi->id_distribusi) }}" method="POST" class="m-0">@csrf @method('PUT')<button type="submit" class="px-2 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded text-[8px] font-black uppercase hover:bg-blue-500 hover:text-white transition-colors">Validasi</button></form>
                                                                        <button @click="submitTolakDirect('{{ $distribusi->id_distribusi }}', 'serapan', '{{ addslashes($row->nama_wilayah ?? '') }}')" type="button" class="px-2 py-1 bg-rose-50 border border-rose-100 text-rose-600 rounded text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors">Tolak</button>
                                                                        @endif
                                                                        @else
                                                                        <form action="{{ route($routePrefix.'.kelola-lahan.serapan.unvalidasi', $distribusi->id_distribusi) }}" method="POST" class="m-0">@csrf @method('PUT')<button type="submit" class="px-2 py-1 bg-blue-500 text-white rounded text-[8px] font-black uppercase hover:bg-blue-600 transition-colors flex items-center gap-1">Unvalidasi</button></form>
                                                                        @endif
                                                                        @endif
                                                                        @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                                                        <button @click='editSerapan("{{ $distribusi->id_distribusi }}", @json(array_merge((array)$row, (array)$tanam, (array)$distribusi)))' class="px-2 py-1 bg-white text-blue-600 border border-blue-200 rounded text-[8px] font-black uppercase hover:bg-blue-50 transition-colors">Edit</button>
                                                                        <button @click='deleteSerapan("{{ $distribusi->id_distribusi }}")' class="px-2 py-1 bg-white text-rose-600 border border-rose-200 rounded text-[8px] font-black uppercase hover:bg-rose-50 transition-colors">Del</button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-32 text-center bg-slate-50/50">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-24 h-24 bg-white border border-slate-100 rounded-[2rem] flex items-center justify-center mb-6 shadow-xl shadow-slate-200/50">
                                    <svg class="w-12 h-12 text-slate-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-black text-slate-500 uppercase tracking-[0.3em]">Data Lahan Belum Tersedia</h4>
                                <p class="text-[11px] font-bold text-slate-300 mt-2 uppercase tracking-widest">Gunakan filter atau pencarian untuk hasil lainnya</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">
                    MENAMPILKAN {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }} DARI {{ $data->total() }} DATA
                </p>
                <div class="premium-pagination">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODALS SECTION - PRODUCTION FLOW --}}
    {{-- ========================================== --}}

    <style>
        .premium-pagination .pagination {
            display: flex;
            gap: 4px;
        }

        .premium-pagination .page-item .page-link {
            border-radius: 12px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            border: 1px solid #e2e8f0;
            padding: 8px 14px;
            color: #64748b;
            transition: all 0.3s;
        }

        .premium-pagination .page-item.active .page-link {
            background: #10b981;
            border-color: #10b981;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
    </style>


    <script>
        function kelolaLahan() {
            return {
                routeBase: @json($routePrefix.
                    '/kelola-lahan'),
                periodMode: @json((!empty($filters['start']) || !empty($filters['end'])) ? 'tanggal' : 'semua'),
                searchQuery: @json($filters['search'] ?? ''),
                selectedResor: @json($filters['resor'] ?? ''),
                selectedSektor: @json($filters['sektor'] ?? ''),
                selectedJenis: @json($filters['jenis'] ?? ''),
                selectedKomoditi: @json($filters['komoditi'] ?? ''),
                kategoriProduksi: @json($filters['kategori'] ?? 'semua'),
                defaultDate: @json(date('Y-m-d')),
                openResors: [],
                activeHistory: sessionStorage.getItem('kelolaLahan_activeHistory') || null,
                activeLahan: null,
                isEditMode: false,
                activeProcessId: null,
                activeTanamId: null,
                activePanenId: null,
                modalTanam: false,
                modalPanen: false,
                modalSerapan: false,
                modalValidasi: false,
                modalJadwalPanen: false,
                lahanStages: @json($lahanStagesMap ?? new stdClass()),
                validasiData: {
                    tanam: [],
                    panen: [],
                    serapan: [],
                    has_active: false
                },
                tolakModalData: {
                    isOpen: false,
                    type: '',
                    id: null,
                    lahanInfo: null,
                    alasan: '',
                    targetReject: 'tanam'
                },
                detailModalData: {
                    isOpen: false,
                    type: '',
                    data: null
                },
                formTanam: {
                    tgl_tanam: @json(date('Y-m-d')),
                    luas_tanam: 0,
                    jenis_bibit: '',
                    kebutuhan_bibit: '',
                    est_awal_panen: @json(date('Y-m-d')),
                    est_akhir_panen: @json(date('Y-m-d')),
                    keterangan_tanam: ''
                },
                formPanen: {
                    tgl_panen: @json(date('Y-m-d')),
                    luas_panen: 0,
                    status_panen: 1,
                    total_panen: 0,
                    keterangan_panen: ''
                },
                formSerapan: {
                    tgl_distribusi: @json(date('Y-m-d')),
                    total_distribusi: 0,
                    distribusi_ke: 1,
                    keterangan_serapan: ''
                },

                init() {
                    @foreach($data as $resor)
                    this.openResors.push(@json(str_replace('.', '_', $resor->id_tingkat)));
                    @endforeach
                },

                notify(type, title, message) {
                    if (typeof $notify === 'function') {
                        $notify(type, title, message);
                    } else {
                        alert(`${title}: ${message || ''}`);
                    }
                },

                async askConfirm(config) {
                    if (typeof $confirm === 'function') {
                        return await $confirm(config);
                    }
                    return confirm(config?.message || config?.title || 'Lanjutkan?');
                },

                toggleResor(id) {
                    if (this.openResors.includes(id)) {
                        this.openResors = this.openResors.filter(item => item !== id);
                    } else {
                        this.openResors.push(id);
                    }
                },

                isResorOpen(id) {
                    return this.openResors.includes(id);
                },

                toggleHistory(id) {
                    this.activeHistory = this.activeHistory === id ? null : id;
                    if (this.activeHistory) {
                        sessionStorage.setItem('kelolaLahan_activeHistory', this.activeHistory);
                    } else {
                        sessionStorage.removeItem('kelolaLahan_activeHistory');
                    }
                },

                openTanamModal(id_lahan, lahanData, mode, id_tanam = null) {    },

                get sisaLahan() {
                    if (!this.activeLahan) return 0;
                    const maxLahan = parseFloat(this.activeLahan.luas_lahan || 0);
                    let terpakai = 0;
                    if (Array.isArray(this.activeLahan.history_tanam) && this.activeLahan.history_tanam.length > 0) {
                        terpakai = this.activeLahan.history_tanam.reduce((sum, tanam) => {
                            if (this.isEditMode && String(tanam.id_tanam) === String(this.activeProcessId)) return sum;
                            return sum + parseFloat(tanam.luas_tanam || 0);
                        }, 0);
                    } else if (this.activeLahan.luas_tanam && !this.isEditMode) {
                        terpakai = parseFloat(this.activeLahan.luas_tanam || 0);
                    }
                    return Math.max(0, maxLahan - terpakai);
                },

                resetTanamForm() {
                    this.formTanam = {
                        tgl_tanam: this.defaultDate,
                        luas_tanam: '',
                        jenis_bibit: '',
                        kebutuhan_bibit: '',
                        est_awal_panen: this.defaultDate,
                        est_akhir_panen: this.defaultDate,
                        keterangan_tanam: ''
                    };
                },

                resetPanenForm() {
                    this.formPanen = {
                        tgl_panen: this.defaultDate,
                        luas_panen: '',
                        status_panen: 1,
                        total_panen: '',
                        keterangan_panen: ''
                    };
                },

                resetSerapanForm() {
                    this.formSerapan = {
                        tgl_distribusi: this.defaultDate,
                        total_distribusi: '',
                        distribusi_ke: 1,
                        keterangan_serapan: ''
                    };
                },

                openStageModal(idLahan, rowData, forcedStage = null, targetTanamId = null, targetPanenId = null) {
                    this.activeLahan = rowData || {};
                    this.isEditMode = false;
                    this.activeProcessId = null;
                    this.activeTanamId = targetTanamId ?? rowData?.id_tanam ?? null;
                    this.activePanenId = targetPanenId ?? rowData?.id_panen ?? null;
                    const stage = forcedStage !== null ? Number(forcedStage) : Number(this.lahanStages[idLahan] ?? 0);

                    if (stage === 1) {
                        this.resetPanenForm();
                        this.modalPanen = true;
                        return;
                    }
                    if (stage === 2) {
                        this.resetSerapanForm();
                        this.modalSerapan = true;
                        return;
                    }
                    this.resetTanamForm();
                    this.modalTanam = true;
                },

                editTanam(idTanam, rowData) {
                    this.activeLahan = rowData || {};
                    this.isEditMode = true;
                    this.activeProcessId = idTanam;
                    this.activeTanamId = idTanam;
                    this.formTanam = {
                        tgl_tanam: rowData?.tgl_tanam || this.defaultDate,
                        luas_tanam: rowData?.luas_tanam ?? 0,
                        jenis_bibit: rowData?.jenis_bibit || rowData?.nama_bibit || '',
                        kebutuhan_bibit: rowData?.kebutuhan_bibit ?? '',
                        est_awal_panen: rowData?.est_awal_panen || this.defaultDate,
                        est_akhir_panen: rowData?.est_akhir_panen || this.defaultDate,
                        keterangan_tanam: rowData?.keterangan_tanam || ''
                    };
                    this.modalTanam = true;
                },

                editPanen(idPanen, rowData) {
                    this.activeLahan = rowData || {};
                    this.isEditMode = true;
                    this.activeProcessId = idPanen;
                    this.activeTanamId = rowData?.id_tanam ?? this.activeTanamId;
                    this.activePanenId = idPanen;
                    this.formPanen = {
                        tgl_panen: rowData?.tgl_panen || this.defaultDate,
                        luas_panen: rowData?.luas_panen ?? 0,
                        status_panen: rowData?.status_panen ?? 1,
                        total_panen: rowData?.total_panen ?? 0,
                        keterangan_panen: rowData?.ket_panen || rowData?.keterangan_panen || ''
                    };
                    this.modalPanen = true;
                },

                editSerapan(idDistribusi, rowData) {
                    this.activeLahan = rowData || {};
                    this.isEditMode = true;
                    this.activeProcessId = idDistribusi;
                    this.activeTanamId = rowData?.id_tanam ?? this.activeTanamId;
                    this.activePanenId = rowData?.id_panen ?? this.activePanenId;
                    this.formSerapan = {
                        tgl_distribusi: rowData?.tgl_distribusi || this.defaultDate,
                        total_distribusi: rowData?.total_distribusi ?? 0,
                        distribusi_ke: rowData?.distribusi_ke ?? 1,
                        keterangan_serapan: rowData?.keterangan_distribusi || rowData?.keterangan_serapan || ''
                    };
                    this.modalSerapan = true;
                },

                openDetailModal(type, data) {
                    this.detailModalData = {
                        isOpen: true,
                        type,
                        data: data || {}
                    };
                },

                closeDetailModal() {
                    this.detailModalData.isOpen = false;
                },

                openTolakModal(id, type, lahanInfo = '') {
                    this.tolakModalData = {
                        isOpen: true,
                        type,
                        id,
                        lahanInfo: typeof lahanInfo === 'object' && lahanInfo !== null ? lahanInfo : {
                            nama_wilayah: lahanInfo || '-'
                        },
                        alasan: '',
                        targetReject: type || 'tanam'
                    };
                },

                async fetchJson(url, options) {
                    const response = await fetch(url, options);
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP ${response.status}`);
                    }
                    return data;
                },

                async submitTanam() {
                    if (!this.activeLahan?.id_lahan) {
                        this.notify('error', 'Data Tidak Lengkap', 'Lahan aktif tidak ditemukan.');
                        return;
                    }
                    const maxLahan = parseFloat(this.activeLahan.luas_lahan || 0);
                    const inputLuas = parseFloat(this.formTanam.luas_tanam || 0);
                    if (inputLuas > maxLahan) {
                        this.notify('warning', 'Validasi Gagal', `Luas tanam (${inputLuas} Ha) tidak boleh melebihi potensi lahan (${maxLahan} Ha).`);
                        return;
                    }
                    try {
                        const url = this.isEditMode ?
                            `/${this.routeBase}/tanam/${this.activeProcessId}` :
                            "{{ route($routePrefix.'.kelola-lahan.tanam.store') }}";
                        const method = this.isEditMode ? 'PUT' : 'POST';
                        const result = await this.fetchJson(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id_lahan: this.activeLahan.id_lahan,
                                ...this.formTanam
                            })
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menyimpan', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.modalTanam = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 1;
                        this.notify('success', 'Tanam Berhasil Dicatat', result.message || 'Data berhasil disimpan.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan Koneksi', error.message);
                    }
                },

                async submitPanen() {
                    if (!this.activeLahan?.id_lahan) {
                        this.notify('error', 'Data Tidak Lengkap', 'Lahan aktif tidak ditemukan.');
                        return;
                    }
                    const inputLuasPanen = parseFloat(this.formPanen.luas_panen || 0);
                    const maxTanam = parseFloat(this.activeLahan.luas_tanam || 0);
                    if (Number(this.formPanen.status_panen) !== 2 && maxTanam > 0 && inputLuasPanen > maxTanam) {
                        this.notify('warning', 'Validasi Gagal', `Luas panen (${inputLuasPanen} Ha) tidak boleh melebihi luas tanam (${maxTanam} Ha).`);
                        return;
                    }
                    try {
                        const url = this.isEditMode ?
                            `/${this.routeBase}/panen/${this.activeProcessId}` :
                            "{{ route($routePrefix.'.kelola-lahan.panen.store') }}";
                        const method = this.isEditMode ? 'PUT' : 'POST';
                        const result = await this.fetchJson(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id_lahan: this.activeLahan.id_lahan,
                                id_tanam: this.activeTanamId ?? this.activeLahan.id_tanam ?? null,
                                ...this.formPanen
                            })
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menyimpan', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.modalPanen = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 2;
                        this.notify('success', 'Panen Berhasil Dicatat', result.message || 'Data berhasil disimpan.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan Koneksi', error.message);
                    }
                },

                async submitSerapan() {
                    if (!this.activeLahan?.id_lahan) {
                        this.notify('error', 'Data Tidak Lengkap', 'Lahan aktif tidak ditemukan.');
                        return;
                    }
                    try {
                        const url = this.isEditMode ?
                            `/${this.routeBase}/serapan/${this.activeProcessId}` :
                            "{{ route($routePrefix.'.kelola-lahan.serapan.store') }}";
                        const method = this.isEditMode ? 'PUT' : 'POST';
                        const result = await this.fetchJson(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id_lahan: this.activeLahan.id_lahan,
                                id_tanam: this.activeTanamId ?? this.activeLahan.id_tanam ?? null,
                                id_panen: this.activePanenId ?? this.activeLahan.id_panen ?? null,
                                ...this.formSerapan
                            })
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menyimpan', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.modalSerapan = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 0;
                        this.notify('success', 'Serapan Berhasil Dicatat', result.message || 'Data berhasil disimpan.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan Koneksi', error.message);
                    }
                },

                async deleteTanam(id) {
                    const ok = await this.askConfirm({
                        type: 'danger',
                        title: 'Hapus Data Tanam?',
                        message: 'Seluruh data panen & serapan terkait juga akan ikut dihapus. Tindakan ini tidak dapat dibatalkan.',
                        confirmText: 'Ya, Hapus Semua'
                    });
                    if (!ok) return;
                    try {
                        const result = await this.fetchJson(`/${this.routeBase}/tanam/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menghapus', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.notify('success', 'Data Tanam Dihapus', result.message || 'Data berhasil dihapus.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan', error.message);
                    }
                },

                async deletePanen(id) {
                    const ok = await this.askConfirm({
                        type: 'danger',
                        title: 'Hapus Data Panen?',
                        message: 'Data serapan terkait juga akan ikut dihapus.',
                        confirmText: 'Ya, Hapus'
                    });
                    if (!ok) return;
                    try {
                        const result = await this.fetchJson(`/${this.routeBase}/panen/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menghapus', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.notify('success', 'Data Panen Dihapus', result.message || 'Data berhasil dihapus.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan', error.message);
                    }
                },

                async deleteSerapan(id) {
                    const ok = await this.askConfirm({
                        type: 'danger',
                        title: 'Hapus Data Serapan?',
                        message: 'Data serapan ini akan dihapus dari sistem secara permanen.',
                        confirmText: 'Ya, Hapus'
                    });
                    if (!ok) return;
                    try {
                        const result = await this.fetchJson(`/${this.routeBase}/serapan/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menghapus', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.notify('success', 'Data Serapan Dihapus', result.message || 'Data berhasil dihapus.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan', error.message);
                    }
                },

                async openValidasiModal(idLahan, rowData) {
                    this.activeLahan = rowData || {};
                    try {
                        const result = await this.fetchJson(`/${this.routeBase}/lahan/${idLahan}/validasi-data`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        this.validasiData = {
                            tanam: result.tanam || [],
                            panen: result.panen || [],
                            serapan: result.serapan || [],
                            has_active: !!result.has_active
                        };
                        this.modalValidasi = true;
                    } catch (error) {
                        this.notify('error', 'Gagal Memuat Data', 'Gagal mengambil data validasi: ' + error.message);
                    }
                },

                async submitValidasi() {
                    const ok = await this.askConfirm({
                        type: 'success',
                        title: 'Selesai Siklus?',
                        message: 'Siklus lahan ini akan diakhiri dan data kelola lahan akan diarsipkan. Lahan akan kosong kembali.',
                        confirmText: 'Ya, Selesai Siklus'
                    });
                    if (!ok || !this.activeLahan?.id_lahan) return;
                    try {
                        const result = await this.fetchJson(`/${this.routeBase}/lahan/${this.activeLahan.id_lahan}/validasi`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        if (result.success === false) {
                            this.notify('error', 'Gagal Memvalidasi', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.modalValidasi = false;
                        this.notify('success', 'Validasi Berhasil!', result.message || 'Siklus berhasil diselesaikan.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan Koneksi', error.message);
                    }
                },

                async sendTolakRequest(id, type, targetReject, alasan = '') {
                    const result = await this.fetchJson(`/${this.routeBase}/${type}/${id}/tolak`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            target_reject: targetReject || type || 'tanam',
                            alasan: alasan || ''
                        })
                    });
                    return result;
                },

                async submitTolakDirect(id, type) {
                    const ok = await this.askConfirm({
                        type: 'danger',
                        title: 'Tolak Validasi?',
                        message: 'Data ini akan ditolak dan perlu diperbaiki sebelum diajukan kembali.',
                        confirmText: 'Ya, Tolak'
                    });
                    if (!ok) return;
                    try {
                        const result = await this.sendTolakRequest(id, type, type, '');
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menolak', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.notify('success', 'Berhasil Ditolak', result.message || 'Data berhasil ditolak.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan Koneksi', error.message);
                    }
                },

                async submitTolakModal() {
                    const alasan = (this.tolakModalData.alasan || '').trim();
                    if (!this.tolakModalData.id || !this.tolakModalData.type) {
                        this.notify('error', 'Data Tidak Lengkap', 'Target penolakan tidak ditemukan.');
                        return;
                    }
                    if (!alasan) {
                        this.notify('warning', 'Alasan Diperlukan', 'Tuliskan alasan penolakan terlebih dahulu.');
                        return;
                    }
                    try {
                        const result = await this.sendTolakRequest(
                            this.tolakModalData.id,
                            this.tolakModalData.type,
                            this.tolakModalData.targetReject,
                            alasan
                        );
                        if (result.success === false) {
                            this.notify('error', 'Gagal Menolak', result.message || 'Terjadi kesalahan server.');
                            return;
                        }
                        this.tolakModalData.isOpen = false;
                        this.notify('success', 'Berhasil Ditolak', result.message || 'Data berhasil ditolak.');
                        setTimeout(() => window.location.reload(), 1500);
                    } catch (error) {
                        this.notify('error', 'Kesalahan Koneksi', error.message);
                    }
                },

                submitFilters() {
                    const url = new URL(window.location.href);
                    const params = {
                        resor: this.selectedResor,
                        sektor: this.selectedSektor,
                        jenis: this.selectedJenis,
                        komoditi: this.selectedKomoditi,
                        kategori: this.kategoriProduksi,
                        search: this.searchQuery
                    };

                    Object.entries(params).forEach(([key, value]) => {
                        if (value === null || value === undefined || String(value).trim() === '') {
                            url.searchParams.delete(key);
                        } else {
                            url.searchParams.set(key, value);
                        }
                    });

                    if (this.periodMode === 'tanggal') {
                        const startDate = document.getElementById('start_date')?.value || '';
                        const endDate = document.getElementById('end_date')?.value || '';
                        if (startDate) url.searchParams.set('start_date', startDate);
                        else url.searchParams.delete('start_date');
                        if (endDate) url.searchParams.set('end_date', endDate);
                        else url.searchParams.delete('end_date');
                    } else {
                        url.searchParams.delete('start_date');
                        url.searchParams.delete('end_date');
                    }

                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                }
            };
        }
    </script>
    {{-- ========================================== --}}
    {{-- MODALS SECTION - PRODUCTION FLOW --}}
    {{-- ========================================== --}}

    <!-- MODAL PROSES TANAM -->
    <div x-show="modalTanam"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak x-transition.opacity>
        <div @click.outside="modalTanam = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 bg-gradient-to-r from-emerald-600 to-teal-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center border border-white/20 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">INPUT PROSES TANAM</h3>
                        <p class="text-[10px] text-emerald-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                    </div>
                </div>
                <button @click="modalTanam = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
                {{-- Kapasitas Lahan Indicator --}}
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">📐 Kapasitas Potensi Lahan</span>
                        <span class="text-[10px] font-black text-emerald-800 bg-emerald-200 px-2 py-0.5 rounded-lg" x-text="(activeLahan?.luas_lahan ?? 0) + ' Ha'"></span>
                    </div>
                    <div class="text-[10px] font-bold text-emerald-600 mb-1">
                        Masukkan luas tanam ≤ sisa kapasitas lahan. Siklus tanam bisa diulang setelah panen & serapan divalidasi.
                    </div>
                    <div class="mt-2 h-2 bg-emerald-100 rounded-full overflow-hidden">
                        <div class="h-2 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-500"
                            :style="'width:' + Math.min(100, ((parseFloat(formTanam.luas_tanam)||0) / Math.max(0.01, parseFloat(activeLahan?.luas_lahan||1))) * 100) + '%'"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-[9px] text-emerald-500 font-bold">0 Ha</span>
                        <span class="text-[9px] font-black" :class="(parseFloat(formTanam.luas_tanam)||0) > parseFloat(activeLahan?.luas_lahan||0) ? 'text-rose-600' : 'text-emerald-700'" x-text="'Input: ' + (parseFloat(formTanam.luas_tanam)||0).toFixed(2) + ' Ha'"></span>
                        <span class="text-[9px] text-emerald-600 font-bold" x-text="'Max: ' + (activeLahan?.luas_lahan ?? 0) + ' Ha'"></span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Tanggal Tanam</label>
                        <input type="date" x-model="formTanam.tgl_tanam" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Luas Tanam (Ha)</label>
                        <input type="number" step="0.01" x-model="formTanam.luas_tanam" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Jenis Bibit</label>
                        <input type="text" placeholder="Contoh: IR-64, Ciherang" x-model="formTanam.jenis_bibit" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all uppercase">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Kebutuhan Bibit (Kg)</label>
                        <input type="number" placeholder="0" x-model="formTanam.kebutuhan_bibit" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all">
                    </div>
                </div>
                <div class="p-5 bg-emerald-50 rounded-3xl border border-emerald-100/50">
                    <label class="block text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-4">Estimasi Panen</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tgl. Awal</span>
                            <input type="date" x-model="formTanam.est_awal_panen" class="w-full text-xs font-bold bg-white border border-emerald-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-emerald-500/10 outline-none">
                        </div>
                        <div>
                            <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tgl. Akhir</span>
                            <input type="date" x-model="formTanam.est_akhir_panen" class="w-full text-xs font-bold bg-white border border-emerald-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-emerald-500/10 outline-none">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Keterangan Lain</label>
                    <textarea rows="3" placeholder="Tambahkan catatan khusus jika ada..." x-model="formTanam.keterangan_tanam" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all resize-none"></textarea>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button @click="modalTanam = false" class="flex-1 px-6 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest shadow-sm">Batal</button>
                <button @click="submitTanam()" class="flex-[2] px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-emerald-500/20 active:scale-[0.98] transition-all shadow-lg">Simpan Data Tanam</button>
            </div>
        </div>
    </div>

    <!-- MODAL PROSES PANEN -->
    <div x-show="modalPanen"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak x-transition.opacity>
        <div @click.outside="modalPanen = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 bg-gradient-to-r from-amber-500 to-orange-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center border border-white/20 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">INPUT PROSES PANEN</h3>
                        <p class="text-[10px] text-orange-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                    </div>
                </div>
                <button @click="modalPanen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
                {{-- Gate: Tanam harus tervalidasi sebelum bisa input panen --}}
                <div class="p-4 bg-amber-50 border border-amber-300 rounded-2xl flex items-start gap-3" x-show="!activeLahan?.tanam_valid_oleh">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                    </svg>
                    <div>
                        <p class="text-[10px] font-black text-amber-800 uppercase tracking-wide">⏳ Menunggu Validasi Tanam</p>
                        <p class="text-[10px] font-bold text-amber-600 mt-0.5">Data tanam untuk lahan ini belum divalidasi oleh Admin. Panen hanya bisa diinput setelah data tanam divalidasi.</p>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-1">Jenis Panen</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-amber-50 transition-colors group">
                            <input type="radio" name="jenis_panen" value="1" x-model.number="formPanen.status_panen" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-amber-500 peer-checked:border-amber-500 peer-checked:bg-amber-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">1-Panen Normal</span>
                        </label>
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-rose-50 transition-colors group">
                            <input type="radio" name="jenis_panen" value="2" x-model.number="formPanen.status_panen" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-rose-500 peer-checked:border-rose-500 peer-checked:bg-rose-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">2-Gagal Panen</span>
                        </label>
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-amber-50 transition-colors group">
                            <input type="radio" name="jenis_panen" value="3" x-model.number="formPanen.status_panen" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-amber-500 peer-checked:border-amber-500 peer-checked:bg-amber-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">3-Panen Dini</span>
                        </label>
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-amber-50 transition-colors group">
                            <input type="radio" name="jenis_panen" value="4" x-model.number="formPanen.status_panen" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-amber-500 peer-checked:border-amber-500 peer-checked:bg-amber-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">4-Panen Tebasan</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Tanggal Panen</label>
                        <input type="date" x-model="formPanen.tgl_panen" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all">
                    </div>
                    <div class="col-span-1" x-effect="if(formPanen.status_panen == 2) { formPanen.luas_panen = 0; }">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Luas Panen (Ha)</label>
                        <input type="number" step="0.01" x-model="formPanen.luas_panen" :disabled="formPanen.status_panen == 2" :class="formPanen.status_panen == 2 ? 'opacity-50 bg-slate-200 cursor-not-allowed' : 'bg-slate-50 focus:ring-amber-500/10 focus:border-amber-500'" class="w-full text-xs font-bold border border-slate-200 rounded-xl px-4 py-3.5 outline-none transition-all focus:ring-4">
                    </div>
                    <div class="col-span-1" x-effect="if(formPanen.status_panen == 2) { formPanen.total_panen = 0; }">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Hasil (Ton)</label>
                        <input type="number" step="0.01" x-model="formPanen.total_panen" :disabled="formPanen.status_panen == 2" :class="formPanen.status_panen == 2 ? 'opacity-50 bg-slate-200 cursor-not-allowed' : 'bg-slate-50 focus:ring-amber-500/10 focus:border-amber-500'" class="w-full text-xs font-bold border border-slate-200 rounded-xl px-4 py-3.5 outline-none transition-all focus:ring-4">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Keterangan Lain</label>
                    <textarea rows="3" placeholder="Tambahkan catatan khusus hasil panen..." x-model="formPanen.keterangan_panen" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all resize-none"></textarea>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button @click="modalPanen = false" class="flex-1 px-6 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest shadow-sm">Batal</button>
                <button @click="submitPanen()" class="flex-[2] px-6 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-amber-500/20 active:scale-[0.98] transition-all shadow-lg">Simpan Hasil Panen</button>
            </div>
        </div>
    </div>

    <!-- MODAL SERAPAN DATA -->
    <div x-show="modalSerapan"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak x-transition.opacity>
        <div @click.outside="modalSerapan = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center border border-white/20 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">INPUT SERAPAN HASIL</h3>
                        <p class="text-[10px] text-blue-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                    </div>
                </div>
                <button @click="modalSerapan = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
                {{-- Gate: Panen harus tervalidasi sebelum bisa input serapan --}}
                <div class="p-4 bg-blue-50 border border-blue-300 rounded-2xl flex items-start gap-3" x-show="!activeLahan?.panen_valid_oleh && !isEditMode">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                    </svg>
                    <div>
                        <p class="text-[10px] font-black text-blue-800 uppercase tracking-wide">⏳ Menunggu Validasi Panen</p>
                        <p class="text-[10px] font-bold text-blue-600 mt-0.5">Data panen untuk lahan ini belum divalidasi oleh Admin. Serapan hanya bisa diinput setelah data panen divalidasi.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">

                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Tanggal Serapan</label>
                        <input type="date" x-model="formSerapan.tgl_distribusi" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Total Serapan (Ton)</label>
                        <input type="number" step="0.01" placeholder="0.00" x-model="formSerapan.total_distribusi" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-1">Tujuan Serapan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-blue-50 transition-colors group">
                            <input type="radio" name="tujuan_serapan" value="1" x-model.number="formSerapan.distribusi_ke" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">1-Bulog</span>
                        </label>
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-blue-50 transition-colors group">
                            <input type="radio" name="tujuan_serapan" value="2" x-model.number="formSerapan.distribusi_ke" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">2-Pabrik Pakan</span>
                        </label>
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-blue-50 transition-colors group">
                            <input type="radio" name="tujuan_serapan" value="3" x-model.number="formSerapan.distribusi_ke" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">3-Tengkulak</span>
                        </label>
                        <label class="relative flex items-center p-4 border border-slate-200 rounded-2xl cursor-pointer hover:bg-blue-50 transition-colors group">
                            <input type="radio" name="tujuan_serapan" value="4" x-model.number="formSerapan.distribusi_ke" class="peer hidden">
                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center mr-3 group-hover:border-blue-500 peer-checked:border-blue-500 peer-checked:bg-blue-500">
                                <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                            </div>
                            <span class="text-[10px] font-black text-slate-600 uppercase tracking-wider peer-checked:text-black">4-Konsumsi Sendiri</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Keterangan Lain</label>
                    <textarea rows="3" placeholder="Tambahkan catatan khusus serapan..." x-model="formSerapan.keterangan_serapan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button @click="modalSerapan = false" class="flex-1 px-6 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest shadow-sm">Batal</button>
                <button @click="submitSerapan()" class="flex-[2] px-6 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-blue-500/20 active:scale-[0.98] transition-all shadow-lg">Simpan Data Serapan</button>
            </div>
        </div>
    </div>

    <!-- MODAL VALIDASI LAHAN -->
    <div x-show="modalValidasi"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak x-transition.opacity>
        <div @click.outside="modalValidasi = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center border border-white/20 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">VALIDASI DATA LAHAN</h3>
                        <p class="text-[10px] text-indigo-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                    </div>
                </div>
                <button @click="modalValidasi = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">

                <div x-show="validasiData.tanam.length === 0 && validasiData.panen.length === 0 && validasiData.serapan.length === 0" class="text-center p-4 text-slate-500 font-bold text-xs">
                    <span x-show="validasiData.has_active" class="text-emerald-600">Semua data pada siklus ini telah tervalidasi. Anda dapat menyelesaikan siklus ini.</span>
                    <span x-show="!validasiData.has_active">Siklus lahan ini belum dimulai atau sudah selesai (kosong).</span>
                </div>

                <!-- List Tanam -->
                <div x-show="validasiData.tanam.length > 0">
                    <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2 px-1 border-b border-emerald-100 pb-2">Data Tanam (Belum Validasi)</h4>
                    <div class="space-y-2">
                        <template x-for="t in validasiData.tanam" :key="t.id_tanam">
                            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 flex justify-between items-center">
                                <div>
                                    <div class="text-[11px] font-bold text-slate-700" x-text="'Tgl: ' + t.tgl_tanam + ' | Bibit: ' + t.nama_bibit"></div>
                                    <div class="text-[9px] text-slate-500" x-text="'Keterangan: ' + (t.keterangan_tanam || '-')"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-xs font-black text-emerald-600 bg-white px-2 py-1 rounded shadow-sm border border-emerald-100" x-text="t.luas_tanam + ' Ha'"></div>
                                    <form :action="`/${routeBase}/tanam/${t.id_tanam}/validasi`" method="POST" class="m-0">
                                        @csrf @method('PUT')
                                        <button type="submit" class="px-2 py-1 bg-emerald-500 text-white rounded shadow-sm text-[10px] font-bold hover:bg-emerald-600">Validasi</button>
                                    </form>
                                    <template x-if="validasiData.serapan.length > 0 || !validasiData.has_active">
                                        <button type="button" @click="submitTolakDirect(t.id_tanam, 'tanam', activeLahan?.nama_wilayah || activeLahan?.alamat_lahan)" class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded shadow-sm text-[10px] font-bold hover:bg-rose-500 hover:text-white transition-colors">Tolak</button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- List Panen -->
                <div x-show="validasiData.panen.length > 0">
                    <h4 class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-2 px-1 border-b border-amber-100 pb-2">Data Panen (Belum Validasi)</h4>
                    <div class="space-y-2">
                        <template x-for="p in validasiData.panen" :key="p.id_panen">
                            <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 flex justify-between items-center">
                                <div>
                                    <div class="text-[11px] font-bold text-slate-700" x-text="'Tgl: ' + p.tgl_panen + ' | Status: ' + (p.status_panen == 1 ? 'Normal' : (p.status_panen == 2 ? 'Gagal' : 'Lainnya'))"></div>
                                    <div class="text-[9px] text-slate-500" x-text="'Keterangan: ' + (p.ket_panen || '-')"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-right">
                                        <div class="text-[10px] font-black text-amber-600 bg-white px-2 py-0.5 rounded shadow-sm border border-amber-100 mb-1" x-text="p.luas_panen + ' Ha'"></div>
                                        <div class="text-[10px] font-black text-amber-600 bg-white px-2 py-0.5 rounded shadow-sm border border-amber-100" x-text="p.total_panen + ' Ton'"></div>
                                    </div>
                                    <form :action="`/${routeBase}/panen/${p.id_panen}/validasi`" method="POST" class="m-0">
                                        @csrf @method('PUT')
                                        <button type="submit" class="px-2 py-1 bg-amber-500 text-white rounded shadow-sm text-[10px] font-bold hover:bg-amber-600">Validasi</button>
                                    </form>
                                    <template x-if="validasiData.serapan.length > 0 || !validasiData.has_active">
                                        <button type="button" @click="submitTolakDirect(p.id_panen, 'panen', activeLahan?.nama_wilayah || activeLahan?.alamat_lahan)" class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded shadow-sm text-[10px] font-bold hover:bg-rose-500 hover:text-white transition-colors">Tolak</button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- List Serapan -->
                <div x-show="validasiData.serapan.length > 0">
                    <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-2 px-1 border-b border-blue-100 pb-2">Data Serapan (Belum Validasi)</h4>
                    <div class="space-y-2">
                        <template x-for="s in validasiData.serapan" :key="s.id_distribusi">
                            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 flex justify-between items-center">
                                <div>
                                    <div class="text-[11px] font-bold text-slate-700" x-text="'Tgl: ' + s.tgl_distribusi + ' | Tujuan: ' + (s.distribusi_ke == 1 ? 'Bulog' : (s.distribusi_ke == 2 ? 'Pabrik Pakan' : (s.distribusi_ke == 3 ? 'Tengkulak' : 'Konsumsi Sendiri')))"></div>
                                    <div class="text-[9px] text-slate-500" x-text="'Keterangan: ' + (s.keterangan_distribusi || '-')"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-xs font-black text-blue-600 bg-white px-2 py-1 rounded shadow-sm border border-blue-100" x-text="s.total_distribusi + ' Ton'"></div>
                                    <form :action="`/${routeBase}/serapan/${s.id_distribusi}/validasi`" method="POST" class="m-0">
                                        @csrf @method('PUT')
                                        <button type="submit" class="px-2 py-1 bg-blue-500 text-white rounded shadow-sm text-[10px] font-bold hover:bg-blue-600">Validasi</button>
                                    </form>
                                    <button type="button" @click="submitTolakDirect(s.id_distribusi, 'serapan', activeLahan?.nama_wilayah || activeLahan?.alamat_lahan)" class="px-2 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded shadow-sm text-[10px] font-bold hover:bg-rose-500 hover:text-white transition-colors">Tolak</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                <button @click="modalValidasi = false" class="flex-1 px-6 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest shadow-sm">Tutup</button>
                <button @click="submitValidasi()" x-show="validasiData.tanam.length === 0 && validasiData.panen.length === 0 && validasiData.serapan.length === 0 && validasiData.has_active" class="flex-[2] px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-emerald-500/20 active:scale-[0.98] transition-all shadow-lg">Selesai Siklus Kelola Lahan</button>
            </div>
        </div>
    </div>


    <!-- MODAL DETAIL PRODUKSI -->
    <div x-show="detailModalData.isOpen"
        class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-cloak x-transition.opacity>
        <div @click.outside="closeDetailModal()" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">Detail Produksi</h3>
                    <p class="text-[10px] text-slate-300 font-bold mt-1 uppercase" x-text="(detailModalData.type || 'data').toUpperCase()"></p>
                </div>
                <button @click="closeDetailModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-8 overflow-y-auto custom-scrollbar space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/70">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Wilayah</p>
                        <p class="text-sm font-black text-slate-800 uppercase" x-text="detailModalData.data?.nama_wilayah || detailModalData.data?.alamat_lahan || '-' "></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/70">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Kecamatan / Poktan</p>
                        <p class="text-sm font-black text-slate-800 uppercase" x-text="[detailModalData.data?.nama_kecamatan, detailModalData.data?.poktan].filter(Boolean).join(' • ') || '-' "></p>
                    </div>
                </div>

                <div x-show="detailModalData.type === 'tanam'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-emerald-100 bg-emerald-50/60">
                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Luas Tanam</p>
                        <p class="text-sm font-black text-emerald-700" x-text="(detailModalData.data?.luas_tanam ?? 0) + ' HA'"></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-emerald-100 bg-emerald-50/60">
                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Tanggal Tanam</p>
                        <p class="text-sm font-black text-emerald-700" x-text="detailModalData.data?.tgl_tanam || '-' "></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-emerald-100 bg-emerald-50/60 sm:col-span-2">
                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Estimasi Panen</p>
                        <p class="text-sm font-black text-emerald-700" x-text="[(detailModalData.data?.est_awal_panen || '-'), (detailModalData.data?.est_akhir_panen || '-')].join(' s/d ')"></p>
                    </div>
                </div>

                <div x-show="detailModalData.type === 'panen'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-amber-100 bg-amber-50/60">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">Luas Panen</p>
                        <p class="text-sm font-black text-amber-700" x-text="(detailModalData.data?.luas_panen ?? 0) + ' HA'"></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-amber-100 bg-amber-50/60">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">Total Panen</p>
                        <p class="text-sm font-black text-amber-700" x-text="(detailModalData.data?.total_panen ?? 0) + ' TON'"></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-amber-100 bg-amber-50/60 sm:col-span-2">
                        <p class="text-[9px] font-black text-amber-500 uppercase tracking-widest mb-1">Tanggal Panen</p>
                        <p class="text-sm font-black text-amber-700" x-text="detailModalData.data?.tgl_panen || '-' "></p>
                    </div>
                </div>

                <div x-show="detailModalData.type === 'serapan'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl border border-blue-100 bg-blue-50/60">
                        <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1">Total Serapan</p>
                        <p class="text-sm font-black text-blue-700" x-text="(detailModalData.data?.total_distribusi ?? 0) + ' TON'"></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-blue-100 bg-blue-50/60">
                        <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1">Tanggal Distribusi</p>
                        <p class="text-sm font-black text-blue-700" x-text="detailModalData.data?.tgl_distribusi || '-' "></p>
                    </div>
                    <div class="p-4 rounded-2xl border border-blue-100 bg-blue-50/60 sm:col-span-2">
                        <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-1">Tujuan</p>
                        <p class="text-sm font-black text-blue-700" x-text="detailModalData.data?.distribusi_ke == 1 ? 'BULOG' : (detailModalData.data?.distribusi_ke == 2 ? 'PABRIK PAKAN' : (detailModalData.data?.distribusi_ke == 3 ? 'TENGKULAK' : 'KONSUMSI SENDIRI'))"></p>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button @click="closeDetailModal()" class="px-6 py-3 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL TOLAK VALIDASI (TELEPORT KE BODY) -->
    <template x-teleport="body">
        <div x-show="tolakModalData.isOpen"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            x-cloak x-transition.opacity>
            <div @click.outside="tolakModalData.isOpen = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 flex flex-col">
                <div class="px-8 py-6 bg-rose-600 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">TOLAK VALIDASI <span x-text="tolakModalData.type"></span></h3>
                            <p class="text-[10px] text-rose-100 font-bold mt-1 uppercase" x-text="'Lahan: ' + (tolakModalData.lahanInfo?.nama_wilayah || '-')"></p>
                        </div>
                    </div>
                </div>
                <div class="p-8 space-y-5">
                    <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 flex gap-3">
                        <div class="text-rose-500 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-[11px] font-bold text-rose-800 leading-relaxed">
                            Pilih tahap yang akan ditolak, lalu tulis alasan. Operator Polsek akan menerima notifikasi dan diminta memperbaiki data sebelum bisa divalidasi ulang.
                        </div>
                    </div>

                    {{-- Pilih Tahap yang Ditolak --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 ml-1">Tahap yang Ditolak <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center gap-2.5 p-3 border-2 rounded-xl cursor-pointer transition-all"
                                :class="tolakModalData.targetReject === 'tanam' ? 'border-rose-500 bg-rose-50' : 'border-slate-200 bg-white hover:border-rose-200'">
                                <input type="radio" name="target_reject" value="tanam" x-model="tolakModalData.targetReject" class="hidden">
                                <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                    :class="tolakModalData.targetReject === 'tanam' ? 'border-rose-500 bg-rose-500' : 'border-slate-300'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="tolakModalData.targetReject === 'tanam'"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase" :class="tolakModalData.targetReject === 'tanam' ? 'text-rose-700' : 'text-slate-600'">🌱 Tanam</div>
                                    <div class="text-[8px] font-bold text-slate-400 leading-tight">Reset semua tahap</div>
                                </div>
                            </label>
                            <label class="relative flex items-center gap-2.5 p-3 border-2 rounded-xl cursor-pointer transition-all"
                                :class="tolakModalData.targetReject === 'panen' ? 'border-amber-500 bg-amber-50' : 'border-slate-200 bg-white hover:border-amber-200'">
                                <input type="radio" name="target_reject" value="panen" x-model="tolakModalData.targetReject" class="hidden">
                                <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                    :class="tolakModalData.targetReject === 'panen' ? 'border-amber-500 bg-amber-500' : 'border-slate-300'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="tolakModalData.targetReject === 'panen'"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase" :class="tolakModalData.targetReject === 'panen' ? 'text-amber-700' : 'text-slate-600'">🌾 Panen</div>
                                    <div class="text-[8px] font-bold text-slate-400 leading-tight">Tanam tetap valid</div>
                                </div>
                            </label>
                            <label class="relative flex items-center gap-2.5 p-3 border-2 rounded-xl cursor-pointer transition-all"
                                :class="tolakModalData.targetReject === 'serapan' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white hover:border-blue-200'">
                                <input type="radio" name="target_reject" value="serapan" x-model="tolakModalData.targetReject" class="hidden">
                                <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                    :class="tolakModalData.targetReject === 'serapan' ? 'border-blue-500 bg-blue-500' : 'border-slate-300'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="tolakModalData.targetReject === 'serapan'"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase" :class="tolakModalData.targetReject === 'serapan' ? 'text-blue-700' : 'text-slate-600'">📦 Serapan</div>
                                    <div class="text-[8px] font-bold text-slate-400 leading-tight">Tanam &amp; Panen tetap valid</div>
                                </div>
                            </label>
                            <label class="relative flex items-center gap-2.5 p-3 border-2 rounded-xl cursor-pointer transition-all"
                                :class="tolakModalData.targetReject === 'semua' ? 'border-rose-700 bg-rose-100' : 'border-slate-200 bg-white hover:border-rose-300'">
                                <input type="radio" name="target_reject" value="semua" x-model="tolakModalData.targetReject" class="hidden">
                                <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                    :class="tolakModalData.targetReject === 'semua' ? 'border-rose-700 bg-rose-700' : 'border-slate-300'">
                                    <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="tolakModalData.targetReject === 'semua'"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase" :class="tolakModalData.targetReject === 'semua' ? 'text-rose-800' : 'text-slate-600'">❌ Semua</div>
                                    <div class="text-[8px] font-bold text-slate-400 leading-tight">Reset seluruh siklus</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea rows="4" x-model="tolakModalData.alasan" placeholder="Tuliskan alasan penolakan secara jelas..." class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 outline-none transition-all resize-none"></textarea>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button @click="tolakModalData.isOpen = false" class="flex-1 px-6 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest shadow-sm">Batal</button>
                    <button @click="submitTolakModal()" class="flex-[2] px-6 py-3.5 bg-gradient-to-r from-rose-500 to-red-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-rose-500/20 active:scale-[0.98] transition-all shadow-lg">Kirim Penolakan</button>
                </div>
            </div>
        </div>
    </template>

    {{-- Jadwal Panen Mendatang Modal --}}
    <div @keydown.escape.window="modalJadwalPanen = false"
        x-show="modalJadwalPanen"
        style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center">

        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            x-show="modalJadwalPanen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="modalJadwalPanen = false"></div>

        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col m-4 transform transition-all"
            x-show="modalJadwalPanen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            {{-- Header --}}
            <div class="px-7 py-5 bg-gradient-to-r from-amber-900 to-amber-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/30 flex items-center justify-center text-amber-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-[0.2em] leading-none">Jadwal Panen Mendatang</h3>
                        <p class="text-[10px] font-bold text-amber-300 uppercase tracking-wider mt-1">Filter berdasarkan wilayah & waktu</p>
                    </div>
                </div>
                <button @click="modalJadwalPanen = false" class="p-2 text-amber-200 hover:text-white hover:bg-amber-700/50 rounded-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route($routePrefix.'.kelola-lahan.daftar.index') }}" class="px-7 py-4 bg-amber-50/50 border-b border-amber-100 flex flex-wrap items-end gap-3 shrink-0">
                @foreach(request()->except(['panen_bulan','panen_tahun','panen_resor','panen_start','panen_end']) as $k => $v)
                @if($v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                @endforeach

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-[9px] font-black text-amber-700 uppercase tracking-[0.2em] mb-1.5">WILAYAH / POLRES</label>
                    <select name="panen_resor" onchange="this.form.submit()" class="w-full h-10 text-[10px] font-black px-3 bg-white text-slate-700 border border-amber-200 rounded-xl focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all uppercase cursor-pointer shadow-sm">
                        <option value="">SEMUA WILAYAH</option>
                        @foreach($polresForHarvest as $pr)
                        <option value="{{ $pr->id_tingkat }}" {{ ($harvestFilters['resor'] ?? '') == $pr->id_tingkat ? 'selected' : '' }}>
                            {{ $pr->nama_tingkat }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-32">
                    <label class="block text-[9px] font-black text-amber-700 uppercase tracking-[0.2em] mb-1.5">MULAI</label>
                    <input type="date" name="panen_start" onchange="this.form.submit()" value="{{ $harvestFilters['panen_start'] ?? '' }}" class="w-full h-10 text-[10px] font-black px-3 bg-white text-slate-700 border border-amber-200 rounded-xl focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all cursor-pointer shadow-sm">
                </div>

                <div class="w-32">
                    <label class="block text-[9px] font-black text-amber-700 uppercase tracking-[0.2em] mb-1.5">SAMPAI</label>
                    <input type="date" name="panen_end" onchange="this.form.submit()" value="{{ $harvestFilters['panen_end'] ?? '' }}" class="w-full h-10 text-[10px] font-black px-3 bg-white text-slate-700 border border-amber-200 rounded-xl focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all cursor-pointer shadow-sm">
                </div>

                <a href="{{ route($routePrefix.'.kelola-lahan.daftar.index') }}" class="h-10 px-4 bg-slate-100 text-slate-600 rounded-xl text-[10px] font-black uppercase hover:bg-slate-200 transition-all flex items-center justify-center">
                    Reset
                </a>
            </form>

            {{-- Table Content --}}
            <div class="overflow-auto custom-scrollbar flex-1 p-0">
                @if($upcomingHarvests->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center px-6">
                    <div class="w-20 h-20 bg-amber-50 rounded-[2rem] flex items-center justify-center mb-5 border border-amber-100">
                        <svg class="w-10 h-10 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Tidak Ada Jadwal Panen</p>
                    <p class="text-xs font-bold text-slate-300 mt-2">Coba ubah filter wilayah atau rentang waktu</p>
                </div>
                @else
                <table class="w-full text-left min-w-[600px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-amber-50/90 backdrop-blur-sm border-b border-amber-100">
                            <th class="px-6 py-4 text-[10px] font-black text-amber-700 uppercase tracking-[0.2em]">WILAYAH / POLSEK</th>
                            <th class="px-6 py-4 text-[10px] font-black text-amber-700 uppercase tracking-[0.2em]">LAHAN / POKTAN</th>
                            <th class="px-6 py-4 text-[10px] font-black text-amber-700 uppercase tracking-[0.2em]">LUAS TANAM</th>
                            <th class="px-6 py-4 text-[10px] font-black text-amber-700 uppercase tracking-[0.2em]">EST. PANEN</th>
                            <th class="px-6 py-4 text-[10px] font-black text-amber-700 uppercase tracking-[0.2em]">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-50">
                        @foreach($upcomingHarvests as $harvest)
                        @php
                        $estAwal = \Carbon\Carbon::parse($harvest->est_awal_panen);
                        $estAkhir = \Carbon\Carbon::parse($harvest->est_akhir_panen ?? $harvest->est_awal_panen);
                        $now = \Carbon\Carbon::now();
                        $daysLeft = $now->diffInDays($estAwal, false);
                        if ($daysLeft > 30) { $urgencyClass = 'text-slate-500'; $urgencyBg = 'bg-slate-50 border-slate-200'; $urgencyLabel = 'Jauh'; }
                        elseif ($daysLeft > 7) { $urgencyClass = 'text-amber-600'; $urgencyBg = 'bg-amber-50 border-amber-200'; $urgencyLabel = $daysLeft . ' Hari'; }
                        elseif ($daysLeft >= 0) { $urgencyClass = 'text-rose-600'; $urgencyBg = 'bg-rose-50 border-rose-200'; $urgencyLabel = $daysLeft . ' Hari!'; }
                        else { $urgencyClass = 'text-emerald-600'; $urgencyBg = 'bg-emerald-50 border-emerald-200'; $urgencyLabel = 'Melewati Est.'; }
                        @endphp
                        <tr class="hover:bg-amber-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <p class="text-xs font-black text-slate-800 uppercase tracking-tight mb-0.5">{{ $harvest->nama_wilayah ?? '-' }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">{{ str_replace('POLSEK ', '', $harvest->nama_tingkat) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-black text-slate-700 uppercase mb-0.5">{{ $harvest->poktan ?? $harvest->alamat_lahan ?? '-' }}</p>
                                <p class="text-[10px] font-bold text-slate-400">ID Lahan: {{ $harvest->id_lahan }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-100 shadow-sm inline-block">
                                    {{ number_format($harvest->luas_tanam, 2) }} HA
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-black text-slate-700 mb-0.5">{{ $estAwal->format('d M Y') }}</p>
                                @if($estAwal->format('Y-m-d') !== $estAkhir->format('Y-m-d'))
                                <p class="text-[10px] font-bold text-slate-400">s/d {{ $estAkhir->format('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-xl border text-[10px] font-black uppercase tracking-widest shadow-sm inline-flex items-center justify-center min-w-[80px] {{ $urgencyBg }} {{ $urgencyClass }}">
                                    {{ $urgencyLabel }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>
{{-- END x-data="kelolaLahan()" --}}
@endsection