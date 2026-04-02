@extends('layouts.app')

@section('header', 'Sikappresisi Polda Jawa Timur')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h4 class="text-emerald-700 font-black uppercase text-lg tracking-tight italic">Hello, {{ Auth::user()->name }}</h4>
        <p class="text-slate-500 text-[10px] mt-1 font-bold leading-relaxed uppercase tracking-wider">
            Sistem Ketahanan Pangan Presisi - Pantau potensi lahan dan hasil panen wilayah Jawa Timur secara real-time.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 bg-white p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center">
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter">170,715.11 <span class="text-sm font-normal text-slate-400 font-sans">Ha</span></h1>
            <p class="text-[10px] font-black text-slate-400 uppercase mt-2 tracking-[0.2em]">Total Potensi Lahan (Ha)</p>
        </div>

        <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-4">
            <ul class="space-y-3">
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Milik Polri :</span>
                    <span class="text-emerald-600 font-black">9.63 <small class="text-slate-400 uppercase">Ha</small></span>
                </li>
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Produktif (Poktan) :</span>
                    <span class="text-emerald-600 font-black">34,882.86 <small class="text-slate-400 uppercase">Ha</small></span>
                </li>
            </ul>
            <ul class="space-y-3">
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Hutan (Perhutani) :</span>
                    <span class="text-emerald-600 font-black">22,573.23 <small class="text-slate-400 uppercase">Ha</small></span>
                </li>
                <li class="flex justify-between text-[11px] font-bold border-b border-slate-50 pb-2">
                    <span class="text-slate-500 uppercase">➤ Luas Baku Sawah :</span>
                    <span class="text-emerald-600 font-black">64,792.29 <small class="text-slate-400 uppercase">Ha</small></span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection