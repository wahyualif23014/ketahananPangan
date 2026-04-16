@extends('layouts.app')

@section('header', 'Dashboard Utama Administrator')

@section('content')
<div class="min-h-screen space-y-4 font-sans">

    {{-- =====================================================================
         1. HEADER
    ===================================================================== --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight leading-none">
                Dashboard
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Selamat datang, <span class="text-emerald-600 font-medium">{{ Auth::user()->nama_anggota }}</span>
                &mdash; Periode 2026
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative hidden md:block">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" placeholder="Cari wilayah atau data..."
                    class="pl-9 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-lg text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 w-60 transition">
            </div>
            <div class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Live
            </div>
        </div>
    </div>

    {{-- =====================================================================
         2. KPI SUMMARY CARDS
    ===================================================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-slate-100/70 rounded-xl p-5 border border-slate-200/60 hover:border-slate-300 hover:shadow-sm transition-all">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Potensi Lahan</p>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-semibold text-slate-900">170,715</span>
                <span class="text-xs text-slate-400 uppercase font-medium">Ha</span>
            </div>
            <p class="text-[10px] text-slate-400 uppercase mt-2">Periode 2026</p>
        </div>

        <div class="bg-slate-100/70 rounded-xl p-5 border border-slate-200/60 hover:border-slate-300 hover:shadow-sm transition-all">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Lahan Tanam</p>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-semibold text-slate-900">242.74</span>
                <span class="text-xs text-slate-400 uppercase font-medium">Ha</span>
            </div>
            <div class="flex items-center gap-1 mt-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <p class="text-[10px] text-slate-400 uppercase">Musim Tanam 2026</p>
            </div>
        </div>

        <div class="bg-slate-100/70 rounded-xl p-5 border border-slate-200/60 hover:border-slate-300 hover:shadow-sm transition-all">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Lahan Panen</p>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-semibold text-slate-900">243.72</span>
                <span class="text-xs text-slate-400 uppercase font-medium">Ha</span>
            </div>
            <div class="flex items-center gap-1 mt-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <p class="text-[10px] text-slate-400 uppercase">Realisasi 2026</p>
            </div>
        </div>

        <div class="bg-slate-100/70 rounded-xl p-5 border border-slate-200/60 hover:border-slate-300 hover:shadow-sm transition-all">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Titik Lahan</p>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-semibold text-slate-900">5,528</span>
                <span class="text-xs text-slate-400 uppercase font-medium">Titik</span>
            </div>
            <div class="flex items-center gap-1 mt-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <p class="text-[10px] text-slate-400 uppercase">659 Polsek aktif</p>
            </div>
        </div>

    </div>

    {{-- =====================================================================
         3. DISTRIBUSI POTENSI LAHAN
    ===================================================================== --}}
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-800">Distribusi Potensi Lahan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kategori lahan seluruh wilayah &mdash; 2026</p>
            </div>
            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-medium uppercase tracking-wide rounded-md border border-slate-200">
                170,715.11 Ha Total
            </span>
        </div>
        <div class="p-6">
            @php
                $potensiItems = [
                    'Luas Baku Sawah'             => ['val' => '64,792.29', 'pct' => 38],
                    'Pesantren'                    => ['val' => '64,792.90', 'pct' => 38],
                    'Produktif (Poktan)'           => ['val' => '34,882.86', 'pct' => 20],
                    'Hutan (Perhutani)'            => ['val' => '22,573.23', 'pct' => 13],
                    'Produktif (Masyarakat)'       => ['val' => '27,316.49', 'pct' => 16],
                    'Produktif (Tumpang Sari)'     => ['val' => '27,316.49', 'pct' => 16],
                    'Milik Polri'                  => ['val' => '9.63',      'pct' => 1],
                    'Lainnya'                      => ['val' => '107.52',    'pct' => 1],
                ];
                $barColors = ['bg-emerald-500','bg-blue-500','bg-amber-500','bg-violet-500','bg-teal-500','bg-sky-500','bg-rose-500','bg-slate-400'];
                $i = 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-4">
                @foreach($potensiItems as $label => $item)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-slate-600">{{ $label }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ $item['val'] }} <small class="text-slate-400 font-normal text-[10px] uppercase">Ha</small></span>
                        </div>
                        <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColors[$i] }} rounded-full" style="width: {{ min($item['pct'], 100) }}%"></div>
                        </div>
                    </div>
                    @php $i++ @endphp
                @endforeach
            </div>
        </div>
    </div>

    {{-- =====================================================================
         4. CHART + STATUS CARDS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Line Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/60 shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Tren Luasan Lahan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Perkembangan potensi lahan per tahun</p>
                </div>
                <div class="flex bg-slate-100 p-1 rounded-lg border border-slate-200 gap-1">
                    <button class="px-4 py-1.5 text-[10px] font-medium bg-white rounded-md shadow-sm text-slate-700 uppercase tracking-wide border border-slate-200">Bulanan</button>
                    <button class="px-4 py-1.5 text-[10px] font-medium text-slate-400 hover:text-slate-600 uppercase tracking-wide transition">Tahunan</button>
                </div>
            </div>
            <div class="h-56">
                <canvas id="productivityChart"></canvas>
            </div>
            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-8">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wide">Total Yield 2026</p>
                    <p class="text-sm font-semibold text-slate-800 mt-0.5">128,429.00 Ha</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wide">Growth Rate</p>
                    <p class="text-sm font-semibold text-emerald-600 mt-0.5">+12.4% ↑</p>
                </div>
            </div>
        </div>

        {{-- Harvest Status Cards --}}
        <div class="flex flex-col gap-4">
            @php
                $harvestCards = [
                    ['label' => 'Panen Normal',  'val' => $harvestStats['normal']  ?? '0', 'color' => 'emerald', 'dot' => 'bg-emerald-500'],
                    ['label' => 'Gagal Panen',   'val' => $harvestStats['failed']  ?? '0', 'color' => 'rose',    'dot' => 'bg-rose-500'],
                    ['label' => 'Panen Dini',    'val' => $harvestStats['early']   ?? '0', 'color' => 'amber',   'dot' => 'bg-amber-500'],
                    ['label' => 'Panen Tahunan', 'val' => $harvestStats['yearly']  ?? '0', 'color' => 'blue',    'dot' => 'bg-blue-500'],
                ];
            @endphp
            @foreach($harvestCards as $card)
                <div class="bg-slate-100/70 rounded-xl p-5 border border-slate-200/60 flex items-center justify-between hover:border-slate-300 hover:shadow-sm transition-all flex-1">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $card['dot'] }} flex-shrink-0"></span>
                        <p class="text-sm font-medium text-slate-600">{{ $card['label'] }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-semibold text-slate-900">{{ $card['val'] }}</span>
                        <span class="text-xs text-slate-400 uppercase ml-1">Ha</span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- =====================================================================
         5. PLANTING & HARVESTING ANALYTICS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Planting Analytics</p>
                    <h3 class="text-lg font-semibold text-slate-800 mt-0.5">242.74 <span class="text-sm font-normal text-slate-400">Ha</span></h3>
                </div>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-medium rounded-md border border-blue-100 uppercase">Lahan Tanam 2026</span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach(['Milik Polri' => '0', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.10'] as $label => $val)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition">
                        <span class="text-sm text-slate-600">{{ $label }}</span>
                        <span class="text-sm font-semibold text-slate-800">{{ $val }} <small class="text-slate-400 font-normal text-[10px] uppercase">Ha</small></span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Harvesting Analytics</p>
                    <h3 class="text-lg font-semibold text-slate-800 mt-0.5">243.72 <span class="text-sm font-normal text-slate-400">Ha</span></h3>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-medium rounded-md border border-emerald-100 uppercase">Lahan Panen 2026</span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach(['Milik Polri' => '0.98', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.10'] as $label => $val)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-slate-50 transition">
                        <span class="text-sm text-slate-600">{{ $label }}</span>
                        <span class="text-sm font-semibold text-slate-800">{{ $val }} <small class="text-slate-400 font-normal text-[10px] uppercase">Ha</small></span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
    

    {{-- =====================================================================
         6. QUARTERLY PERFORMANCE
    ===================================================================== --}}
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">Monitoring Target & Hasil Kwartal</h3>
            <p class="text-xs text-slate-500 mt-0.5">Progres capaian per-triwulan tahun 2026</p>
        </div>
        <div class="p-6 space-y-6">
            @php
                $kwartalData = [
                    ['category' => 'Milik Polri',                'unit' => 'Ha',  'q' => ['9.63', '0', '0', '0'],    'accent' => 'blue'],
                    ['category' => 'Produktif (Poktan Binaan)',   'unit' => 'Ha',  'q' => ['34,882.86','107.08','0','0'], 'accent' => 'emerald'],
                    ['category' => 'Hasil Panen Kwartal',        'unit' => 'Ton', 'q' => ['988.92', '0', '0', '0'],   'accent' => 'amber'],
                ];
                $qColors = ['blue' => 'text-blue-600 bg-blue-50 border-blue-100', 'emerald' => 'text-emerald-600 bg-emerald-50 border-emerald-100', 'amber' => 'text-amber-600 bg-amber-50 border-amber-100'];
            @endphp
            @foreach($kwartalData as $row)
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-3">{{ $row['category'] }}</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($row['q'] as $qi => $val)
                            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200/60 hover:border-slate-300 hover:shadow-sm transition-all">
                                <p class="text-[10px] text-slate-400 uppercase tracking-wide mb-1">Q{{ $qi + 1 }} 2026</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xl font-semibold text-slate-900">{{ $val }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase">{{ $row['unit'] }}</span>
                                </div>
                                @if($val !== '0')
                                    <div class="mt-2">
                                        <span class="text-[9px] px-2 py-0.5 rounded border font-medium {{ $qColors[$row['accent']] }}">Terisi</span>
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <span class="text-[9px] px-2 py-0.5 rounded border font-medium text-slate-400 bg-slate-100 border-slate-200">Belum</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- =====================================================================
         7. SERAPAN HASIL
    ===================================================================== --}}
    <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-800">Serapan Hasil Panen</h3>
            <p class="text-xs text-slate-500 mt-0.5">Distribusi serapan seluruh saluran &mdash; 2026</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            @php
                $serapanData = [
                    ['label' => 'Serapan Bulog',         'val' => '565.42', 'unit' => 'Ton', 'accent' => 'blue',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['label' => 'Serapan Pabrik Pakan',  'val' => '0',      'unit' => 'Ton', 'accent' => 'indigo',  'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
                    ['label' => 'Serapan Tengkulak',     'val' => '516.92', 'unit' => 'Ton', 'accent' => 'amber',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
                    ['label' => 'Konsumsi Sendiri',      'val' => '0',      'unit' => 'Ton', 'accent' => 'emerald', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                ];
                $serapanBg   = ['blue' => 'bg-blue-50 text-blue-600',    'indigo' => 'bg-indigo-50 text-indigo-500', 'amber' => 'bg-amber-50 text-amber-600', 'emerald' => 'bg-emerald-50 text-emerald-600'];
                $serapanDot  = ['blue' => 'bg-blue-500', 'indigo' => 'bg-indigo-500', 'amber' => 'bg-amber-500', 'emerald' => 'bg-emerald-500'];
            @endphp
            @foreach($serapanData as $s)
                <div class="p-6 flex flex-col items-center text-center group hover:bg-slate-50 transition-all">
                    <div class="p-3 {{ $serapanBg[$s['accent']] }} rounded-xl mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $s['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-semibold text-slate-900">{{ $s['val'] }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-medium">{{ $s['unit'] }}</span>
                    </div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mt-2 leading-tight">{{ $s['label'] }}</p>
                    <div class="flex items-center gap-1.5 mt-3">
                        <span class="w-1.5 h-1.5 rounded-full {{ $serapanDot[$s['accent']] }}"></span>
                        <span class="text-[10px] text-slate-400 uppercase">Tahun 2026</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- =====================================================================
         8. GEOSPATIAL MAP + DONUT CHARTS
    ===================================================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Peta Penyebaran Potensi Lahan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Distribusi geografis wilayah &mdash; 2026</p>
                </div>

            </div>
            <div id="map" class="h-[420px] w-full z-0"></div>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">

            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-6 flex flex-col items-center justify-center flex-1">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-4">Total Titik Lahan</p>
                <div class="relative w-40 h-40">
                    <canvas id="totalTitikChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-semibold text-slate-900">5,528</span>
                        <span class="text-[10px] text-slate-400 uppercase mt-0.5">Lahan</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Aktif</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-200"></span>Lainnya</div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200/60 shadow-sm p-6 flex flex-col items-center justify-center flex-1">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-4">Pengelolaan Polsek</p>
                <div class="relative w-40 h-40">
                    <canvas id="pengelolaanPolsekChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-semibold text-slate-900">659</span>
                        <span class="text-[10px] text-slate-400 uppercase mt-0.5">Polsek</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Aktif</div>
                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-200"></span>Lainnya</div>
                </div>
            </div>

        </div>

    </div>

    {{-- =====================================================================
         9. PENDING VALIDASI
    ===================================================================== --}}
    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-emerald-400 uppercase tracking-widest mb-1">Sistem Validasi Terintegrasi</p>
                <h3 class="text-lg font-semibold text-white">Laporan Pending Validasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Satuan wilayah yang belum melakukan sinkronisasi data final</p>
            </div>
            <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition-all active:scale-95 hidden md:block">
                Kirim Notifikasi Massal
            </button>
        </div>
        <div class="p-6">
            @php
                $pendingSatwil = ['Polres Jember', 'Polres Jombang', 'Polres Malang', 'Polres Pasuruan Kota'];
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                @foreach($pendingSatwil as $polres)
                    <div class="flex items-center gap-3 p-4 bg-white/[0.04] border border-white/[0.06] rounded-lg hover:border-emerald-500/30 hover:bg-white/[0.06] transition-all group cursor-pointer">
                        <div class="relative flex h-2.5 w-2.5 flex-shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500/80"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-200 group-hover:text-emerald-400 transition-colors truncate">{{ $polres }}</p>
                            <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-wide">Menunggu validasi pendataan</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-600 group-hover:text-emerald-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                <p class="text-xs text-slate-500">Total <span class="text-slate-300 font-medium">4 satwil</span> memerlukan tindakan segera</p>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // 1. LINE CHART
    const lineCtx = document.getElementById('productivityChart').getContext('2d');
    const grad = lineCtx.createLinearGradient(0, 0, 0, 220);
    grad.addColorStop(0, 'rgba(16, 185, 129, 0.12)');
    grad.addColorStop(1, 'rgba(16, 185, 129, 0)');

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: ['2021', '2022', '2023', '2024', '2025', '2026'],
            datasets: [{
                label: 'Luas Lahan (Ha)',
                data: [85000, 105000, 95000, 140000, 165000, 170715],
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
                    display: false,
                    beginAtZero: false
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        font: { size: 11, weight: '500' },
                        color: '#94a3b8'
                    }
                }
            }
        }
    });

    // 2. LEAFLET MAP
    var map = L.map('map', {
        zoomControl: false,
        scrollWheelZoom: false
    }).setView([-7.5360, 112.2384], 8);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    var sampleData = [
        { lat: -7.2504, lng: 112.7688, title: "Polrestabes Surabaya", status: "Produktif" },
        { lat: -7.9839, lng: 112.6214, title: "Polres Malang",        status: "Panen"     },
        { lat: -7.5457, lng: 112.1679, title: "Polres Mojokerto",     status: "Produktif" },
        { lat: -7.8482, lng: 112.5239, title: "Polres Blitar",        status: "Tanam"     }
    ];

    sampleData.forEach(function (point) {
        L.circleMarker([point.lat, point.lng], {
            radius: 7,
            fillColor: '#10b981',
            color: '#ffffff',
            weight: 2,
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
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        animation: { animateScale: true, animateRotate: true }
    };

    new Chart(document.getElementById('totalTitikChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [5528, 1400],
                backgroundColor: ['#3b82f6', '#f1f5f9'],
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: donutOptions
    });

    new Chart(document.getElementById('pengelolaanPolsekChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [659, 200],
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