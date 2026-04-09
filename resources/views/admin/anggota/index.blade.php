@extends('layouts.app')

@section('content')
@php
    $groupedPersonels = $personels->groupBy(function($item) {
        return $item->kesatuan ?? 'PUSAT DATA POLDA JATIM'; 
    });
@endphp

<div x-data='{ 
    selected: [], 
    selectAll: false,
    {{-- Ambil ID Anggota untuk fitur Select All --}}
    allIds: @json($personels->pluck("id_anggota")),
    
    toggleAll() {
        this.selectAll = !this.selectAll;
        this.selected = this.selectAll ? this.allIds : [];
    }
}' class="space-y-6 pb-12 font-sans" style="font-family: "Ramabhadra", sans-serif;">

    {{-- 1. Tactical Header Section --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900/95 backdrop-blur-xl p-10 shadow-2xl border border-slate-700/50">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex items-center gap-2 mb-3 text-[10px] font-black tracking-[0.3em] uppercase text-slate-400">
                    <span>DATA PERSONEL</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3"></path></svg>
                    <span class="text-emerald-400 italic">DATABASE REALTIME</span>
                </nav>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic leading-none">
                    Personel <span class="text-emerald-500">Satgas</span>
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-slate-800/40 p-1.5 rounded-2xl border border-slate-700/50">
                    <button @click="window.location.reload()" class="p-2.5 bg-white text-emerald-600 rounded-xl shadow-sm hover:bg-emerald-50 transition-all active:scale-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2"></path></svg>
                    </button>
                    <button class="p-2.5 bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 active:scale-95 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="absolute -right-10 -top-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-[80px]"></div>
    </div>

    {{-- 2. Alert Info --}}
    <div class="bg-[#FFFBEB] border border-amber-200/60 p-6 rounded-[2.5rem] flex items-center gap-5 shadow-sm">
        <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-lg">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
        </div>
        <div>
            <h4 class="text-[13px] font-black text-slate-700 uppercase tracking-tight">TOTAL DATA: <span class="text-emerald-600 text-lg">{{ $personels->count() }}</span> PERSONEL TERDAFTAR</h4>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Status: Sinkronisasi Database Berhasil</p>
        </div>
    </div>

    {{-- 3. Professional Table Container --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="w-10 pl-8 pr-0 py-6 text-center">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="w-4 h-4 rounded border-slate-300 text-emerald-600">
                        </th>
                        <th class="px-4 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Personel</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Kontak</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Jabatan</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Role</th>
                        <th class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($groupedPersonels as $unit => $members)
                        {{-- Dropdown Header Grup --}}
                        <tr x-data="{ expanded: true }" class="bg-slate-100/80 hover:bg-slate-200/80 cursor-pointer transition-colors border-y border-slate-200" @click="expanded = !expanded">
                            <td colspan="6" class="px-8 py-3.5">
                                <div class="flex items-center gap-3">
                                    <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    <span class="text-[11px] font-black text-slate-600 uppercase tracking-[0.3em] italic">{{ $unit }}</span>
                                    <span class="px-2 py-0.5 bg-white rounded text-[8px] font-bold text-slate-400 border border-slate-200">{{ $members->count() }} DATA</span>
                                </div>
                            </td>
                        </tr>

                        {{-- Looping Anggota Database --}}
                        @foreach($members as $p)
                        <tr x-show="expanded" x-collapse class="group hover:bg-slate-200/70 transition-all duration-200">
                            <td class="w-10 pl-8 pr-0 py-5 text-center border-none">
                                <input type="checkbox" :value="{{ $p->id_anggota }}" x-model="selected" class="w-4 h-4 rounded border-slate-300 text-emerald-600">
                            </td>
                            <td class="px-4 py-5 border-none">
                                <div class="flex items-center gap-3">
                                    {{-- Avatar Bulat --}}
                                    <div class="w-11 h-11 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm border-2 border-white shadow-md group-hover:scale-110 transition-transform duration-300">
                                        {{ substr($p->nama_anggota, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-[13px] font-black text-slate-800 uppercase italic tracking-tight group-hover:text-blue-600 transition-colors">{{ $p->nama_anggota }}</h4>
                                        <p class="text-[9px] font-bold text-blue-500 uppercase tracking-tighter">NRP : <span class="text-slate-400 italic">{{ $p->username }}</span></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-[10px] font-bold text-slate-500 uppercase tracking-tighter border-none">{{ $p->no_telp_anggota }}</td>
                            <td class="px-6 py-5 text-[10px] font-black text-slate-700 uppercase italic border-none">
                                {{ $p->jabatan->nama_jabatan ?? '-' }}
                            </td>
                            <td class="px-6 py-5 border-none">
                                <span class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-blue-100 group-hover:bg-white transition-colors">
                                    {{ $p->role }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-center border-none">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <button class="p-2 bg-white text-blue-500 rounded-lg border border-slate-200 hover:bg-blue-600 hover:text-white shadow-sm transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2"></path></svg></button>
                                    <button class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white shadow-sm transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"></path></svg></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection