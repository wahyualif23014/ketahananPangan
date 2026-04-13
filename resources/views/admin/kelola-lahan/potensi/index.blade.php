@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
    <div class="space-y-6 pb-20 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

        {{-- ==============================================================================
        1. MODULE HEADER (Identik dengan Patokan Jabatan)
        ============================================================================== --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2 mb-8 transition-all duration-500 animate-in fade-in slide-in-from-top-4">
            <div>
                {{-- Breadcrumb Tactical --}}
                <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                    <span>DATA UTAMA</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600">Potensi Lahan</span>
                </nav>
                {{-- Main Title --}}
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase leading-none">
                    POTENSI <span class="text-emerald-600">LAHAN</span>
                </h2>
            </div>

            {{-- Action Bar: Search & Refresh --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="CARI DATA POTENSI..."
                        class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase">
                </div>

                <div class="flex items-center gap-2">
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
                        Validasi
                    </button>
                </div>
            </div>
        </div>

        {{-- ==============================================================================
        2. SUMMARY STATS (Consistent 3-Column Tactical Grid)
        ============================================================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6">
            {{-- Item 1: Luasan Total --}}
            <div
                class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5">Total Capaian</p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">170,715.11 <span
                                class="text-[10px] text-slate-400 font-normal ml-0.5">HA</span></h3>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500/20 group-hover:bg-emerald-500 transition-all">
                </div>
            </div>

            {{-- Item 2: Lahan Produktif --}}
            <div
                class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5">Lahan Produktif
                        </p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">62,199.35 <span
                                class="text-[10px] text-slate-400 font-normal ml-0.5">HA</span></h3>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-600/20 group-hover:bg-blue-600 transition-all">
                </div>
            </div>

            {{-- Item 3: Baku Sawah --}}
            <div
                class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center border border-amber-100 group-hover:bg-amber-500 group-hover:text-white transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5">Luas Baku Sawah
                        </p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">64,792.29 <span
                                class="text-[10px] text-slate-400 font-normal ml-0.5">HA</span></h3>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-600/20 group-hover:bg-amber-600 transition-all">
                </div>
            </div>
        </div>

        {{-- ==============================================================================
        3. DETAIL BREAKDOWN (Refining User Data List)
        ============================================================================== --}}
        <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.2rem] border border-white/60 shadow-xl">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-10">
                @php
                    $potensiDetail = [
                        'Milik Polri' => '9.63',
                        'Produktif (Poktan)' => '34,882.86',
                        'Produktif (Masyarakat)' => '27,316.49',
                        'Hutan (Perhutani)' => '22,573.23',
                        'Lainnya' => '107.52'
                    ];
                @endphp
                @foreach($potensiDetail as $label => $val)
                    <div
                        class="flex justify-between items-center text-[10px] font-bold border-b border-slate-100 pb-2.5 group hover:border-emerald-200 transition-colors">
                        <span class="text-slate-400 uppercase tracking-tighter">➤ {{ $label }} :</span>
                        <span class="text-slate-800 font-black tracking-tight">{{ $val }} <small
                                class="text-slate-400 uppercase font-normal ml-0.5">Ha</small></span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ==============================================================================
        4. DATA TABLE SECTION
        ============================================================================== --}}
        <div
            class="bg-white/80 backdrop-blur-xl rounded-[2.2rem] border border-white shadow-2xl shadow-slate-200/40 overflow-hidden">
            {{-- Table Header --}}
            <div
                class="p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white/40">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg leading-none italic">Rincian
                            Per-Satwil</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Database pemetaan
                            potensi wilayah polda jatim</p>
                    </div>
                </div>
                <div
                    class="px-4 py-1.5 bg-slate-900 text-white rounded-full text-[9px] font-black uppercase tracking-widest shadow-lg shadow-slate-900/10">
                    Total: 0 Data
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">
                                Kesatuan Wilayah</th>
                            <th
                                class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">
                                Total Potensi</th>
                            <th
                                class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] border-b border-slate-100">
                                Status Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr class="group">
                            <td class="px-8 py-24 text-center" colspan="3">
                                <div class="flex flex-col items-center justify-center opacity-40">
                                    <div class="relative mb-6">
                                        <div
                                            class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center animate-pulse">
                                            <svg class="w-10 h-10 text-emerald-200" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div
                                            class="absolute -right-2 -bottom-2 w-8 h-8 bg-white rounded-full border-4 border-white shadow-sm flex items-center justify-center">
                                            <span class="text-emerald-500 font-black text-[14px]">!</span>
                                        </div>
                                    </div>
                                    <p class="text-slate-500 uppercase font-black tracking-[0.3em] text-[10px]">Database
                                        Belum Terinput</p>
                                    <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest mt-2 italic">
                                        Menunggu sinkronisasi data dari admin satuan wilayah.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection