@extends('layouts.app')

@section('header', 'Kelola Jabatan')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

    .jabatan-container {
        font-family: 'Outfit', sans-serif;
    }

    [x-cloak] {
        display: none !important;
    }

    .topo-pattern {
        background-color: transparent;
        background-image: radial-gradient(#3b82f6 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.1;
    }
</style>

<div class="space-y-8 pb-24 jabatan-container max-w-7xl mx-auto" x-data="jabatanApp()">

    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-blue-600 drop-shadow-sm border-b-2 border-blue-600 pb-0.5">Daftar Jabatan</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                POSISI <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-500 to-indigo-500">JABATAN</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Struktur pengorganisasian peran dan posisi anggota.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" x-model="searchQuery" placeholder="CARI JABATAN..." 
                    class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none uppercase shadow-sm">
            </div>
            
            <button onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-white text-blue-600 rounded-2xl shadow-sm hover:shadow-md border border-slate-200 hover:bg-slate-50 transition-all duration-300 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>

            <button @click="openModal('add')" 
                class="flex items-center gap-2 px-6 py-3.5 bg-slate-900 text-white rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700 text-xs font-black uppercase tracking-widest">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="px-4 mb-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl shadow-sm flex items-center gap-4 animate-in fade-in slide-in-from-top-4 relative z-50">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm tracking-wide">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="px-4 mb-4">
        <div class="bg-rose-50 border border-rose-200 text-rose-600 px-6 py-4 rounded-2xl shadow-sm flex flex-col gap-2 animate-in fade-in slide-in-from-top-4 relative z-50">
            <div class="flex items-center gap-4">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold text-sm tracking-wide">Terdapat beberapa kesalahan saat menambahkan data:</span>
            </div>
            <ul class="list-disc list-inside text-xs font-semibold ml-10">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Stats Dashboard --}}
    <div class="grid grid-cols-1 relative px-2 max-w-2xl mt-4">
        <div class="absolute inset-0 bg-slate-100 rounded-[3rem] -z-10 transform scale-y-110 scale-x-105"></div>
        <div class="absolute inset-0 topo-pattern -z-10 rounded-[3rem]"></div>

        <div class="group relative bg-white p-6 md:p-8 rounded-[2rem] border border-blue-100 shadow-xl shadow-blue-900/5 hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-900/10 transition-all duration-500 overflow-hidden flex items-center justify-between">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700 ease-in-out opacity-60"></div>
            <div class="relative z-10 flex flex-col justify-center">
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.3em] mb-1">TOTAL DATA</p>
                <h3 class="text-2xl lg:text-3xl font-black text-slate-800 uppercase italic leading-none">
                    Terdapat <span class="text-blue-500 text-3xl lg:text-4xl mx-1" x-data="{ count: 0 }" x-init="let end = {{ count($jabatans) }}; let duration = 1000; let start = null; let step = timestamp => { if (!start) start = timestamp; let progress = Math.min((timestamp - start) / duration, 1); count = Math.floor(progress * end); if (progress < 1) window.requestAnimationFrame(step); }; window.requestAnimationFrame(step);" x-text="count">0</span>
                    Posisi Jabatan
                </h3>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-600 text-white rounded-[1.2rem] flex items-center justify-center transform group-hover:rotate-[15deg] group-hover:scale-110 transition-all duration-500 shadow-lg shadow-blue-500/30 relative z-10 flex-shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
            </div>
        </div>
    </div>


    {{-- Main List (Premium Style) --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-2 mt-12 bg-clip-padding backdrop-filter backdrop-blur-3xl bg-opacity-70">
        
        <!-- Header Panel -->
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-1.5 h-8 bg-blue-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">DAFTAR KESATUAN JABATAN</h3>
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-blue-400 bg-blue-400/20 px-3 py-1.5 rounded-lg border border-blue-400/30">
                PENGELOLAAN POSISI
            </div>
        </div>

        <!-- List Data -->
        <div class="divide-y divide-slate-100/80">
            <template x-for="item in filteredItems" :key="item.id_jabatan">
                <div class="group/parent transition-all duration-300 hover:bg-slate-50/50">
                     
                    <div class="p-5 md:px-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start md:items-center gap-5">
                            
                            <!-- Icon Label -->
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-50 text-blue-500 border border-blue-100 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover/parent:bg-blue-500 group-hover/parent:text-white group-hover/parent:shadow-md group-hover/parent:shadow-blue-500/40">
                                <span class="font-black text-lg uppercase tracking-tight" x-text="(item.nama_jabatan || 'NA').substring(0,2)"></span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-lg md:text-xl font-black text-slate-800 uppercase tracking-tight group-hover/parent:text-blue-600 transition-colors" x-text="item.nama_jabatan"></h3>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-400 tracking-wider">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span x-text="'DITAMBAHKAN: ' + (item.created_at_formatted || 'TIDAK DIKETAHUI')"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-4 md:pl-0 pl-16">
                            <div class="bg-slate-50 border border-slate-100 text-slate-500 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-[0.2em] shadow-inner">
                                <span x-text="'#' + item.id_jabatan"></span>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button @click="openModal('edit', item)" class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white hover:shadow-md hover:shadow-indigo-500/30 transition-all active:scale-95 group/btn">
                                    <svg class="w-4 h-4 group-hover/btn:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button @click="openModal('delete', item)" class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white hover:shadow-md hover:shadow-rose-500/30 transition-all active:scale-95 group/btn">
                                    <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Error State Empty -->
            <div x-show="filteredItems.length === 0" style="display: none;" class="text-center py-20 px-4">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-100 rounded-full mb-6">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-800 uppercase mb-2">Tidak Ditemukan</h3>
                <p class="text-sm font-semibold text-slate-500 uppercase tracking-widest max-w-sm mx-auto">Data jabatan kosong atau pencarian tidak cocok.</p>
            </div>
        </div>
    </div>


    {{-- Universal Modal Component (AlpineJS) --}}
    <div x-show="modalMode !== null" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-modal="true">
        <div x-show="modalMode !== null" x-transition.opacity.duration.300ms @click="closeModal()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        
        <div x-show="modalMode !== null" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-[2rem] shadow-2xl shadow-blue-900/20 w-full max-w-lg relative z-10 flex flex-col overflow-hidden border border-slate-100">
            
            <!-- Form Connected via Alpine getFormAction() -->
            <form :action="getFormAction()" method="POST">
                @csrf
                <input type="hidden" name="_method" x-bind:value="getFormMethod()">
                <!-- ID Jabatan dikirim hidden hanya saat edit/delete karena input text nya didisable. Saat add, dikirim dari input text di bawah -->
                <template x-if="modalMode === 'edit' || modalMode === 'delete'">
                    <input type="hidden" name="id_jabatan" x-model="formData.id_jabatan">
                </template>
                
                <div class="px-8 py-5 border-b border-slate-100" :class="modalMode === 'delete' ? 'bg-rose-50' : 'bg-slate-50'">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-inner"
                                 :class="modalMode === 'delete' ? 'bg-rose-500' : (modalMode === 'edit' ? 'bg-indigo-500' : 'bg-blue-500')">
                                <template x-if="modalMode === 'add'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg></template>
                                <template x-if="modalMode === 'edit'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></template>
                                <template x-if="modalMode === 'delete'"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></template>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold tracking-widest uppercase text-slate-400">Panel Kelola</p>
                                <h3 class="text-xl font-black text-slate-800 uppercase" x-text="getModalTitle()"></h3>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 bg-white hover:bg-slate-200 p-2 rounded-xl transition-colors border border-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="px-8 py-6 space-y-5">
                    <template x-if="modalMode === 'delete'">
                        <div class="bg-white border-2 border-rose-200 rounded-2xl p-5 text-center shadow-sm">
                            <h4 class="text-lg font-bold text-slate-800">Menghapus Jabatan?</h4>
                            <p class="text-sm text-slate-500 font-medium mt-1 mb-3">Tindakan ini akan menghapus (<span class="text-rose-600 font-bold" x-text="formData.nama_jabatan"></span>).</p>
                        </div>
                    </template>

                    <template x-if="modalMode !== 'delete'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">ID Jabatan <span class="text-rose-500">*</span></label>
                                <input type="number" name="id_jabatan" x-model="formData.id_jabatan" required placeholder="Contoh: 1, 2, 3" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 text-sm text-slate-800 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder:font-normal uppercase tracking-wide"
                                    :readonly="modalMode === 'edit'" :class="modalMode === 'edit' ? 'cursor-not-allowed opacity-70' : ''">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Nama Jabatan <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_jabatan" x-model="formData.nama_jabatan" required placeholder="Contoh: KANIT, BABINKAMTIBMAS" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 text-sm text-slate-800 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder:font-normal uppercase tracking-wide">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button type="button" @click="closeModal()" class="flex-1 bg-white hover:bg-slate-100 text-slate-600 font-bold py-3 rounded-xl transition-colors border border-slate-200 uppercase tracking-widest text-xs">
                        Batal
                    </button>
                    <button type="submit" 
                        class="flex-1 text-white font-bold py-3 rounded-xl shadow-md transition-colors uppercase tracking-widest text-xs"
                        :class="modalMode === 'delete' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' : (modalMode === 'edit' ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/30' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/30')"
                        x-text="getSubmitText()">
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function jabatanApp() {
        return {
            items: @json($jabatans),
            searchQuery: '',
            modalMode: null,
            formData: {
                id_jabatan: '',
                nama_jabatan: ''
            },
            
            get filteredItems() {
                if (this.searchQuery === "") return this.items;
                return this.items.filter(i =>
                    (i.nama_jabatan || "").toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    (i.id_jabatan || "").toString().includes(this.searchQuery)
                );
            },
            
            openModal(mode, data = null) {
                this.modalMode = mode;
                if (mode === 'add') {
                    this.formData = { id_jabatan: '', nama_jabatan: '' };
                } else if (data) {
                    this.formData = { 
                        id_jabatan: data.id_jabatan, 
                        nama_jabatan: data.nama_jabatan 
                    };
                }
            },
            
            closeModal() {
                this.modalMode = null;
            },
            
            getModalTitle() {
                if (this.modalMode === 'add') return 'Tambah Jabatan';
                if (this.modalMode === 'edit') return 'Edit Jabatan';
                if (this.modalMode === 'delete') return 'Hapus Jabatan';
                return '';
            },

            getSubmitText() {
                if (this.modalMode === 'add') return 'Simpan Baru';
                if (this.modalMode === 'edit') return 'Perbarui';
                if (this.modalMode === 'delete') return 'Konfirmasi';
                return '';
            },

            getFormAction() {
                if (this.modalMode === 'add') return "{{ route('admin.jabatan.store') }}";
                if (this.modalMode === 'edit' && this.formData.id_jabatan) {
                    return "{{ url('/admin/data-utama/jabatan') }}/" + this.formData.id_jabatan;
                }
                if (this.modalMode === 'delete' && this.formData.id_jabatan) {
                    return "{{ url('/admin/data-utama/jabatan') }}/" + this.formData.id_jabatan;
                }
                return "#";
            },
            
            getFormMethod() {
                if (this.modalMode === 'add') return "POST";
                if (this.modalMode === 'edit') return "PUT";
                if (this.modalMode === 'delete') return "DELETE";
                return "POST";
            }
        }
    }
</script>
@endsection