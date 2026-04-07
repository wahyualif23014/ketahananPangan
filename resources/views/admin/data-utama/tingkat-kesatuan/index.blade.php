@extends('layouts.app')

@section('header', 'Kelola Tingkat Kesatuan')

@section('content')
    <div class="space-y-6 pb-20">
        {{-- 1. Header Page --}}
        <div
            class="relative overflow-hidden rounded-[2.5rem] bg-slate-900/90 backdrop-blur-xl p-10 shadow-2xl border border-slate-700/50">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                        <h4 class="text-emerald-400 font-black uppercase text-[10px] tracking-[0.4em] opacity-80">
                            DATA UTAMA » <span class="text-white">TINGKAT KESATUAN KEPOLISIAN</span>
                        </h4>
                    </div>
                    <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic leading-none">
                        Hirarki <span class="text-emerald-500">Kesatuan</span>
                    </h1>
                </div>
            </div>
            <div class="absolute -right-10 -top-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-[80px]"></div>
        </div>

        {{-- 2. Summary Stats (Sesuai Gambar) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-white shadow-sm flex items-center gap-5">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Data</p>
                    <h3 class="text-xl font-black text-slate-800 uppercase italic">Terdapat <span
                            class="text-emerald-600 text-2xl">39</span> Polres</h3>
                </div>
            </div>
            <div class="bg-white/60 backdrop-blur-xl p-6 rounded-3xl border border-white shadow-sm flex items-center gap-5">
                <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Data</p>
                    <h3 class="text-xl font-black text-slate-800 uppercase italic">Terdapat <span
                            class="text-blue-600 text-2xl">659</span> Polsek</h3>
                </div>
            </div>
        </div>

        {{-- 3. Toolbar: Search & Refresh --}}
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="relative w-full md:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" placeholder="Cari data kesatuan..."
                    class="block w-full pl-10 pr-4 py-3 bg-white border-none rounded-2xl text-xs font-bold text-slate-700 shadow-sm focus:ring-4 focus:ring-emerald-500/10 transition-all">
            </div>
            <button
                class="p-3 bg-white hover:bg-emerald-50 text-emerald-600 rounded-2xl shadow-sm transition-all active:scale-90 border border-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </button>
        </div>

        {{-- 4. Hierarchical Expandable Table --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-xl overflow-hidden">
            <div class="grid grid-cols-12 bg-slate-900/5 border-b border-slate-200/50">
                <div class="col-span-8 px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tingkat
                    Kesatuan</div>
                <div class="col-span-4 px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Wilayah
                    Administratif</div>
            </div>

            {{-- Accordion Loop --}}
        @php
            // Mockup Data
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
        <div x-data="{ expanded: true }" class="group/parent">
            {{-- Polres Row --}}
            <div @click="expanded = !expanded" 
                :class="expanded ? 'bg-emerald-50/30' : 'bg-white hover:bg-slate-50'"
                class="grid grid-cols-1 md:grid-cols-12 items-center px-6 md:px-0 transition-all duration-300 cursor-pointer border-b border-slate-50">
                
                <div class="col-span-1 md:col-span-8 px-4 md:px-10 py-8">
                    <div class="flex items-start md:items-center gap-6">
                        {{-- Collapse Indicator --}}
                        <div :class="expanded ? 'bg-emerald-500 text-white rotate-180' : 'bg-slate-100 text-slate-400'" 
                             class="mt-1 md:mt-0 flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-500 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        
                        <div class="space-y-1">
                            <h3 class="text-lg md:text-xl font-black text-slate-800 uppercase italic tracking-tight group-hover/parent:text-emerald-600 transition-colors">
                                {{ $polres['nama'] }}
                            </h3>
                            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 text-[10px] md:text-[11px] font-bold uppercase tracking-wide">
                                <span class="text-slate-400">Kapolres: <span class="text-slate-700">{{ $polres['kapolres'] }}</span></span>
                                <span class="hidden md:inline text-slate-200">|</span>
                                <span class="flex items-center gap-1.5 text-emerald-600">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 004.815 4.815l.773-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
                                    {{ $polres['kontak'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-4 px-4 md:px-10 py-4 md:py-8 flex md:justify-end items-center gap-4">
                    <div class="flex flex-col items-start md:items-end">
                        <span class="px-5 py-2 bg-white border border-emerald-100 text-emerald-600 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                            {{ $polres['polsek_count'] }} Sub-Units
                        </span>
                    </div>
                </div>
            </div>

            {{-- Polsek Child Rows --}}
            <div x-show="expanded" x-collapse>
                <div class="relative bg-slate-50/50 px-6 md:px-0">
                    {{-- Visual Tree Line --}}
                    <div class="absolute left-10 md:left-[3.25rem] top-0 bottom-8 w-0.5 bg-emerald-100 rounded-full"></div>

                    @foreach($polres['polsek'] as $polsek)
                    <div class="grid grid-cols-1 md:grid-cols-12 items-center border-b border-white hover:bg-white transition-all duration-200">
                        <div class="col-span-1 md:col-span-8 px-14 md:px-24 py-6 relative">
                            {{-- Connector Dot --}}
                            <div class="absolute left-10 md:left-[3.25rem] top-1/2 -translate-y-1/2 -translate-x-1/2 w-3 h-3 bg-emerald-500 rounded-full border-4 border-white shadow-sm"></div>
                            
                            <div class="space-y-1">
                                <h4 class="text-[13px] font-black text-slate-700 uppercase italic tracking-wide">{{ $polsek['nama'] }}</h4>
                                <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-3 text-[10px] font-bold uppercase text-slate-400">
                                    <span>Kapolsek: <span class="text-slate-500">{{ $polsek['kapolsek'] }}</span></span>
                                    <span class="hidden md:inline text-slate-200">/</span>
                                    <span class="text-slate-400 italic">{{ $polsek['kontak'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 md:col-span-4 px-14 md:px-10 py-4 md:py-6 md:text-right">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] bg-slate-100 px-3 py-1 rounded-lg">
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

{{-- Alpine Plugins --}}
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection