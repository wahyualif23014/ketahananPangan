@extends('layouts.app')

@section('header', 'Manajemen Data Personel')

@section('content')
{{-- Parent Container dengan Alpine.js untuk State Management --}}
<div x-data="{ 
    showModal: false, 
    search: '', 
    selectAll: false, 
    selected: [],
    // Data Personel Mockup Sesuai Gambar
    personels: [
        { id: 1, kesatuan: 'POLDA JAWA TIMUR', nama: 'IRJEN POL NANANG AVIANTO, M.SI.', nrp: '69040180', kontak: '+62 888-8888-1991', jabatan: 'KAPOLDA', akses: 'ENDUSER', proses: 'DIO VLADIKA', waktu: '09-01-2026 17:57' },
        { id: 2, kesatuan: 'POLDA JAWA TIMUR', nama: 'KOMBES POL SIH HARNO, S.H., M.H.', nrp: '20251235', kontak: '+62 813-3316-1393', jabatan: 'KARO SDM', akses: 'ADMINISTRATOR', proses: 'DIO VLADIKA', waktu: '13-01-2026 13:09' },
        { id: 3, kesatuan: 'POLRESTABES SURABAYA', nama: 'BRIPDA M. WILDANOL ULUM RAMADHAN, S.P.', nrp: 'OPERATOR3', kontak: '+62 896-0679-2520', jabatan: '-', akses: 'OPERATOR', proses: 'DIO VLADIKA', waktu: '09-01-2026 17:57' }
    ],
    // Fungsi Toggle All Checkbox
    toggleAll() {
        this.selectAll = !this.selectAll;
        this.selected = this.selectAll ? this.personels.map(p => p.id) : [];
    }
}" class="space-y-6 pb-12 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

    {{-- 1. Header Section (Tactical Dark) --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900/95 backdrop-blur-xl p-10 shadow-2xl border border-slate-700/50">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex items-center gap-2 mb-3 text-[10px] font-black tracking-[0.3em] uppercase">
                    <span class="text-slate-400">DATA PERSONEL</span>
                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3"></path></svg>
                    <span class="text-emerald-400 italic">DATA PERSONEL KEPOLISIAN</span>
                </nav>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic leading-none">
                    Personel <span class="text-emerald-500">Satgas</span>
                </h1>
            </div>

            {{-- Toolbar Aksi Sesuai Gambar --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="search" placeholder="CARI DATA..." 
                        class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-2xl text-[11px] font-bold text-white placeholder-slate-500 focus:ring-4 focus:ring-emerald-500/20 outline-none uppercase tracking-widest transition-all">
                </div>
                
                <div class="flex items-center gap-2 bg-slate-800/40 p-1.5 rounded-2xl border border-slate-700/50">
                    <button title="Refresh" class="p-2.5 bg-white text-emerald-600 rounded-xl shadow-sm hover:bg-emerald-50 transition-all active:scale-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                    <button x-show="selected.length > 0" x-transition title="Hapus Terpilih" class="p-2.5 bg-white text-rose-500 rounded-xl shadow-sm hover:bg-rose-50 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    <button @click="showModal = true" class="p-2.5 bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-500/20 hover:bg-blue-600 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="absolute -right-10 -top-20 h-64 w-64 rounded-full bg-emerald-500/10 blur-[80px]"></div>
    </div>

    {{-- 2. Summary Info Box (Soft Ivory Contrast) --}}
    <div class="bg-[#FFFBEB] border border-amber-200/60 p-6 rounded-[2.5rem] flex items-center gap-5 shadow-sm">
        <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
        </div>
        <div>
            <h4 class="text-[13px] font-black text-slate-700 uppercase tracking-tight">TERDAPAT <span class="text-emerald-600">21</span> KESATUAN BELUM ADA DATA PERSONEL</h4>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">STATUS: <span class="text-slate-600">0 Polres</span> DAN <span class="text-slate-600">21 Polsek</span> BELUM TERUKUR</p>
        </div>
    </div>

    {{-- 3. Professional Table Container --}}
    {{-- Table Section --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="w-12 pl-6 py-6 text-center">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20">
                        </th>
                        <th class="px-4 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Personel</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Kontak</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Jabatan</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Akses</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Proses</th>
                        <th class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $kesatuans = [
                            'POLDA JAWA TIMUR' => [1, 2],
                            'POLRESTABES SURABAYA' => [3]
                        ];
                    @endphp

                    @foreach($kesatuans as $unit => $ids)
                        <tr class="bg-stone-100/80 border-y border-stone-200">
                            <td colspan="7" class="px-8 py-3 text-[11px] font-black text-stone-500 uppercase tracking-[0.3em] italic">
                                {{ $unit }}
                            </td>
                        </tr>

                        @foreach($ids as $id)
                        <template x-for="p in personels.filter(i => i.id === {{ $id }})" :key="p.id">
                            <tr class="group hover:bg-slate-100/80 transition-all duration-200">
                                {{-- Checkbox Column (Persempit Jarak) --}}
                                <td class="w-12 pl-6 py-5 text-center">
                                    <input type="checkbox" :value="p.id" x-model="selected" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20">
                                </td>
                                {{-- Nama & Avatar (Lebih Dekat) --}}
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-4">
                                        {{-- Avatar Bulat Sempurna --}}
                                        <div class="w-11 h-11 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-black text-xs border border-slate-200 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all duration-500" x-text="p.nama.substring(0,1)"></div>
                                        <div>
                                            <h4 class="text-[13px] font-black text-slate-800 uppercase italic tracking-tight" x-text="p.nama"></h4>
                                            <p class="text-[9px] font-bold text-blue-500 uppercase tracking-tighter">NRP : <span class="text-slate-400 italic" x-text="p.nrp"></span></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-[10px] font-bold text-slate-500 uppercase tracking-tighter" x-text="p.kontak"></td>
                                <td class="px-6 py-5 text-[10px] font-black text-slate-700 uppercase italic" x-text="p.jabatan"></td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 bg-white border border-slate-200 text-slate-400 rounded-lg text-[9px] font-black uppercase tracking-widest group-hover:bg-blue-50 group-hover:text-blue-600 group-hover:border-blue-100 transition-all" x-text="p.akses"></span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-1">
                                        <h5 class="text-[10px] font-black text-slate-700 uppercase italic" x-text="p.proses"></h5>
                                        <p class="text-[9px] font-bold text-slate-400 italic" x-text="p.waktu"></p>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <button class="p-2 bg-white text-emerald-500 rounded-lg border border-slate-200 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                        <button class="p-2 bg-white text-blue-500 rounded-lg border border-slate-200 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection