@extends('layouts.app')

@section('header', 'Kelola Data Wilayah')

@section('content')
    <div class="space-y-6 pb-12 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

        {{-- ==============================================================================
             1. MODULE HEADER (Identik dengan Patokan Jabatan)
             ============================================================================== --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2 mb-8 transition-all duration-500 animate-in fade-in slide-in-from-top-4">
            <div>
                {{-- Breadcrumb Tactical: tracking-[0.2em] & font-black --}}
                <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                    <span>DATA UTAMA</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-blue-600">Wilayah Satwil</span>
                </nav>
                {{-- Main Title: text-3xl sesuai patokan Jabatan --}}
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                    DATA <span class="text-blue-600">WILAYAH</span>
                </h2>
            </div>

            {{-- Action Bar: Search, Refresh, & Add (Sejajar dengan Judul) --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Search Input Tactical --}}
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
                    {{-- Refresh Button --}}
                    <button onclick="window.location.reload()" title="Refresh"
                        class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                    {{-- Add Button (Primary Action) --}}
                    <button class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>
        </div>

        {{-- ==============================================================================
             2. SUMMARY STATS (Sesuai UI asli Anda - Tidak dirubah)
             ============================================================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6">
            <div class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5 group-hover:text-emerald-600 transition-colors">Wilayah Utama</p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">Terdapat <span class="text-emerald-600 text-2xl tracking-tighter">38</span> Kota</h3>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500/20 group-hover:bg-emerald-500 transition-all"></div>
            </div>

            <div class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5 group-hover:text-blue-600 transition-colors">Area Satwil</p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">Terdapat <span class="text-blue-600 text-2xl tracking-tighter">666</span> Kec.</h3>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-600/20 group-hover:bg-blue-600 transition-all"></div>
            </div>

            <div class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center border border-amber-100 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5 group-hover:text-amber-600 transition-colors">Unit Terkecil</p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">Terdapat <span class="text-amber-600 text-2xl tracking-tighter">8494</span> Desa</h3>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-600/20 group-hover:bg-amber-600 transition-all"></div>
            </div>
        </div>

        {{-- ==============================================================================
             3. DATA TABLE SECTION (Sesuai UI asli Anda - Tidak dirubah)
             ============================================================================== --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-xl overflow-hidden">
            <div class="p-8 border-b border-slate-200/50 flex justify-between items-center bg-white/40">
                <div>
                    <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg leading-none italic">Daftar Wilayah / Satwil</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Database cakupan operasional polda jatim</p>
                </div>
                <div class="px-4 py-1.5 bg-slate-900 text-white rounded-full text-[9px] font-black uppercase tracking-widest shadow-lg shadow-slate-900/10">
                    Total: 0 Data
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">No</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Nama Wilayah</th>
                            <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">Aksi Tactical</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr class="group">
                            <td class="px-8 py-24 text-center" colspan="3">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="relative mb-6">
                                        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center animate-pulse">
                                            <svg class="w-10 h-10 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        </div>
                                        <div class="absolute -right-2 -bottom-2 w-8 h-8 bg-white rounded-full border-4 border-white shadow-sm flex items-center justify-center">
                                            <span class="text-blue-500 font-black text-[14px]">!</span>
                                        </div>
                                    </div>
                                    <p class="text-slate-500 uppercase font-black tracking-[0.3em] text-[10px]">Database Wilayah Masih Kosong</p>
                                    <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest mt-2 italic">Belum ada data wilayah yang tersinkronisasi.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection