@extends('layouts.app')

@section('header', 'Dashboard Anggota Satgas')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h4 class="text-emerald-700 font-black uppercase text-lg tracking-tight italic">Hello, {{ Auth::user()->name }}</h4>
        <p class="text-slate-500 text-[10px] mt-1 font-bold leading-relaxed uppercase tracking-wider">
            Selamat datang di Sistem Ketahanan Pangan Presisi. Anda login sebagai Anggota Satgas Pangan Jawa Timur.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center border-l-8 border-l-emerald-500">
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter">170,715.11 <span class="text-sm font-normal text-slate-400 font-sans">Ha</span></h1>
            <p class="text-[10px] font-black text-slate-400 uppercase mt-2 tracking-[0.2em]">Total Potensi Lahan Jatim</p>
        </div>

        <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            <ul class="space-y-3">
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Milik Polri :</span>
                    <span class="text-emerald-600 font-black">9.63 <small class="text-slate-400 font-sans uppercase">Ha</small></span>
                </li>
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Produktif (Poktan) :</span>
                    <span class="text-emerald-600 font-black">34,882.86 <small class="text-slate-400 font-sans uppercase">Ha</small></span>
                </li>
            </ul>
            <ul class="space-y-3">
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Hutan (Perhutani) :</span>
                    <span class="text-emerald-600 font-black">22,573.23 <small class="text-slate-400 font-sans uppercase">Ha</small></span>
                </li>
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Luas Baku Sawah :</span>
                    <span class="text-emerald-600 font-black">64,792.29 <small class="text-slate-400 font-sans uppercase">Ha</small></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm min-h-[300px]">
        <div class="flex justify-between items-center mb-6 border-b border-slate-50 pb-4">
            <h3 class="text-xs font-black uppercase text-slate-500 tracking-[0.2em]">Statistik Hasil Panen Tahun 2026</h3>
            <div class="px-3 py-1 bg-blue-50 rounded-full text-blue-600 text-[10px] font-black italic">988.92 TON</div>
        </div>
        <div class="w-full h-48 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center">
            <svg class="w-10 h-10 text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-slate-300 text-[10px] font-black uppercase tracking-widest italic">Data Visualisasi Grafik</span>
        </div>
    </div>
</div>
@endsection