@extends('layouts.app')

@section('header', 'Kelola Komoditas Lahan')

@section('content')
<div class="space-y-6 pb-12">
    {{-- Header Page --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900/90 backdrop-blur-xl p-10 shadow-2xl border border-slate-700/50">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h4 class="text-indigo-400 font-black uppercase text-[10px] tracking-[0.4em] mb-3 opacity-80">Master Data Utama</h4>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic leading-none">
                    Komoditas <span class="text-indigo-500">Lahan</span>
                </h1>
                <p class="text-slate-400 text-xs mt-4 font-medium max-w-xl leading-relaxed uppercase tracking-widest opacity-70">
                    Daftar jenis hasil bumi dan tanaman pangan yang dikelola oleh Satgas Pangan Polda Jatim.
                </p>
            </div>
            <button class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all shadow-lg shadow-indigo-500/20 active:scale-95">
                + Tambah Komoditas
            </button>
        </div>
        <div class="absolute -right-10 -top-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-[80px]"></div>
    </div>

    {{-- Content Table --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-xl overflow-hidden">
        <div class="p-8 border-b border-slate-200/50 flex justify-between items-center bg-slate-50/30">
            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg italic">Jenis Tanaman Pangan</h3>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Update: {{ date('d M Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/5">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">No</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Nama Komoditas</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Satuan Ukur</th>
                        <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="group hover:bg-white/50 transition-colors">
                        <td class="px-8 py-12 text-xs font-bold text-slate-400 italic text-center" colspan="4">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <p class="uppercase font-black tracking-widest text-[10px]">Data Komoditas Belum Ada</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection