@extends('layouts.app')

@section('header', 'Kelola Komoditas Lahan')

@section('content')
    {{-- Menggunakan x-data yang sama strukturnya dengan Jabatan untuk konsistensi fungsional --}}
    <div x-data='{ 
        search: "",
        items: [], {{-- Isi dengan data komoditas dari controller --}}
        showModal: false
    }' class="space-y-6 pb-24 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

        {{-- 1. Toolbar Section (Identik dengan Jabatan) --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2">
            <div>
                {{-- Breadcrumb: Ukuran & Tracking disamakan dengan Jabatan --}}
                <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                    <span>DATA UTAMA</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-indigo-600">Komoditas Lahan</span>
                </nav>
                {{-- Heading: text-3xl sesuai patokan Jabatan --}}
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                    DATA <span class="text-indigo-600">KOMODITAS</span>
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Search Bar: Ukuran w-64 & pl-10 disamakan --}}
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="search" placeholder="CARI DATA KOMODITAS..."
                        class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none uppercase">
                </div>

                <div class="flex items-center gap-2">
                    {{-- Refresh Button --}}
                    <button @click="window.location.reload()" title="Refresh"
                        class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                    {{-- Add Button: Indigo theme tapi ukuran py-3 & text-[11px] identik Jabatan --}}
                    <button @click="showModal = true"
                        class="flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. Data Table Section (Identik dengan Jabatan: bg-white solid & rounded-[2.5rem]) --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        {{-- Header Table: bg-slate-50 & py-6 sesuai Jabatan --}}
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="w-12 pl-8 py-6 text-center">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            </th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Komoditas</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">ID</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Satuan Ukur</th>
                            <th class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {{-- Contoh Row Data (Struktur Identik Jabatan) --}}
                        <tr class="group hover:bg-slate-50 transition-all duration-200">
                            <td class="w-12 pl-8 py-5 text-center">
                                <input type="checkbox" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    {{-- Avatar Icon Box --}}
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px] border border-slate-200 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500">
                                        KM
                                    </div>
                                    <h4 class="text-[13px] font-black text-slate-800 uppercase italic tracking-tight group-hover:text-indigo-600">Padi Gogo</h4>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[11px] font-black text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">#1</span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-[10px] font-bold text-slate-400 italic uppercase">Hektar (HA)</span>
                            </td>
                            <td class="px-8 py-5">
                                {{-- Action Buttons --}}
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                    <button class="p-2 bg-white text-indigo-500 rounded-lg border border-slate-200 hover:bg-indigo-600 hover:text-white shadow-sm transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </button>
                                    <button class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white shadow-sm transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Empty State (Jika Data Kosong) --}}
                        {{-- 
                        <tr class="group">
                            <td class="px-8 py-20 text-center" colspan="5">
                                <div class="flex flex-col items-center opacity-30">
                                    <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <p class="uppercase font-black tracking-widest text-[10px]">Data Komoditas Belum Ada</p>
                                </div>
                            </td>
                        </tr>
                        --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection