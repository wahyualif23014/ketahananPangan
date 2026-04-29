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

<div class="space-y-8 pb-24 potensi-container max-w-7xl mx-auto" x-data="potensiLahanManager()"
    @set-penggerak.window="formData.nama_personel = $event.detail.nama; formData.hp_personel = $event.detail.hp"
    @set-pj.window="formData.pj_lahan = $event.detail.nama; formData.hp_pj = $event.detail.hp; formData.id_pj_anggota = $event.detail.id"
    @open-edit-modal.window="openModal($event.detail)">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex flex-wrap items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <a href="{{ route('view.kelola-lahan.index') }}" class="group flex items-center gap-1.5 text-[10px] bg-slate-100 hover:bg-emerald-50 text-slate-500 hover:text-emerald-600 px-2.5 py-1 rounded-md border border-slate-200 hover:border-emerald-200 transition-all">
                    <svg class="w-3 h-3 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    KEMBALI
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Potensi Lahan</span>
            </nav>
            <div class="flex items-center gap-3">
                <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                    POTENSI <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-teal-500">LAHAN</span>
                </h2>
                @if(request('search'))
                <div class="flex items-center gap-2 mt-1 sm:mt-0">
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-100 flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        "{{ request('search') }}"
                    </span>
                    <a href="{{ route('view.kelola-lahan.potensi.index') }}" class="text-[10px] font-black text-rose-500 hover:text-rose-700 bg-white border border-slate-200 px-2.5 py-1.5 rounded-xl transition-all shadow-sm">
                        BATAL
                    </a>
                </div>
                @endif
            </div>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Pendataan lokasi dan statistik pemanfaatan lahan untuk ketahanan pangan operasional.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group hidden sm:block">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                    id="search-lahan"
                    placeholder="CARI LAHAN..."
                    value="{{ request('search') }}"
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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                </svg>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <!-- DISTRIBUSI TINGKATAN -->
            <div class="flex-1 group relative bg-white p-5 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-x-2 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex flex-col justify-center gap-2.5">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>

                <div class="relative z-10 flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
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
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
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
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path>
            </svg>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR PENGAJUAN LAHAN</h3>
                <span class="text-[10px] font-black text-slate-400 bg-white/5 px-2.5 py-1 rounded-lg border border-white/10">
                    {{ $lahanList->total() }} Data
                </span>
                @if(request('search'))
                <span class="text-[10px] font-black text-emerald-300 bg-emerald-500/20 px-2.5 py-1 rounded-lg border border-emerald-400/30 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    "{{ request('search') }}"
                </span>
                <a href="{{ route('view.kelola-lahan.potensi.index') }}" class="text-[10px] font-black text-rose-300 bg-rose-500/20 px-2 py-1 rounded-lg border border-rose-400/30 hover:bg-rose-500/40 transition-colors">
                    ✕ Hapus Filter
                </a>
                @endif
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-emerald-400 bg-emerald-400/20 px-3 py-1.5 rounded-lg border border-emerald-400/30 flex-shrink-0">
                {{ request('search') ? 'HASIL PENCARIAN' : 'SEMUA WILAYAH' }}
            </div>
        </div>

        @php
        $jenisInfo = [
        1 => ['label' => 'POKTAN BINAAN', 'cls' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
        2 => ['label' => 'HUTAN SOSIAL', 'cls' => 'bg-green-100 text-green-700 border-green-200', 'dot' => 'bg-green-500'],
        3 => ['label' => 'LBS (SAWAH)', 'cls' => 'bg-teal-100 text-teal-700 border-teal-200', 'dot' => 'bg-teal-500'],
        4 => ['label' => 'PESANTREN', 'cls' => 'bg-blue-100 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500'],
        5 => ['label' => 'MILIK POLRI', 'cls' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'dot' => 'bg-indigo-500'],
        6 => ['label' => 'MASY. BINAAN', 'cls' => 'bg-cyan-100 text-cyan-700 border-cyan-200', 'dot' => 'bg-cyan-500'],
        7 => ['label' => 'TUMPANG SARI', 'cls' => 'bg-lime-100 text-lime-700 border-lime-200', 'dot' => 'bg-lime-500'],
        8 => ['label' => 'PERHUTANI', 'cls' => 'bg-orange-100 text-orange-700 border-orange-200', 'dot' => 'bg-orange-500'],
        9 => ['label' => 'LAINNYA', 'cls' => 'bg-slate-100 text-slate-600 border-slate-200', 'dot' => 'bg-slate-400'],
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
                        $jenis = $jenisInfo[$item['id_jenis_lahan']] ?? $jenisInfo[9];
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
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg> Tervalidasi
                                </span>
                                <div class="text-[9px] font-bold text-slate-400 mt-1 uppercase truncate max-w-[120px]" title="{{ $item['valid_oleh'] }}">{{ $item['valid_oleh'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 w-72">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button data-item="{{ json_encode($item) }}" onclick='openViewModal(JSON.parse(this.dataset.item))'
                                        class="inline-flex items-center gap-1 text-[10px] font-black text-sky-600 bg-sky-50 border border-sky-100 px-2.5 py-1.5 rounded-lg hover:bg-sky-500 hover:text-white transition-all">
                                        Detail
                                    </button>



                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-50 rounded-[1.5rem] flex items-center justify-center border border-slate-100">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                        </svg>
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
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Detail Potensi Lahan
                    </h3>
                    <button onclick="closeViewModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
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
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
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
            1: 'PRODUKTIF (POKTAN BINAAN POLRI)',
            2: 'HUTAN (PERHUTANAN SOSIAL)',
            3: 'LUAS BAKU SAWAH (LBS)',
            4: 'PESANTREN',
            5: 'MILIK POLRI',
            6: 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7: 'PRODUKTIF (TUMPANG SARI)',
            8: 'HUTAN (PERHUTANI/INHUTANI)',
            9: 'LAHAN LAINNYA'
        };

        function openViewModal(item) {
            var labels = {
                1: 'PRODUKTIF (POKTAN BINAAN POLRI)',
                2: 'HUTAN (PERHUTANAN SOSIAL)',
                3: 'LUAS BAKU SAWAH (LBS)',
                4: 'PESANTREN',
                5: 'MILIK POLRI',
                6: 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
                7: 'PRODUKTIF (TUMPANG SARI)',
                8: 'HUTAN (PERHUTANI/INHUTANI)',
                9: 'LAHAN LAINNYA'
            };

            function set(id, val) {
                var el = document.getElementById(id);
                if (el) el.textContent = val || '-';
            }
            // Institusi
            set('vm_id_tingkat', item.id_tingkat);
            set('vm_id_lahan', item.id_lahan);
            // Personel
            set('vm_cp_polisi', item.cp_polisi);
            set('vm_no_cp_polisi', item.no_cp_polisi);
            set('vm_cp_lahan', item.cp_lahan);
            set('vm_no_cp_lahan', item.no_cp_lahan);
            set('vm_ket_polisi', item.ket_polisi);
            // Teknis
            set('vm_jenis', labels[item.id_jenis_lahan] || 'LAHAN LAINNYA');
            set('vm_luas', parseFloat(item.luas_lahan || 0).toFixed(2));
            set('vm_poktan', item.poktan);
            set('vm_jml_petani', item.jml_petani);
            // Lokasi
            set('vm_lokasi', [item.kab_nama, item.kec_nama, item.desa_nama].filter(Boolean).join(' → '));
            set('vm_alamat', item.alamat_lahan);
            set('vm_keterangan_lahan', item.keterangan_lahan);
            // Koordinat
            set('vm_lat', item.latitude);
            set('vm_lng', item.longitude);
            var mapsLink = document.getElementById('vm_maps_link');
            if (item.latitude && item.longitude) {
                mapsLink.href = 'https://www.google.com/maps?q=' + item.latitude + ',' + item.longitude;
                mapsLink.classList.remove('hidden');
            } else {
                mapsLink.classList.add('hidden');
            }
            // Dokumentasi
            var fotoEl = document.getElementById('vm_foto');
            var fotoWrap = document.getElementById('vm_foto_wrap');
            if (item.dokumentasi_lahan) {
                fotoEl.src = '/' + item.dokumentasi_lahan;
                fotoWrap.classList.remove('hidden');
            } else {
                fotoWrap.classList.add('hidden');
            }
            // Validasi & Edit
            set('vm_edit_oleh', item.edit_oleh);
            set('vm_tgl_edit', item.tgl_edit);
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
            set('vm_tgl_valid', item.tgl_valid);
            // Tab reset
            switchTab('tab-personel');
            var modal = document.getElementById('viewModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function switchTab(tabId) {
            ['tab-personel', 'tab-teknis', 'tab-lokasi', 'tab-validasi'].forEach(function(t) {
                document.getElementById(t).classList.add('hidden');
                var btn = document.getElementById('btn-' + t);
                btn.classList.remove('bg-indigo-600', 'text-white');
                btn.classList.add('text-slate-500', 'hover:text-slate-800');
            });
            document.getElementById(tabId).classList.remove('hidden');
            var activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.add('bg-indigo-600', 'text-white');
            activeBtn.classList.remove('text-slate-500', 'hover:text-slate-800');
        }

        function closeViewModal() {
            var modal = document.getElementById('viewModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function openEditModal(item) {
            // Dispatch ke window agar Alpine bisa menangkap dengan @open-edit-modal.window
            window.dispatchEvent(new CustomEvent('open-edit-modal', {
                detail: item
            }));
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewModal();
            }
        });
    </script>

    {{-- ----------------------------- --}}
    {{-- MODALS SECTION (ALPINE JS)    --}}
    {{-- ----------------------------- --}}

    <!-- VIEW MODAL (Plain JS) -->
    <div id="viewModal" class="hidden fixed inset-0 z-[200] items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[92vh]">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-widest">Detail Potensi Lahan</h3>
                        <p class="text-[10px] text-blue-200 font-medium">ID: <span id="vm_id_lahan" class="font-black"></span> &bull; Tingkat: <span id="vm_id_tingkat"></span></p>
                    </div>
                </div>
                <button onclick="closeViewModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <!-- Tab Nav -->
            <div class="flex gap-1 px-6 pt-4 pb-0 bg-slate-50 border-b border-slate-100 flex-shrink-0">
                <button id="btn-tab-personel" onclick="switchTab('tab-personel')" class="px-4 py-2 text-[10px] font-black uppercase rounded-t-xl transition-all bg-indigo-600 text-white">👤 Personel</button>
                <button id="btn-tab-teknis" onclick="switchTab('tab-teknis')" class="px-4 py-2 text-[10px] font-black uppercase rounded-t-xl transition-all text-slate-500 hover:text-slate-800">📊 Teknis</button>
                <button id="btn-tab-lokasi" onclick="switchTab('tab-lokasi')" class="px-4 py-2 text-[10px] font-black uppercase rounded-t-xl transition-all text-slate-500 hover:text-slate-800">📍 Lokasi</button>
                <button id="btn-tab-validasi" onclick="switchTab('tab-validasi')" class="px-4 py-2 text-[10px] font-black uppercase rounded-t-xl transition-all text-slate-500 hover:text-slate-800">✅ Validasi</button>
            </div>
            <!-- Tab Content -->
            <div class="flex-1 overflow-y-auto custom-scrollbar">

                {{-- TAB: PERSONEL --}}
                <div id="tab-personel" class="p-6 space-y-4">
                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl space-y-2">
                        <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 flex items-center gap-1">🚔 Polisi Penggerak</p>
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-slate-800" id="vm_cp_polisi"></p>
                            <p class="text-xs text-emerald-700 font-bold bg-emerald-100 px-2 py-0.5 rounded-lg" id="vm_no_cp_polisi"></p>
                        </div>
                    </div>
                    <div class="p-4 bg-blue-50 border border-blue-100 rounded-2xl space-y-2">
                        <p class="text-[9px] font-black uppercase tracking-widest text-blue-600 flex items-center gap-1">👥 Penanggung Jawab Lahan</p>
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-slate-800" id="vm_cp_lahan"></p>
                            <p class="text-xs text-blue-700 font-bold bg-blue-100 px-2 py-0.5 rounded-lg" id="vm_no_cp_lahan"></p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Keterangan Peran / Catatan</p>
                        <p class="text-sm text-slate-700 font-medium" id="vm_ket_polisi"></p>
                    </div>
                </div>

                {{-- TAB: TEKNIS --}}
                <div id="tab-teknis" class="hidden p-6 space-y-4">
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Jenis Lahan</p>
                        <p class="text-sm font-black text-slate-800" id="vm_jenis"></p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1">Luas Lahan</p>
                            <p class="text-2xl font-black text-emerald-700" id="vm_luas"></p>
                            <p class="text-[10px] text-emerald-500 font-bold">HA</p>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Jml. Poktan</p>
                            <p class="text-2xl font-black text-slate-700" id="vm_poktan"></p>
                            <p class="text-[10px] text-slate-400 font-bold">Poktan</p>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Jml. Petani</p>
                            <p class="text-2xl font-black text-slate-700" id="vm_jml_petani"></p>
                            <p class="text-[10px] text-slate-400 font-bold">Orang</p>
                        </div>
                    </div>
                </div>

                {{-- TAB: LOKASI --}}
                <div id="tab-lokasi" class="hidden p-6 space-y-4">
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Wilayah</p>
                        <p class="text-sm font-bold text-slate-800" id="vm_lokasi"></p>
                    </div>
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Alamat Lengkap</p>
                        <p class="text-sm text-slate-700 font-medium" id="vm_alamat"></p>
                    </div>
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Keterangan Tambahan</p>
                        <p class="text-sm text-slate-700 font-medium" id="vm_keterangan_lahan"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Latitude</p>
                            <p class="text-sm font-mono font-bold text-slate-700" id="vm_lat"></p>
                        </div>
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Longitude</p>
                            <p class="text-sm font-mono font-bold text-slate-700" id="vm_lng"></p>
                        </div>
                    </div>
                    <a id="vm_maps_link" href="#" target="_blank" class="hidden flex items-center justify-center gap-2 p-3 bg-blue-600 text-white rounded-xl text-xs font-black hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        Buka di Google Maps
                    </a>
                    <div id="vm_foto_wrap" class="hidden">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Dokumentasi Foto Lahan</p>
                        <img id="vm_foto" src="" alt="Dokumentasi Lahan" class="w-full rounded-2xl object-cover max-h-48 border border-slate-200">
                    </div>
                </div>

                {{-- TAB: VALIDASI --}}
                <div id="tab-validasi" class="hidden p-6 space-y-4">
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Diinput Oleh</p>
                        <p class="text-sm font-bold text-slate-800" id="vm_edit_oleh"></p>
                        <p class="text-xs text-slate-400 mt-1" id="vm_tgl_edit"></p>
                    </div>
                    <div id="vm_validasi_box" class="p-4 rounded-xl border">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Status Validasi</p>
                        <p class="text-sm font-bold" id="vm_valid_oleh"></p>
                        <p class="text-xs text-slate-400 mt-1" id="vm_tgl_valid"></p>
                    </div>
                </div>

            </div>
            <!-- Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end flex-shrink-0">
                <button onclick="closeViewModal()" class="px-6 py-2.5 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition-all uppercase tracking-widest">Tutup</button>
            </div>
        </div>
    </div>

</div> 
@endsection