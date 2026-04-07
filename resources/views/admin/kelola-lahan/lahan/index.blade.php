@extends('layouts.app')

@section('header', 'Daftar Kelola Lahan Operasional')

@section('content')
<div class="space-y-6 pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Card Tanam --}}
        <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-4xl font-black text-slate-800 tracking-tighter italic">242.74 <span class="text-xs font-normal text-slate-400 uppercase font-sans">Ha</span></h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mt-2">Total Lahan Tanam 2026</p>
                </div>
                <div class="p-3 bg-blue-500/10 text-blue-600 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg></div>
            </div>
            <div class="space-y-2 border-t border-slate-200/50 pt-4">
                @foreach(['Milik Polri' => '0', 'Produktif (Poktan)' => '107.08', 'Masyarakat' => '39.31'] as $label => $v)
                <div class="flex justify-between text-[10px] font-bold"><span class="text-slate-400 uppercase">➤ {{ $label }}</span><span class="text-slate-700 font-black">{{ $v }} Ha</span></div>
                @endforeach
            </div>
        </div>

        {{-- Card Panen --}}
        <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-4xl font-black text-slate-800 tracking-tighter italic">243.72 <span class="text-xs font-normal text-slate-400 uppercase font-sans">Ha</span></h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mt-2">Total Lahan Panen 2026</p>
                </div>
                <div class="p-3 bg-emerald-500/10 text-emerald-600 rounded-2xl"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>
            </div>
            <div class="space-y-2 border-t border-slate-200/50 pt-4 text-emerald-600">
                @foreach(['Milik Polri' => '0.98', 'Produktif (Poktan)' => '107.08', 'Masyarakat' => '39.31'] as $label => $v)
                <div class="flex justify-between text-[10px] font-bold"><span class="text-slate-400 uppercase">➤ {{ $label }}</span><span class="text-emerald-600 font-black">{{ $v }} Ha</span></div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Operation Table --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white/60 shadow-xl overflow-hidden">
        <div class="p-8 border-b border-slate-200/50 flex justify-between items-center bg-slate-900/5">
            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg italic">Log Aktivitas <span class="text-blue-500">Pengelolaan</span></h3>
            <button class="px-6 py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20">+ Input Lahan Baru</button>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-900/5">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">No</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Satwil</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Luas (Ha)</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                </tr>
            </thead>
            <tbody><tr><td class="px-8 py-10 text-center opacity-30 italic font-bold uppercase tracking-widest text-[10px]" colspan="4">Tidak ada data operasional terbaru.</td></tr></tbody>
        </table>
    </div>
</div>
@endsection