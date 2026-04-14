@extends('layouts.app')

@section('header', 'Kelola Komoditas Lahan')

@section('content')
<div class="space-y-6 pb-24 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

    {{-- 1. Toolbar Section --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                <span>DATA UTAMA</span>
                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-indigo-600">Komoditas Lahan</span>
            </nav>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                DATA <span class="text-indigo-600">KOMODITAS</span>
            </h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" placeholder="CARI DATA KOMODITAS..."
                    class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none uppercase">
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.location.reload()"
                    class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
                <button class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah
                </button>
            </div>
        </div>
    </div>

    @php
    // Mengambil data dari database dan mengelompokkan berdasarkan jenis_komoditi
    $groupedKomoditi = DB::table('komoditi')
    ->where('delete_status', '1')
    ->get()
    ->groupBy('jenis_komoditi');
    @endphp

    {{-- 2. Data Table Section per Kategori --}}
    @forelse($groupedKomoditi as $jenis => $items)
    <div x-data="{ open: true }" class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden mb-8">
        {{-- Category Header --}}
        <div @click="open = !open" class="bg-slate-900/[0.02] px-10 py-5 border-b border-slate-100 flex justify-between items-center cursor-pointer hover:bg-slate-50 transition-all">
            <div class="flex items-center gap-3">
                <div class="w-2 h-6 bg-indigo-600 rounded-full"></div>
                <h3 class="text-[12px] font-black text-slate-700 uppercase tracking-[0.2em]">
                    KATEGORI: <span class="text-indigo-600">{{ $jenis }}</span>
                    <span class="ml-2 text-[10px] text-slate-400 font-bold">({{ $items->count() }} Item)</span>
                </h3>
            </div>
            <svg class="w-5 h-5 text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <div x-show="open" x-collapse>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="w-12 pl-10 py-6 text-center">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            </th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Komoditas</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">ID Komoditi</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Terakhir Diupdate</th>
                            <th class="px-10 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($items as $item)
                        <tr class="group hover:bg-slate-50 transition-all duration-200">
                            <td class="w-12 pl-10 py-5 text-center">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px] border border-slate-200 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500">
                                        {{ substr($item->nama_komoditi, 0, 2) }}
                                    </div>
                                    <h4 class="text-[13px] font-black text-slate-800 uppercase italic tracking-tight group-hover:text-indigo-600">
                                        {{ $item->nama_komoditi }}
                                    </h4>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[11px] font-black text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">#{{ $item->id_komoditi }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-bold text-slate-400 italic uppercase">
                                    {{ date('d M Y', strtotime($item->date_transaction)) }}
                                </span>
                            </td>
                            <td class="px-10 py-5 text-right">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button class="p-2 bg-white text-indigo-500 rounded-lg border border-slate-200 hover:bg-indigo-600 hover:text-white shadow-sm transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <button class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white shadow-sm transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-[2.5rem] border border-slate-200 p-20 text-center">
        <div class="flex flex-col items-center opacity-30">
            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <p class="uppercase font-black tracking-widest text-[10px]">Data Komoditas Kosong</p>
        </div>
    </div>
    @endforelse
</div>

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection