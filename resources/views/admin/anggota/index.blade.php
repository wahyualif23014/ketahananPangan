@extends('layouts.app')

@section('content')
    @php
        $groupedPersonels = $personels->groupBy(function ($item) {
            return $item->kesatuan ?? 'PUSAT DATA POLDA JATIM';
        });
    @endphp

    <div x-data="anggotaPage" class="space-y-8 pb-12 antialiased text-slate-800">

        {{-- 1. Toolbar & Header Section --}}
        <div class="space-y-8 pb-4 antialiased text-slate-900" style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-4 transition-all duration-700 animate-in fade-in slide-in-from-top-4">
                <div class="space-y-1">
                    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                        <span>Data Utama</span>
                        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <span class="text-emerald-600">Data Personel</span>
                    </nav>
                    <div class="flex items-center gap-3">
                        <h1 class="text-4xl lg:text-4xl font-semibold text-slate-900 tracking-tight">
                            Data <span class="text-emerald-500 font-normal">Personel</span>
                        </h1>
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100">
                            {{ $personels->count() }} Anggota
                        </span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full sm:w-80">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="search" placeholder="Cari personel..."
                            class="block w-full h-12 pl-11 pr-4 bg-white border border-slate-200 rounded-2xl text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all duration-200">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button @click="window.location.reload()"
                            class="flex items-center justify-center h-12 w-12 bg-white border border-slate-200 text-slate-500 rounded-2xl hover:bg-slate-50 hover:text-emerald-600 transition-all active:scale-95 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>

                        {{-- Bulk Delete Button --}}
                        <button x-show="selected.length > 0" x-cloak x-transition
                            @click="bulkDelete()"
                            class="flex items-center justify-center h-12 px-5 bg-rose-50 border border-rose-100 text-rose-600 rounded-2xl hover:bg-rose-100 transition-all shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <span class="text-sm font-bold">Hapus (<span x-text="selected.length"></span>)</span>
                        </button>

                        {{-- Add Data Button --}}
                        <button @click="openModal('add')"
                            class="flex-1 sm:flex-none flex items-center justify-center h-12 px-6 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition-all active:scale-95 shadow-lg shadow-emerald-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span class="text-sm font-bold whitespace-nowrap">Tambah Data</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Data Table Section --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden" :class="isLoading ? 'opacity-50 pointer-events-none' : ''">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="w-10 pl-8 pr-0 py-6 text-center">
                                <input type="checkbox" @click="toggleAll()" :checked="isAllSelected"
                                    class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </th>
                            <th class="px-4 py-6 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Nama Personel</th>
                            <th class="px-6 py-6 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Kontak</th>
                            <th class="px-6 py-6 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Jabatan</th>
                            <th class="px-6 py-6 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Role</th>
                            <th class="px-6 py-6 text-[11px] font-bold text-slate-500 uppercase tracking-widest">Proses</th>
                            <th class="px-8 py-6 text-right text-[11px] font-bold text-slate-500 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>

                    @forelse($groupedPersonels as $unit => $members)
                        <tbody x-data="{ expanded: true }" class="divide-y divide-slate-100">
                            <tr class="bg-slate-50/50 hover:bg-slate-100/50 cursor-pointer transition-colors border-y border-slate-200"
                                @click="expanded = !expanded">
                                <td colspan="6" class="px-8 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 text-slate-400 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                        <span class="text-[11px] font-black text-slate-600 uppercase tracking-[0.2em] italic">{{ $unit }}</span>
                                        <span class="px-2 py-0.5 bg-white rounded text-[9px] font-bold text-slate-400 border border-slate-200">{{ $members->count() }} DATA</span>
                                    </div>
                                </td>
                            </tr>

                            @foreach($members as $p)
                                <tr x-show="expanded" x-collapse class="group hover:bg-blue-50/30 transition-all duration-200">
                                    <td class="w-10 pl-8 pr-0 py-5 text-center">
                                        <input type="checkbox" :value="{{ $p->id_anggota }}" x-model="selected"
                                            class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    </td>
                                    <td class="px-4 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm group-hover:scale-110 transition-transform duration-300">
                                                {{ substr($p->nama_anggota, 0, 1) }}
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-slate-800 tracking-tight group-hover:text-blue-600 transition-colors italic">
                                                    {{ $p->nama_anggota }}</h4>
                                                <p class="text-[10px] font-bold text-blue-500 uppercase tracking-tighter">NRP :
                                                    <span class="text-slate-400 italic">{{ $p->username }}</span></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-xs font-semibold text-slate-500 uppercase tracking-tighter">
                                        {{ $p->no_telp_anggota ?? '-' }}</td>
                                    <td class="px-6 py-5 text-xs font-bold text-slate-700 uppercase italic">
                                        {{ $p->jabatan->nama_jabatan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold uppercase tracking-widest border border-blue-100 group-hover:bg-white transition-colors">
                                            {{ $p->role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-bold uppercase tracking-widest border border-blue-100 group-hover:bg-white transition-colors">
                                            {{-- proses --}}
                                            {{ $p->username }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                            {{-- Trigger Edit Modal --}}
                                            <button @click="openModal('edit', {{ json_encode($p) }})"
                                                class="p-2 bg-white text-blue-500 rounded-lg border border-slate-200 hover:bg-blue-600 hover:text-white shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </button>
                                            <button @click="deleteItem({{ $p->id_anggota }})"
                                                class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 00-2 2H6a2 2 0 00-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Data tidak ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
        </div>

        {{-- 3. Modal Form Section --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-[99] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:block sm:p-0">
        <div x-show="showModal" 
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
            class="fixed inset-0 transition-opacity bg-slate-500/30" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showModal" 
            x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2 sm:scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
            x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-2 sm:scale-[0.98]" 
            class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl border border-slate-200 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-900" id="modal-title">
                    Data Personel Kepolisian
                </h3>
            </div>
            <div class="px-6 py-5 space-y-4">
                
                {{-- Field: Nama Personel --}}
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input type="text" x-model="formData.nama_anggota" placeholder="Contoh: Briptu John Doe"
                        class="w-full h-9 px-3 bg-white border border-slate-300 rounded-md text-sm text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">NRP</label>
                        <input type="text" x-model="formData.username" placeholder="Nomor Registrasi"
                            class="w-full h-9 px-3 bg-white border border-slate-300 rounded-md text-sm text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                    </div>
                    {{-- Field: Kontak --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Nomor Telepon</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-2.5 rounded-l-md border border-r-0 border-slate-300 bg-slate-50 text-slate-500 text-xs">
                                +62
                            </span>
                            <input type="text" x-model="formData.no_telp_anggota"
                                class="w-full h-9 px-3 border border-slate-300 rounded-r-md text-sm text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                        </div>
                    </div>
                </div>

                {{-- Field: Satuan --}}
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-slate-700">Satuan Kerja</label>
                    <select x-model="formData.id_kesatuan" class="w-full h-9 px-3 bg-white border border-slate-300 rounded-md text-sm text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                        <option value="">Pilih Kesatuan</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Jabatan</label>
                        <select x-model="formData.id_jabatan" class="w-full h-9 px-3 bg-white border border-slate-300 rounded-md text-sm text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            <option value="">Pilih Jabatan</option>
                        </select>
                    </div>

                    {{-- Field: Akses Sebagai --}}
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium text-slate-700">Hak Akses</label>
                        <select x-model="formData.role" class="w-full h-9 px-3 bg-white border border-slate-300 rounded-md text-sm text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            <option value="admin">Admin System</option>
                            <option value="operator">Operator</option>
                            <option value="view">Viewer</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Modal Footer: Standard buttons, no heavy shadows --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                <button @click="closeModal()" class="h-9 px-4 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button @click="submitForm()" class="h-9 px-4 text-sm font-medium text-white bg-slate-900 rounded-md hover:bg-slate-800 transition-colors">
                    <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Personel'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Script Alpine --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('anggotaPage', () => ({
                selected: [],
                search: '',
                isLoading: false,
                showModal: false,
                isEdit: false,
                allIds: @json($personels->pluck("id_anggota")),
                
                // Form State
                formData: {
                    id_anggota: null,
                    nama_anggota: '',
                    username: '',
                    id_kesatuan: '',
                    id_jabatan: '',
                    no_telp_anggota: '',
                    role: ''
                },

                get isAllSelected() {
                    return this.selected.length === this.allIds.length && this.allIds.length > 0;
                },

                toggleAll() {
                    this.selected = this.isAllSelected ? [] : [...this.allIds];
                },

                // Modal Controls
                openModal(mode, data = null) {
                    this.isEdit = (mode === 'edit');
                    if (this.isEdit && data) {
                        this.formData = { ...data };
                    } else {
                        this.resetForm();
                    }
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    setTimeout(() => this.resetForm(), 300);
                },

                resetForm() {
                    this.formData = {
                        id_anggota: null,
                        nama_anggota: '',
                        username: '',
                        id_kesatuan: '',
                        id_jabatan: '',
                        no_telp_anggota: '',
                        role: ''
                    };
                },

                // CRUD Placeholder (Silahkan dihubungkan ke backend nanti)
                async submitForm() {
                    this.isLoading = true;
                    console.log('Submit Data:', this.formData);
                    
                    // Simulasi delay
                    setTimeout(() => {
                        this.isLoading = false;
                        this.closeModal();
                    }, 1000);
                },

                async deleteItem(id) {
                    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        console.log('Delete ID:', id);
                    }
                },

                async bulkDelete() {
                    if (confirm(`Hapus ${this.selected.length} data terpilih?`)) {
                        console.log('Bulk Delete IDs:', this.selected);
                    }
                }
            }))
        })
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar for Modal */
        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
    </style>

    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection