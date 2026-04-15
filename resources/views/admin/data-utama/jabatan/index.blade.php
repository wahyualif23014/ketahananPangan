@extends('layouts.app')

@section('header', 'Kelola Jabatan')

@section('content')
    <div x-data='{
            showModal: false,
            selectAll: false,
            selected: [],
            search: "",
            items: @json($jabatans),

            get filteredItems() {
                if (this.search === "") return this.items;
                return this.items.filter(i =>
                    i.nama_jabatan.toLowerCase().includes(this.search.toLowerCase())
                );
            },

            toggleAll() {
                this.selectAll = !this.selectAll;
                this.selected = this.selectAll ? this.filteredItems.map(i => i.id_jabatan) : [];
            }
        }' class="space-y-6 pb-24 font-sans" style="font-family: " Ramabhadra", sans-serif;">

        {{-- 1. Toolbar Section --}}
        <div class="space-y-8 pb-20 antialiased text-slate-900"
            style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">
            <div
                class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 gap-2 px-4 transition-all mb-10 duration-700 animate-in fade-in slide-in-from-top-4">
                <div>
                    <nav class="flex items-center gap-2  font-medium text-slate-500 mb-1">
                        <span>Data Utama</span>
                        <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-blue-600 ">Jabatan Anggota</span>
                    </nav>
                    <h2 class="text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">Data
                        <span class="text-blue-500 font-normal">Jabatan</span>
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="search" placeholder="CARI DATA JABATAN..."
                            class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none uppercase">
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="window.location.reload()" title="Refresh"
                            class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </button>
                        <button @click="showModal = true"
                            class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Tambah
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. Data Table --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="w-12 pl-8 py-6 text-center">
                                    <input type="checkbox" @click="toggleAll()" :checked="selectAll"
                                        class="w-4 h-4 rounded border-slate-300 text-blue-600">
                                </th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama
                                    Jabatan</th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">ID
                                </th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Tgl
                                    Dibuat</th>
                                <th
                                    class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredItems" :key="item.id_jabatan">
                                <tr class="group hover:bg-slate-50 transition-all duration-200">
                                    <td class="w-12 pl-8 py-5 text-center">
                                        <input type="checkbox" :value="item.id_jabatan" x-model="selected"
                                            class="w-4 h-4 rounded border-slate-300 text-blue-600">
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px] border border-slate-200 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500"
                                                x-text="item.nama_jabatan.substring(0,2)"></div>
                                            <h4 class="text-[13px] font-black text-slate-800 uppercase italic tracking-tight group-hover:text-blue-600"
                                                x-text="item.nama_jabatan"></h4>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span
                                            class="text-[11px] font-black text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg"
                                            x-text="'#' + item.id_jabatan"></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-[10px] font-bold text-slate-400 italic"
                                            x-text="item.created_at_formatted || '-'"></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div
                                            class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                            <button
                                                class="p-2 bg-white text-blue-500 rounded-lg border border-slate-200 hover:bg-blue-600 hover:text-white shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button
                                                class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection