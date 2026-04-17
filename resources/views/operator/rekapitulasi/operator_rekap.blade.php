@extends('layouts.app')

@section('header', 'Laporan Rekapitulasi Produksi')

@section('content')
    <div class="space-y-8 pb-20 antialiased text-slate-900"
        style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">

        {{-- [SEC 1] - HEADER SECTION (Refactored: Search Side-by-Side) --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-4 transition-all mb-10 duration-700 animate-in fade-in slide-in-from-top-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                    <span>Rekapitulasi Data</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600 font-semibold">Produksi Lahan</span>
                </nav>
                <h2 class="text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">
                    Laporan <span class="text-slate-400 font-normal">Rekapitulasi</span>
                </h2>
            </div>

            {{-- Right: Actions Container (Search & Buttons Inline) --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <div class="relative group w-full sm:w-72">
                    <div
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari data lokasi..."
                        class="block w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm shadow-sm transition-all outline-none focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500/40">
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button onclick="window.location.reload()" title="Refresh"
                        class="p-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-emerald-600 transition-all active:scale-95 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                    <button
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all active:scale-95 text-sm font-medium shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="whitespace-nowrap">Export Data</span>
                    </button>
                </div>
            </div>
        </div>



        {{-- [SEC 3] - FILTER SECTION (Unified Style) --}}
        <div class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden" x-data="{ open: true }">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    <span class="text-sm font-bold text-slate-700 uppercase tracking-tight">Filter Laporan</span>
                </div>
                <button @click="open = !open" class="text-xs font-bold text-emerald-600 hover:underline transition-all">
                    <span x-text="open ? 'Sembunyikan' : 'Tampilkan Filter'"></span>
                </button>
            </div>

            <div x-show="open" x-collapse class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="space-y-4">
                        <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Kategori Lokasi</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Polres /
                                    Satwil</label>
                                <select
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50">
                                    <option>PILIH KEPOLISIAN RESOR</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Polsek /
                                    Sektor</label>
                                <select
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50">
                                    <option>PILIH KEPOLISIAN SEKTOR</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Spesifikasi Lahan --}}
                    <div class="space-y-4">
                        <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Spesifikasi Lahan</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Jenis Lahan</label>
                                <select
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50">
                                    <option>PILIH JENIS LAHAN</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Komoditi</label>
                                <select
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50">
                                    <option>PILIH KOMODITI LAHAN</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Periode Laporan dengan Logika Switching --}}
                    <div class="space-y-4" x-data="{ filterType: 'tahun' }">
                        <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Periode Laporan</h4>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-4">

                            {{-- Radio Options --}}
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="periode" value="tahun" x-model="filterType"
                                        class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                                    <span
                                        :class="filterType === 'tahun' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'"
                                        class="text-xs group-hover:text-slate-900 transition-colors uppercase">TAHUN</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="periode" value="kwartal" x-model="filterType"
                                        class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                                    <span
                                        :class="filterType === 'kwartal' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'"
                                        class="text-xs group-hover:text-slate-900 transition-colors uppercase">KWARTAL</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="periode" value="tanggal" x-model="filterType"
                                        class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                                    <span
                                        :class="filterType === 'tanggal' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'"
                                        class="text-xs group-hover:text-slate-900 transition-colors uppercase">TANGGAL</span>
                                </label>
                            </div>

                            {{-- Input Area --}}
                            <div class="grid grid-cols-1 gap-3">
                                <div x-show="filterType === 'tahun' || filterType === 'kwartal'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    class="grid grid-cols-2 gap-3">
                                    <input type="number" value="2026" placeholder="Tahun"
                                        class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">

                                    <select x-show="filterType === 'tahun'"
                                        class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                        <option>SEMUA BULAN</option>
                                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                                            <option>{{ $bulan }}</option>
                                        @endforeach
                                    </select>

                                    <select x-show="filterType === 'kwartal'"
                                        class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                        <option>KWARTAL I (Jan-Mar)</option>
                                        <option>KWARTAL II (Apr-Jun)</option>
                                        <option>KWARTAL III (Jul-Sep)</option>
                                        <option>KWARTAL IV (Okt-Des)</option>
                                    </select>
                                </div>

                                {{-- Tampilan untuk Tanggal --}}
                                <div x-show="filterType === 'tanggal'" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0">
                                    <div class="relative group">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <input type="date" value="2026-04-15"
                                            class="w-full h-10 pl-10 pr-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- [SEC 4] - DATA TABLE (Consistent Header Style) --}}
        <div class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Rincian Produksi Wilayah</h3>
                <span
                    class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold rounded uppercase tracking-wider">Auto-Refresh:
                    ON</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr
                            class="bg-slate-50/50 text-[11px] font-semibold text-slate-500 uppercase tracking-widest border-b border-slate-100">
                            <th rowspan="2" class="px-8 py-5 border-b border-slate-200">Wilayah / Satuan Kerja</th>
                            <th rowspan="2" class="px-6 py-5 text-right border-b border-slate-200">Potensi Lahan</th>
                            <th rowspan="2" class="px-6 py-5 text-right border-b border-slate-200">Tanam Lahan</th>
                            <th colspan="2"
                                class="px-6 py-3 text-center border-b border-l border-slate-200 bg-slate-100/30">Hasil
                                Produksi</th>
                        </tr>
                        <tr class="bg-slate-50/50 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            <th class="px-6 py-4 text-right border-b border-l border-slate-100">Panen (Ha/Ton)</th>
                            <th class="px-6 py-4 text-right border-b border-l border-slate-100">Serapan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        {{-- Row: Polres --}}
                        <tr class="bg-slate-50/50 group hover:bg-slate-100/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                                    <span class="font-bold text-slate-900 uppercase italic tracking-tight">POLRES
                                        BANGKALAN</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right font-bold text-slate-900 tracking-tight">8.00 <span
                                    class="text-[10px] text-slate-400">HA</span></td>
                            <td class="px-6 py-5 text-right font-bold text-rose-500 tracking-tight">0.00 <span
                                    class="text-[10px] text-slate-400">HA</span></td>
                            <td
                                class="px-6 py-5 text-right font-bold text-rose-500 border-l border-slate-100/50 tracking-tight italic">
                                0.00 / 0.00 <span class="text-[10px] text-slate-400 font-normal ml-0.5">TON</span></td>
                            <td
                                class="px-6 py-5 text-right font-bold text-rose-500 border-l border-slate-100/50 tracking-tight italic">
                                0.00 <span class="text-[10px] text-slate-400 font-normal ml-0.5">TON</span></td>
                        </tr>

                        {{-- Row: Polsek --}}
                        <tr class="bg-white hover:bg-blue-50/30 transition-colors group">
                            <td class="px-8 py-4 pl-16">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 5l7 7-7 7" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="text-xs font-bold text-slate-600 uppercase">POLSEK AROSBAYA</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-700 tracking-tighter">8.00</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-700 tracking-tighter">0.00</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-600 border-l border-slate-100/50 italic">
                                0.00 / 0.00</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-600 border-l border-slate-100/50 italic">
                                0.00</td>
                        </tr>

                        {{-- Row: Desa --}}
                        <tr class="bg-white hover:bg-slate-50 transition-colors">
                            <td class="px-8 py-3.5 pl-24">
                                <span class="text-[11px] font-medium text-slate-500 uppercase tracking-tight">Desa Arosbaya
                                    Kec. Arosbaya</span>
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 italic tracking-tighter">
                                0.00</td>
                            <td class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 italic tracking-tighter">
                                0.00</td>
                            <td
                                class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 border-l border-slate-100/50 italic">
                                0.00 / 0.00</td>
                            <td
                                class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 border-l border-slate-100/50 italic">
                                0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- [SEC 5] - MOBILE ADAPTATION --}}
        <div class="sm:hidden px-4 space-y-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900 uppercase italic">Polres Bangkalan</h3>
                    <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded">POLRES</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-center">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Potensi</p>
                        <p class="text-sm font-bold text-slate-800 tracking-tight">8.00 HA</p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Tanam</p>
                        <p class="text-sm font-bold text-rose-500 tracking-tight">0.00 HA</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Script Required for Collapse --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection