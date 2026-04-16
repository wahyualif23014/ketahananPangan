@extends('layouts.app')

@section('header', 'Kelola Jabatan')

@section('content')
    {{-- Container Utama --}}
    <div x-data="jabatanPage" class="space-y-6 pb-24 font-sans" style="font-family: 'Inter', sans-serif;">

        {{-- 1. Toolbar Section --}}
        <div class="space-y-8 pb-20 antialiased text-slate-900">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-4 transition-all mb-10 duration-700 animate-in fade-in slide-in-from-top-4">
                <div>
                    <nav class="flex items-center gap-2 font-medium text-slate-500 mb-1">
                        <span>Data Utama</span>
                        <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-blue-600">Jabatan Anggota</span>
                    </nav>
                    <h2 class="text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">
                        Data <span class="text-blue-500 font-normal">Jabatan</span>
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="search" placeholder="CARI DATA JABATAN..."
                            class="block w-full md:w-64 pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-[11px] font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none uppercase">
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Hapus Massal --}}
                        <button x-show="selected.length > 0" x-cloak x-transition @click="bulkDelete()"
                            class="flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-2xl shadow-lg shadow-rose-500/20 hover:bg-rose-600 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus (<span x-text="selected.length"></span>)
                        </button>

                        <button @click="window.location.reload()" title="Refresh"
                            class="p-3 bg-white text-emerald-600 rounded-2xl shadow-sm border border-slate-200 hover:bg-emerald-50 transition-all active:scale-90">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>

                        {{-- Tombol Tambah --}}
                        <button @click="openModal('add')"
                            class="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all active:scale-95 text-[11px] font-black uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. Data Table --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-xl shadow-slate-200/40 overflow-hidden"
                :class="isLoading ? 'opacity-50 pointer-events-none' : ''">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="w-12 pl-8 py-6 text-center">
                                    <input type="checkbox" @click="toggleAll()" :checked="isAllSelected"
                                        class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Nama Jabatan</th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">ID</th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Tgl Dibuat</th>
                                <th class="px-8 py-6 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredItems" :key="item.id_jabatan">
                                <tr class="group hover:bg-slate-50 transition-all duration-200">
                                    <td class="w-12 pl-8 py-5 text-center">
                                        <input type="checkbox" :value="item.id_jabatan" x-model="selected"
                                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px] border border-slate-200 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500"
                                                x-text="item.nama_jabatan.substring(0,2)"></div>
                                            <h4 class="text-[13px] font-black text-slate-800 uppercase tracking-tight group-hover:text-blue-600"
                                                x-text="item.nama_jabatan"></h4>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-[11px] font-black text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg"
                                            x-text="'#' + item.id_jabatan"></span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="text-[10px] font-bold text-slate-400 italic"
                                            x-text="item.created_at_formatted || '-'"></span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                            {{-- Tombol Edit --}}
                                            <button @click="openModal('edit', item)"
                                                class="p-2 bg-white text-blue-500 rounded-lg border border-slate-200 hover:bg-blue-600 hover:text-white shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M16.5 3.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            {{-- Tombol Hapus --}}
                                            <button @click="deleteItem(item.id_jabatan)"
                                                class="p-2 bg-white text-rose-500 rounded-lg border border-slate-200 hover:bg-rose-600 hover:text-white shadow-sm transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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

        {{-- 3. Modal Form (Add / Edit) --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-[99] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="inline-block align-middle bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-8 group">
                    
                    <div class="mb-8">
                        <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tight italic" x-text="isEdit ? 'Ubah Jabatan' : 'Tambah Jabatan'"></h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Silahkan lengkapi informasi dibawah ini</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Nama Jabatan</label>
                            <input type="text" x-model="formData.nama_jabatan" placeholder="Masukan nama jabatan..."
                                class="w-full px-5 py-4 bg-slate-100 border-none rounded-2xl text-sm font-bold text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-10">
                        <button @click="closeModal()" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                        <button @click="submitForm()" :disabled="isLoading"
                            class="flex-1 px-6 py-4 bg-blue-600 text-white rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                            <template x-if="!isLoading">
                                <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Data'"></span>
                            </template>
                            <template x-if="isLoading">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Alpine.js Logic --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('jabatanPage', () => ({
                showModal: false,
                isEdit: false,
                selected: [],
                search: '',
                isLoading: false,
                items: @json($jabatans),

                // Form Data State
                formData: {
                    id_jabatan: null,
                    nama_jabatan: ''
                },

                get filteredItems() {
                    if (this.search === '') return this.items;
                    return this.items.filter(i => 
                        i.nama_jabatan.toLowerCase().includes(this.search.toLowerCase())
                    );
                },

                get isAllSelected() {
                    return this.filteredItems.length > 0 && this.selected.length === this.filteredItems.length;
                },

                toggleAll() {
                    this.selected = this.isAllSelected ? [] : this.filteredItems.map(i => i.id_jabatan);
                },

                // Modal Control
                openModal(mode, item = null) {
                    this.isEdit = (mode === 'edit');
                    if (this.isEdit && item) {
                        this.formData = { 
                            id_jabatan: item.id_jabatan, 
                            nama_jabatan: item.nama_jabatan 
                        };
                    } else {
                        this.formData = { id_jabatan: null, nama_jabatan: '' };
                    }
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.formData = { id_jabatan: null, nama_jabatan: '' };
                },

                // Submit logic (Create & Update)
                async submitForm() {
                    if (!this.formData.nama_jabatan) return alert('Nama jabatan wajib diisi');
                    
                    this.isLoading = true;
                    try {
                        // Tentukan URL dan Method
                        const url = this.isEdit 
                            ? `{{ url('admin/data-utama/jabatan') }}/${this.formData.id_jabatan}` 
                            : `{{ url('admin/data-utama/jabatan') }}`; // Sesuaikan jika route store berbeda
                        
                        const method = this.isEdit ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const res = await response.json();

                        if (response.ok) {
                            if (this.isEdit) {
                                // Sync array lokal (Update baris)
                                const index = this.items.findIndex(i => i.id_jabatan === this.formData.id_jabatan);
                                if (index !== -1) this.items[index].nama_jabatan = this.formData.nama_jabatan;
                            } else {
                                // Reload atau push data baru ke array jika Anda return data dari controller
                                window.location.reload(); 
                            }
                            this.closeModal();
                        } else {
                            alert(res.message || 'Gagal memproses data');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan sistem');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async deleteItem(id) {
                    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
                    await this.performDelete([id]);
                },

                async bulkDelete() {
                    if (!confirm(`Hapus ${this.selected.length} data yang dipilih?`)) return;
                    await this.performDelete(this.selected);
                    this.selected = [];
                },

                async performDelete(ids) {
                    this.isLoading = true;
                    try {
                        const response = await fetch('{{ route("admin.jabatan.batch-delete") }}', { 
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ ids })
                        });

                        if (response.ok) {
                            this.items = this.items.filter(item => !ids.includes(item.id_jabatan));
                        } else {
                            const err = await response.json();
                            alert(err.message || 'Gagal menghapus data');
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        alert('Terjadi kesalahan koneksi.');
                    } finally {
                        this.isLoading = false;
                    }
                }
            }))
        })
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
@endsection