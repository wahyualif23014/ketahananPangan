@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

    .potensi-container {
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
</style>

<div class="space-y-8 pb-24 potensi-container max-w-7xl mx-auto" x-data="potensiLahanManager()">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Potensi Lahan</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                POTENSI <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">LAHAN</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Pendataan lokasi dan statistik pemanfaatan lahan untuk ketahanan pangan operasional.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group hidden sm:block">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" placeholder="CARI LAHAN..." 
                    class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase shadow-sm">
            </div>
            <button onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-slate-900 text-emerald-400 rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <button @click="openModal()" 
                class="flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-2xl shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-105 active:scale-95 transition-all text-xs font-black uppercase tracking-widest border-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah
            </button>
        </div>
    </div>

    @php
        // Pemetaan Klasifikasi Lahan untuk Modal Form
        $kategoriMapping = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',
            2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',
            4 => 'PESANTREN',
            5 => 'MILIK POLRI',
            6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',
            8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA'
        ];

        // LOGIKA PENYIAPAN DATA STATISTIK
        $allLahanData = DB::table('lahan')->where('deletestatus', '!=', '0')->get();

        $totalLuasLahan = 0;
        $totalLokasiLahan = 0;
        $unikLokasi = [];
        $breakdownByJenis = [];

        $luasBelumValidasi = 0;
        $countBelumValidasi = 0;
        $totalCount = count($allLahanData);

        foreach ($kategoriMapping as $k => $v) {
            $breakdownByJenis[$k] = ['nama' => $v, 'luas' => 0, 'lokasi' => []];
        }

        $tingkatAdaLahan = [];

        foreach ($allLahanData as $lahan) {
            // Track Polres (asumsi 2 segmen pertama dari id_tingkat merepresentasikan Polres)
            $parts = explode('.', $lahan->id_tingkat);
            if (count($parts) >= 2) {
                $tingkatAdaLahan[$parts[0] . '.' . $parts[1]] = true;
            }

            if ($lahan->status_lahan == '1') {
                $luas = (float) $lahan->luas_lahan;
                $totalLuasLahan += $luas;
                $unikLokasi[$lahan->id_wilayah] = true;

                if (isset($breakdownByJenis[$lahan->id_jenis_lahan])) {
                    $breakdownByJenis[$lahan->id_jenis_lahan]['luas'] += $luas;
                    $breakdownByJenis[$lahan->id_jenis_lahan]['lokasi'][$lahan->id_wilayah] = true;
                }
            } else {
                $luasBelumValidasi += (float) $lahan->luas_lahan;
                $countBelumValidasi++;
            }
        }

        $totalLokasiLahan = count($unikLokasi);
        $persenBelumValidasi = $totalCount > 0 ? round(($countBelumValidasi / $totalCount) * 100, 2) : 0;

        // Hitung Sumber Data (Perhitungan Unik/Distinct)
        $distinctPolsek = [];
        $distinctKabKota = [];
        $distinctKecamatan = [];
        $distinctDesa = [];

        foreach ($allLahanData as $lahan) {
            // Polsek Unik = id_tingkat dengan 2 titik atau lebih
            $idT = (string)$lahan->id_tingkat;
            if (mb_substr_count($idT, '.') >= 2) {
                $distinctPolsek[$idT] = true;
            }

            // Wilayah Unik = pecah id_wilayah untuk mendeteksi parent-nya
            $idW = (string)$lahan->id_wilayah;
            $parts = explode('.', $idW);
            $dotsW = count($parts) - 1;

            // Jika punya minimal 1 titik (misal 35.01), catat sebagai partisipasi Kab/Kota
            if ($dotsW >= 1) {
                $kabId = $parts[0] . '.' . $parts[1];
                $distinctKabKota[$kabId] = true;
            }
            // Jika punya minimal 2 titik (misal 35.01.01), catat sebagai partisipasi Kecamatan
            if ($dotsW >= 2) {
                $kecId = $parts[0] . '.' . $parts[1] . '.' . $parts[2];
                $distinctKecamatan[$kecId] = true;
            }
            // Jika punya minimal 3 titik (misal 35.01.01.2001), catat sebagai partisipasi Desa
            if ($dotsW >= 3) {
                $distinctDesa[$idW] = true;
            }
        }

        $submissionByKategori = [
            'POLSEK' => count($distinctPolsek),
            'KAB_KOTA' => count($distinctKabKota),
            'KECAMATAN' => count($distinctKecamatan),
            'DESA' => count($distinctDesa)
        ];
    @endphp

    {{-- Stats Dashboard --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 relative px-2">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-105"></div>
        <div class="absolute inset-0 topo-pattern -z-10 rounded-[3rem]"></div>

        <!-- TOTAL POTENSI LAHAN -->
        <div class="lg:col-span-7 group relative bg-white p-6 md:p-8 rounded-[2rem] border border-emerald-100 shadow-xl shadow-emerald-900/5 transition-all duration-500 overflow-hidden flex flex-col justify-between min-h-[380px]">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            
            <div class="flex justify-between items-start mb-4 relative z-10 w-full">
                <div>
                    <h3 class="text-[12px] font-black text-slate-400 border-b border-emerald-100 pb-1 mb-2 uppercase tracking-[0.2em] inline-block">TOTAL POTENSI LAHAN</h3>
                    <div class="flex items-end gap-2 text-slate-800 tracking-tight leading-none truncate">
                        <h2 class="text-4xl md:text-5xl font-black">
                            {{ number_format($totalLuasLahan, 2) }}
                        </h2>
                        <span class="text-emerald-500 text-lg md:text-xl font-bold mb-0.5 md:mb-1 w-8">Ha</span>
                    </div>
                    <p class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest mt-2 px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-100 inline-block">Terdiri dari <span class="text-slate-800 font-black text-sm">{{ $totalLokasiLahan }}</span> Lokasi Potensi Lahan</p>
                </div>
            </div>

            <!-- List Distribusi -->
            <div class="relative z-10 flex-1 overflow-y-auto custom-scrollbar pr-2 mt-2 space-y-1">
                @foreach($breakdownByJenis as $id => $data)
                    @if($data['luas'] > 0 || count($data['lokasi']) > 0)
                    <div class="flex items-center justify-between text-xs py-2 border-b border-slate-50 last:border-0 hover:bg-slate-50 px-2 rounded-lg transition-colors group/item">
                        <div class="flex items-center gap-2 truncate max-w-[55%]">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-200 group-hover/item:bg-emerald-500 transition-colors flex-shrink-0"></div>
                            <span class="font-black text-slate-600 tabular-nums">{{ $id }}.</span>
                            <span class="font-bold text-slate-600 truncate group-hover/item:text-emerald-600 transition-colors" title="{{ $data['nama'] }}">{{ $data['nama'] }}</span>
                        </div>
                        <span class="font-black text-slate-800 tabular-nums flex-shrink-0 text-right">{{ number_format($data['luas'], 2) }} <span class="text-emerald-600 font-semibold italic">Ha</span> <span class="text-slate-300 mx-1">/</span> <span class="text-slate-500">{{ count($data['lokasi']) }}</span> <span class="text-slate-400 font-medium">lokasi</span></span>
                    </div>
                    @endif
                @endforeach
            </div>
            
            <div class="absolute right-4 bottom-4 w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-600 text-white rounded-[1rem] flex items-center justify-center transform group-hover:-rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-emerald-500/30 opacity-20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <!-- DISTRIBUSI TINGKATAN -->
            <div class="flex-1 group relative bg-white p-5 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-x-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex flex-col justify-center gap-2.5">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
                
                <div class="relative z-10 flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-tight">SUMBER DATA</h3>
                        <p class="text-[11px] font-bold text-slate-800 leading-tight">Berdasarkan Tingkatan</p>
                    </div>
                </div>
                
                <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3 mt-2">
                    <div class="text-center bg-slate-50 p-2.5 rounded-2xl border border-slate-100 shadow-sm relative group/stat hover:border-indigo-200 transition-colors">
                        <div class="absolute inset-0 bg-indigo-500/5 rounded-2xl opacity-0 group-hover/stat:opacity-100 transition-opacity"></div>
                        <p class="text-xl md:text-2xl font-black text-indigo-600 relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $submissionByKategori['POLSEK'] }}; let duration = 1500; window.requestAnimationFrame(function step(t) { let start = this.start || (this.start = t); let progress = Math.min((t - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) requestAnimationFrame(step); else count = end; })" x-text="count">0</p>
                        <p class="text-[8px] sm:text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1 relative z-10">Polsek</p>
                    </div>
                    <div class="text-center bg-slate-50 p-2.5 rounded-2xl border border-slate-100 shadow-sm relative group/stat hover:border-blue-200 transition-colors">
                        <div class="absolute inset-0 bg-blue-500/5 rounded-2xl opacity-0 group-hover/stat:opacity-100 transition-opacity"></div>
                        <p class="text-xl md:text-2xl font-black text-blue-600 relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $submissionByKategori['KAB_KOTA'] }}; let duration = 1500; window.requestAnimationFrame(function step(t) { let start = this.start || (this.start = t); let progress = Math.min((t - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) requestAnimationFrame(step); else count = end; })" x-text="count">0</p>
                        <p class="text-[8px] sm:text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1 relative z-10">Kab/Kota</p>
                    </div>
                    <div class="text-center bg-slate-50 p-2.5 rounded-2xl border border-slate-100 shadow-sm relative group/stat hover:border-teal-200 transition-colors">
                        <div class="absolute inset-0 bg-teal-500/5 rounded-2xl opacity-0 group-hover/stat:opacity-100 transition-opacity"></div>
                        <p class="text-xl md:text-2xl font-black text-teal-600 relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $submissionByKategori['KECAMATAN'] }}; let duration = 1500; window.requestAnimationFrame(function step(t) { let start = this.start || (this.start = t); let progress = Math.min((t - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) requestAnimationFrame(step); else count = end; })" x-text="count">0</p>
                        <p class="text-[8px] sm:text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1 relative z-10">Kecamatan</p>
                    </div>
                    <div class="text-center bg-slate-50 p-2.5 rounded-2xl border border-slate-100 shadow-sm relative group/stat hover:border-emerald-200 transition-colors">
                        <div class="absolute inset-0 bg-emerald-500/5 rounded-2xl opacity-0 group-hover/stat:opacity-100 transition-opacity"></div>
                        <p class="text-xl md:text-2xl font-black text-emerald-600 relative z-10" x-data="{ count: 0 }" x-init="let end = {{ $submissionByKategori['DESA'] }}; let duration = 1500; window.requestAnimationFrame(function step(t) { let start = this.start || (this.start = t); let progress = Math.min((t - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) requestAnimationFrame(step); else count = end; })" x-text="count">0</p>
                        <p class="text-[8px] sm:text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1 relative z-10">Kel/Desa</p>
                    </div>
                </div>
            </div>

            <!-- BELUM DIVALIDASI -->
            <div class="flex-1 group relative bg-white p-6 rounded-[2rem] border border-amber-100 shadow-xl shadow-amber-900/5 hover:-translate-x-2 hover:shadow-2xl hover:shadow-amber-900/10 transition-all duration-500 overflow-hidden flex items-center gap-5">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
                <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-600 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-12 transition-all duration-500 shadow-lg shadow-amber-500/30 flex-shrink-0 relative z-10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="relative z-10 flex flex-col justify-center">
                    <h3 class="text-3xl font-black text-slate-800 leading-none mb-1">
                        Total <span class="text-amber-500 italic"><span x-data="{ count: 0 }" x-init="let end = {{ $persenBelumValidasi }}; let duration = 1500; window.requestAnimationFrame(function step(t) { let start = this.start || (this.start = t); let progress = Math.min((t - start) / duration, 1); count = (progress * end).toFixed(2); if (progress < 1) requestAnimationFrame(step); else count = end; })" x-text="count">0</span>%</span>
                    </h3>
                    <p class="text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">
                        Data Belum Divalidasi,<br>dari <span class="text-slate-800 font-black">{{ number_format($luasBelumValidasi, 2) }} Ha</span> Lahan
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Dataset Card --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-2 mt-12">

        <!-- Header Panel -->
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR PENGAJUAN LAHAN</h3>
                <span class="text-[10px] font-black text-slate-400 bg-white/5 px-2.5 py-1 rounded-lg border border-white/10">
                    {{ $lahanList->total() }} Data
                </span>
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-emerald-400 bg-emerald-400/20 px-3 py-1.5 rounded-lg border border-emerald-400/30 flex-shrink-0">
                SEMUA WILAYAH
            </div>
        </div>

        @php
            $jenisInfo = [
                1 => ['label' => 'POKTAN BINAAN', 'cls' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
                2 => ['label' => 'HUTAN SOSIAL',  'cls' => 'bg-green-100 text-green-700 border-green-200',     'dot' => 'bg-green-500'],
                3 => ['label' => 'LBS (SAWAH)',   'cls' => 'bg-teal-100 text-teal-700 border-teal-200',       'dot' => 'bg-teal-500'],
                4 => ['label' => 'PESANTREN',     'cls' => 'bg-blue-100 text-blue-700 border-blue-200',       'dot' => 'bg-blue-500'],
                5 => ['label' => 'MILIK POLRI',   'cls' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'dot' => 'bg-indigo-500'],
                6 => ['label' => 'MASY. BINAAN',  'cls' => 'bg-cyan-100 text-cyan-700 border-cyan-200',       'dot' => 'bg-cyan-500'],
                7 => ['label' => 'TUMPANG SARI',  'cls' => 'bg-lime-100 text-lime-700 border-lime-200',       'dot' => 'bg-lime-500'],
                8 => ['label' => 'PERHUTANI',     'cls' => 'bg-orange-100 text-orange-700 border-orange-200', 'dot' => 'bg-orange-500'],
                9 => ['label' => 'LAINNYA',       'cls' => 'bg-slate-100 text-slate-600 border-slate-200',    'dot' => 'bg-slate-400'],
            ];
        @endphp

        {{-- DATA TABLE --}}
        <div class="p-4 md:p-6 space-y-4">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-4 py-3 rounded-tl-xl text-center w-12 text-xs font-black text-slate-500 uppercase tracking-widest">No</th>
                            <th class="px-4 py-3 text-xs font-black text-slate-500 uppercase tracking-widest">Lokasi</th>
                            <th class="px-4 py-3 text-xs font-black text-slate-500 uppercase tracking-widest">Penanggung Jawab</th>
                            <th class="px-4 py-3 text-xs font-black text-slate-500 uppercase tracking-widest">Detail Lahan</th>
                            <th class="px-4 py-3 text-xs font-black text-slate-500 uppercase tracking-widest">Validasi</th>
                            <th class="px-4 py-3 rounded-tr-xl text-right text-xs font-black text-slate-500 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lahanList as $idx => $item)
                        @php
                            $jenis   = $jenisInfo[$item['id_jenis_lahan']] ?? $jenisInfo[9];
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-4 py-4 text-center text-xs font-bold text-slate-400">
                                {{ $lahanList->firstItem() + $idx }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-xs font-black text-slate-800 uppercase">{{ $item['kab_nama'] }}</div>
                                <div class="text-[10px] font-bold text-emerald-600 uppercase">{{ $item['kec_nama'] }} &bull; {{ $item['desa_nama'] }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-1">{{ $item['alamat_lahan'] ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[9px] font-black uppercase text-emerald-500 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded w-16 text-center shadow-sm">Penggerak</span>
                                    <span class="text-[11px] font-bold text-slate-700 uppercase line-clamp-1 flex-1">{{ $item['cp_polisi'] ?: '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black uppercase text-blue-500 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded w-16 text-center shadow-sm">T. Jawab</span>
                                    <span class="text-[11px] font-bold text-slate-700 uppercase line-clamp-1 flex-1">{{ $item['cp_lahan'] ?: '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="mb-1.5">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-black border rounded-md uppercase {{ $jenis['cls'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $jenis['dot'] }}"></span>
                                        {{ $jenis['label'] }}
                                    </span>
                                </div>
                                <div class="flex items-end gap-1">
                                    <span class="text-[13px] font-black text-slate-800 italic leading-none">{{ number_format((float)$item['luas_lahan'], 2) }}</span>
                                    <span class="text-[9px] font-black text-slate-400 mb-0.5">HA</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 w-36">
                                @if(!$item['valid_oleh'])
                                <span class="inline-flex items-center gap-1 text-[9px] font-black text-amber-600 bg-amber-50 border border-amber-200 px-2 py-1 rounded-lg shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Belum Divalidasi
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 text-[9px] font-black text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-lg">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg> Tervalidasi
                                </span>
                                <div class="text-[9px] font-bold text-slate-400 mt-1 uppercase truncate max-w-[120px]" title="{{ $item['valid_oleh'] }}">{{ $item['valid_oleh'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 w-72">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button onclick='openViewModal(@json($item))'
                                        class="inline-flex items-center gap-1 text-[10px] font-black text-sky-600 bg-sky-50 border border-sky-100 px-2.5 py-1.5 rounded-lg hover:bg-sky-500 hover:text-white transition-all">
                                        Detail
                                    </button>
                                    @if(!$item['valid_oleh'])
                                    <form action="/admin/kelola-lahan/potensi/validasi/{{ $item['id_lahan'] }}" method="POST" class="inline m-0">
                                        @csrf @method('PUT')
                                        <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-1.5 rounded-lg hover:bg-emerald-500 hover:text-white transition-all">
                                            Validasi
                                        </button>
                                    </form>
                                    @endif
                                    <button onclick='openEditModal(@json($item))'
                                        class="inline-flex items-center gap-1 text-[10px] font-black text-blue-600 bg-blue-50 border border-blue-100 px-2.5 py-1.5 rounded-lg hover:bg-blue-500 hover:text-white transition-all">
                                        Edit
                                    </button>
                                    <form action="/admin/kelola-lahan/potensi/delete/{{ $item['id_lahan'] }}" method="POST" class="inline m-0" onsubmit="return confirm('Yakin hapus data lahan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-black text-rose-600 bg-rose-50 border border-rose-100 px-2.5 py-1.5 rounded-lg hover:bg-rose-500 hover:text-white transition-all">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-50 rounded-[1.5rem] flex items-center justify-center border border-slate-100">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    </div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Belum ada data lahan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($lahanList->hasPages())
            <div class="border-t border-slate-100 pt-5 mt-2">
                {{ $lahanList->links() }}
            </div>
            @endif
        </div>
    </div>


    {{-- VIEW DETAIL MODAL --}}
    <div id="viewModal" class="fixed inset-0 z-[200] hidden items-center justify-center p-4" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeViewModal()"></div>
        <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="bg-white rounded-[2rem] shadow-2xl w-full overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                <div class="px-8 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between flex-shrink-0">
                    <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Detail Potensi Lahan
                    </h3>
                    <button onclick="closeViewModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto custom-scrollbar space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Polisi Penggerak</p>
                            <p class="text-sm font-black text-slate-800" id="vm_cp_polisi">-</p>
                            <p class="text-xs text-slate-500 mt-1" id="vm_no_cp_polisi">-</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Penanggung Jawab</p>
                            <p class="text-sm font-black text-slate-800" id="vm_cp_lahan">-</p>
                            <p class="text-xs text-slate-500 mt-1" id="vm_no_cp_lahan">-</p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Alamat Lahan</p>
                        <p class="text-sm font-bold text-slate-700" id="vm_alamat">-</p>
                        <p class="text-xs text-slate-400 mt-0.5" id="vm_lokasi">-</p>
                        <a id="vm_maps_link" href="#" target="_blank" class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-black text-white bg-gradient-to-r from-emerald-500 to-teal-500 px-3 py-1.5 rounded-lg transition-all shadow hidden">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            Buka di Google Maps
                        </a>
                    </div>
                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                        <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1" id="vm_jenis">-</p>
                        <h4 class="text-3xl font-black text-emerald-700"><span id="vm_luas">0</span> <span class="text-sm text-emerald-600">HA</span></h4>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                            <p class="text-[9px] font-black uppercase tracking-widest text-indigo-400 mb-2">Proses Oleh</p>
                            <p class="text-sm font-bold text-slate-700" id="vm_edit_oleh">-</p>
                            <p class="text-[10px] text-slate-400 mt-1" id="vm_tgl_edit">-</p>
                        </div>
                        <div class="p-4 rounded-xl border" id="vm_validasi_box">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Validasi Oleh</p>
                            <p class="text-sm font-bold" id="vm_valid_oleh">-</p>
                            <p class="text-[10px] text-slate-400 mt-1" id="vm_tgl_valid">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    var jenisLabels = {
        1: 'PRODUKTIF (POKTAN BINAAN POLRI)', 2: 'HUTAN (PERHUTANAN SOSIAL)',
        3: 'LUAS BAKU SAWAH (LBS)', 4: 'PESANTREN', 5: 'MILIK POLRI',
        6: 'PRODUKTIF (MASYARAKAT BINAAN POLRI)', 7: 'PRODUKTIF (TUMPANG SARI)',
        8: 'HUTAN (PERHUTANI/INHUTANI)', 9: 'LAHAN LAINNYA'
    };
    function openViewModal(item) {
        // Polisi Penggerak = cp_polisi, Penanggung Jawab = cp_lahan (sesuai swap)
        document.getElementById('vm_cp_polisi').textContent    = item.cp_polisi || '-';
        document.getElementById('vm_no_cp_polisi').textContent = item.no_cp_polisi || '-';
        document.getElementById('vm_cp_lahan').textContent     = item.cp_lahan || '-';
        document.getElementById('vm_no_cp_lahan').textContent  = item.no_cp_lahan || '-';
        document.getElementById('vm_alamat').textContent       = item.alamat_lahan || '-';
        document.getElementById('vm_lokasi').textContent       = [item.kab_nama, item.kec_nama, item.desa_nama].filter(Boolean).join(' → ');
        document.getElementById('vm_luas').textContent         = parseFloat(item.luas_lahan || 0).toFixed(2);
        document.getElementById('vm_jenis').textContent        = jenisLabels[item.id_jenis_lahan] || 'LAHAN LAINNYA';
        document.getElementById('vm_edit_oleh').textContent    = item.edit_oleh || 'Belum diproses';
        document.getElementById('vm_tgl_edit').textContent     = item.tgl_edit || '-';
        var vBox = document.getElementById('vm_validasi_box');
        if (item.valid_oleh) {
            vBox.className = 'p-4 rounded-xl border bg-emerald-50 border-emerald-100';
            document.getElementById('vm_valid_oleh').className = 'text-sm font-bold text-emerald-700';
            document.getElementById('vm_valid_oleh').textContent = item.valid_oleh;
        } else {
            vBox.className = 'p-4 rounded-xl border bg-amber-50 border-amber-100';
            document.getElementById('vm_valid_oleh').className = 'text-sm font-bold text-amber-600';
            document.getElementById('vm_valid_oleh').textContent = 'Menunggu Validasi';
        }
        document.getElementById('vm_tgl_valid').textContent = item.tgl_valid || '-';
        var mapsLink = document.getElementById('vm_maps_link');
        if (item.latitude && item.longitude) {
            mapsLink.href = 'https://www.google.com/maps?q=' + item.latitude + ',' + item.longitude;
            mapsLink.classList.remove('hidden');
        } else { mapsLink.classList.add('hidden'); }
        var modal = document.getElementById('viewModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeViewModal() {
        var modal = document.getElementById('viewModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
    function openEditModal(item) {
        var event = new CustomEvent('open-edit-modal', { detail: item });
        document.dispatchEvent(event);
    }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeViewModal(); } });
    </script>

            {{-- ----------------------------- --}}
            {{-- MODALS SECTION (ALPINE JS)    --}}
            {{-- ----------------------------- --}}

            <!-- 1. VIEW MODAL -->
            <div x-show="isViewOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div @click.outside="isViewOpen = false" x-show="isViewOpen" x-transition.opacity.duration.300ms class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                    <div class="px-8 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Detail Potensi Lahan
                        </h3>
                        <button @click="isViewOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Penggerak</p>
                                <p class="text-sm font-bold text-slate-800" x-text="activeData?.cp_lahan || '-'"></p>
                                <p class="text-xs text-slate-500" x-text="activeData?.no_cp_lahan || '-'"></p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Polisi T. Jawab</p>
                                <p class="text-sm font-bold text-slate-800" x-text="activeData?.cp_polisi || '-'"></p>
                                <p class="text-xs text-slate-500" x-text="activeData?.no_cp_polisi || '-'"></p>
                            </div>
                        </div>
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1" x-text="activeLabel"></p>
                            <h4 class="text-3xl font-black text-emerald-700"><span x-text="activeData?.luas_lahan || '0'"></span><span class="text-sm text-emerald-600 ml-1">HA</span></h4>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 border-b pb-2">Informasi Validasi</p>
                            <div class="text-xs font-medium text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100" x-html="
                                activeData?.tgl_valid 
                                ? `<span class='text-emerald-500 font-bold'>✓ Tervalidasi</span> oleh ${activeData?.nama_validator || 'Admin'} pada ${activeData?.tgl_valid}` 
                                : `<span class='text-amber-500 font-bold'>⏳ Menunggu Validasi</span>` 
                            "></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. EDIT MODAL -->
            <div x-show="isEditOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div @click.outside="isEditOpen = false" x-show="isEditOpen" x-transition.opacity.duration.300ms class="bg-white rounded-[2rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                    <div class="px-8 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Ubah Data Lahan
                        </h3>
                        <button @click="isEditOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form :action="`/admin/kelola-lahan/potensi/update/${activeData?.id_lahan}`" method="POST" class="flex-1 overflow-y-auto custom-scrollbar">
                        @csrf @method('PUT')
                        <div class="p-8 space-y-6">
                            
                            <!-- Penggerak -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nama Penggerak</label>
                                    <input type="text" name="cp_lahan" :value="activeData?.cp_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kontak Penggerak</label>
                                    <input type="text" name="no_cp_lahan" :value="activeData?.no_cp_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                            </div>
                            <!-- Penanggung Jawab -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nama T. Jawab</label>
                                    <input type="text" name="cp_polisi" :value="activeData?.cp_polisi" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kontak T. Jawab</label>
                                    <input type="text" name="no_cp_polisi" :value="activeData?.no_cp_polisi" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                            </div>
                            <!-- Lahan Detail -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Luas Lahan (HA)</label>
                                    <input type="number" step="0.01" name="luas_lahan" :value="activeData?.luas_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Jenis Lahan</label>
                                    <select name="id_jenis_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none uppercase" x-model="activeData.id_jenis_lahan">
                                        @foreach($kategoriMapping as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="pt-4 flex gap-3">
                                <button type="button" @click="isEditOpen = false" class="flex-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl transition-all">Batal</button>
                                <button type="submit" class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. DELETE MODAL -->
            <div x-show="isDeleteOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div @click.outside="isDeleteOpen = false" x-show="isDeleteOpen" x-transition.opacity.duration.300ms class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden border border-rose-100 flex flex-col items-center text-center p-8">
                    <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-2 uppercase">Hapus Data?</h3>
                    <p class="text-xs text-slate-500 font-medium mb-8">Data lahan seluas <strong class="text-rose-500" x-text="activeData?.luas_lahan + ' HA'"></strong> milik <strong class="text-slate-700 uppercase" x-text="activeData?.cp_lahan"></strong> akan dihapus sementara dari sistem.</p>
                    
                    <form :action="`/admin/kelola-lahan/potensi/destroy/${activeData?.id_lahan}`" method="POST" class="w-full flex gap-3">
                        @csrf @method('DELETE')
                        <button type="button" @click="isDeleteOpen = false" class="flex-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="flex-1 bg-rose-500 text-white hover:bg-rose-600 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">Ya, Hapus</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- [MODAL] - PROGRESSIVE STEP FORM --}}
    <template x-teleport="body">
        <div x-show="isOpen" x-data="{ 
                                currentStep: 1,
                                totalSteps: 4,
                                nextStep() { if(this.currentStep < this.totalSteps) this.currentStep++ },
                                prevStep() { if(this.currentStep > 1) this.currentStep-- },
                                isStep(step) { return this.currentStep === step }
                            }" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>

            <div class="flex items-center justify-center min-h-screen p-4">
                {{-- Overlay --}}
                <div @click="closeModal()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity">
                </div>

                {{-- Modal Content --}}
                <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">

                    {{-- Header & Step Indicator --}}
                    <div class="bg-white border-b border-slate-100 px-8 py-6">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M9 20l-5.447-2.724A2 2 0 013 15.382V6.418a2 2 0 011.106-1.789L9 2m0 18l6-3m-6 3V7.5m6 9.5l5.447 2.724A2 2 0 0021 17.618V8.582a2 2 0 00-1.106-1.789L15 4m0 13V4m0 0L9 7.5"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight"
                                        x-text="isEdit ? 'Sunting Potensi Lahan' : 'Tambah Potensi Lahan'"></h3>
                                    <p class="text-xs text-slate-500">Lengkapi informasi aset ketahanan pangan secara
                                        bertahap.</p>
                                </div>
                            </div>
                            <button @click="closeModal()"
                                class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-50 rounded-full transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" />
                                </svg>
                            </button>
                        </div>

                        {{-- Step Progress Bar --}}
                        <div class="flex items-center justify-between max-w-3xl mx-auto relative">
                            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
                            <div class="absolute top-1/2 left-0 h-0.5 bg-emerald-500 -translate-y-1/2 z-0 transition-all duration-500"
                                :style="`width: ${(currentStep - 1) / (totalSteps - 1) * 100}%` "></div>

                            <template x-for="step in totalSteps">
                                <div class="relative z-10 flex flex-col items-center gap-2">
                                    <div :class="currentStep >= step ? 'bg-emerald-600 text-white scale-110 shadow-lg shadow-emerald-100' : 'bg-white text-slate-400 border-2 border-slate-100'"
                                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300">
                                        <template x-if="currentStep > step">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </template>
                                        <span x-show="currentStep <= step" x-text="step"></span>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider transition-colors duration-300"
                                        :class="currentStep >= step ? 'text-emerald-700' : 'text-slate-400'"
                                        x-text="['Institusi', 'Personel', 'Teknis', 'Lokasi'][step-1]"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Main Form Body --}}
                    <form @submit.prevent="saveData()" class="flex flex-col lg:flex-row flex-1 overflow-hidden">

                        {{-- LEFT: Form Inputs --}}
                        <div class="flex-1 overflow-y-auto p-8 space-y-8 scroll-smooth">

                            {{-- STEP 1: Institusi & Klasifikasi --}}
                            <div x-show="isStep(1)" x-transition.opacity.duration.400ms class="space-y-6">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Institusi &
                                        Klasifikasi</h4>
                                    <p class="text-xs text-slate-500">Tentukan kesatuan kepolisian dan kategori lahan.
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600 ml-1">Kepolisian
                                            Resor</label>
                                        <select x-model="formData.id_resor"
                                            class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-600 outline-none transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%2364748B%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:20px_20px] bg-[right_12px_center] bg-no-repeat">
                                            <option value="">PILIH KEPOLISIAN RESOR</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600 ml-1">Kepolisian
                                            Sektor</label>
                                        <select x-model="formData.id_sektor"
                                            class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-600 outline-none transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%2364748B%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:20px_20px] bg-[right_12px_center] bg-no-repeat">
                                            <option value="">PILIH KEPOLISIAN SEKTOR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-600 ml-1">Jenis Lahan</label>
                                    <select x-model="formData.jenis_lahan"
                                        class="w-full h-11 px-4 bg-white border border-emerald-200 rounded-xl text-sm font-bold text-emerald-800 focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-600 outline-none transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%23059669%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:20px_20px] bg-[right_12px_center] bg-no-repeat shadow-sm">
                                        <option value="">PILIH JENIS LAHAN</option>
                                    </select>
                                    <p class="text-[10px] text-slate-400 italic">* Klasifikasi lahan menentukan proses
                                        validasi lanjutan.</p>
                                </div>
                            </div>

                            {{-- STEP 2: Data Personel --}}
                            <div x-show="isStep(2)" x-transition.opacity.duration.400ms class="space-y-8">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Data Personel</h4>
                                    <p class="text-xs text-slate-500">Informasi polisi penggerak dan penanggung jawab di
                                        lapangan.</p>
                                </div>

                                {{-- Polisi Penggerak --}}
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                                    <h5
                                        class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                stroke-width="2" />
                                        </svg>
                                        Polisi Penggerak
                                    </h5>
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-7 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Nama Lengkap
                                                Personel</label>
                                            <input type="text" x-model="formData.nama_personel"
                                                class="w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all"
                                                placeholder="Contoh: Aiptu John Doe">
                                        </div>
                                        <div class="col-span-12 md:col-span-5 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Nomor
                                                WhatsApp</label>
                                            <div class="flex">
                                                <span
                                                    class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-slate-500 text-xs font-bold">+62</span>
                                                <input type="text" x-model="formData.hp_personel"
                                                    class="w-full h-10 px-4 bg-white border border-slate-200 rounded-r-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Penanggung Jawab --}}
                                <div class="p-6 bg-white rounded-2xl border border-slate-200 space-y-4">
                                    <h5
                                        class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                stroke-width="2" />
                                        </svg>
                                        Penanggung Jawab Lahan
                                    </h5>
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-7 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Nama Penanggung
                                                Jawab</label>
                                            <input type="text" x-model="formData.pj_lahan"
                                                class="w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all"
                                                placeholder="Nama Pemilik / Ketua Poktan">
                                        </div>
                                        <div class="col-span-12 md:col-span-5 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Kontak Person</label>
                                            <div class="flex">
                                                <span
                                                    class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-slate-500 text-xs font-bold">+62</span>
                                                <input type="text" x-model="formData.hp_pj"
                                                    class="w-full h-10 px-4 bg-white border border-slate-200 rounded-r-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all">
                                            </div>
                                        </div>
                                        <div class="col-span-12 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Keterangan
                                                Peran</label>
                                            <input type="text" x-model="formData.ket_pj"
                                                class="w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all"
                                                placeholder="Contoh: Ketua Kelompok Tani Mulyo">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 3: Data Teknis Lahan --}}
                            <div x-show="isStep(3)" x-transition.opacity.duration.400ms class="space-y-6">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Data Teknis Lahan</h4>
                                    <p class="text-xs text-slate-500">Detail produktivitas dan kapasitas lahan yang
                                        didaftarkan.</p>
                                </div>

                                <div class="grid grid-cols-3 gap-6">
                                    <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-3">
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center block">Jml.
                                            Poktan</label>
                                        <input type="number" x-model="formData.jml_poktan"
                                            class="w-full text-center text-xl font-bold bg-slate-50 border-none rounded-lg h-12 focus:ring-0"
                                            placeholder="0">
                                        <p class="text-[9px] text-slate-400 text-center font-medium leading-tight">Total
                                            kelompok tani terdaftar</p>
                                    </div>
                                    <div
                                        class="p-5 bg-emerald-50 border border-emerald-100 rounded-2xl shadow-sm space-y-3">
                                        <label
                                            class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest text-center block">Luas
                                            Lahan (HA)</label>
                                        <input type="number" step="0.01" x-model="formData.luas"
                                            class="w-full text-center text-xl font-bold bg-white border-none rounded-lg h-12 text-emerald-700 focus:ring-2 focus:ring-emerald-200"
                                            placeholder="0.00">
                                        <p
                                            class="text-[9px] text-emerald-500 text-center font-medium leading-tight tracking-tighter">
                                            Luas total area dalam Hektar</p>
                                    </div>
                                    <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-3">
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center block">Jml.
                                            Petani</label>
                                        <input type="number" x-model="formData.jml_petani"
                                            class="w-full text-center text-xl font-bold bg-slate-50 border-none rounded-lg h-12 focus:ring-0"
                                            placeholder="0">
                                        <p class="text-[9px] text-slate-400 text-center font-medium leading-tight">Total
                                            anggota petani aktif</p>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-600 ml-1">Komoditi Utama</label>
                                    <select x-model="formData.komoditi"
                                        class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all">
                                        <option value="AKASIA">AKASIA</option>
                                        <option value="JAGUNG">JAGUNG</option>
                                        <option value="PADI">PADI</option>
                                        <option value="SINGKONG">SINGKONG</option>
                                    </select>
                                </div>
                            </div>

                            {{-- STEP 4: Lokasi & Dokumentasi --}}
                            <div x-show="isStep(4)" x-transition.opacity.duration.400ms class="space-y-6">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Lokasi & Dokumentasi
                                    </h4>
                                    <p class="text-xs text-slate-500">Koordinat geospasial dan bukti foto lokasi lahan.
                                    </p>
                                </div>


                                <div class="space-y-4">
                                    {{-- Alamat --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Alamat Lengkap Lahan</label>
                                        <textarea x-model="formData.alamat" rows="2"
                                            class="w-full p-4 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-600 transition-all placeholder:text-slate-300"
                                            placeholder="Contoh: Jl. Raya Kediri No. 12, RT/RW..."></textarea>
                                    </div>

                                    {{-- Wilayah Selects --}}
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-400 uppercase ml-1">Kabupaten</label>
                                            <select
                                                class="w-full h-10 px-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-emerald-500">
                                                <option>PILIH KABUPATEN</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-400 uppercase ml-1">Kecamatan</label>
                                            <select
                                                class="w-full h-10 px-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-emerald-500">
                                                <option>PILIH KECAMATAN</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-400 uppercase ml-1">Desa</label>
                                            <select
                                                class="w-full h-10 px-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-emerald-500">
                                                <option>PILIH DESA</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Catatan --}}
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-semibold text-slate-700 uppercase tracking-tighter">Catatan
                                            Tambahan</label>
                                        <textarea x-model="formData.keterangan_lain" rows="2"
                                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:bg-white focus:border-emerald-600 transition-all"
                                            placeholder="Catatan mengenai akses jalan atau kondisi tanah..."></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Visuals (Peta & Dokumentasi) --}}
                            {{-- RIGHT COLUMN: Visuals (Peta & Dokumentasi) --}}
                            <div
                                class="hidden lg:flex flex-col w-[420px] bg-slate-50/50 border-l border-slate-100 overflow-y-auto">
                                <div class="p-8 space-y-8">
                                    {{-- Map Section --}}
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Titik
                                                Koordinat</h4>
                                            <button type="button" @click="getCurrentLocation()"
                                                class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                                        stroke-width="2.5" />
                                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2.5" />
                                                </svg>
                                                Gunakan GPS
                                            </button>
                                        </div>

                                        {{-- Map Display --}}
                                        <div
                                            class="relative w-full h-64 bg-slate-200 rounded-2xl overflow-hidden border border-slate-200 shadow-inner group">
                                            <div id="map" class="w-full h-full z-0"></div> {{-- ID PENTING UNTUK LEAFLET
                                                --}}

                                            {{-- Overlay info --}}
                                            <div
                                                class="absolute bottom-2 left-2 right-2 flex justify-between items-center z-[10] pointer-events-none">
                                                <div
                                                    class="bg-white/90 backdrop-blur px-2 py-1 rounded shadow-sm border border-slate-200 text-[9px] font-bold text-slate-500 uppercase tracking-tighter">
                                                    Geser marker ke lokasi lahan
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Lat/Lng Inputs --}}
                                        <div class="grid grid-cols-2 gap-3">
                                            <div
                                                class="bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:border-emerald-500">
                                                <span
                                                    class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Latitude</span>
                                                <input type="text" x-model="formData.lat"
                                                    class="text-sm font-bold text-slate-800 bg-transparent border-none p-0 w-full focus:ring-0 outline-none"
                                                    placeholder="-0.0000">
                                            </div>
                                            <div
                                                class="bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:border-emerald-500">
                                                <span
                                                    class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Longitude</span>
                                                <input type="text" x-model="formData.lng"
                                                    class="text-sm font-bold text-slate-800 bg-transparent border-none p-0 w-full focus:ring-0 outline-none"
                                                    placeholder="000.0000">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Photo Section --}}
                                    <div class="space-y-4">
                                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                            Dokumentasi Foto</h4>

                                        <div class="relative group cursor-pointer">
                                            <input type="file" class="hidden" id="land_photo"
                                                @change="handleFileUpload">
                                            <label for="land_photo"
                                                class="block w-full aspect-video bg-white border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center text-slate-400 group-hover:border-emerald-400 group-hover:bg-emerald-50/30 transition-all cursor-pointer overflow-hidden">
                                                <template x-if="!imagePreview">
                                                    <div class="flex flex-col items-center">
                                                        <svg class="w-8 h-8 mb-2 group-hover:scale-110 group-hover:text-emerald-500 transition-all"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <span class="text-[10px] font-bold uppercase">Upload Foto
                                                            Lahan</span>
                                                    </div>
                                                </template>
                                                <template x-if="imagePreview">
                                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                                </template>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Navigation --}}
                            <div
                                class="px-8 py-4 bg-white border-t border-slate-100 flex items-center justify-between sticky bottom-0 z-10">
                                <div>
                                    <button type="button" @click="closeModal()"
                                        class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                                        Batal
                                    </button>
                                </div>

                                <div class="flex items-center gap-3">
                                    {{-- Back Button --}}
                                    <button type="button" x-show="currentStep > 1" @click="prevStep()"
                                        class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M15 19l-7-7 7-7" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        Kembali
                                    </button>

                                    {{-- Next Button --}}
                                    <button type="button" x-show="currentStep < totalSteps" @click="nextStep()"
                                        class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg shadow-slate-200 active:scale-95">
                                        Lanjut
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>

                                    {{-- Final Submit Button --}}
                                    <button type="submit" x-show="currentStep === totalSteps" :disabled="isLoading"
                                        class="px-10 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-200 active:scale-95 disabled:opacity-50">
                                        <template x-if="isLoading">
                                            <svg class="animate-spin h-3 w-3 text-white" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </template>
                                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Selesaikan Pendaftaran'"></span>
                                        <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
    </template>

    <script>
        function potensiLahanManager() {
            return {
                currentStep: 1,
                isOpen: false,
                isEdit: false,
                isLoading: false,
                is_validated: false,
                formData: {
                    id: null,
                    id_resor: '',
                    id_sektor: '',
                    jenis_lahan: 'Produktif',
                    luas: '',
                    nama_personel: '',
                    pj_lahan: ''
                },

                openModal(item = null) {
                    if (item) {
                        this.isEdit = true;
                        this.formData = {
                            ...item
                        };
                    } else {
                        this.isEdit = false;
                        this.resetForm();
                    }
                    this.isOpen = true;
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                },

                resetForm() {
                    this.formData = {
                        id: null,
                        id_resor: '',
                        id_sektor: '',
                        jenis_lahan: 'Produktif',
                        luas: '',
                        nama_personel: '',
                        pj_lahan: ''
                    };
                },

                async saveData() {
                    this.isLoading = true;

                    // Method & URL Selection
                    const method = this.isEdit ? 'PUT' : 'POST';
                    const url = this.isEdit ? `/api/potensi-lahan/${this.formData.id}` : '/api/potensi-lahan';

                    try {
                        // Logic Fetch API (Aktifkan jika backend sudah siap)
                        /*
                        const response = await fetch(url, {
                            method: method,
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.formData)
                        });
                        */

                        // Simulasi Proses
                        await new Promise(r => setTimeout(r, 1000));

                        console.log('Success:', this.formData);
                        this.closeModal();
                        // window.location.reload(); 
                    } catch (e) {
                        alert('Gagal menyimpan data.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                deleteItem(id) {
                    if (confirm('Apakah Anda yakin ingin menghapus data lahan ini?')) {
                        console.log('Menghapus ID:', id);
                        // Tambahkan logic fetch DELETE di sini
                    }
                }
            }
        }
    </script>

    <style>
        /* Mobile Table Transformation */
        @media (max-width: 768px) {
            #tabel-potensi thead {
                display: none;
            }

            #tabel-potensi tr {
                display: block;
                margin-bottom: 1rem;
                padding: 1rem;
                border-bottom: 1px solid #f1f5f9;
            }

            #tabel-potensi td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0;
                border: none !important;
            }

            #tabel-potensi td:before {
                content: attr(data-label);
                font-size: 10px;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
            }

            #tabel-potensi .text-right,
            #tabel-potensi .text-center {
                text-align: right !important;
            }
        }
    </style>
    @endsection
