@extends('layouts.app')

@section('header', 'Rekapitulasi Global Polda Jawa Timur')

@section('content')
<div class="space-y-6 pb-12">
    {{-- High-Level Aggregate Widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
        @php
            $rekapStats = [
                ['label' => 'Panen Normal', 'val' => '0', 'unit' => 'Ha', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Gagal Panen', 'val' => '0', 'unit' => 'Ha', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                ['label' => 'Panen Dini', 'val' => '0', 'unit' => 'Ha', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['label' => 'Panen Tahunan', 'val' => '0', 'unit' => 'Ha', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp
        @foreach($rekapStats as $rs)
        <div class="bg-white/40 backdrop-blur-xl p-6 rounded-[2.5rem] border border-white/60 shadow-lg flex flex-col items-center group hover:bg-slate-900 transition-all duration-500">
            <div class="p-2 bg-slate-100 rounded-xl mb-3 text-slate-500 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $rs['icon'] }}"></path></svg>
            </div>
            <h4 class="text-2xl font-black text-slate-800 group-hover:text-white">{{ $rs['val'] }} <small class="text-xs text-slate-400 group-hover:text-emerald-400 font-sans font-normal uppercase">{{ $rs['unit'] }}</small></h4>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 group-hover:text-slate-500 transition-colors">{{ $rs['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Map & Visual Recap Placeholder (Referencing image_dbabc2) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl min-h-[400px] flex flex-col justify-center items-center text-slate-300 relative overflow-hidden">
            <div class="absolute inset-0 bg-slate-50/50 flex items-center justify-center italic font-black uppercase text-sm tracking-[0.5em] opacity-20">Interactive Map Satwil Jawa Timur</div>
            <svg class="w-20 h-20 mb-4 opacity-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 20l-5.447-2.724A2 2 0 013 15.382V7.416a2 2 0 011.082-1.789l5.447-2.724a2 2 0 011.836 0l5.447 2.724A2 2 0 0118 7.416v7.966a2 2 0 01-1.082 1.79l-5.447 2.723a2 2 0 01-1.836 0z"></path></svg>
        </div>
        
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl flex flex-col items-center">
                <h4 class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest">Total Titik Lahan</h4>
                <div class="w-32 h-32 border-8 border-blue-500 rounded-full flex items-center justify-center"><span class="text-lg font-black text-slate-800">5,498</span></div>
                <p class="mt-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic opacity-50">Lahan Terverifikasi</p>
            </div>
            <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl flex flex-col items-center">
                <h4 class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest">Pengelolaan Lahan Polsek</h4>
                <div class="w-32 h-32 border-8 border-emerald-500 rounded-full flex items-center justify-center"><span class="text-lg font-black text-slate-800">659</span></div>
                <p class="mt-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic opacity-50">Satuan Polsek Aktif</p>
            </div>
        </div>
    </div>
</div>
@endsection