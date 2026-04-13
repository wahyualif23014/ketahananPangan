@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
    <div class="space-y-6 pb-20 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

        {{-- header --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2 mb-8 transition-all duration-500 animate-in fade-in slide-in-from-top-4">
            <div>
                <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                    <span>DATA UTAMA</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600">Data Produksi Lahan</span>
                </nav>
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                    DATA <span class="text-emerald-600">PRODUKSI LAHAN</span>
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="CARI DATA POTENSI..."
                        class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase">
                </div>

                <div class="flex items-center gap-2">
                    {{-- Refresh Button --}}
                    <button onclick="window.location.reload()" title="Refresh"
                        class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                    <button
                        class="flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-2xl shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"></path>
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>
        </div>


        {{-- 2. FLOATING TACTICAL --}}
        <div class="px-2 mb-10 transition-all duration-500 delay-150 animate-in fade-in slide-in-from-top-4">
            <div class="flex flex-col lg:flex-row items-center gap-4">
                {{--
                <div class="flex items-center gap-2 mr-2 group min-w-fit">
                    <div class="w-1.5 h-6 bg-blue-600 rounded-full group-hover:h-8 transition-all duration-300"></div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.3em] italic">Filter Data</span>
                </div> --}}

                {{-- Modular Grid System --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 w-full">

                    <div class="relative group shadow-sm hover:shadow-md transition-all duration-300">
                        <div
                            class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors z-20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                        <select
                            class="relative z-10 w-full pl-11 pr-10 py-3 bg-white/80 backdrop-blur-md border border-white rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all appearance-none cursor-pointer shadow-slate-200/50">
                            <option value="">PILIH KEPOLISIAN RESOR</option>
                            <option>POLRESTABES SURABAYA</option>
                            <option>POLRESTA SIDOARJO</option>
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400 z-20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>

                    {{-- Module 2: Kepolisian Sektor --}}
                    <div class="relative group shadow-sm hover:shadow-md transition-all duration-300">
                        <div
                            class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors z-20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <select
                            class="relative z-10 w-full pl-11 pr-10 py-3 bg-white/80 backdrop-blur-md border border-white rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all appearance-none cursor-pointer shadow-slate-200/50">
                            <option value="">PILIH KEPOLISIAN SEKTOR</option>
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400 z-20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>

                    {{-- Module 3: Jenis Lahan --}}
                    <div class="relative group shadow-sm hover:shadow-md transition-all duration-300">
                        <div
                            class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors z-20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 4L9 7">
                                </path>
                            </svg>
                        </div>
                        <select
                            class="relative z-10 w-full pl-11 pr-10 py-3 bg-white/80 backdrop-blur-md border border-white rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-blue-500/10 focus:bg-white transition-all appearance-none cursor-pointer shadow-slate-200/50">
                            <option value="">PILIH JENIS LAHAN</option>
                            <option>MILIK POLRI</option>
                            <option>MASYARAKAT</option>
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400 z-20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </div>
                    </div>

                    {{-- Module 4: Status Toggle (Floating Pill) --}}
                    <div
                        class="flex items-center justify-between px-5 py-3 bg-white/80 backdrop-blur-md border border-white rounded-2xl shadow-sm shadow-slate-200/50">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">Belum
                            Validasi</span>
                        <label class="relative inline-flex items-center cursor-pointer group" x-data="{ checked: false }">
                            <input type="checkbox" class="sr-only peer" x-model="checked">
                            <div
                                class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600 transition-all">
                            </div>
                        </label>
                    </div>

                </div>
            </div>
        </div>


@endsection