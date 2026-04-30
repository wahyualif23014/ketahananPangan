@extends('layouts.app')

@section('header', 'Data Produksi Lahan')

@section('content')
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
                    DAFTAR <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">KELOLA</span>
                </h2>
                @if(collect($filters)->filter()->isNotEmpty())
                <a href="{{ route('admin.kelola-lahan.daftar.index') }}" class="text-[10px] font-black text-rose-500 hover:text-rose-700 bg-white border border-slate-200 px-2.5 py-1.5 rounded-xl transition-all shadow-sm">
                    RESET FILTER
                </a>
                @endif
            </div>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Monitoring statistik produksi, tanam, dan panen lahan di seluruh wilayah operasional.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group hidden sm:block">
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
                class="p-3.5 bg-slate-900 text-emerald-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
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
                            <template x-for="p in filteredPolseks" :key="p.id_tingkat">
                                <option :value="p.id_tingkat" x-text="p.id_tingkat + ' - ' + p.nama_tingkat"></option>
                            </template>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-6 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">PERIODE WAKTU</label>
                        <div class="flex items-center h-12 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60 w-fit">
                            <button type="button" @click="periodMode = 'semua'; submitFilters()"
                                :class="periodMode === 'semua' ? 'bg-white shadow-md text-emerald-600 border border-emerald-100' : 'text-slate-400 hover:text-slate-600'"
                                class="px-5 h-full text-[10px] font-black uppercase tracking-widest rounded-lg transition-all duration-300">
                                SEMUA
                            </button>
                            <button type="button" @click="periodMode = 'tanggal'"
                                :class="periodMode === 'tanggal' ? 'bg-white shadow-md text-emerald-600 border border-emerald-100' : 'text-slate-400 hover:text-slate-600'"
                                class="px-5 h-full text-[10px] font-black uppercase tracking-widest rounded-lg transition-all duration-300">
                                TANGGAL
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] transition-opacity ml-1" :class="periodMode === 'semua' ? 'opacity-30' : ''">MULAI</label>
                            <input type="date" id="start_date" value="{{ $filters['start'] ?? '' }}"
                                @change="submitFilters()"
                                :disabled="periodMode === 'semua'"
                                :class="periodMode === 'semua' ? 'bg-slate-50/50 text-slate-300 border-slate-100 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 cursor-text'"
                                class="w-full sm:w-40 h-10 text-[11px] font-bold px-4 border rounded-xl outline-none transition-all">
                        </div>
                        <div class="pt-5 hidden sm:block text-slate-300 font-black text-[10px]">SAMPAI</div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] transition-opacity ml-1" :class="periodMode === 'semua' ? 'opacity-30' : ''">SELESAI</label>
                            <input type="date" id="end_date" value="{{ $filters['end'] ?? '' }}"
                                @change="submitFilters()"
                                :disabled="periodMode === 'semua'"
                                :class="periodMode === 'semua' ? 'bg-slate-50/50 text-slate-300 border-slate-100 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 cursor-text'"
                                class="w-full sm:w-40 h-10 text-[11px] font-bold px-4 border rounded-xl outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-10 w-px bg-slate-100 hidden lg:block mx-2"></div>
                    <div class="space-y-2 flex-1 sm:flex-none">
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
                    'val'   => $stats['potensi'],
                    'unit'  => 'HA',
                    'desc'  => 'Luas Lahan Terdata',
                    'color' => 'bg-emerald-500',
                    'icon'  => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>',
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
                    'val'   => $stats['tanam'],
                    'unit'  => 'HA',
                    'desc'  => 'Tahap Pertumbuhan',
                    'color' => 'bg-blue-500',
                    'icon'  => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>',
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
                    'val'   => $stats['panen'],
                    'unit'  => 'HA',
                    'desc'  => 'Tervalidasi Panen',
                    'color' => 'bg-amber-500',
                    'icon'  => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>',
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
                    'val'   => $stats['serapan'],
                    'unit'  => 'TON',
                    'desc'  => 'Output Produksi',
                    'color' => 'bg-indigo-500',
                    'icon'  => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>',
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
                        return null;
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
                    <div class="flex items-baseline gap-1">
                        <h4 class="text-2xl font-black text-slate-800 tracking-tight" x-data="{ count: 0 }" x-init="setTimeout(() => { let end = {{ (float)str_replace(',', '', $card['val']) }}; let duration = 1500; let startTime = null; function step(timestamp) { if(!startTime) startTime = timestamp; let progress = Math.min((timestamp - startTime) / duration, 1); count = (progress * end).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}); if(progress < 1) requestAnimationFrame(step); } requestAnimationFrame(step); }, 200)" x-text="count">0.00</h4>
                        <span class="text-[10px] font-black text-emerald-500">{{ $card['unit'] }}</span>
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
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <div>
                    <h3 class="text-xs font-black text-white uppercase tracking-[0.2em] leading-none mb-1">RINCIAN PRODUKSI WILAYAH</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Monitoring Real-time &bullet; Updated Today</p>
                </div>
            </div>
            <div class="flex items-center gap-3 relative z-10">
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
                                        <svg class="w-6 h-6 transition-transform duration-300" :class="isResorOpen('{{ $resorId }}') ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
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
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
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
                                                    <button @click='editTanam("{{ $row->id_tanam }}", @json($row))' class="px-2 py-1 bg-white border border-emerald-200 text-emerald-600 rounded text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-colors shadow-sm">Edit</button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-400 italic">Belum Input</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-6 border-r border-slate-50 align-top">
                                        @if($row->id_panen)
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-black text-amber-600 bg-amber-50 w-fit px-2 py-0.5 rounded-lg border border-amber-100">{{ number_format($row->total_panen, 2) }} TON</span>
                                                <span class="text-[9px] font-bold text-slate-500 tracking-tight">Tgl: {{ \Carbon\Carbon::parse($row->tgl_panen)->format('d M Y') }}</span>
                                                @php
                                                    $stsPanen = $row->status_panen == 1 ? 'Normal' : ($row->status_panen == 2 ? 'Gagal' : ($row->status_panen == 3 ? 'Dini' : 'Tebasan'));
                                                @endphp
                                                <span class="text-[9px] font-bold text-slate-500 tracking-tight">Jenis: {{ $stsPanen }}</span>
                                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                                    <button @click='editPanen("{{ $row->id_panen }}", @json($row))' class="px-2 py-1 bg-white border border-amber-200 text-amber-600 rounded text-[9px] font-black uppercase hover:bg-amber-500 hover:text-white transition-colors shadow-sm">Edit</button>
                                                </div>
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
                                                    <button @click='editSerapan("{{ $row->id_distribusi }}", @json($row))' class="px-2 py-1 bg-white border border-blue-200 text-blue-600 rounded text-[9px] font-black uppercase hover:bg-blue-500 hover:text-white transition-colors shadow-sm">Edit</button>
                                                </div>
                                                @if(!$row->serapan_valid_oleh)
                                                <form action="{{ route('admin.kelola-lahan.serapan.validasi', $row->id_distribusi) }}" method="POST" class="mt-1">
                                                    @csrf @method('PUT')
                                                    <button class="px-2 py-1 bg-white border border-indigo-200 text-indigo-600 rounded text-[9px] font-black uppercase hover:bg-indigo-500 hover:text-white transition-colors shadow-sm w-full text-center">Validasi</button>
                                                </form>
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

                                            {{-- === PIPELINE VISUAL === --}}
                                            <div class="flex items-center gap-1">

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

                                            {{-- === ACTION BUTTONS === --}}
                                            <div class="flex flex-col gap-1.5 mt-2">
                                                <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 0)'
                                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-emerald-500 hover:text-white active:scale-95 transition-all shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                    Tanam
                                                </button>
                                                <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 1)'
                                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-amber-500 hover:text-white active:scale-95 transition-all shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                    Panen
                                                </button>
                                                <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 2)'
                                                    class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-500 hover:text-white active:scale-95 transition-all shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                                    Serapan
                                                </button>
                                            </div>

                                        </div>
                                    </td>
                                    <td class="px-4 py-6 text-right">
                                        <div class="flex flex-col items-end gap-1.5">
                                            <button @click='openValidasiModal("{{ $row->id_lahan }}", @json($row))' title="Validasi Lahan" class="p-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-500 hover:text-white rounded-lg transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </button>
                                            <button onclick="window.location.href='{{ route('admin.kelola-lahan.potensi.index') }}?search={{ $row->id_lahan }}&action=view'" title="Detail Lahan" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-lg transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </button>
                                            <button onclick="window.location.href='{{ route('admin.kelola-lahan.potensi.index') }}?search={{ $row->id_lahan }}&action=edit'" title="Edit Lahan" class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white rounded-lg transition-all shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button @click="toggleHistory('{{ $row->id_lahan }}')" title="Riwayat & Kelola Siklus" class="p-2 mt-2 rounded-lg transition-all shadow-sm flex items-center justify-center" :class="activeHistory === '{{ $row->id_lahan }}' ? 'bg-slate-800 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                                                <svg class="w-4 h-4 transition-transform duration-300" :class="activeHistory === '{{ $row->id_lahan }}' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                {{-- EXPANDABLE HISTORY ROW --}}
                                <tr x-show="isResorOpen('{{ $resorId }}') && activeHistory === '{{ $row->id_lahan }}'" x-transition x-cloak class="bg-slate-50 border-b-4 border-slate-200/60">
                                    <td colspan="6" class="p-6">
                                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                                            <div class="flex justify-between items-center mb-6">
                                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                                                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </div>
                                                    Riwayat Tanam & Siklus Produksi
                                                </h4>
                                                <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 0)' class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase hover:bg-emerald-600 transition-all shadow-md shadow-emerald-500/20 flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                    Tambah Tanam Baru
                                                </button>
                                            </div>
                                            
                                            @if($row->history_tanam->isEmpty())
                                                <div class="text-center py-10 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada riwayat produksi</p>
                                                </div>
                                            @else
                                                <div class="space-y-6">
                                                    @foreach($row->history_tanam as $tanam)
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
                                                                        <button @click='editTanam("{{ $tanam->id_tanam }}", @json(array_merge((array)$row, (array)$tanam)))' class="px-2.5 py-1.5 bg-white border border-emerald-200 text-emerald-600 rounded-lg text-[9px] font-black uppercase hover:bg-emerald-500 hover:text-white transition-all shadow-sm">Edit</button>
                                                                        <button @click='deleteTanam("{{ $tanam->id_tanam }}")' class="px-2.5 py-1.5 bg-white border border-rose-200 text-rose-600 rounded-lg text-[9px] font-black uppercase hover:bg-rose-500 hover:text-white transition-all shadow-sm">Hapus</button>
                                                                        <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 1, "{{ $tanam->id_tanam }}")' class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-amber-600 transition-all shadow-sm shadow-amber-500/20 ml-2">+ Panen</button>
                                                                        <button @click='openStageModal("{{ $row->id_lahan }}", @json($row), 2, "{{ $tanam->id_tanam }}")' class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-[9px] font-black uppercase tracking-wider hover:bg-blue-600 transition-all shadow-sm shadow-blue-500/20">+ Serapan</button>
                                                                    </div>
                                                                </div>

                                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                                    {{-- PANEN COLUMN --}}
                                                                    <div>
                                                                        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Riwayat Panen
                                                                        </h5>
                                                                        @if($tanam->panens->isEmpty())
                                                                            <div class="p-3 bg-white rounded-lg border border-dashed border-slate-200 text-center text-[10px] font-bold text-slate-400 italic">Belum ada panen</div>
                                                                        @else
                                                                            <div class="space-y-2.5">
                                                                                @foreach($tanam->panens as $panen)
                                                                                <div class="bg-white rounded-lg border border-amber-100 p-3 flex justify-between items-center shadow-sm hover:border-amber-300 transition-colors">
                                                                                    <div>
                                                                                        <div class="flex items-center gap-1.5 mb-1">
                                                                                            <span class="text-[10px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($panen->tgl_panen)->format('d M Y') }}</span>
                                                                                            <span class="text-[8px] font-black text-white bg-amber-500 px-1.5 py-0.5 rounded">{{ $panen->status_panen == 1 ? 'NORMAL' : ($panen->status_panen == 2 ? 'GAGAL' : ($panen->status_panen == 3 ? 'DINI' : 'TEBASAN')) }}</span>
                                                                                        </div>
                                                                                        <p class="text-xs font-black text-slate-800">{{ number_format($panen->total_panen, 2) }} TON <span class="text-[9px] font-bold text-slate-400 ml-1">dari {{ number_format($panen->luas_panen, 2) }} HA</span></p>
                                                                                    </div>
                                                                                    <div class="flex flex-col gap-1">
                                                                                        <button @click='editPanen("{{ $panen->id_panen }}", @json(array_merge((array)$row, (array)$tanam, (array)$panen)))' class="px-2 py-1 bg-amber-50 text-amber-600 rounded text-[8px] font-black uppercase hover:bg-amber-500 hover:text-white transition-colors">Edit</button>
                                                                                        <button @click='deletePanen("{{ $panen->id_panen }}")' class="px-2 py-1 bg-rose-50 text-rose-600 rounded text-[8px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors">Del</button>
                                                                                    </div>
                                                                                </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    {{-- SERAPAN COLUMN --}}
                                                                    <div>
                                                                        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Riwayat Serapan
                                                                        </h5>
                                                                        @if($tanam->distribusis->isEmpty())
                                                                            <div class="p-3 bg-white rounded-lg border border-dashed border-slate-200 text-center text-[10px] font-bold text-slate-400 italic">Belum ada serapan</div>
                                                                        @else
                                                                            <div class="space-y-2.5">
                                                                                @foreach($tanam->distribusis as $distribusi)
                                                                                <div class="bg-white rounded-lg border border-blue-100 p-3 flex justify-between items-center shadow-sm hover:border-blue-300 transition-colors">
                                                                                    <div>
                                                                                        <div class="flex items-center gap-1.5 mb-1">
                                                                                            <span class="text-[10px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($distribusi->tgl_distribusi)->format('d M Y') }}</span>
                                                                                            <span class="text-[8px] font-black text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ $distribusi->distribusi_ke == 1 ? 'BULOG' : ($distribusi->distribusi_ke == 2 ? 'PABRIK' : ($distribusi->distribusi_ke == 3 ? 'TENGKULAK' : 'KONSUMSI')) }}</span>
                                                                                        </div>
                                                                                        <p class="text-xs font-black text-slate-800">{{ number_format($distribusi->total_distribusi, 2) }} TON</p>
                                                                                    </div>
                                                                                    <div class="flex flex-col gap-1">
                                                                                        <button @click='editSerapan("{{ $distribusi->id_distribusi }}", @json(array_merge((array)$row, (array)$tanam, (array)$distribusi)))' class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-[8px] font-black uppercase hover:bg-blue-500 hover:text-white transition-colors">Edit</button>
                                                                                        <button @click='deleteSerapan("{{ $distribusi->id_distribusi }}")' class="px-2 py-1 bg-rose-50 text-rose-600 rounded text-[8px] font-black uppercase hover:bg-rose-500 hover:text-white transition-colors">Del</button>
                                                                                    </div>
                                                                                </div>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
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
                                    <svg class="w-12 h-12 text-slate-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h4 class="text-sm font-black text-slate-500 uppercase tracking-[0.3em]">Data Lahan Belum Tersedia</h4>
                                <p class="text-[11px] font-bold text-slate-300 mt-2 uppercase tracking-widest">Gunakan filter atau pencarian untuk hasil lainnya</p>                            </div>
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
            periodMode: @json(($filters['start'] || $filters['end']) ? 'tanggal' : 'semua'),
            searchQuery: @json($filters['search'] ?? ''),
            selectedResor: @json($filters['resor'] ?? ''),
            selectedSektor: @json($filters['sektor'] ?? ''),
            selectedJenis: @json($filters['jenis'] ?? ''),
            selectedKomoditi: @json($filters['komoditi'] ?? ''),
            kategoriProduksi: @json($filters['kategori'] ?? 'semua'),
            polseks: @json($polsekList),
            openResors: [],
            activeHistory: null,

            toggleHistory(id) {
                if (this.activeHistory === id) {
                    this.activeHistory = null;
                } else {
                    this.activeHistory = id;
                }
            },

            // Production Flow State (Real)
            activeLahan: null,
            isEditMode: false,
            activeProcessId: null,
            activeTanamId: null,
            modalTanam: false,
            modalPanen: false,
            modalSerapan: false,
            modalValidasi: false,
            validasiData: { tanam: [], panen: [], serapan: [] },
            lahanStages: @json($lahanStagesMap ?? new stdClass()),

            // Form Data
            formTanam: {
                tgl_tanam: '{{ date('Y-m-d') }}',
                luas_tanam: 0,
                jenis_bibit: '',
                kebutuhan_bibit: '',
                est_awal_panen: '{{ date('Y-m-d') }}',
                est_akhir_panen: '{{ date('Y-m-d') }}',
                keterangan_tanam: ''
            },
            formPanen: {
                tgl_panen: '{{ date('Y-m-d') }}',
                luas_panen: 0,
                status_panen: 1, // 1: normal, 2: gagal, 3: dini, 4: tebasan
                total_panen: 0,
                keterangan_panen: ''
            },
            formSerapan: {
                tgl_distribusi: '{{ date('Y-m-d') }}',
                total_distribusi: 0,
                distribusi_ke: 1, // 1: bulog, 2: pabrik, 3: tengkulak, 4: konsumsi sendiri
                keterangan_serapan: ''
            },

            init() {
                // Initialize all resors as open by default
                @foreach($data as $resor)
                    this.openResors.push('{{ str_replace('.', '_', $resor->id_tingkat) }}');
                @endforeach
            },

            openStageModal(id_lahan, rowData, forcedStage = null, targetTanamId = null) {
                this.activeLahan = rowData;
                this.isEditMode = false;
                this.activeTanamId = targetTanamId;
                const stage = forcedStage !== null ? forcedStage : this.lahanStages[id_lahan];
                if (stage === 0) {
                    this.formTanam.luas_tanam = rowData.luas_lahan;
                    this.modalTanam = true;
                } else if (stage === 1) {
                    this.formPanen.luas_panen = rowData.luas_tanam || rowData.luas_lahan;
                    this.modalPanen = true;
                } else if (stage === 2) {
                    this.formSerapan.total_distribusi = rowData.total_panen || 0;
                    this.modalSerapan = true;
                }
            },

            editTanam(id_tanam, rowData) {
                this.activeLahan = rowData;
                this.isEditMode = true;
                this.activeProcessId = id_tanam;
                this.formTanam = {
                    tgl_tanam: rowData.tgl_tanam,
                    luas_tanam: rowData.luas_tanam,
                    jenis_bibit: rowData.nama_bibit || '',
                    kebutuhan_bibit: rowData.kebutuhan_bibit || '',
                    est_awal_panen: rowData.est_awal_panen,
                    est_akhir_panen: rowData.est_akhir_panen,
                    keterangan_tanam: rowData.keterangan_tanam || ''
                };
                this.modalTanam = true;
            },

            editPanen(id_panen, rowData) {
                this.activeLahan = rowData;
                this.isEditMode = true;
                this.activeProcessId = id_panen;
                this.formPanen = {
                    tgl_panen: rowData.tgl_panen,
                    luas_panen: rowData.luas_panen,
                    status_panen: rowData.status_panen || 1,
                    total_panen: rowData.total_panen || 0,
                    keterangan_panen: rowData.ket_panen || ''
                };
                this.modalPanen = true;
            },

            editSerapan(id_distribusi, rowData) {
                this.activeLahan = rowData;
                this.isEditMode = true;
                this.activeProcessId = id_distribusi;
                this.formSerapan = {
                    tgl_distribusi: rowData.tgl_distribusi,
                    total_distribusi: rowData.total_distribusi || 0,
                    distribusi_ke: rowData.distribusi_ke || 1,
                    keterangan_serapan: rowData.keterangan_distribusi || ''
                };
                this.modalSerapan = true;
            },

            async submitTanam() {
                try {
                    const url = this.isEditMode ? `/admin/kelola-lahan/tanam/${this.activeProcessId}` : "{{ route('admin.kelola-lahan.tanam.store') }}";
                    const method = this.isEditMode ? 'PUT' : 'POST';
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_lahan: this.activeLahan.id_lahan,
                            ...this.formTanam
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalTanam = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 1;
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + (result.message || 'Terjadi kesalahan server.'));
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi: ' + error.message);
                }
            },

            async submitPanen() {
                try {
                    const url = this.isEditMode ? `/admin/kelola-lahan/panen/${this.activeProcessId}` : "{{ route('admin.kelola-lahan.panen.store') }}";
                    const method = this.isEditMode ? 'PUT' : 'POST';
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_lahan: this.activeLahan.id_lahan,
                            id_tanam: this.activeTanamId,
                            ...this.formPanen
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalPanen = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 2;
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + (result.message || 'Terjadi kesalahan server.'));
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi: ' + error.message);
                }
            },

            async submitSerapan() {
                try {
                    const url = this.isEditMode ? `/admin/kelola-lahan/serapan/${this.activeProcessId}` : "{{ route('admin.kelola-lahan.serapan.store') }}";
                    const method = this.isEditMode ? 'PUT' : 'POST';
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id_lahan: this.activeLahan.id_lahan,
                            id_tanam: this.activeTanamId,
                            ...this.formSerapan
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalSerapan = false;
                        if (!this.isEditMode) this.lahanStages[this.activeLahan.id_lahan] = 0; // Reset to Tanam
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + (result.message || 'Terjadi kesalahan server.'));
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi: ' + error.message);
                }
            },

            async deleteTanam(id) {
                if(!confirm('Apakah Anda yakin ingin menghapus data tanam ini beserta panen & serapan di dalamnya?')) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/tanam/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else alert('Gagal: ' + result.message);
                } catch (e) { alert('Terjadi kesalahan: ' + e.message); }
            },

            async deletePanen(id) {
                if(!confirm('Apakah Anda yakin ingin menghapus data panen ini beserta serapannya?')) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/panen/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else alert('Gagal: ' + result.message);
                } catch (e) { alert('Terjadi kesalahan: ' + e.message); }
            },

            async deleteSerapan(id) {
                if(!confirm('Apakah Anda yakin ingin menghapus data serapan ini?')) return;
                try {
                    const response = await fetch(`/admin/kelola-lahan/serapan/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else alert('Gagal: ' + result.message);
                } catch (e) { alert('Terjadi kesalahan: ' + e.message); }
            },

            async openValidasiModal(id_lahan, rowData) {
                this.activeLahan = rowData;
                try {
                    const response = await fetch(`/admin/kelola-lahan/lahan/${id_lahan}/validasi-data`);
                    const result = await response.json();
                    this.validasiData = result;
                    this.modalValidasi = true;
                } catch (error) {
                    alert('Gagal mengambil data validasi: ' + error.message);
                }
            },

            async submitValidasi() {
                if(!confirm('Apakah Anda yakin ingin memvalidasi SEMUA data Tanam, Panen, dan Serapan untuk lahan ini?')) return;
                
                try {
                    const response = await fetch(`/admin/kelola-lahan/lahan/${this.activeLahan.id_lahan}/validasi`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const result = await response.json();
                    if (result.success) {
                        this.modalValidasi = false;
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + (result.message || 'Terjadi kesalahan server.'));
                    }
                } catch (error) {
                    alert('Terjadi kesalahan koneksi: ' + error.message);
                }
            },

            toggleResor(id) {
                if (this.openResors.includes(id)) {
                    this.openResors = this.openResors.filter(i => i !== id);
                } else {
                    this.openResors.push(id);
                }
            },

            isResorOpen(id) {
                return this.openResors.includes(id);
            },

            get filteredPolseks() {
                if (!this.selectedResor) return [];
                return this.polseks.filter(p => p.id_tingkat.startsWith(this.selectedResor + '.'));
            },

            submitFilters() {
                const url = new URL(window.location.href);
                const params = {
                    resor: this.selectedResor,
                    sektor: this.selectedSektor,
                    jenis: this.selectedJenis,
                    komoditi: this.selectedKomoditi,
                    kategori: this.kategoriProduksi,
                    start_date: document.getElementById('start_date').value,
                    end_date: document.getElementById('end_date').value,
                    search: this.searchQuery
                };

                Object.keys(params).forEach(key => {
                    if (params[key]) url.searchParams.set(key, params[key]);
                    else url.searchParams.delete(key);
                });

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
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">INPUT PROSES TANAM</h3>
                    <p class="text-[10px] text-emerald-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                </div>
            </div>
            <button @click="modalTanam = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
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
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">INPUT PROSES PANEN</h3>
                    <p class="text-[10px] text-orange-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                </div>
            </div>
            <button @click="modalPanen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
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
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">INPUT SERAPAN HASIL</h3>
                    <p class="text-[10px] text-blue-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                </div>
            </div>
            <button @click="modalSerapan = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
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
</div>

<!-- MODAL VALIDASI LAHAN -->
<div x-show="modalValidasi" 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
     x-cloak x-transition.opacity>
    <div @click.outside="modalValidasi = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
        <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center border border-white/20 shadow-inner">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest leading-none">VALIDASI DATA LAHAN</h3>
                    <p class="text-[10px] text-indigo-100 font-bold mt-1 uppercase opacity-80" x-text="'LOKASI: ' + activeLahan?.nama_wilayah"></p>
                </div>
            </div>
            <button @click="modalValidasi = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-2xl transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
            
            <div x-show="validasiData.tanam.length === 0 && validasiData.panen.length === 0 && validasiData.serapan.length === 0" class="text-center p-4 text-slate-500 font-bold text-xs">
                Tidak ada data yang perlu divalidasi.
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
                            <div class="text-xs font-black text-emerald-600 bg-white px-2 py-1 rounded shadow-sm border border-emerald-100" x-text="t.luas_tanam + ' Ha'"></div>
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
                            <div class="text-right">
                                <div class="text-[10px] font-black text-amber-600 bg-white px-2 py-0.5 rounded shadow-sm border border-amber-100 mb-1" x-text="p.luas_panen + ' Ha'"></div>
                                <div class="text-[10px] font-black text-amber-600 bg-white px-2 py-0.5 rounded shadow-sm border border-amber-100" x-text="p.total_panen + ' Ton'"></div>
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
                            <div class="text-xs font-black text-blue-600 bg-white px-2 py-1 rounded shadow-sm border border-blue-100" x-text="s.total_distribusi + ' Ton'"></div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
            <button @click="modalValidasi = false" class="flex-1 px-6 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black text-slate-500 hover:bg-slate-100 transition-all uppercase tracking-widest shadow-sm">Tutup</button>
            <button @click="submitValidasi()" x-show="validasiData.tanam.length > 0 || validasiData.panen.length > 0 || validasiData.serapan.length > 0" class="flex-[2] px-6 py-3.5 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:shadow-xl hover:shadow-indigo-500/20 active:scale-[0.98] transition-all shadow-lg">Validasi Semua Data</button>
        </div>
    </div>
</div>

@endsection