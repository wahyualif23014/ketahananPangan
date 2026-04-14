@extends('layouts.app')

@section('header', 'Kelola Data Wilayah')

@section('content')
<div class="space-y-6 pb-20 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

    {{-- 1. Toolbar Section --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2 mb-8 transition-all duration-500 animate-in fade-in slide-in-from-top-4">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                <span>DATA UTAMA</span>
                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-blue-600">Wilayah Satwil</span>
            </nav>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                DATA <span class="text-blue-600">WILAYAH</span>
            </h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" placeholder="CARI DATA WILAYAH..."
                    class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none uppercase">
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.location.reload()" title="Refresh"
                    class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @php
    // Ambil semua data wilayah
    $allWilayah = DB::table('wilayah')->get();

    // Filter data Kabupaten (Format: 35.XX.XX)
    $kabupatenList = $allWilayah->filter(function($item) {
    $parts = explode('.', $item->kode_wilayah);
    return count($parts) == 3;
    });
    @endphp

    {{-- 2. Hierarchical Data Section --}}
    <div class="space-y-4">
        @forelse($kabupatenList as $kab)
        @php
        // Cari Kecamatan untuk Kabupaten ini (Format: 35.XX) - Berdasarkan logika filtermu sebelumnya
        // Namun jika mengikuti hirarki Jombang, kita cari yang berawalan kode kabupaten tersebut
        $kecamatanList = $allWilayah->filter(function($item) use ($kab) {
        $parts = explode('.', $item->kode_wilayah);
        return count($parts) == 2 && str_starts_with($kab->kode_wilayah, $item->kode_wilayah);
        });

        // Catatan: Jika database kodenya murni hirarki (Parent.Child),
        // pastikan logic str_starts_with sesuai dengan urutan kode wilayahmu.
        @endphp

        <div x-data="{ open: false }" class="bg-white/80 backdrop-blur-xl rounded-[2.2rem] border border-white shadow-lg overflow-hidden">
            {{-- Header Kabupaten --}}
            <div @click="open = !open" class="px-8 py-5 flex justify-between items-center cursor-pointer hover:bg-slate-50 transition-all">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic tracking-tight leading-none">{{ $kab->nama_wilayah }}</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">KODE: {{ $kab->kode_wilayah }}</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            {{-- Content Kecamatan --}}
            <div x-show="open" x-collapse class="bg-slate-50/50 border-t border-slate-100">
                <div class="p-6 space-y-4">
                    @php
                    // Ambil semua kecamatan yang kodenya merupakan prefix dari desa-desa di bawah kabupaten ini
                    // Atau cari kecamatan yang kodenya bagian dari hirarki kabupaten ini
                    $kecamatanInKab = $allWilayah->filter(function($item) use ($kab) {
                    $parts = explode('.', $item->kode_wilayah);
                    return count($parts) == 2 && explode('.', $kab->kode_wilayah)[1] == $parts[1];
                    });
                    @endphp

                    @foreach($kecamatanInKab as $kec)
                    <div x-data="{ subOpen: false }" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div @click="subOpen = !subOpen" class="px-6 py-4 flex justify-between items-center cursor-pointer hover:bg-blue-50/30 transition-all">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <h4 class="text-[12px] font-black text-slate-700 uppercase tracking-wide">{{ $kec->nama_wilayah }}</h4>
                            </div>
                            <span class="text-[9px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded-lg uppercase tracking-widest">Kecamatan</span>
                        </div>

                        {{-- Content Desa --}}
                        <div x-show="subOpen" x-collapse class="px-6 pb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 pt-2 border-t border-slate-100">
                                @php
                                $desaList = $allWilayah->filter(function($item) use ($kec) {
                                $parts = explode('.', $item->kode_wilayah);
                                return count($parts) == 4 && str_starts_with($item->kode_wilayah, $kec->kode_wilayah);
                                });
                                @endphp

                                @forelse($desaList as $desa)
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 group hover:border-blue-200 transition-all">
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-[11px] font-bold text-slate-600 uppercase group-hover:text-slate-900">{{ $desa->nama_wilayah }}</span>
                                </div>
                                @empty
                                <p class="col-span-full text-[10px] text-slate-400 italic py-2">Tidak ada data desa di kecamatan ini.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-[2.5rem] p-20 text-center border border-slate-200">
            <p class="text-slate-400 uppercase font-black tracking-widest text-xs">Belum ada data wilayah yang tersedia.</p>
        </div>
        @endforelse
    </div>
</div>

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection