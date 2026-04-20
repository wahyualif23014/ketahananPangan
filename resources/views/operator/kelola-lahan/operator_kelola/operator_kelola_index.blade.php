@extends('layouts.app')

@section('header', 'Data Kelola Lahan')

@section('content')
<div class="space-y-8 pb-20 antialiased text-slate-900"
    style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">

    <div
        class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-4 transition-all mb-10 duration-700 animate-in fade-in slide-in-from-top-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <span>Data Utama</span>
                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-emerald-600">Data Produksi Lahan</span>
            </nav>
            <h2 class="text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">
                Data <span class="text-slate-400 font-normal">Kelola Lahan</span>
            </h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" placeholder="Search coordinates or resor..."
                    class="block w-full md:w-72 pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm shadow-sm transition-all outline-none focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500/40">
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.location.reload()" title="Refresh"
                    class="p-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-emerald-600 transition-all active:scale-95 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- [SEC 1.5] - Dashboard Filter Bar (Alpine.js + Tailwind) --}}
    <div x-data="{ periodMode: 'semua' }" class="mx-4 mb-6 bg-white border border-slate-200 rounded-xl shadow-sm px-5 py-4">
        <div class="flex flex-col gap-4">
            {{-- Row 1: Institutional Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Resor</label>
                    <select class="w-full h-8 text-xs font-medium px-2.5 bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-slate-700">
                        <option value="">Semua Resor</option>
                        <option value="1">Resor A</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sektor</label>
                    <select class="w-full h-8 text-xs font-medium px-2.5 bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-slate-700">
                        <option value="">Semua Sektor</option>
                        <option value="1">Sektor 1</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Jenis Lahan</label>
                    <select class="w-full h-8 text-xs font-medium px-2.5 bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-slate-700">
                        <option value="">Semua Jenis</option>
                        <option value="sawah">Sawah</option>
                        <option value="ladang">Ladang</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Komoditi</label>
                    <select class="w-full h-8 text-xs font-medium px-2.5 bg-slate-50 border border-slate-200 rounded-md focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all text-slate-700">
                        <option value="">Semua Komoditi</option>
                        <option value="padi">Padi</option>
                        <option value="jagung">Jagung</option>
                    </select>
                </div>
            </div>

            {{-- Row 2: Period & Actions --}}
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 pt-4 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Periode Waktu</label>
                        <div class="flex items-center h-8 bg-slate-100/80 p-0.5 rounded-md border border-slate-200/60 w-full sm:w-auto">
                            <button type="button" @click="periodMode = 'semua'"
                                :class="periodMode === 'semua' ? 'bg-white shadow-[0_1px_2px_rgba(0,0,0,0.04)] text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                                class="flex-1 sm:px-3 h-full text-[11px] font-semibold rounded-[4px] transition-all">
                                Semua
                            </button>
                            <button type="button" @click="periodMode = 'tanggal'"
                                :class="periodMode === 'tanggal' ? 'bg-white shadow-[0_1px_2px_rgba(0,0,0,0.04)] text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                                class="flex-1 sm:px-3 h-full text-[11px] font-semibold rounded-[4px] transition-all">
                                Tanggal
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="space-y-1.5 flex-1 sm:flex-none">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest transition-opacity" :class="periodMode === 'semua' ? 'opacity-40' : ''">Mulai</label>
                            <div class="relative">
                                <input type="date" :disabled="periodMode === 'semua'"
                                    :class="periodMode === 'semua' ? 'bg-slate-50/50 text-slate-400 border-slate-200/50 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-text'"
                                    class="w-full sm:w-32 h-8 text-[11px] font-medium px-2.5 border rounded-md outline-none transition-all">
                            </div>
                        </div>
                        <span class="text-slate-300 pb-1 mt-5">-</span>
                        <div class="space-y-1.5 flex-1 sm:flex-none">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest transition-opacity" :class="periodMode === 'semua' ? 'opacity-40' : ''">Selesai</label>
                            <div class="relative">
                                <input type="date" :disabled="periodMode === 'semua'"
                                    :class="periodMode === 'semua' ? 'bg-slate-50/50 text-slate-400 border-slate-200/50 cursor-not-allowed' : 'bg-white text-slate-700 border-slate-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 cursor-text'"
                                    class="w-full sm:w-32 h-8 text-[11px] font-medium px-2.5 border rounded-md outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-end gap-3 shrink-0 mt-2 lg:mt-0">
                    <div class="w-full sm:w-auto space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kategori Lahan</label>
                        <select class="w-full sm:w-40 h-8 text-xs font-bold px-2.5 bg-orange-50 text-orange-700 border border-orange-200 rounded-md focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all uppercase tracking-wider cursor-pointer">
                            <option value="">Tanam Lahan</option>
                            <option value="panen">Panen Lahan</option>
                            <option value="potensi">Potensi Lahan</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="px-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        @php
        $cards = [
        ['title' => 'Total Potensi Lahan', 'val' => '171,094.31', 'color' => 'emerald'],
        ['title' => 'Total Tanam Lahan', 'val' => '43,054.18', 'color' => 'blue'],
        ['title' => 'Total Panen Lahan', 'val' => '51.45', 'color' => 'amber'],
        ['title' => 'Total Serapan', 'val' => '26.45', 'color' => 'indigo'],
        ];
        @endphp

        @foreach($cards as $card)
        <div
            class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden group hover:border-slate-300 transition-all">
            <div class="p-6">
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ $card['title'] }}
                </p>
                <div class="flex items-baseline gap-1">
                    <h3 class="text-3xl font-bold tracking-tight text-slate-900">{{ $card['val'] }}</h3>
                    <span class="text-sm font-medium text-slate-400">Ha</span>
                </div>
            </div>

            {{-- Breakdown Section: 2-column Grid --}}
            <div class="bg-slate-50 border-t border-slate-100 p-6 grid grid-cols-2 gap-y-4 gap-x-6">
                <div class="space-y-0.5">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">Milik Polri</p>
                    <p class="text-xs font-semibold text-slate-700">9.63 <span class="text-slate-300">Ha</span></p>
                </div>
                <div class="space-y-0.5">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">Poktan Binaan</p>
                    <p class="text-xs font-semibold text-slate-700">35,031 <span class="text-slate-300">Ha</span></p>
                </div>
                <div class="space-y-0.5 border-t border-slate-200/60 pt-3">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">Masyarakat</p>
                    <p class="text-xs font-semibold text-slate-700">27,320 <span class="text-slate-300">Ha</span></p>
                </div>
                <div class="space-y-0.5 border-t border-slate-200/60 pt-3">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tight">LBS (Sawah)</p>
                    <p class="text-xs font-semibold text-slate-700">65,013 <span class="text-slate-300">Ha</span></p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- [SEC 3] - Clean Data Table --}}
    <div class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Rincian Produksi Wilayah</h3>
            <span class="px-2 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold rounded uppercase">Auto-Refresh:
                ON</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50/50 text-[11px] font-semibold text-slate-500 uppercase tracking-widest border-b border-slate-100">
                        <th class="px-8 py-4">Wilayah & Lokasi</th>
                        <th class="px-8 py-4 text-center">Status</th>
                        <th class="px-8 py-4 text-center">Luas</th>
                        <th class="px-8 py-4">Polisi Penggerak</th>
                        <th class="px-8 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @for ($i = 0; $i < 3; $i++)
                        <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <p class="font-semibold text-slate-900 uppercase tracking-tight">Kec. Arosbaya</p>
                            <p class="text-xs text-slate-400 tracking-tight">Resor Bangkalan › Desa Dlemer</p>
                        </td>
                        <td class="px-8 py-5 text-center">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-wider">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Proses Tanam
                            </span>
                        </td>
                        <td class="px-8 py-5 text-center font-medium text-slate-700">12.50 <span
                                class="text-slate-300">Ha</span></td>
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-slate-700">Brigpol Achmad Furkon</span>
                                <span class="text-[10px] text-slate-400 uppercase font-medium">21 Jan 2026</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="p-2 text-slate-400 hover:text-emerald-600 transition-colors"><svg
                                        class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg></button>
                                <button class="p-2 text-slate-400 hover:text-blue-600 transition-colors"><svg
                                        class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg></button>
                            </div>
                        </td>
                        </tr>
                        @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection