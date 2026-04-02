@extends('layouts.app')

@section('header', 'Panel Kerja Operator Lapangan')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex-1">
            <h4 class="text-emerald-700 font-black uppercase text-lg tracking-tight italic">Halo, {{ Auth::user()->name }}!</h4>
            <p class="text-slate-500 text-[10px] mt-1 font-bold leading-relaxed uppercase tracking-wider">
                Selamat bertugas. Pastikan data laporan harian wilayah Anda telah diinput sebelum pukul 16:00 WIB.
            </p>
        </div>
        <div class="flex gap-3 w-full md:w-auto">
            <a href="#" class="flex-1 md:flex-none bg-slate-900 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition text-center shadow-lg shadow-slate-900/20">
                + Input Laporan
            </a>
            <a href="#" class="flex-1 md:flex-none bg-slate-100 text-slate-700 px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition text-center">
                Riwayat
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center border-l-8 border-l-emerald-500">
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter">65<span class="text-sm font-normal text-slate-400 font-sans">%</span></h1>
            <p class="text-[10px] font-black text-slate-400 uppercase mt-2 tracking-[0.2em]">Progress Panen Wilayah</p>
        </div>

        <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="mb-4 border-b border-slate-50 pb-2">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status Kondisi Wilayah Anda</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <ul class="space-y-3">
                    <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                        <span class="text-slate-500 uppercase">➤ Kondisi Tanaman :</span>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[9px] font-black uppercase">Optimal</span>
                    </li>
                    <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                        <span class="text-slate-500 uppercase">➤ Ketersediaan Air :</span>
                        <span class="text-slate-800 font-black uppercase">Cukup</span>
                    </li>
                </ul>
                <ul class="space-y-3">
                    <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                        <span class="text-slate-500 uppercase">➤ Luas Lahan Tergarap :</span>
                        <span class="text-slate-800 font-black">450.5 <small class="text-slate-400 uppercase font-sans">Ha</small></span>
                    </li>
                    <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                        <span class="text-slate-500 uppercase">➤ Prediksi Hasil :</span>
                        <span class="text-blue-600 font-black">12.5 <small class="text-slate-400 uppercase font-sans">Ton</small></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100 flex items-center gap-4">
        <div class="p-3 bg-amber-500 text-white rounded-xl shadow-lg shadow-amber-500/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <div>
            <h5 class="text-xs font-black text-amber-900 uppercase tracking-widest">Peringatan Input Data</h5>
            <p class="text-[10px] text-amber-700 font-bold uppercase mt-1">Anda memiliki 2 lahan yang belum divalidasi laporannya untuk periode minggu ini.</p>
        </div>
    </div>
</div>
@endsection