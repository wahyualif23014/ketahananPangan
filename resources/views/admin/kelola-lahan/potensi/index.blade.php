@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header & Summary (Referencing image_dc1861) --}}
    <div class="bg-white/40 backdrop-blur-xl p-10 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50 flex flex-col lg:flex-row gap-10 items-center">
        <div class="lg:w-1/3 text-center lg:text-left border-b lg:border-b-0 lg:border-r border-slate-200/50 pb-8 lg:pb-0 lg:pr-10">
            <h4 class="text-emerald-500 font-black uppercase text-[10px] tracking-[0.4em] mb-3 opacity-80">Total Capaian</h4>
            <h1 class="text-6xl font-black text-slate-800 tracking-tighter italic leading-none">170,715.11 <span class="text-sm font-normal text-slate-400 font-sans uppercase">Ha</span></h1>
            <p class="text-[11px] font-black text-slate-400 uppercase mt-4 tracking-[0.2em] italic">Potensi Lahan Jawa Timur 2026</p>
        </div>
        
        <div class="lg:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 w-full">
            @php
                $potensi = [
                    'Milik Polri' => '9.63', 'Produktif (Poktan)' => '34,882.86', 
                    'Produktif (Masyarakat)' => '27,316.49', 'Hutan (Perhutani)' => '22,573.23',
                    'Luas Baku Sawah' => '64,792.29', 'Lainnya' => '107.52'
                ];
            @endphp
            @foreach($potensi as $label => $val)
            <div class="flex justify-between items-center text-[11px] font-bold border-b border-slate-100 pb-2 group hover:border-emerald-200 transition-colors">
                <span class="text-slate-500 uppercase tracking-tighter">➤ {{ $label }} :</span>
                <span class="text-emerald-600 font-black">{{ $val }} <small class="text-slate-400 uppercase">Ha</small></span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Detail Table per Satwil --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-xl overflow-hidden">
        <div class="p-8 border-b border-slate-200/50 flex justify-between items-center bg-slate-50/30">
            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg italic">Rincian Potensi Lahan <span class="text-emerald-500">Per-Satwil</span></h3>
            <button class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg shadow-slate-900/20">+ Validasi Data</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/5">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Kesatuan Wilayah</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Potensi</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px] font-bold text-slate-600 uppercase">
                    <tr><td class="px-8 py-6" colspan="3 text-center italic opacity-50 text-center uppercase tracking-widest py-12">Belum ada rincian data satwil terinput.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection