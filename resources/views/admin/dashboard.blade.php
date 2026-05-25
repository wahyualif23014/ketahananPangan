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

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-2xl flex items-center gap-3 shadow-sm mx-1 mt-6">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-600 rounded-2xl flex items-center gap-3 shadow-sm mx-1 mt-6">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
    @endif

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
         2. KPI SUMMARY CARDS (4 Kolom Besar)
    ===================================================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-[1.02]"></div>

        {{-- 1. POTENSI LAHAN --}}
        <div x-data="{ open: true }" class="group relative bg-white p-6 md:p-10 rounded-[2rem] border border-emerald-100 shadow-xl hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-40 h-40 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-teal-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                </div>
                <button @click="open = !open" class="text-xs text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-xl uppercase tracking-widest font-black transition-colors border border-emerald-100 shadow-sm">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup</span>
                </button>
            </div>
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-black uppercase tracking-[0.3em] mb-2">TOTAL POTENSI LAHAN</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-5xl lg:text-6xl font-black text-slate-800 italic leading-none">{{ number_format($potensiTotal, 2) }}</h3>
                    <span class="text-sm md:text-base text-emerald-500 uppercase font-black">Ha</span>
                </div>
            </div>
            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-6 pt-5 border-t border-slate-100 flex-grow">
                <ul class="space-y-3 max-h-56 overflow-y-auto pr-2 custom-scrollbar">
                    @php $no1 = 1; @endphp
                    @foreach($jenisLahanList as $id => $nama)
                    @php $det = $potensiDetails[$id] ?? null; @endphp
                    @if($det)
                    <li class="flex flex-col border-l-4 border-emerald-300 pl-3">
                        <span class="text-[11px] md:text-xs font-bold text-slate-500 uppercase mb-0.5">{{ $no1++ }}. {{ $nama }}</span>
                        <span class="text-sm md:text-base font-black text-slate-800">{{ number_format($det->total_luas, 2) }} Ha <span class="text-slate-400 font-medium mx-1">/</span> <span class="text-emerald-600">{{ $det->total_lokasi }} lokasi</span></span>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- 2. TOTAL LAHAN TANAM --}}
        <div x-data="{ open: true }" class="group relative bg-white p-6 md:p-10 rounded-[2rem] border border-blue-100 shadow-xl hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-40 h-40 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <button @click="open = !open" class="text-xs text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl uppercase tracking-widest font-black transition-colors border border-blue-100 shadow-sm">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup</span>
                </button>
            </div>
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-black uppercase tracking-[0.3em] mb-2">TOTAL LAHAN TANAM</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-5xl lg:text-6xl font-black text-slate-800 italic leading-none">{{ number_format($tanamTotal, 2) }}</h3>
                    <span class="text-sm md:text-base text-blue-500 uppercase font-black">Ha</span>
                </div>
            </div>
            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-6 pt-5 border-t border-slate-100 flex-grow">
                <ul class="space-y-3 max-h-56 overflow-y-auto pr-2 custom-scrollbar">
                    @php $no2 = 1; @endphp
                    @foreach($jenisLahanList as $id => $nama)
                    @php $det = $tanamDetails[$id] ?? null; @endphp
                    @if($det)
                    <li class="flex flex-col border-l-4 border-blue-300 pl-3">
                        <span class="text-[11px] md:text-xs font-bold text-slate-500 uppercase mb-0.5">{{ $no2++ }}. {{ $nama }}</span>
                        <span class="text-sm md:text-base font-black text-slate-800">{{ number_format($det->total_luas, 2) }} Ha <span class="text-slate-400 font-medium mx-1">/</span> <span class="text-blue-600">{{ $det->total_lokasi }} lokasi</span></span>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- 3. TOTAL LAHAN PANEN --}}
        <div x-data="{ open: true }" class="group relative bg-white p-6 md:p-10 rounded-[2rem] border border-amber-100 shadow-xl hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-40 h-40 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-amber-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <button @click="open = !open" class="text-xs text-amber-600 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-xl uppercase tracking-widest font-black transition-colors border border-amber-100 shadow-sm">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup</span>
                </button>
            </div>
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-black uppercase tracking-[0.3em] mb-2">TOTAL LAHAN PANEN</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-5xl lg:text-6xl font-black text-slate-800 italic leading-none">{{ number_format($panenTotal, 2) }}</h3>
                    <span class="text-sm md:text-base text-amber-500 uppercase font-black">Ha</span>
                </div>
            </div>
            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-6 pt-5 border-t border-slate-100 flex-grow">
                <ul class="space-y-3 max-h-56 overflow-y-auto pr-2 custom-scrollbar">
                    @php $no3 = 1; @endphp
                    @foreach($jenisLahanList as $id => $nama)
                    @php $det = $panenDetails[$id] ?? null; @endphp
                    @if($det)
                    <li class="flex flex-col border-l-4 border-amber-300 pl-3">
                        <span class="text-[11px] md:text-xs font-bold text-slate-500 uppercase mb-0.5">{{ $no3++ }}. {{ $nama }}</span>
                        <span class="text-sm md:text-base font-black text-slate-800">{{ number_format($det->total_luas, 2) }} Ha <span class="text-slate-400 font-medium mx-1">/</span> <span class="text-amber-600">{{ $det->total_lokasi }} lokasi</span></span>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- 4. TOTAL TITIK LAHAN --}}
        <div x-data="{ open: true }" class="group relative bg-white p-6 md:p-10 rounded-[2rem] border border-rose-100 shadow-xl hover:-translate-y-2 hover:shadow-2xl transition-all duration-500 flex flex-col overflow-hidden">
            <div class="absolute -right-8 -top-8 w-40 h-40 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex items-center justify-between mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-red-500 text-white rounded-2xl flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-rose-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <button @click="open = !open" class="text-xs text-rose-600 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-xl uppercase tracking-widest font-black transition-colors border border-rose-100 shadow-sm">
                    <span x-show="!open">Lihat Rincian</span>
                    <span x-show="open" x-cloak>Tutup</span>
                </button>
            </div>
            <div class="relative z-10">
                <p class="text-slate-400 text-xs font-black uppercase tracking-[0.3em] mb-2">TOTAL TITIK LAHAN</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-5xl lg:text-6xl font-black text-slate-800 italic leading-none">{{ number_format($totalTitikLahan) }}</h3>
                    <span class="text-sm md:text-base text-rose-500 uppercase font-black">Titik</span>
                </div>
            </div>
            <div x-show="open" x-collapse x-cloak class="relative z-10 mt-6 pt-5 border-t border-slate-100 flex-grow">
                <div class="flex flex-col gap-4">
                    <div class="bg-rose-50 border border-rose-100 rounded-xl p-5">
                        <p class="text-xs md:text-sm font-black text-rose-400 uppercase tracking-widest">Polsek Mengelola</p>
                        <p class="text-xl md:text-2xl font-black text-rose-700 mt-1">{{ number_format($totalPolsek) }} <span class="text-xs md:text-sm font-bold">Polsek Aktif</span></p>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
                        <p class="text-xs md:text-sm font-black text-indigo-400 uppercase tracking-widest">Total Serapan</p>
                        <p class="text-xl md:text-2xl font-black text-indigo-700 mt-1">{{ number_format($totalSerapan, 2) }} <span class="text-xs md:text-sm font-bold">Ton</span></p>
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- =====================================================================
         4. TOTAL HASIL SERAPAN
    ===================================================================== --}}
    <div class="bg-white/90 backdrop-blur-3xl rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-1 mt-6">
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center justify-between relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path>
            </svg>
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
         5. CHART TREN LUASAN LAHAN + STATUS CARDS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Chart dengan filter tahun & bulan --}}
        <div class="lg:col-span-2 bg-white/90 backdrop-blur rounded-[2rem] border border-slate-200/60 shadow-xl p-6 md:p-8 relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-50 rounded-full opacity-60 -z-10 pointer-events-none"></div>
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6 relative z-10">
                <div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Tren Luasan Lahan</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Perkembangan potensi lahan per periode</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Filter Tahun Chart --}}
                    <form method="GET" action="{{ route('admin.dashboard') }}" id="chart-filter-form" class="flex items-center gap-2">
                        <input type="hidden" name="quarter" value="{{ $quarterFilter }}">
                        <input type="hidden" name="year" value="{{ $yearFilter }}">
                        <select name="chart_year" id="chart-year-select"
                            onchange="document.getElementById('chart-filter-form').submit()"
                            class="h-9 px-3 text-[10px] font-black tracking-widest text-slate-700 uppercase bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 outline-none cursor-pointer">
                            @foreach($chartYears as $cy)
                            <option value="{{ $cy }}" {{ (request('chart_year', $yearFilter) == $cy) ? 'selected' : '' }}>{{ $cy }}</option>
                            @endforeach
                        </select>
                        <select name="chart_month" id="chart-month-select"
                            onchange="document.getElementById('chart-filter-form').submit()"
                            class="h-9 px-3 text-[10px] font-black tracking-widest text-emerald-700 uppercase bg-emerald-50 border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 outline-none cursor-pointer">
                            <option value="all" {{ (request('chart_month','all') == 'all') ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'] as $mi => $mn)
                            <option value="{{ $mi+1 }}" {{ (request('chart_month') == $mi+1) ? 'selected' : '' }}>{{ $mn }}</option>
                            @endforeach
                        </select>
                    </form>
                    {{-- Toggle Bulanan/Tahunan --}}
                    <div class="flex bg-slate-50 p-1 rounded-xl border border-slate-200 gap-1 shadow-inner" id="chart-toggle-group">
                        <button id="btn-chart-monthly" class="px-4 py-2 text-[10px] font-black bg-white rounded-lg shadow-sm text-emerald-600 uppercase tracking-widest border border-emerald-100 transition-all">Bulanan</button>
                        <button id="btn-chart-yearly" class="px-4 py-2 text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Tahunan</button>
                    </div>
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
                    <p class="text-[10px] font-black text-blue-100 uppercase tracking-[0.2em]">Tanam Lahan</p>
                    <h3 class="text-xl font-black text-white mt-0.5">{{ number_format($tanamTotal, 2) }} <span class="text-xs font-bold text-blue-200">Ha</span></h3>
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
                    <p class="text-[10px] font-black text-emerald-100 uppercase tracking-[0.2em]">Panen Lahan</p>
                    <h3 class="text-xl font-black text-white mt-0.5">{{ number_format($panenTotal, 2) }} <span class="text-xs font-bold text-emerald-200">Ha</span></h3>
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
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path>
            </svg>
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
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
                <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path>
                </svg>
                <div class="flex items-center gap-4 relative z-10 w-full">
                    <div class="w-1.5 h-8 bg-teal-500 rounded-full"></div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Peta Penyebaran Potensi Lahan</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-widest">Distribusi geografis wilayah &mdash; Tahun {{ $yearFilter }}{{ $quarterFilter !== 'all' ? ' · Q' . $quarterFilter : '' }}</p>
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

                {{-- Top Right Legend Overlay -- all 3 statuses --}}
                <div class="absolute top-6 right-6 z-[400] pointer-events-none hidden md:block">
                    <div class="bg-slate-900/80 backdrop-blur-md px-4 py-3 rounded-2xl shadow-xl border border-slate-700/50 text-right space-y-1.5">
                        <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2">Status Lahan &bull; {{ $yearFilter }}{{ $quarterFilter !== 'all' ? ' Q'.$quarterFilter : '' }}</p>
                        <div class="flex items-center justify-end gap-2">
                            <span class="text-xs font-medium text-white">Produktif</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <span class="text-xs font-medium text-white">Tanam</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.8)]"></span>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <span class="text-xs font-medium text-white">Panen</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(245,158,11,0.8)]"></span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Map Stats Bar --}}
            @php
                $mapProduktif = $mapData->where('status','Produktif')->count();
                $mapTanam     = $mapData->where('status','Tanam')->count();
                $mapPanen     = $mapData->where('status','Panen')->count();
                $mapTotal     = $mapData->count();
            @endphp
            <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100">
                <div class="flex flex-col items-center py-4 gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Produktif</span>
                    <span class="text-xl font-black text-slate-800">{{ $mapProduktif }}</span>
                    <span class="text-[9px] text-slate-400">titik lahan</span>
                </div>
                <div class="flex flex-col items-center py-4 gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanam</span>
                    <span class="text-xl font-black text-slate-800">{{ $mapTanam }}</span>
                    <span class="text-[9px] text-slate-400">titik lahan</span>
                </div>
                <div class="flex flex-col items-center py-4 gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Panen</span>
                    <span class="text-xl font-black text-slate-800">{{ $mapPanen }}</span>
                    <span class="text-[9px] text-slate-400">titik lahan</span>
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
                        <span class="text-2xl font-black text-slate-800">{{ number_format($totalTitikLahan) }}</span>
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
                        <span class="text-2xl font-black text-slate-800">{{ number_format($totalPolsek) }}</span>
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
            <button type="button" onclick="document.getElementById('modalNotifikasi').classList.remove('hidden')" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-emerald-500/30 active:scale-95 hidden md:flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Kirim Notifikasi Massal
            </button>
        </div>

        <div class="px-8 py-4 border-b border-slate-800 bg-slate-900/50 relative z-10" id="pending-section">
            <form method="GET" action="#pending-section" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="year" value="{{ request('year', date('Y')) }}">
                <input type="hidden" name="quarter" value="{{ request('quarter', 'all') }}">

                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" name="pending_search" value="{{ request('pending_search') }}" placeholder="Cari wilayah atau alamat..." class="w-full bg-slate-800/50 border border-slate-700 text-slate-200 text-xs rounded-xl pl-9 pr-3 py-2 focus:ring-2 focus:ring-emerald-500/50 focus:outline-none placeholder-slate-500 transition-all">
                </div>

                <select name="pending_jenis" class="bg-slate-800/50 border border-slate-700 text-slate-300 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500/50 focus:outline-none transition-all cursor-pointer min-w-[140px]">
                    <option value="">Semua Jenis Lahan</option>
                    @foreach($jenisLahanList as $id => $nama)
                    <option value="{{ $id }}" {{ request('pending_jenis') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>

                <select name="pending_year" class="bg-slate-800/50 border border-slate-700 text-slate-300 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500/50 focus:outline-none transition-all cursor-pointer w-24">
                    <option value="">Tahun</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                    <option value="{{ $y }}" {{ request('pending_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <select name="pending_month" class="bg-slate-800/50 border border-slate-700 text-slate-300 text-xs rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500/50 focus:outline-none transition-all cursor-pointer w-28">
                    <option value="">Bulan</option>
                    @foreach(['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agt','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'] as $m => $mName)
                    <option value="{{ $m }}" {{ request('pending_month') == $m ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-emerald-400 text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm">
                        Cari
                    </button>
                    <a href="?year={{ request('year', date('Y')) }}&quarter={{ request('quarter', 'all') }}#pending-section" class="p-2 bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-slate-400 hover:text-slate-200 rounded-xl transition-all group" title="Refresh">
                        <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </a>
                </div>
            </form>
        </div>
        <div class="p-6" x-data="{ activeTab: 'potensi' }">
            <div class="flex items-center justify-between mb-4 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2">
                    <button @click="activeTab = 'potensi'" :class="activeTab === 'potensi' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50' : 'bg-transparent text-slate-400 border-transparent hover:text-slate-300'" class="px-4 py-2 text-xs font-black uppercase tracking-widest border rounded-lg transition-all">Data Potensi ({{ count($pendingPotensi) }})</button>
                    <button @click="activeTab = 'kelola'" :class="activeTab === 'kelola' ? 'bg-amber-500/20 text-amber-400 border-amber-500/50' : 'bg-transparent text-slate-400 border-transparent hover:text-slate-300'" class="px-4 py-2 text-xs font-black uppercase tracking-widest border rounded-lg transition-all">Kelola Lahan ({{ count($pendingKelola) }})</button>
                </div>

                @if(request('pending_jenis'))
                <div class="hidden md:flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-lg">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Filter:</span>
                    <span class="text-[10px] font-black text-indigo-400 uppercase tracking-wider">{{ $jenisLahanList[request('pending_jenis')] ?? 'Semua Jenis Lahan' }}</span>
                </div>
                @endif
            </div>

            <!-- Tab Data Potensi -->
            <div x-show="activeTab === 'potensi'" x-transition.opacity.duration.300ms>
                @if(count($pendingPotensi) > 0)
                <div class="max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-900/95 backdrop-blur z-10">
                            <tr class="border-b border-slate-800 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="pb-3 pl-2">Satwil</th>
                                <th class="pb-3">Alamat Lahan</th>
                                <th class="pb-3">Jenis Lahan</th>
                                <th class="pb-3 pr-2 text-right">Luas (Ha)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($pendingPotensi as $item)
                            <tr class="hover:bg-white/[0.02] transition-colors group cursor-pointer" onclick="window.location.href='{{ route('admin.kelola-lahan.potensi.index', ['search' => $item->id_lahan]) }}'">
                                <td class="py-3 pl-2 w-1/4">
                                    <p class="text-xs font-medium text-slate-200 group-hover:text-emerald-400 transition-colors">{{ $item->satwil }}</p>
                                </td>
                                <td class="py-3 w-1/3">
                                    <p class="text-[10px] text-slate-400">{{ \Illuminate\Support\Str::limit($item->alamat_lahan, 60) }}</p>
                                </td>
                                <td class="py-3 w-1/4">
                                    <span class="inline-block px-2 py-1 bg-slate-800 text-slate-300 border border-slate-700 text-[9px] font-black uppercase tracking-widest rounded shadow-sm">
                                        {{ $jenisLahanList[$item->id_jenis_lahan] ?? 'Lainnya' }}
                                    </span>
                                </td>
                                <td class="py-3 pr-2 text-right w-1/6">
                                    <p class="text-xs font-bold text-emerald-400">{{ number_format($item->luas_lahan, 2) }}</p>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-8 text-center border border-white/[0.06] rounded-lg bg-white/[0.02]">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-300">Semua Data Potensi Telah Divalidasi</p>
                </div>
                @endif
            </div>

            <!-- Tab Kelola Lahan -->
            <div x-show="activeTab === 'kelola'" x-transition.opacity.duration.300ms style="display: none;">
                @if(count($pendingKelola) > 0)
                <div class="max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-900/95 backdrop-blur z-10">
                            <tr class="border-b border-slate-800 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="pb-3 pl-2">Satwil</th>
                                <th class="pb-3">Jenis Lahan</th>
                                <th class="pb-3">Kategori & Tanggal</th>
                                <th class="pb-3 pr-2 text-right">Luas (Ha)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @foreach($pendingKelola as $item)
                            <tr class="hover:bg-white/[0.02] transition-colors group cursor-pointer" onclick="window.location.href='{{ route('admin.kelola-lahan.daftar.index', ['search' => $item->id_lahan]) }}'">
                                <td class="py-3 pl-2 w-1/4">
                                    <p class="text-xs font-medium text-slate-200 group-hover:text-amber-400 transition-colors">{{ $item->satwil }}</p>
                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ \Illuminate\Support\Str::limit($item->alamat_lahan, 40) }}</p>
                                </td>
                                <td class="py-3 w-1/4">
                                    <span class="inline-block px-2 py-1 bg-slate-800 text-slate-300 border border-slate-700 text-[9px] font-black uppercase tracking-widest rounded shadow-sm">
                                        {{ $jenisLahanList[$item->id_jenis_lahan] ?? 'Lainnya' }}
                                    </span>
                                </td>
                                <td class="py-3 w-1/4">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest {{ $item->jenis == 'Tanam' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                                        {{ $item->jenis }}
                                    </span>
                                    <p class="text-[10px] text-slate-400 mt-1">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</p>
                                </td>
                                <td class="py-3 pr-2 text-right w-1/4">
                                    <p class="text-xs font-bold text-amber-400">{{ number_format($item->luas, 2) }}</p>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="py-8 text-center border border-white/[0.06] rounded-lg bg-white/[0.02]">
                    <div class="w-12 h-12 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-300">Semua Data Kelola Telah Divalidasi</p>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-800">
                <p class="text-xs text-slate-500">
                    Total <span class="text-white font-black text-sm">{{ number_format($totalPendingPotensi + $totalPendingKelola) }}</span> data belum divalidasi
                    <span class="text-slate-600 mx-1">—</span>
                    <span class="text-emerald-400">{{ number_format($totalPendingPotensi) }} Potensi</span>
                    <span class="text-slate-600 mx-1">+</span>
                    <span class="text-amber-400">{{ number_format($totalPendingKelola) }} Kelola</span>
                </p>
                <form method="POST" action="{{ route('admin.dashboard.notify-pending') }}" class="md:hidden">
                    @csrf
                    <button type="button" onclick="document.getElementById('modalNotifikasi').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition-all active:scale-95">
                        Kirim Notifikasi
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- ============================================================
     MODAL KIRIM NOTIFIKASI MASSAL
============================================================ --}}
<div id="modalNotifikasi" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="document.getElementById('modalNotifikasi').classList.add('hidden')"></div>

    {{-- Modal Panel --}}
    <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-700/80 rounded-[2rem] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]" x-data="notifModal()" x-init="init()">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-emerald-900/60 to-teal-900/40 px-7 py-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Kirim Notifikasi Massal</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5 uppercase tracking-wide">Pilih target penerima &amp; jenis data</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalNotifikasi').classList.add('hidden')" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.dashboard.notify-pending') }}" id="formNotifMassal" class="flex flex-col flex-1 overflow-hidden">
            @csrf

            <div class="px-7 py-6 space-y-6 overflow-y-auto flex-1">

                {{-- SECTION 1: Target Penerima --}}
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.18em] mb-3">① Target Penerima</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                        {{-- Semua Polres --}}
                        <label class="group cursor-pointer">
                            <input type="radio" name="target_type" value="all" class="sr-only" x-model="targetType" />
                            <div :class="targetType === 'all' ? 'border-emerald-500 bg-emerald-500/10 text-emerald-400' : 'border-slate-700 bg-slate-800/50 text-slate-400 hover:border-slate-600'" class="rounded-xl border p-4 flex flex-col items-center gap-2 transition-all">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="targetType === 'all' ? 'bg-emerald-500/20' : 'bg-slate-700'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-center leading-tight">Semua Polres</span>
                            </div>
                        </label>

                        {{-- Pilih Polres --}}
                        <label class="group cursor-pointer">
                            <input type="radio" name="target_type" value="polres" class="sr-only" x-model="targetType" />
                            <div :class="targetType === 'polres' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-slate-700 bg-slate-800/50 text-slate-400 hover:border-slate-600'" class="rounded-xl border p-4 flex flex-col items-center gap-2 transition-all">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="targetType === 'polres' ? 'bg-blue-500/20' : 'bg-slate-700'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-center leading-tight">Pilih Polres</span>
                            </div>
                        </label>

                        {{-- Polres Dengan Pending --}}
                        <label class="group cursor-pointer">
                            <input type="radio" name="target_type" value="pending" class="sr-only" x-model="targetType" />
                            <div :class="targetType === 'pending' ? 'border-amber-500 bg-amber-500/10 text-amber-400' : 'border-slate-700 bg-slate-800/50 text-slate-400 hover:border-slate-600'" class="rounded-xl border p-4 flex flex-col items-center gap-2 transition-all">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="targetType === 'pending' ? 'bg-amber-500/20' : 'bg-slate-700'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-center leading-tight">Hanya Yang Pending</span>
                            </div>
                        </label>

                    </div>

                    {{-- Sub-panel: Pilih Polres (checkboxes) --}}
                    <div x-show="targetType === 'polres'" x-transition class="mt-4 bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Pilih Polres yang Akan Dikirim Notifikasi:</p>
                        <div class="max-h-48 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
                            @foreach($polresList as $polres)
                            <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-slate-700/50 cursor-pointer group transition-colors">
                                <input type="checkbox" name="target_polres[]" value="{{ $polres->id_tingkat }}"
                                    class="w-4 h-4 rounded border-slate-600 text-emerald-500 bg-slate-700 focus:ring-emerald-500 focus:ring-offset-slate-800 cursor-pointer">
                                <div>
                                    <span class="text-xs font-semibold text-slate-200 group-hover:text-white">{{ $polres->nama_tingkat }}</span>
                                    <span class="text-[10px] text-slate-500 ml-2">{{ $polres->id_tingkat }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <div class="mt-3 flex gap-2">
                            <button type="button" onclick="document.querySelectorAll('#formNotifMassal input[name=\'target_polres[]\']').forEach(c=>c.checked=true)" class="text-[10px] font-black text-emerald-400 hover:text-emerald-300 uppercase tracking-widest transition-colors">Pilih Semua</button>
                            <span class="text-slate-700">|</span>
                            <button type="button" onclick="document.querySelectorAll('#formNotifMassal input[name=\'target_polres[]\']').forEach(c=>c.checked=false)" class="text-[10px] font-black text-slate-500 hover:text-slate-300 uppercase tracking-widest transition-colors">Hapus Pilihan</button>
                        </div>
                    </div>

                </div>

                {{-- SECTION 2: Jenis Data --}}
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.18em] mb-3">② Jenis Data yang Dikirim</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                        <label class="cursor-pointer">
                            <input type="radio" name="data_type" value="all" class="sr-only" x-model="dataType" />
                            <div :class="dataType === 'all' ? 'border-emerald-500 bg-emerald-500/10' : 'border-slate-700 bg-slate-800/50 hover:border-slate-600'" class="rounded-xl border p-4 flex items-center gap-3 transition-all">
                                <div class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center" :class="dataType === 'all' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest" :class="dataType === 'all' ? 'text-emerald-400' : 'text-slate-400'">Semua Data</p>
                                    <p class="text-[9px] text-slate-500 mt-0.5">Potensi + Kelola</p>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="data_type" value="potensi" class="sr-only" x-model="dataType" />
                            <div :class="dataType === 'potensi' ? 'border-emerald-400 bg-emerald-500/10' : 'border-slate-700 bg-slate-800/50 hover:border-slate-600'" class="rounded-xl border p-4 flex items-center gap-3 transition-all">
                                <div class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center" :class="dataType === 'potensi' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest" :class="dataType === 'potensi' ? 'text-emerald-400' : 'text-slate-400'">Data Potensi</p>
                                    <p class="text-[9px] text-slate-500 mt-0.5">Hanya lahan potensi</p>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="data_type" value="kelola" class="sr-only" x-model="dataType" />
                            <div :class="dataType === 'kelola' ? 'border-amber-400 bg-amber-500/10' : 'border-slate-700 bg-slate-800/50 hover:border-slate-600'" class="rounded-xl border p-4 flex items-center gap-3 transition-all">
                                <div class="w-7 h-7 rounded-lg flex-shrink-0 flex items-center justify-center" :class="dataType === 'kelola' ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-700 text-slate-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest" :class="dataType === 'kelola' ? 'text-amber-400' : 'text-slate-400'">Kelola Lahan</p>
                                    <p class="text-[9px] text-slate-500 mt-0.5">Tanam &amp; Panen</p>
                                </div>
                            </div>
                        </label>

                    </div>
                </div>

                {{-- Preview info --}}
                <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-4 flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-relaxed">
                        Notifikasi akan dikirim ke <strong class="text-slate-200">Operator Polres</strong> sesuai pilihan di atas.
                        Pesan berisi peringatan untuk segera melakukan validasi pada data yang masih pending.
                        Total pending saat ini: <span class="text-emerald-400 font-black">{{ number_format($totalPendingPotensi) }} Potensi</span> +
                        <span class="text-amber-400 font-black">{{ number_format($totalPendingKelola) }} Kelola</span>.
                    </p>
                </div>

            </div>

            {{-- Footer Buttons --}}
            <div class="px-7 py-5 bg-slate-950/40 border-t border-slate-800 flex flex-wrap items-center justify-end gap-3 flex-shrink-0">
                <button type="button" onclick="document.getElementById('modalNotifikasi').classList.add('hidden')" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all whitespace-nowrap">
                    Batal
                </button>
                <button type="submit" class="flex-1 min-w-[140px] px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-emerald-500/20 active:scale-95 flex items-center justify-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Kirim Notifikasi
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    function notifModal() {
        return {
            targetType: 'all',
            dataType: 'all',
            init() {
                // watch targetType to update hidden field if needed
                this.$watch('targetType', (val) => {
                    if (val !== 'polres') {
                        // uncheck all polres checkboxes
                        document.querySelectorAll('#formNotifMassal input[name="target_polres[]"]')
                            .forEach(c => c.checked = false);
                    }
                });
            }
        }
    }
</script>

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
                        'rgba(59, 130, 246, 0.8)', // blue
                        'rgba(99, 102, 241, 0.8)', // indigo
                        'rgba(245, 158, 11, 0.8)', // amber
                        'rgba(16, 185, 129, 0.8)' // emerald
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
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500',
                                family: 'Outfit'
                            },
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '700',
                                family: 'Outfit'
                            },
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
            var color = '#10b981'; // Produktif (emerald)
            if (point.status === 'Tanam') {
                color = '#3b82f6'; // Tanam (blue)
            } else if (point.status === 'Panen') {
                color = '#f59e0b'; // Panen (amber)
            }
            L.circleMarker([point.lat, point.lng], {
                radius: 8,
                fillColor: color,
                color: '#ffffff',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map).bindPopup(
                '<div style="font-size:12px;font-weight:600;">' + point.title + '</div>' +
                '<div style="font-size:11px;color:#64748b;">Status: ' + point.status + '</div>'
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