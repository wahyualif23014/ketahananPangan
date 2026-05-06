@extends('layouts.app')

@section('header', 'Pesan Komunikasi')

@section('content')
<div class="max-w-7xl mx-auto pb-24" x-data="pesanManager()">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-6">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">MANAJEMEN STRUKTUR</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-sky-600 drop-shadow-sm border-b-2 border-sky-600 pb-0.5">Pesan Komunikasi</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                PESAN <span class="bg-clip-text text-transparent bg-gradient-to-r from-sky-500 to-indigo-500">INTERNAL</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Sistem komunikasi internal antar pengguna aplikasi SIKAP PRESISI.</p>
        </div>
        <div class="flex gap-3">
            <button @click="isComposeOpen = true" class="flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 text-white rounded-2xl shadow-xl shadow-sky-500/30 hover:shadow-sky-500/50 hover:scale-105 active:scale-95 transition-all text-xs font-black uppercase tracking-widest border-sky-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Tulis Pesan
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mx-4 mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-2xl flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mx-4 mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-600 rounded-2xl flex items-center gap-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
    @endif

    <div class="mx-4 bg-white rounded-[2.5rem] border border-slate-200 shadow-2xl shadow-slate-200/50 overflow-hidden flex flex-col md:flex-row h-[700px]">
        
        <!-- Sidebar Messages -->
        <div class="w-full md:w-96 bg-slate-50 border-r border-slate-200 flex flex-col">
            <div class="p-6 border-b border-slate-200">
                <div class="flex items-center bg-slate-200/50 p-1 rounded-xl mb-3">
                    <button @click="tab = 'masuk'; activePesan = null" :class="tab === 'masuk' ? 'bg-white shadow-sm text-sky-600' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all">Pesan Masuk</button>
                    <button @click="tab = 'terkirim'; activePesan = null" :class="tab === 'terkirim' ? 'bg-white shadow-sm text-sky-600' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-2 text-[10px] font-black uppercase tracking-widest rounded-lg transition-all">Terkirim</button>
                </div>
                <button @click="markAllAsRead" x-show="tab === 'masuk'" class="w-full py-2 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-sky-600 bg-white hover:bg-sky-50 border border-slate-200 hover:border-sky-200 rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Tandai Semua Terbaca
                </button>
                
                <div x-show="selectedMessages.length > 0" class="flex gap-2 mt-2">
                    <button @click="deleteMultipleMessages" class="flex-1 py-1.5 text-[10px] font-black uppercase tracking-widest text-white bg-rose-500 hover:bg-rose-600 rounded-lg transition-all flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus (<span x-text="selectedMessages.length"></span>)
                    </button>
                    <button @click="selectedMessages = []" class="px-3 bg-slate-200 text-slate-600 hover:bg-slate-300 rounded-lg">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div x-show="selectedMessages.length === 0" class="flex gap-2 mt-2">
                    <button @click="selectAll" class="w-full py-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500 hover:text-sky-600 bg-white border border-slate-200 rounded-lg transition-all flex items-center justify-center gap-1">
                        Pilih Semua Pesan
                    </button>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto" x-show="tab === 'masuk'">
                @forelse($pesanMasuk as $pesan)
                <div @click="openMessage({{ $pesan->toJson() }}, 'masuk')" :class="activePesan?.id === {{ $pesan->id }} ? 'bg-sky-50 border-l-4 border-sky-500' : 'hover:bg-slate-100 border-l-4 border-transparent'" class="p-5 border-b border-slate-100 cursor-pointer transition-all">
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" value="{{ $pesan->id }}" x-model="selectedMessages" @click.stop class="w-3 h-3 text-sky-600 rounded border-slate-300 cursor-pointer">
                            <span class="font-bold text-slate-800 text-sm line-clamp-1">{{ $pesan->sender->nama_anggota }} {{ $pesan->sender->tingkat ? '- ' . $pesan->sender->tingkat->nama_tingkat : ($pesan->sender->role === 'admin' ? '- POLDA JATIM' : '') }}</span>
                            @if(!$pesan->is_read)
                            <span x-show="!readMessages.includes('{{ $pesan->id }}')" class="w-2 h-2 rounded-full bg-sky-500"></span>
                            @endif
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold flex-shrink-0">{{ $pesan->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 mb-1 line-clamp-1">{{ $pesan->judul ?? 'Tanpa Judul' }}</p>
                    <p class="text-[11px] text-slate-500 line-clamp-2">{{ Str::limit(strip_tags($pesan->isi_pesan), 100) }}</p>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-[11px] font-black uppercase tracking-widest">Tidak ada pesan masuk</p>
                </div>
                @endforelse
            </div>
            
            <div class="flex-1 overflow-y-auto" x-show="tab === 'terkirim'" style="display: none;">
                @forelse($pesanTerkirim as $pesan)
                <div @click="openMessage({{ $pesan->toJson() }}, 'terkirim')" :class="activePesan?.id === {{ $pesan->id }} ? 'bg-sky-50 border-l-4 border-sky-500' : 'hover:bg-slate-100 border-l-4 border-transparent'" class="p-5 border-b border-slate-100 cursor-pointer transition-all">
                    <div class="flex justify-between items-start mb-1">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" value="{{ $pesan->id }}" x-model="selectedMessages" @click.stop class="w-3 h-3 text-sky-600 rounded border-slate-300 cursor-pointer">
                            <span class="font-bold text-slate-800 text-sm line-clamp-1">Ke: {{ $pesan->recipient->nama_anggota }} {{ $pesan->recipient->tingkat ? '- ' . $pesan->recipient->tingkat->nama_tingkat : ($pesan->recipient->role === 'admin' ? '- POLDA JATIM' : '') }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold flex-shrink-0">{{ $pesan->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs font-semibold text-slate-600 mb-1 line-clamp-1">{{ $pesan->judul ?? 'Tanpa Judul' }}</p>
                    <p class="text-[11px] text-slate-500 line-clamp-2">{{ Str::limit(strip_tags($pesan->isi_pesan), 100) }}</p>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    <p class="text-[11px] font-black uppercase tracking-widest">Tidak ada pesan terkirim</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Main Message Area -->
        <div class="flex-1 bg-white flex flex-col items-center justify-center relative">
            <template x-if="!activePesan">
                <div class="text-center p-8">
                    <div class="w-24 h-24 bg-sky-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 transform -rotate-6">
                        <svg class="w-12 h-12 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight uppercase mb-2">Pilih Pesan</h3>
                    <p class="text-sm text-slate-400 font-medium">Silakan pilih pesan dari daftar di sebelah kiri untuk membacanya.</p>
                </div>
            </template>
            
            <template x-if="activePesan">
                <div class="w-full h-full flex flex-col absolute inset-0 bg-white">
                    <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight uppercase" x-text="activePesan.judul || 'Tanpa Judul'"></h3>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest" x-text="tab === 'masuk' ? 'Dari: ' + activePesan.sender.nama_anggota + (activePesan.sender.tingkat ? ' - ' + activePesan.sender.tingkat.nama_tingkat : (activePesan.sender.role === 'admin' ? ' - POLDA JATIM' : '')) : 'Kepada: ' + activePesan.recipient.nama_anggota + (activePesan.recipient.tingkat ? ' - ' + activePesan.recipient.tingkat.nama_tingkat : (activePesan.recipient.role === 'admin' ? ' - POLDA JATIM' : ''))"></span>
                                <span class="text-xs text-slate-400 font-medium" x-text="formatDate(activePesan.created_at)"></span>
                            </div>
                        </div>
                    </div>
                    <div class="p-8 flex-1 overflow-y-auto">
                        <div class="prose prose-slate max-w-none text-slate-600 whitespace-pre-wrap leading-relaxed" x-text="activePesan.isi_pesan"></div>
                    </div>
                    <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                        <button @click="deletePesan(activePesan)" class="flex items-center gap-2 px-5 py-2.5 bg-white text-rose-600 border border-rose-200 rounded-xl hover:bg-rose-50 transition-all text-[10px] font-black uppercase tracking-widest shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus
                        </button>
                        <button @click="replyPesan(activePesan)" x-show="tab === 'masuk'" class="flex items-center gap-2 px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-100 transition-all text-[10px] font-black uppercase tracking-widest shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                            Balas
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    
    <!-- Compose Modal -->
    <div x-show="isComposeOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="isComposeOpen = false" x-show="isComposeOpen" x-transition class="bg-white rounded-[2rem] shadow-2xl w-full max-w-3xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-8 py-5 bg-gradient-to-r from-sky-600 to-indigo-600 flex items-center justify-between">
                <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Tulis Pesan Baru
                </h3>
                <button @click="isComposeOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            @php
                $routeUrl = $role === 'admin' ? route('admin.pesan.store') : route('operator.pesan.store');
            @endphp
            
            <form action="{{ $routeUrl }}" method="POST" class="flex flex-col flex-1 overflow-y-auto">
                @csrf
                <div class="p-8 space-y-6">
                    <div class="relative" x-data="{ 
                        dropdownOpen: false, 
                        searchQuery: '', 
                        selectedText: '-- Pilih Penerima --',
                        options: [
                            @if($role === 'admin' || $isPolres)
                            { value: 'role_operator', label: '[ROLE] Semua Operator {{ $isPolres ? 'Jajaran' : '' }}' },
                            @if($role === 'admin')
                            { value: 'role_admin', label: '[ROLE] Semua Admin' },
                            { value: 'role_view', label: '[ROLE] Semua Viewer' },
                            @endif
                            @if($isPolres)
                            { value: 'role_admin', label: '[ROLE] Semua Admin' },
                            @endif
                            @endif
                            @foreach($anggotas as $anggota)
                            { value: '{{ $anggota->id_anggota }}', label: '{{ addslashes($anggota->nama_anggota) }} - {{ $anggota->role }}' },
                            @endforeach
                        ],
                        get filteredOptions() {
                            if (this.searchQuery === '') return this.options;
                            return this.options.filter(opt => opt.label.toLowerCase().includes(this.searchQuery.toLowerCase()));
                        },
                        selectOption(opt) {
                            this.composeData.recipient_id = opt.value;
                            this.selectedText = opt.label;
                            this.dropdownOpen = false;
                            this.searchQuery = '';
                        },
                        init() {
                            this.$watch('composeData.recipient_id', value => {
                                if (!value) {
                                    this.selectedText = '-- Pilih Penerima --';
                                } else {
                                    const found = this.options.find(o => o.value === value);
                                    if (found) this.selectedText = found.label;
                                }
                            });
                        }
                    }" @click.outside="dropdownOpen = false">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kepada</label>
                        
                        <!-- Hidden input for standard form submission -->
                        <input type="hidden" name="recipient_id" x-model="composeData.recipient_id" required>
                        
                        <!-- Select Trigger -->
                        <div @click="dropdownOpen = !dropdownOpen" class="w-full text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer flex justify-between items-center outline-none uppercase transition-all" :class="{'ring-4 ring-sky-500/10 border-sky-500': dropdownOpen}">
                            <span x-text="selectedText" :class="{'text-slate-400': !composeData.recipient_id}" class="truncate"></span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        
                        <!-- Dropdown Content -->
                        <div x-show="dropdownOpen" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden flex flex-col max-h-60" style="display: none;">
                            <div class="p-2 border-b border-slate-100 bg-slate-50/50">
                                <div class="relative">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input type="text" x-model="searchQuery" class="w-full text-xs font-bold bg-white border border-slate-200 rounded-lg pl-9 pr-3 py-2 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400 uppercase placeholder:normal-case placeholder:text-slate-400" placeholder="Cari penerima...">
                                </div>
                            </div>
                            <div class="overflow-y-auto flex-1 p-1">
                                <template x-if="filteredOptions.length === 0">
                                    <div class="p-4 text-center text-xs text-slate-400 font-bold uppercase tracking-widest">Tidak ada hasil</div>
                                </template>
                                
                                <template x-for="opt in filteredOptions" :key="opt.value">
                                    <div @click="selectOption(opt)" class="px-3 py-2 text-xs font-bold text-slate-700 hover:bg-sky-50 hover:text-sky-600 rounded-lg cursor-pointer uppercase transition-colors truncate" x-text="opt.label" :class="{'bg-sky-50 text-sky-600': composeData.recipient_id === opt.value}"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Judul Pesan</label>
                        <input type="text" name="judul" x-model="composeData.judul" class="w-full text-sm font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none" placeholder="Masukkan judul pesan...">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Isi Pesan</label>
                        <textarea name="isi_pesan" x-model="composeData.isi_pesan" rows="8" class="w-full text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-sky-500/10 focus:border-sky-500 outline-none" placeholder="Tuliskan isi pesan Anda di sini..." required></textarea>
                    </div>
                </div>
                <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="isComposeOpen = false" class="px-6 py-3 bg-white text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition-all text-xs font-black uppercase tracking-widest">Batal</button>
                    <button type="submit" class="px-6 py-3 bg-sky-600 text-white rounded-xl hover:bg-sky-700 transition-all text-xs font-black uppercase tracking-widest shadow-lg shadow-sky-500/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pesanManager', () => ({
        tab: 'masuk',
        activePesan: null,
        isComposeOpen: false,
        readMessages: [],
        selectedMessages: [],
        composeData: {
            recipient_id: '',
            judul: '',
            isi_pesan: ''
        },
        
        openMessage(pesan, currentTab) {
            this.activePesan = pesan;
            
            if (currentTab === 'masuk' && !pesan.is_read) {
                // Send read request
                const rolePath = '{{ $role === 'admin' ? '/admin' : '/operator' }}';
                fetch(rolePath + `/pesan/${pesan.id}/read`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    pesan.is_read = 1; // local update
                    if (!this.readMessages.includes(pesan.id)) {
                        this.readMessages.push(pesan.id.toString());
                    }
                });
            }
        },
        
        markAllAsRead() {
            const rolePath = '{{ $role === 'admin' ? '/admin' : '/operator' }}';
            fetch(rolePath + `/pesan/read-all`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            }).then(() => {
                window.location.reload();
            });
        },
        
        replyPesan(pesan) {
            this.composeData.recipient_id = pesan.sender_id;
            this.composeData.judul = 'Re: ' + (pesan.judul || 'Tanpa Judul');
            this.composeData.isi_pesan = '\n\n--- Membalas Pesan Sebelumnya ---\n' + pesan.isi_pesan;
            this.isComposeOpen = true;
        },
        
        deletePesan(pesan) {
            if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
                const rolePath = '{{ $role === 'admin' ? '/admin' : '/operator' }}';
                fetch(rolePath + `/pesan/${pesan.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Gagal menghapus pesan.');
                    }
                });
            }
        },
        
        selectAll() {
            // Collect IDs based on the active tab
            const checkboxes = document.querySelectorAll(`[x-show="tab === '${this.tab}'"] input[type="checkbox"]`);
            this.selectedMessages = Array.from(checkboxes).map(cb => cb.value);
        },
        
        deleteMultipleMessages() {
            if (this.selectedMessages.length === 0) return;
            if (confirm(`Apakah Anda yakin ingin menghapus ${this.selectedMessages.length} pesan terpilih?`)) {
                const rolePath = '{{ $role === 'admin' ? '/admin' : '/operator' }}';
                fetch(rolePath + `/pesan/delete-multiple`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: this.selectedMessages })
                }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Gagal menghapus pesan terpilih.');
                    }
                });
            }
        },
        
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', {
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }));
});
</script>
@endsection
