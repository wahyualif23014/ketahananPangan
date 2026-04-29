@extends('layouts.app')

@section('header', 'Dashboard Utama Administrator')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    
    .dashboard-container {
        font-family: 'Outfit', sans-serif;
    }
    .topo-pattern {
        background-color: transparent;
        background-image: radial-gradient(#10b981 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.1;
    }
</style>

<div class="space-y-8 pb-24 dashboard-container max-w-7xl mx-auto relative px-2">
    {{-- Background pattern --}}
    <div class="fixed inset-0 topo-pattern -z-10 pointer-events-none"></div>

    {{-- =====================================================================
         1. HEADER
    ===================================================================== --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">ADMINISTRATOR AREA</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Dashboard Utama</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                DASHBOARD <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">SIKAP PRESISI</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-4xl">Selamat datang, <span class="text-emerald-600 font-bold">{{ Auth::user()->nama_anggota }}</span> &mdash; Ini merupakan Aplikasi Sistem Ketahanan Pangan Presisi Polda Jawa Timur (SIKAP PRESISI). Aplikasi ini dapat Anda akses melalui Komputer Pribadi, Laptop, Tablet dan Perangkat Telepon Genggam yang Anda miliki.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 bg-white/80 backdrop-blur-md border border-slate-200 rounded-2xl shadow-sm">
                <span class="relative flex h-2.5 w-2.5 ml-1">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-[11px] font-black tracking-widest text-slate-600 uppercase ml-1">LIVE</span>
                
                <select name="quarter" onchange="this.form.submit()" class="bg-transparent border-none text-[11px] font-black tracking-widest text-slate-600 uppercase focus:ring-0 cursor-pointer pl-1 pr-2 py-1">
                    <option value="all" {{ $quarterFilter == 'all' ? 'selected' : '' }}>Semua Q</option>
                    <option value="1" {{ $quarterFilter == '1' ? 'selected' : '' }}>Q1</option>
                    <option value="2" {{ $quarterFilter == '2' ? 'selected' : '' }}>Q2</option>
                    <option value="3" {{ $quarterFilter == '3' ? 'selected' : '' }}>Q3</option>
                    <option value="4" {{ $quarterFilter == '4' ? 'selected' : '' }}>Q4</option>
                </select>

                <select name="year" onchange="this.form.submit()" class="bg-transparent border-none text-[11px] font-black tracking-widest text-emerald-600 uppercase focus:ring-0 cursor-pointer pl-1 pr-6 py-1">
                    @php $currentYear = date('Y'); @endphp
                    @for($y = 2024; $y <= $currentYear + 2; $y++)
                        <option value="{{ $y }}" {{ $yearFilter == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
            <button onclick="window.location.reload()" title="Refresh Dashboard"
                class="p-3.5 bg-slate-900 text-emerald-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
    </div>

    {{-- =====================================================================
         2. KPI SUMMARY CARDS
    ===================================================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-[1.02]"></div>

        {{-- 1. POTENSI LAHAN --}}
        <div x-data="{ open: false }" class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-emerald-900/10 transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </div>
                <button @click="open = !open" class="text-[10px] text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg uppercase tracking-widest font-black transition-colors border border-emerald-100">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup Rincian</span>
                </button>
            </div>
            <div class="relative z-10 flex flex-col justify-center flex-grow">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL POTENSI LAHAN</p>
                <div class="flex items-baseline gap-1.5">
                    <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">{{ number_format($potensiTotal, 2) }}</h3>
                    <span class="text-xs text-emerald-500 uppercase font-black">Ha</span>
                </div>
            </div>
            
            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-4 pt-4 border-t border-slate-100">
                <ul class="space-y-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                    @php $no1 = 1; @endphp
                    @foreach($jenisLahanList as $id => $nama)
                        @php $det = $potensiDetails[$id] ?? null; @endphp
                        @if($det)
                        <li class="flex flex-col border-l-2 border-emerald-300 pl-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase">{{ $no1++ }}. {{ $nama }}</span>
                            <span class="text-xs font-black text-slate-800">{{ number_format($det->total_luas, 2) }} Ha <span class="text-slate-400 font-medium mx-1">/</span> <span class="text-emerald-600">{{ $det->total_lokasi }} lokasi</span></span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- 2. TANAM 2026 --}}
        <div x-data="{ open: false }" class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
                <button @click="open = !open" class="text-[10px] text-blue-600 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded-lg uppercase tracking-widest font-black transition-colors border border-blue-100">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup Rincian</span>
                </button>
            </div>
            <div class="relative z-10 flex flex-col justify-center flex-grow">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL LAHAN TANAM</p>
                <div class="flex items-baseline gap-1.5">
                    <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">{{ number_format($tanamTotal, 2) }}</h3>
                    <span class="text-xs text-blue-500 uppercase font-black">Ha</span>
                </div>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-1">TAHUN 2026</p>
            </div>

            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-4 pt-4 border-t border-slate-100">
                <ul class="space-y-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                    @php $no2 = 1; @endphp
                    @foreach($jenisLahanList as $id => $nama)
                        @php $det = $tanamDetails[$id] ?? null; @endphp
                        @if($det)
                        <li class="flex flex-col border-l-2 border-blue-300 pl-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase">{{ $no2++ }}. {{ $nama }}</span>
                            <span class="text-xs font-black text-slate-800">{{ number_format($det->total_luas, 2) }} Ha <span class="text-slate-400 font-medium mx-1">/</span> <span class="text-blue-600">{{ $det->total_lokasi }} lokasi</span></span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- 3. PANEN 2026 --}}
        <div x-data="{ open: false }" class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-amber-100 shadow-xl shadow-amber-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-amber-900/10 transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-amber-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <button @click="open = !open" class="text-[10px] text-amber-600 bg-amber-50 hover:bg-amber-100 px-2.5 py-1 rounded-lg uppercase tracking-widest font-black transition-colors border border-amber-100">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup Rincian</span>
                </button>
            </div>
            <div class="relative z-10 flex flex-col justify-center flex-grow">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL LAHAN PANEN</p>
                <div class="flex items-baseline gap-1.5">
                    <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">{{ number_format($panenTotal, 2) }}</h3>
                    <span class="text-xs text-amber-500 uppercase font-black">Ha</span>
                </div>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-1">TAHUN 2026</p>
            </div>

            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-4 pt-4 border-t border-slate-100">
                <ul class="space-y-2 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                    @php $no3 = 1; @endphp
                    @foreach($jenisLahanList as $id => $nama)
                        @php $det = $panenDetails[$id] ?? null; @endphp
                        @if($det)
                        <li class="flex flex-col border-l-2 border-amber-300 pl-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase">{{ $no3++ }}. {{ $nama }}</span>
                            <span class="text-xs font-black text-slate-800">{{ number_format($det->total_luas, 2) }} Ha <span class="text-slate-400 font-medium mx-1">/</span> <span class="text-amber-600">{{ $det->total_lokasi }} lokasi</span></span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- 4. TITIK LAHAN --}}
        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-rose-100 shadow-xl shadow-rose-900/5 hover:-translate-y-2 hover:shadow-2xl hover:shadow-rose-900/10 transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-rose-400 to-red-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-rose-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </div>
            <div class="relative z-10 flex flex-col justify-center flex-grow">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL TITIK LAHAN</p>
                <div class="flex items-baseline gap-1.5">
                    <h3 class="text-3xl font-black text-slate-800 uppercase italic leading-none">{{ number_format($totalTitikLahan) }}</h3>
                    <span class="text-xs text-rose-500 uppercase font-black">Titik</span>
                </div>
            </div>
            
            <div class="relative z-10 mt-4 pt-4 border-t border-slate-100">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">PENGELOLAAN LAHAN POLSEK</p>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-black text-slate-800">{{ number_format($totalPolsek) }}</span>
                    <span class="text-[10px] text-rose-600 font-bold uppercase bg-rose-50 px-2 py-0.5 rounded border border-rose-100">Polsek Aktif</span>
                </div>
            </div>
        </div>

    </div>

    {{-- =====================================================================
         3. TOTAL HASIL SERAPAN TAHUN 2026
    ===================================================================== --}}
    <div class="bg-white/90 backdrop-blur-3xl rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-1 mt-6">
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center justify-between relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-1.5 h-8 bg-blue-500 rounded-full"></div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Total Hasil Serapan Tahun {{ $yearFilter }}</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">Distribusi panen berdasarkan saluran serapan</p>
                </div>
            </div>
            <span class="relative z-10 hidden md:block px-3 py-1.5 bg-blue-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-blue-500/30">
                {{ number_format($totalSerapan, 2) }} Ton Total
            </span>
        </div>
        <div class="p-6 md:p-8">
            <div class="h-64 md:h-80 w-full relative">
                <canvas id="serapanChart"></canvas>
            </div>
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">Bulog</p>
                    <p class="text-lg font-black text-blue-600">{{ number_format($serapanBulog, 2) }} <span class="text-[10px] text-blue-400">TON</span></p>
                </div>
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-center">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Pabrik Pakan</p>
                    <p class="text-lg font-black text-indigo-600">{{ number_format($serapanPabrik, 2) }} <span class="text-[10px] text-indigo-400">TON</span></p>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Tengkulak</p>
                    <p class="text-lg font-black text-amber-600">{{ number_format($serapanTengkulak, 2) }} <span class="text-[10px] text-amber-500">TON</span></p>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Konsumsi Sendiri</p>
                    <p class="text-lg font-black text-emerald-600">{{ number_format($serapanKonsumsi, 2) }} <span class="text-[10px] text-emerald-500">TON</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         4. CHART + STATUS CARDS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Line Chart --}}
        {{-- Line Chart --}}
        <div class="lg:col-span-2 bg-white/90 backdrop-blur rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 p-6 md:p-8 relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-50 rounded-full opacity-60 -z-10 pointer-events-none"></div>
            <div class="flex items-start justify-between mb-8 relative z-10">
                <div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Tren Luasan Lahan</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Perkembangan potensi lahan per tahun</p>
                </div>
                <div class="flex bg-slate-50 p-1 rounded-xl border border-slate-200 gap-1 shadow-inner" id="chart-toggle-group">
                    <button id="btn-chart-monthly" class="px-4 py-2 text-[10px] font-black bg-white rounded-lg shadow-sm text-emerald-600 uppercase tracking-widest border border-emerald-100 transition-all">Bulanan</button>
                    <button id="btn-chart-yearly" class="px-4 py-2 text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Tahunan</button>
                </div>
            </div>
            <div class="h-56">
                <canvas id="productivityChart"></canvas>
            </div>
            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-8">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wide">Total Potensi {{ $yearFilter }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-0.5" id="chart-total-label">{{ number_format($potensiTotal, 2) }} Ha</p>
                </div>
            </div>
        </div>

        {{-- Harvest Status Cards --}}
        <div class="flex flex-col gap-4">
            @php
            $harvestCards = [
            ['label' => 'Panen Normal', 'val' => $harvestStats['normal'] ?? 0, 'color' => 'emerald', 'dot' => 'bg-emerald-500'],
            ['label' => 'Gagal Panen (Puso)', 'val' => $harvestStats['failed'] ?? 0, 'color' => 'rose', 'dot' => 'bg-rose-500'],
            ['label' => 'Panen Dini', 'val' => $harvestStats['early'] ?? 0, 'color' => 'amber', 'dot' => 'bg-amber-500'],
            ['label' => 'Panen Tebasan', 'val' => $harvestStats['tebasan'] ?? 0, 'color' => 'blue', 'dot' => 'bg-blue-500'],
            ];
            @endphp
            @foreach($harvestCards as $card)
            <div class="bg-white rounded-[1.5rem] p-6 border border-slate-200/60 flex flex-col justify-between hover:-translate-y-1 hover:shadow-lg transition-all duration-300 flex-1 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-16 h-16 {{ $card['dot'] }} opacity-5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                <div class="flex items-center gap-3 mb-2 relative z-10">
                    <span class="w-3 h-3 rounded-full {{ $card['dot'] }} flex-shrink-0 shadow-sm"></span>
                    <p class="text-xs font-black text-slate-500 uppercase tracking-widest">{{ $card['label'] }}</p>
                </div>
                <div class="text-left relative z-10">
                    <span class="text-2xl font-black text-slate-800 italic leading-none">{{ number_format($card['val'], 2) }}</span>
                    <span class="text-[10px] font-black text-slate-400 uppercase ml-1 tracking-widest">Ha</span>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    {{-- =====================================================================
         5. PLANTING & HARVESTING ANALYTICS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white/90 backdrop-blur rounded-[2.5rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden relative">
            <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-blue-500 border-b border-blue-400 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-blue-100 uppercase tracking-[0.2em]">Planting Analytics</p>
                    <h3 class="text-xl font-black text-white mt-0.5">242.74 <span class="text-xs font-bold text-blue-200">Ha</span></h3>
                </div>
                <span class="px-2.5 py-1.5 bg-blue-400/30 text-white text-[10px] font-black rounded-lg border border-blue-300/50 uppercase tracking-widest">Musim {{ $yearFilter }}</span>
            </div>
            <div class="p-6">
                @php
                $barColorsTanam = ['bg-blue-500','bg-emerald-500','bg-amber-500','bg-violet-500','bg-teal-500','bg-sky-500','bg-rose-500','bg-slate-400'];
                $i = 0;
                @endphp
                <div class="flex flex-col gap-y-4">
                    @foreach($plantingAnalytics as $label => $item)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-slate-600">{{ $label }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $item['val'] }} <small class="text-slate-400 font-normal text-[10px] uppercase">Ha</small></span>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColorsTanam[$i % 8] }} rounded-full" style="width: <?php echo min($item['pct'], 100); ?>%;"></div>
                        </div>
                    </div>
                    @php $i++ @endphp
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white/90 backdrop-blur rounded-[2.5rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 overflow-hidden relative">
            <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-emerald-500 border-b border-emerald-400 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black text-emerald-100 uppercase tracking-[0.2em]">Harvesting Analytics</p>
                    <h3 class="text-xl font-black text-white mt-0.5">243.72 <span class="text-xs font-bold text-emerald-200">Ha</span></h3>
                </div>
                <span class="px-2.5 py-1.5 bg-emerald-400/30 text-white text-[10px] font-black rounded-lg border border-emerald-300/50 uppercase tracking-widest">Realisasi {{ $yearFilter }}</span>
            </div>
            <div class="p-6">
                @php
                $barColorsPanen = ['bg-emerald-500','bg-blue-500','bg-amber-500','bg-violet-500','bg-teal-500','bg-sky-500','bg-rose-500','bg-slate-400'];
                $j = 0;
                @endphp
                <div class="flex flex-col gap-y-4">
                    @foreach($harvestingAnalytics as $label => $item)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-slate-600">{{ $label }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $item['val'] }} <small class="text-slate-400 font-normal text-[10px] uppercase">Ha</small></span>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColorsPanen[$j % 8] }} rounded-full" style="width: <?php echo min($item['pct'], 100); ?>%;"></div>
                        </div>
                    </div>
                    @php $j++ @endphp
                    @endforeach
                </div>
            </div>
        </div>

    </div>


    {{-- =====================================================================
         6. QUARTERLY PERFORMANCE
    ===================================================================== --}}
    <div class="bg-white/90 backdrop-blur-3xl rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-1">
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Monitoring Target & Hasil Kwartal</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">Progres capaian per-triwulan tahun {{ $yearFilter }}</p>
                </div>
            </div>
        </div>
        <div class="p-6">
            @php
            $qStyles = [
                'blue' => ['bg' => 'bg-blue-50/50', 'border' => 'border-blue-100', 'hover' => 'hover:border-blue-300 hover:shadow-blue-500/10 hover:-translate-y-1', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100 text-blue-700 border-blue-200', 'icon' => 'text-blue-500'],
                'emerald' => ['bg' => 'bg-emerald-50/50', 'border' => 'border-emerald-100', 'hover' => 'hover:border-emerald-300 hover:shadow-emerald-500/10 hover:-translate-y-1', 'text' => 'text-emerald-800', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icon' => 'text-emerald-500'],
                'amber' => ['bg' => 'bg-amber-50/50', 'border' => 'border-amber-100', 'hover' => 'hover:border-amber-300 hover:shadow-amber-500/10 hover:-translate-y-1', 'text' => 'text-amber-800', 'badge' => 'bg-amber-100 text-amber-700 border-amber-200', 'icon' => 'text-amber-500'],
                'rose' => ['bg' => 'bg-rose-50/50', 'border' => 'border-rose-100', 'hover' => 'hover:border-rose-300 hover:shadow-rose-500/10 hover:-translate-y-1', 'text' => 'text-rose-800', 'badge' => 'bg-rose-100 text-rose-700 border-rose-200', 'icon' => 'text-rose-500'],
                'indigo' => ['bg' => 'bg-indigo-50/50', 'border' => 'border-indigo-100', 'hover' => 'hover:border-indigo-300 hover:shadow-indigo-500/10 hover:-translate-y-1', 'text' => 'text-indigo-800', 'badge' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'icon' => 'text-indigo-500'],
                'teal' => ['bg' => 'bg-teal-50/50', 'border' => 'border-teal-100', 'hover' => 'hover:border-teal-300 hover:shadow-teal-500/10 hover:-translate-y-1', 'text' => 'text-teal-800', 'badge' => 'bg-teal-100 text-teal-700 border-teal-200', 'icon' => 'text-teal-500'],
                'sky' => ['bg' => 'bg-sky-50/50', 'border' => 'border-sky-100', 'hover' => 'hover:border-sky-300 hover:shadow-sky-500/10 hover:-translate-y-1', 'text' => 'text-sky-800', 'badge' => 'bg-sky-100 text-sky-700 border-sky-200', 'icon' => 'text-sky-500'],
                'violet' => ['bg' => 'bg-violet-50/50', 'border' => 'border-violet-100', 'hover' => 'hover:border-violet-300 hover:shadow-violet-500/10 hover:-translate-y-1', 'text' => 'text-violet-800', 'badge' => 'bg-violet-100 text-violet-700 border-violet-200', 'icon' => 'text-violet-500'],
                'slate' => ['bg' => 'bg-slate-50/50', 'border' => 'border-slate-200', 'hover' => 'hover:border-slate-400 hover:shadow-slate-500/10 hover:-translate-y-1', 'text' => 'text-slate-800', 'badge' => 'bg-slate-200 text-slate-700 border-slate-300', 'icon' => 'text-slate-500']
            ];
            $qLabels = ['Q1 Jan-Mar', 'Q2 Apr-Jun', 'Q3 Jul-Sep', 'Q4 Okt-Des'];
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($kwartalData as $row)
                @php $style = $qStyles[$row['accent']] ?? $qStyles['slate']; @endphp
                <div class="bg-white rounded-[1.5rem] border {{ $style['border'] }} shadow-sm overflow-hidden flex flex-col group {{ $style['hover'] }} transition-all duration-300">
                    <div class="{{ $style['bg'] }} px-5 py-4 border-b {{ $style['border'] }} flex items-center gap-3 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-16 h-16 bg-white opacity-40 rounded-full group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm {{ $style['icon'] }} relative z-10 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <p class="text-[10px] font-black {{ $style['text'] }} uppercase tracking-widest leading-tight relative z-10">{{ $row['category'] }}</p>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3 flex-1 bg-slate-50/30">
                        @foreach($row['q'] as $qi => $val)
                        <div class="bg-white rounded-xl p-3 border {{ $style['border'] }} relative overflow-hidden flex flex-col justify-between group/item hover:border-slate-300 transition-colors {{ $quarterFilter != 'all' && $quarterFilter != ($qi + 1) ? 'opacity-40 grayscale' : '' }}">
                            <div class="absolute -right-6 -bottom-6 w-16 h-16 rounded-full {{ $style['bg'] }} opacity-50 pointer-events-none"></div>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-2 relative z-10">{{ $qLabels[$qi] }} <span class="hidden lg:inline">{{ $yearFilter }}</span></p>
                            <div class="relative z-10 flex flex-col items-start gap-1.5">
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-lg font-black tracking-tight {{ $val['luas'] > 0 ? $style['text'] : 'text-slate-300' }}">{{ number_format($val['luas'], 2) }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase">Ha</span>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-sm font-bold tracking-tight {{ $val['hasil'] > 0 ? 'text-amber-500' : 'text-slate-300' }}">{{ number_format($val['hasil'], 2) }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase">Ton</span>
                                    </div>
                                </div>
                                @if($val['luas'] > 0)
                                <span class="text-[8px] px-1.5 py-0.5 rounded border font-black uppercase tracking-widest {{ $style['badge'] }}">Tercapai</span>
                                @else
                                <span class="text-[8px] px-1.5 py-0.5 rounded border font-black uppercase tracking-widest text-slate-400 bg-slate-50 border-slate-200">Nihil</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>


    {{-- =====================================================================
         8. GEOSPATIAL MAP + DONUT CHARTS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-8 bg-white/90 backdrop-blur-3xl rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20">
            <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center relative overflow-hidden">
                <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
                <div class="flex items-center gap-4 relative z-10 w-full">
                    <div class="w-1.5 h-8 bg-teal-500 rounded-full"></div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Peta Penyebaran Potensi Lahan</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">Distribusi geografis wilayah &mdash; 2026</p>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div id="map" class="h-[450px] w-full z-0"></div>
                
                {{-- Floating Overlays for premium look --}}
                <div class="absolute bottom-6 left-6 z-[400] flex flex-col gap-3 pointer-events-none">
                    <div class="bg-white/95 backdrop-blur-xl px-5 py-3.5 rounded-2xl shadow-2xl border border-white/60 flex items-center gap-4">
                        <div class="relative flex h-4 w-4">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-4 w-4 bg-teal-500 border-2 border-white shadow-sm"></span>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-widest text-slate-500 uppercase leading-none mb-1">Live Geospatial</p>
                            <p class="text-sm font-black text-slate-800 leading-none">Polda Jawa Timur</p>
                        </div>
                    </div>
                </div>
                
                {{-- Top Right Legend Overlay --}}
                <div class="absolute top-6 right-6 z-[400] pointer-events-none hidden md:block">
                    <div class="bg-slate-900/80 backdrop-blur-md px-4 py-3 rounded-2xl shadow-xl border border-slate-700/50 text-right">
                        <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-1">Status Lahan</p>
                        <div class="flex items-center justify-end gap-2">
                            <span class="text-xs font-medium text-white">Produktif</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">

            <div class="bg-white/90 backdrop-blur rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 p-6 md:p-8 flex flex-col items-center justify-center flex-1 relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-48 h-48 bg-blue-50 rounded-full opacity-60 -z-10 pointer-events-none"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 relative z-10">Total Titik Lahan</p>
                <div class="relative w-40 h-40 z-10">
                    <canvas id="totalTitikChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-black text-slate-800">5,528</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Lahan</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest z-10">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Aktif</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-200"></span>Lainnya</div>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur rounded-[2rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 p-6 md:p-8 flex flex-col items-center justify-center flex-1 relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-48 h-48 bg-emerald-50 rounded-full opacity-60 -z-10 pointer-events-none"></div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 relative z-10">Pengelolaan Polsek</p>
                <div class="relative w-40 h-40 z-10">
                    <canvas id="pengelolaanPolsekChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-black text-slate-800">659</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Polsek</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest z-10">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Aktif</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-200"></span>Lainnya</div>
                </div>
            </div>

        </div>

    </div>

    {{-- =====================================================================
         9. PENDING VALIDASI
    ===================================================================== --}}
    <div class="bg-slate-900/95 backdrop-blur-3xl rounded-[2.5rem] border border-slate-800 shadow-2xl overflow-hidden relative z-20 mx-1">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-900/20 to-transparent pointer-events-none"></div>
        <div class="px-8 py-6 border-b border-slate-800 flex items-center justify-between relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-1.5 h-10 bg-emerald-500 rounded-full"></div>
                <div>
                    <p class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.2em] mb-1">Sistem Validasi Terintegrasi</p>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Laporan Pending Validasi</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">Satuan wilayah yang belum melakukan sinkronisasi data final</p>
                </div>
            </div>
            <button class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-emerald-500/30 active:scale-95 hidden md:block">
                Kirim Notifikasi Massal
            </button>
        </div>
        <div class="p-6">
            @if($pendingValidation->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                @forelse($pendingValidation as $polres)
                <div class="flex items-center gap-3 p-4 bg-white/[0.04] border border-white/[0.06] rounded-lg hover:border-emerald-500/30 hover:bg-white/[0.06] transition-all group cursor-pointer">
                    <div class="relative flex h-2.5 w-2.5 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500/80"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-200 group-hover:text-emerald-400 transition-colors truncate">{{ $polres->satwil }}</p>
                        <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wide"><span class="text-amber-400 font-bold">{{ $polres->pending_count }} Lahan</span> Menunggu validasi</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-600 group-hover:text-emerald-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                @empty
                <div class="col-span-2 text-center py-6 text-slate-400 text-sm">Tidak ada data pending validasi</div>
                @endforelse
            </div>
            @else
            <div class="py-8 text-center border border-white/[0.06] rounded-lg bg-white/[0.02]">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <p class="text-sm font-medium text-slate-300">Semua Data Telah Divalidasi</p>
                <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-widest">Tidak ada satuan wilayah yang tertunda</p>
            </div>
            @endif
            <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                <p class="text-xs text-slate-500">Total <span class="text-slate-300 font-medium">{{ $totalPendingSatwil }} satwil</span> memerlukan tindakan segera</p>
                <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition-all active:scale-95 md:hidden">
                    Kirim Notifikasi
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ===========================================================================
     JAVASCRIPT
=========================================================================== --}}
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script id="chart-tahunan-data" type="application/json">
    <?php echo json_encode($chartTahunan ?? ['labels' => [], 'data' => []]); ?>
</script>
<script id="chart-bulanan-data" type="application/json">
    <?php echo json_encode($chartBulanan ?? ['labels' => [], 'data' => []]); ?>
</script>
<script id="serapan-data" type="application/json">
    <?php echo json_encode([$serapanBulog ?? 0, $serapanPabrik ?? 0, $serapanTengkulak ?? 0, $serapanKonsumsi ?? 0]); ?>
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Serapan Bar Chart
        const serapanCtx = document.getElementById('serapanChart').getContext('2d');
        const serapanData = JSON.parse(document.getElementById('serapan-data').textContent);
        
        new Chart(serapanCtx, {
            type: 'bar',
            data: {
                labels: ['Bulog', 'Pabrik Pakan', 'Tengkulak', 'Konsumsi Sendiri'],
                datasets: [{
                    label: 'Total Serapan (Ton)',
                    data: serapanData,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',   // blue
                        'rgba(99, 102, 241, 0.8)',   // indigo
                        'rgba(245, 158, 11, 0.8)',   // amber
                        'rgba(16, 185, 129, 0.8)'    // emerald
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(99, 102, 241)',
                        'rgb(245, 158, 11)',
                        'rgb(16, 185, 129)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        borderColor: '#334155',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 11, weight: '500', family: 'Outfit' },
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11, weight: '700', family: 'Outfit' },
                            color: '#64748b'
                        }
                    }
                }
            }
        });

        // 1. LINE CHART
        const lineCtx = document.getElementById('productivityChart').getContext('2d');
        const grad = lineCtx.createLinearGradient(0, 0, 0, 220);
        grad.addColorStop(0, 'rgba(16, 185, 129, 0.12)');
        grad.addColorStop(1, 'rgba(16, 185, 129, 0)');

        const chartTahunanRaw = JSON.parse(document.getElementById('chart-tahunan-data').textContent);
        const chartBulananRaw = JSON.parse(document.getElementById('chart-bulanan-data').textContent);

        const dynamicChartData = {
            monthly: {
                labels: chartBulananRaw.labels,
                data: chartBulananRaw.data
            },
            yearly: {
                labels: chartTahunanRaw.labels,
                data: chartTahunanRaw.data
            }
        };

        const prodChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: dynamicChartData.monthly.labels,
                datasets: [{
                    label: 'Luas Lahan (Ha)',
                    data: dynamicChartData.monthly.data,
                    borderColor: '#10b981',
                    backgroundColor: grad,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        borderColor: '#334155',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 10
                    }
                },
                scales: {
                    y: {
                        display: false,
                        beginAtZero: false
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });

        // Chart Toggle Logic
        const btnMonthly = document.getElementById('btn-chart-monthly');
        const btnYearly = document.getElementById('btn-chart-yearly');
        
        function updateProdChart(mode) {
            const activeClasses = ['bg-white', 'shadow-sm', 'text-emerald-600', 'border', 'border-emerald-100'];
            const inactiveClasses = ['text-slate-400', 'hover:text-slate-600', 'border-transparent'];
            
            if (mode === 'monthly') {
                btnMonthly.classList.add(...activeClasses);
                btnMonthly.classList.remove(...inactiveClasses);
                btnYearly.classList.remove(...activeClasses);
                btnYearly.classList.add(...inactiveClasses);
                
                prodChart.data.labels = dynamicChartData.monthly.labels;
                prodChart.data.datasets[0].data = dynamicChartData.monthly.data;
            } else {
                btnYearly.classList.add(...activeClasses);
                btnYearly.classList.remove(...inactiveClasses);
                btnMonthly.classList.remove(...activeClasses);
                btnMonthly.classList.add(...inactiveClasses);
                
                prodChart.data.labels = dynamicChartData.yearly.labels;
                prodChart.data.datasets[0].data = dynamicChartData.yearly.data;
            }
            prodChart.update();
        }
        
        btnMonthly.addEventListener('click', () => updateProdChart('monthly'));
        btnYearly.addEventListener('click', () => updateProdChart('yearly'));
        
        // initialize to monthly
        updateProdChart('monthly');

        // 2. LEAFLET MAP
        var map = L.map('map', {
            zoomControl: false,
            scrollWheelZoom: false
        }).setView([-7.5360, 112.2384], 8);

        L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            attribution: '&copy; Google Maps',
            maxZoom: 20
        }).addTo(map);

        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        var sampleData = <?php echo json_encode($mapData); ?>;

        sampleData.forEach(function(point) {
            L.circleMarker([point.lat, point.lng], {
                radius: 8,
                fillColor: '#10b981',
                color: '#ffffff',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map).bindPopup(
                '<div style="font-size:12px;font-weight:600;">' + point.title + '</div>' +
                '<div style="font-size:11px;color:#64748b;">' + point.status + '</div>'
            );
        });

        // 3. DONUT CHARTS
        const donutOptions = {
            cutout: '78%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        };

        const totalTitikData = <?php echo json_encode([$totalTitikLahan, max(0, 1000 - $totalTitikLahan)]); ?>;
        new Chart(document.getElementById('totalTitikChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: totalTitikData,
                    backgroundColor: ['#3b82f6', '#f1f5f9'],
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: donutOptions
        });

        const polsekData = <?php echo json_encode([$polsekAktif, max(0, 800 - $polsekAktif)]); ?>;
        new Chart(document.getElementById('pengelolaanPolsekChart'), {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: polsekData,
                    backgroundColor: ['#10b981', '#f1f5f9'],
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: donutOptions
        });

    });
</script>
@endsection