@extends('layouts.app')

@section('header', 'Kelola Data Wilayah')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Page --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900/90 backdrop-blur-xl p-10 shadow-2xl border border-slate-700/50">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h4 class="text-amber-400 font-black uppercase text-[10px] tracking-[0.4em] mb-3 opacity-80">Master Data Utama</h4>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic leading-none">
                    Data <span class="text-amber-500">Wilayah</span>
                </h1>
                <p class="text-slate-400 text-xs mt-4 font-medium max-w-xl leading-relaxed uppercase tracking-widest opacity-70">
                    Manajemen cakupan wilayah operasional 38 Satuan Wilayah (Satwil) Jawa Timur.
                </p>
            </div>
            <button class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all shadow-lg shadow-amber-500/20 active:scale-95">
                + Tambah Wilayah
            </button>
        </div>
        <div class="absolute -right-10 -top-20 h-64 w-64 rounded-full bg-amber-500/20 blur-[80px]"></div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-xl overflow-hidden">
        <div class="p-8 border-b border-slate-200/50 flex justify-between items-center">
            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg italic">Daftar Wilayah / Satwil</h3>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total: 0 Data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/5">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">No</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Wilayah</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="group hover:bg-white/50 transition-colors">
                        <td class="px-8 py-6 text-xs font-bold text-slate-400 italic" colspan="3">
                            <div class="flex flex-col items-center py-10 opacity-30">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                <p class="uppercase font-black tracking-widest text-[10px]">Belum Ada Data Wilayah</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection