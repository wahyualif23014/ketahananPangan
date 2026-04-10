@extends('layouts.app')

@section('header', 'Kelola Tingkat Kesatuan')

@section('content')
    <div class="space-y-6 pb-20 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

        {{-- ==============================================================================
        1. MODULE HEADER
        ============================================================================== --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 transition-all duration-500 animate-in fade-in slide-in-from-top-4">
            <div>
                <nav class="flex items-center gap-2 text-[9px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                    <span>DATA UTAMA</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600">Tingkat Kesatuan</span>
                </nav>
                <h1 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tighter uppercase leading-none ">
                    TINGKAT <span class="text-emerald-600">KESATUAN</span>
                </h1>
            </div>

            {{-- Action Bar: Search & Refresh --}}
            <div class="flex items-center gap-3">
                <div class="relative group">
                    <div
                        class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="CARI DATA KESATUAN..."
                        class="pl-11 pr-6 py-3 bg-slate-100/50 border-none rounded-2xl text-[10px] font-bold uppercase tracking-widest text-slate-700 focus:ring-2 focus:ring-emerald-500/20 focus:bg-white transition-all w-full md:w-72 shadow-inner">
                </div>

                {{-- Refresh Button --}}
                <button
                    class="p-3 bg-white border border-slate-100 text-emerald-500 rounded-2xl hover:bg-emerald-50 hover:border-emerald-200 transition-all shadow-sm active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- ==============================================================================
        2. SUMMARY STATS (Sikron & Lebih Padat)
        ============================================================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div
                class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5">Cakupan Wilayah
                        </p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">
                            Terdapat <span class="text-emerald-600 text-2xl tracking-tighter">39</span> Polres
                        </h3>
                    </div>
                </div>
                <div
                    class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500/20 group-hover:bg-emerald-500 transition-all">
                </div>
            </div>

            <div
                class="group relative overflow-hidden bg-white/60 backdrop-blur-xl p-5 rounded-[1.8rem] border border-white shadow-xl shadow-slate-200/40 transition-all hover:-translate-y-1">
                <div class="flex items-center gap-4 relative z-10">
                    <div
                        class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-[8px] font-black uppercase tracking-[0.3em] mb-0.5">Sub-Unit Kerja</p>
                        <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none">
                            Terdapat <span class="text-blue-600 text-2xl tracking-tighter">659</span> Polsek
                        </h3>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-600/20 group-hover:bg-blue-600 transition-all">
                </div>
            </div>
        </div>

        {{-- ==============================================================================
        3. HIERARCHICAL TABLE (Premium Tree View)
        ============================================================================== --}}
        <div
            class="bg-white/80 backdrop-blur-xl rounded-[2.2rem] border border-white shadow-2xl shadow-slate-200/40 overflow-hidden">
            {{-- Table Head --}}
            <div class="grid grid-cols-12 bg-slate-900/[0.02] border-b border-slate-100">
                <div class="col-span-8 px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Struktur
                    Kesatuan & Personel Komando</div>
                <div
                    class="col-span-4 px-10 py-5 text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">
                    Wilayah Administratif</div>
            </div>

            @php
                $polresData = [
                    [
                        'id' => 1,
                        'nama' => 'POLRES BANGKALAN',
                        'kapolres' => 'AKBP HENDRO SUKMONO, S.H., S.I.K., M.I.K.',
                        'kontak' => '+62 812-2221-1647',
                        'polsek_count' => 17,
                        'polsek' => [
                            ['nama' => 'POLSEK AROSBAYA', 'kapolsek' => 'IPTU SYS EKO RATNA PURNOMO, S.H', 'kontak' => '+62 811-3172-99', 'wilayah' => 'KECAMATAN AROSBAYA'],
                            ['nama' => 'POLSEK BLEGA', 'kapolsek' => 'AKP MUH. SYAMSURI, SH', 'kontak' => '+62 812-3311-6696', 'wilayah' => 'KECAMATAN BLEGA'],
                            ['nama' => 'POLSEK BURNEH', 'kapolsek' => 'MAS HERLY SUSANTO, S.H.', 'kontak' => '+62 822-3220-9703', 'wilayah' => 'KECAMATAN BURNEH'],
                        ]
                    ]
                ];
            @endphp

            @foreach($polresData as $polres)
                <div x-data="{ expanded: true }" class="group/parent border-b border-slate-50 last:border-0">
                    <div @click="expanded = !expanded" :class="expanded ? 'bg-emerald-50/20' : 'bg-white hover:bg-slate-50'"
                        class="grid grid-cols-12 items-center transition-all duration-300 cursor-pointer">

                        <div class="col-span-8 px-10 py-7">
                            <div class="flex items-center gap-5">
                                <div :class="expanded ? 'bg-emerald-600 text-white rotate-180 shadow-emerald-200' : 'bg-white text-slate-400 border border-slate-100'"
                                    class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-500 shadow-lg group-hover/parent:scale-105">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <h3
                                        class="text-lg font-black text-slate-800 uppercase italic tracking-tight group-hover/parent:text-emerald-600 transition-colors leading-none">
                                        {{ $polres['nama'] }}
                                    </h3>
                                    <div
                                        class="flex flex-wrap items-center gap-x-4 text-[9px] font-bold uppercase tracking-widest text-slate-400">
                                        <span
                                            class="flex items-center gap-1.5 bg-white px-2 py-0.5 rounded-lg border border-slate-100">
                                            <span class="text-slate-300 italic">KAPOLRES:</span> <span
                                                class="text-slate-700">{{ $polres['kapolres'] }}</span>
                                        </span>
                                        <span class="flex items-center gap-1.5 text-emerald-600">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 004.815 4.815l.773-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z">
                                                </path>
                                            </svg>
                                            {{ $polres['kontak'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-4 px-10 text-right">
                            <span
                                class="inline-flex items-center px-4 py-1.5 bg-emerald-600 text-white rounded-xl text-[9px] font-black uppercase tracking-[0.15em] shadow-md shadow-emerald-200">
                                {{ $polres['polsek_count'] }} Sub-Units
                            </span>
                        </div>
                    </div>

                    <div x-show="expanded" x-collapse>
                        <div class="relative bg-slate-50/40">
                            <div
                                class="absolute left-[3.1rem] top-0 bottom-10 w-[2px] bg-gradient-to-b from-emerald-200 to-transparent opacity-40">
                            </div>

                            @foreach($polres['polsek'] as $polsek)
                                <div
                                    class="grid grid-cols-12 items-center border-t border-white/60 hover:bg-white transition-all duration-300 group/child">
                                    <div class="col-span-8 px-24 py-5 relative">
                                        <div
                                            class="absolute left-[3.1rem] top-1/2 -translate-y-1/2 -translate-x-1/2 w-3.5 h-3.5 bg-white border-[3px] border-emerald-400 rounded-full shadow-sm group-hover/child:bg-emerald-500 group-hover/child:border-white transition-all">
                                        </div>

                                        <div class="space-y-1">
                                            <h4
                                                class="text-[13px] font-black text-slate-700 uppercase italic tracking-wide group-hover/child:text-emerald-600 transition-colors leading-none">
                                                {{ $polsek['nama'] }}
                                            </h4>
                                            <div
                                                class="flex items-center gap-3 text-[9px] font-bold uppercase tracking-widest text-slate-400">
                                                <span>KAPOLSEK: <span class="text-slate-600">{{ $polsek['kapolsek'] }}</span></span>
                                                <span class="text-slate-200">/</span>
                                                <span class="italic text-slate-500 font-medium">{{ $polsek['kontak'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-4 px-10 text-right">
                                        <span
                                            class="text-[9px] font-black text-slate-400 uppercase tracking-[0.1em] bg-white border border-slate-100 px-3 py-1 rounded-lg shadow-sm group-hover/child:text-emerald-600 transition-all">
                                            {{ $polsek['wilayah'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection