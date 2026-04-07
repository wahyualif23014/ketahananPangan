@extends('layouts.app')

@section('header', 'Kelola Jabatan')

@section('content')
{{-- Container Utama dengan State Management Alpine.js --}}
<div x-data="{ 
    showModal: false, 
    selectAll: false, 
    selected: [],
    search: '',
    items: [
        { id: 1, nama: 'KAPOLDA', nrp: 'UNDEFINED', time: '24-11-2025 08:44' },
        { id: 2, nama: 'WAKAPOLDA', nrp: 'UNDEFINED', time: '24-11-2025 08:44' },
        { id: 3, nama: 'KAPOLRES', nrp: 'UNDEFINED', time: '24-11-2025 08:44' },
        { id: 4, nama: 'WAKAPOLRES', nrp: 'UNDEFINED', time: '24-11-2025 08:44' },
        { id: 5, nama: 'KASATGAS', nrp: 'UNDEFINED', time: '24-11-2025 08:44' },
        { id: 6, nama: 'WAKASATGAS', nrp: 'UNDEFINED', time: '24-11-2025 08:44' },
    ]
}" class="space-y-6">

    {{-- 1. Toolbar Section (Kontras Tinggi) --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 px-2">
        <div>
            <nav class="flex items-center gap-2 text-[10px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">
                <span>DATA UTAMA</span>
                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                <span class="text-blue-600">Jabatan Anggota</span>
            </nav>
            <h2 class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic">Master <span class="text-blue-600">Jabatan</span></h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Search Input dengan Background Slate-100 --}}
            <div class="relative group flex-1 md:flex-none">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" x-model="search" placeholder="CARI DATA JABATAN..." 
                    class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
            </div>

            <div class="flex items-center gap-2">
                <button title="Refresh" class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
                <button x-show="selected.length > 0" x-transition title="Hapus Masal" class="p-3 bg-rose-50 text-rose-600 rounded-2xl shadow-sm border border-rose-200 hover:bg-rose-100 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                <button @click="showModal = true" class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    Tambah
                </button>
            </div>
        </div>
    </div>

    {{-- 2. Table Section (Latar Putih Bersih di atas Background Slate) --}}
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-8 py-6 w-10 text-center">
                            <input type="checkbox" @click="selectAll = !selectAll; selected = selectAll ? items.map(i => i.id) : []" 
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600/20">
                        </th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Jabatan</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Proses (Timestamp)</th>
                        <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="item in items" :key="item.id">
                        <tr class="group hover:bg-slate-50/50 transition-all duration-300">
                            <td class="px-8 py-5 text-center">
                                <input type="checkbox" :value="item.id" x-model="selected" 
                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600/20">
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 font-black text-[10px] border border-slate-200 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all duration-500" x-text="item.nama.substring(0,2)"></div>
                                    <div>
                                        <h4 class="text-sm font-black text-slate-800 uppercase italic tracking-tight" x-text="item.nama"></h4>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">NRP : <span class="text-slate-400 italic" x-text="item.nrp"></span></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2 text-slate-500">
                                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-[10px] font-bold italic" x-text="item.time"></span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-4 py-1 bg-slate-100 text-slate-500 rounded-full text-[9px] font-black uppercase tracking-widest border border-slate-200 group-hover:bg-blue-50 group-hover:text-blue-600 group-hover:border-blue-100 transition-all">
                                    UNDEFINED
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
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
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Modal Tambah (Kontras Overlay Slate-900) --}}
    <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>

            <div class="relative bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full border border-slate-100 overflow-hidden" x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-10">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter">Entry <span class="text-blue-600">Jabatan Baru</span></h3>
                        <button @click="showModal = false" class="text-slate-300 hover:text-slate-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form action="#" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Jabatan</label>
                            <input type="text" name="nama_jabatan" required placeholder="INPUT NAMA JABATAN..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500/50 outline-none uppercase transition-all">
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="showModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all border border-slate-200">Batal</button>
                            <button type="submit" class="flex-1 px-6 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Plugin Alpine --}}
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection