@extends('layouts.app')

@section('header', 'Laporan Rekapitulasi Produksi')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    
    .rekapitulasi-container {
        font-family: 'Outfit', sans-serif;
    }
    
    [x-cloak] {
        display: none !important;
    }

    .topo-pattern {
        background-color: transparent;
        background-image: radial-gradient(#10b981 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.1;
    }
</style>

<div class="space-y-8 pb-24 rekapitulasi-container max-w-7xl mx-auto">

    {{-- [SEC 1] - HEADER SECTION --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 px-4 mb-2 transition-all duration-700 animate-in fade-in slide-in-from-top-8">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2">
                <span class="text-[10px] border-b-2 border-slate-300 pb-0.5">REKAPITULASI DATA</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-[10px] text-emerald-600 drop-shadow-sm border-b-2 border-emerald-600 pb-0.5">Produksi Lahan</span>
            </nav>
            <h2 class="text-3xl lg:text-5xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                LAPORAN <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-500 to-indigo-500">REKAPITULASI</span>
            </h2>
            <p class="mt-3 text-sm text-slate-500 font-medium max-w-lg">Rekapitulasi total data produksi lahan di berbagai wilayah.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('admin.rekapitulasi.index') }}" method="GET" id="form-filter" class="flex items-center gap-2">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text"
                        name="search"
                        id="search-input"
                        value="{{ request('search') }}"
                        placeholder="CARI LOKASI..."
                        class="block w-full md:w-72 pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[11px] font-black tracking-wider text-slate-700 placeholder-slate-400 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none uppercase shadow-sm">
                </div>

                @foreach(request()->except('search') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            <button type="button" onclick="window.location.reload()" title="Refresh Data"
                class="p-3.5 bg-white text-emerald-600 rounded-2xl shadow-sm hover:shadow-md border border-slate-200 hover:bg-slate-50 transition-all duration-300 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <div x-data="{ showExportModal: false }">
                <button type="button"
                    @click="showExportModal = true"
                    class="flex items-center gap-2 px-6 py-3.5 bg-slate-900 text-white rounded-2xl shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all duration-300 active:scale-95 border border-slate-700 text-xs font-black uppercase tracking-widest">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export
                </button>

                    <div x-show="showExportModal"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                        style="display: none;">

                        <div @click.away="showExportModal = false"
                            class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200">

                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                                <h3 class="text-lg font-bold text-slate-800 uppercase tracking-tight">Form Eksport Data</h3>
                            </div>

                            <form action="{{ route('admin.rekapitulasi.export') }}" method="GET" @submit="showExportModal = false">
                                @foreach(request()->query() as $key => $value)
                                @if($key !== 'mode')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                                @endforeach

                                <div class="p-6 space-y-4">
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pilih Mode Laporan:</p>

                                    <div class="space-y-3">
                                        @php
                                        $modes = [
                                        '1' => 'MODE 1 (REKAPITULASI DATA PRODUKSI LAHAN PER-WILAYAH)',
                                        '2' => 'MODE 2 (REKAPITULASI TOTAL DATA PRODUKSI LAHAN PER-POLSEK)',
                                        '3' => 'MODE 3 (PERINCIAN DATA PRODUKSI LAHAN)',
                                        '4' => 'MODE 4 (REKAPITULASI DATA POTENSI DAN TANAM PER-POLRES)'
                                        ];
                                        @endphp

                                        @foreach($modes as $val => $label)
                                        <label class="flex items-center p-3 rounded-xl border border-slate-100 hover:bg-emerald-50/50 hover:border-emerald-200 transition-all cursor-pointer group">
                                            <input type="radio" name="mode" value="{{ $val }}" {{ $val == '1' ? 'checked' : '' }}
                                                class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                            <span class="ml-3 text-sm font-medium text-slate-700 group-hover:text-emerald-700">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end space-x-3">
                                    <button type="button" @click="showExportModal = false"
                                        class="px-5 py-2 text-sm font-bold text-white bg-slate-700 hover:bg-slate-800 rounded-lg transition-colors flex items-center">
                                        BATAL <span class="ml-2">✕</span>
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 text-sm font-bold text-slate-900 bg-amber-400 hover:bg-amber-500 rounded-lg transition-colors shadow-sm flex items-center">
                                        EKSPORT <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 2v3a1 1 0 001 1h3m-3 7a1 1 0 11-2 0 1 1 0 012 0zm-3-1a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- [SEC 3] - FILTER SECTION --}}
    <form action="{{ url()->current() }}" method="GET" class="mx-2 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-xl shadow-slate-200/50 mb-12 relative z-20 bg-clip-padding backdrop-filter backdrop-blur-3xl bg-opacity-70 overflow-hidden" x-data="{ open: true }">
        <div class="px-8 py-5 border-b border-slate-100/80 flex flex-wrap items-center justify-between bg-gradient-to-r from-slate-50 to-white gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <span class="text-[11px] font-black text-slate-700 uppercase tracking-widest">Parameter Laporan</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-md shadow-emerald-500/20 active:scale-95">
                    Terapkan
                </button>
                <button type="button" @click="open = !open" class="text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-4 py-2.5 rounded-xl border border-emerald-100 transition-all active:scale-95">
                    <span x-text="open ? 'Sembunyikan' : 'Tampilkan'"></span>
                </button>
            </div>
        </div>

        <div x-show="open" x-collapse class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Kategori Lokasi — Polres + Polsek cascading via AJAX --}}
                <div class="space-y-4" x-data="{
                    /* ── Shared state ─────────────────────────────── */
                    formEl: null,

                    /* ── Polres state ─────────────────────────────── */
                    polresOpen: false,
                    polresSearch: '',
                    polresHighlight: -1,
                    polresValue: '{{ request('polres', '') }}',
                    polresLabel: '{{ request('polres') ? optional(($polresList ?? collect())->firstWhere('id_tingkat', request('polres')))->nama_tingkat : '' }}',
                    polresItems: [
                        @foreach($polresList ?? [] as $p)
                        { value: '{{ $p->id_tingkat }}', label: '{{ addslashes($p->nama_tingkat) }}' },
                        @endforeach
                    ],
                    get polresFiltered() {
                        if (!this.polresSearch) return this.polresItems;
                        return this.polresItems.filter(i => i.label.toLowerCase().includes(this.polresSearch.toLowerCase()));
                    },

                    /* ── Polsek state ─────────────────────────────── */
                    polsekOpen: false,
                    polsekSearch: '',
                    polsekHighlight: -1,
                    polsekLoading: false,
                    polsekValue: '{{ request('polsek', '') }}',
                    polsekLabel: '{{ request('polsek') ? optional(($polsekList ?? collect())->firstWhere('id_tingkat', request('polsek')))->nama_tingkat : '' }}',
                    polsekItems: [
                        @foreach($polsekList ?? [] as $ps)
                        { value: '{{ $ps->id_tingkat }}', label: '{{ addslashes($ps->nama_tingkat) }}' },
                        @endforeach
                    ],
                    get polsekFiltered() {
                        if (!this.polsekSearch) return this.polsekItems;
                        return this.polsekItems.filter(i => i.label.toLowerCase().includes(this.polsekSearch.toLowerCase()));
                    },

                    /* ── Polres actions ───────────────────────────── */
                    selectPolres(item) {
                        this.polresValue  = item.value;
                        this.polresLabel  = item.label;
                        this.polresOpen   = false;
                        this.polresSearch = '';
                        this.polresHighlight = -1;
                        /* reset polsek */
                        this.polsekValue  = '';
                        this.polsekLabel  = '';
                        this.polsekItems  = [];
                        /* fetch polsek options */
                        this.fetchPolsek(item.value);
                    },
                    clearPolres() {
                        this.polresValue  = '';
                        this.polresLabel  = '';
                        this.polresSearch = '';
                        this.polresOpen   = false;
                        this.polsekValue  = '';
                        this.polsekLabel  = '';
                        this.polsekItems  = [];
                        this.$nextTick(() => this.formEl.submit());
                    },
                    polresOnEnter() {
                        if (this.polresHighlight >= 0 && this.polresHighlight < this.polresFiltered.length) {
                            this.selectPolres(this.polresFiltered[this.polresHighlight]);
                        } else if (this.polresFiltered.length === 1) {
                            this.selectPolres(this.polresFiltered[0]);
                        }
                    },

                    /* ── AJAX fetch polsek ────────────────────────── */
                    async fetchPolsek(polresId) {
                        this.polsekLoading = true;
                        try {
                            const res = await fetch(`{{ route('admin.rekapitulasi.polsek') }}?polres=${encodeURIComponent(polresId)}`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            this.polsekItems = await res.json();
                        } catch(e) {
                            this.polsekItems = [];
                        } finally {
                            this.polsekLoading = false;
                        }
                    },

                    /* ── Polsek actions ───────────────────────────── */
                    selectPolsek(item) {
                        this.polsekValue  = item.value;
                        this.polsekLabel  = item.label;
                        this.polsekOpen   = false;
                        this.polsekSearch = '';
                        this.polsekHighlight = -1;
                        this.$nextTick(() => this.formEl.submit());
                    },
                    clearPolsek() {
                        this.polsekValue  = '';
                        this.polsekLabel  = '';
                        this.polsekSearch = '';
                        this.polsekOpen   = false;
                        this.$nextTick(() => this.formEl.submit());
                    },
                    polsekOnEnter() {
                        if (this.polsekHighlight >= 0 && this.polsekHighlight < this.polsekFiltered.length) {
                            this.selectPolsek(this.polsekFiltered[this.polsekHighlight]);
                        } else if (this.polsekFiltered.length === 1) {
                            this.selectPolsek(this.polsekFiltered[0]);
                        }
                    }
                }" x-init="formEl = $el.closest('form')">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Kategori Lokasi</h4>
                    <div class="space-y-3">

                        {{-- Polres --}}
                        <div class="relative">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Polres / Satwil</label>
                            <input type="hidden" name="polres" :value="polresValue">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text"
                                    x-show="!polresLabel || polresOpen"
                                    x-model="polresSearch"
                                    @focus="polresOpen = true; polresHighlight = -1"
                                    @click.away="polresOpen = false; polresSearch = ''"
                                    @keydown.arrow-down.prevent="if(polresHighlight < polresFiltered.length-1) polresHighlight++"
                                    @keydown.arrow-up.prevent="if(polresHighlight > 0) polresHighlight--"
                                    @keydown.enter.prevent="polresOnEnter()"
                                    @keydown.escape.prevent="polresOpen = false; polresSearch = ''"
                                    placeholder="Cari polres..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="polresLabel && !polresOpen"
                                    @click="polresOpen = true; polresHighlight = -1"
                                    class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="polresLabel"></span>
                                </div>
                                <button type="button" x-show="polresValue" @click.stop="clearPolres()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="polresOpen && polresFiltered.length > 0"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <template x-for="(item, index) in polresFiltered" :key="item.value">
                                    <div @mousedown.prevent="selectPolres(item)"
                                        class="px-3.5 py-2.5 text-sm cursor-pointer transition-colors border-b border-slate-50 last:border-0 hover:bg-emerald-50 hover:text-emerald-700"
                                        :class="polresHighlight === index ? 'bg-emerald-100 text-emerald-800 font-semibold' : (polresValue === item.value ? 'bg-emerald-50/40 text-emerald-600 font-medium' : 'text-slate-700')"
                                        x-text="item.label">
                                    </div>
                                </template>
                            </div>
                            <div x-show="polresOpen && polresSearch && polresFiltered.length === 0"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl p-4 text-center">
                                <p class="text-xs text-slate-400 font-medium">Tidak ditemukan</p>
                            </div>
                        </div>

                        {{-- Polsek (cascading, loaded via AJAX after Polres) --}}
                        <div class="relative">
                            <label class="block text-xs font-semibold mb-1.5 ml-1"
                                :class="polresValue ? 'text-slate-600' : 'text-slate-400'">
                                Polsek / Sektor
                                <span x-show="polsekLoading" class="ml-1 inline-flex items-center gap-1 text-[10px] text-emerald-500 font-normal">
                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    memuat...
                                </span>
                            </label>
                            <input type="hidden" name="polsek" :value="polsekValue">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    :class="polresValue ? 'text-slate-400' : 'text-slate-300'">
                                    {{-- spinner inside input while loading --}}
                                    <svg x-show="polsekLoading" class="w-3.5 h-3.5 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <svg x-show="!polsekLoading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                {{-- Empty/disabled state when no polres --}}
                                <div x-show="!polresValue && !polsekLabel"
                                    class="w-full h-10 pl-9 pr-3 bg-slate-100 border border-dashed border-slate-200 rounded-lg text-sm flex items-center cursor-not-allowed">
                                    <span class="text-slate-400 text-xs italic">Pilih Polres dulu...</span>
                                </div>
                                {{-- Search input --}}
                                <input type="text"
                                    x-show="polresValue && (!polsekLabel || polsekOpen)"
                                    x-model="polsekSearch"
                                    @focus="if(polresValue){ polsekOpen = true; polsekHighlight = -1; }"
                                    @click.away="polsekOpen = false; polsekSearch = ''"
                                    @keydown.arrow-down.prevent="if(polsekHighlight < polsekFiltered.length-1) polsekHighlight++"
                                    @keydown.arrow-up.prevent="if(polsekHighlight > 0) polsekHighlight--"
                                    @keydown.enter.prevent="polsekOnEnter()"
                                    @keydown.escape.prevent="polsekOpen = false; polsekSearch = ''"
                                    :placeholder="polsekLoading ? 'Memuat polsek...' : 'Cari polsek...'"
                                    :disabled="polsekLoading || !polresValue"
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                {{-- Selected label --}}
                                <div x-show="polsekLabel && !polsekOpen"
                                    @click="if(polresValue){ polsekOpen = true; polsekHighlight = -1; }"
                                    class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="polsekLabel"></span>
                                </div>
                                <button type="button" x-show="polsekValue" @click.stop="clearPolsek()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="polsekOpen && polsekFiltered.length > 0"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <template x-for="(item, index) in polsekFiltered" :key="item.value">
                                    <div @mousedown.prevent="selectPolsek(item)"
                                        class="px-3.5 py-2.5 text-sm cursor-pointer transition-colors border-b border-slate-50 last:border-0 hover:bg-emerald-50 hover:text-emerald-700"
                                        :class="polsekHighlight === index ? 'bg-emerald-100 text-emerald-800 font-semibold' : (polsekValue === item.value ? 'bg-emerald-50/40 text-emerald-600 font-medium' : 'text-slate-700')"
                                        x-text="item.label">
                                    </div>
                                </template>
                            </div>
                            <div x-show="polsekOpen && polsekSearch && polsekFiltered.length === 0"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl p-4 text-center">
                                <p class="text-xs text-slate-400 font-medium">Tidak ditemukan</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Spesifikasi Lahan --}}

                <div class="space-y-4">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Spesifikasi Lahan</h4>
                    <div class="space-y-3">

                        {{-- Jenis Lahan Searchable --}}
                        <div x-data="{
                            isOpen: false,
                            search: '',
                            highlightedIndex: -1,
                            selectedValue: '{{ request('jenis_lahan', '') }}',
                            selectedLabel: '{{ request('jenis_lahan') && isset($jenisLahanList) ? optional($jenisLahanList->firstWhere('id_jenis_lahan', request('jenis_lahan')))->nama_jenis_lahan : '' }}',
                            items: [
                                @if(isset($jenisLahanList) && count($jenisLahanList) > 0)
                                @foreach($jenisLahanList as $item)
                                { value: '{{ $item->id_jenis_lahan }}', label: '{{ addslashes($item->nama_jenis_lahan) }}' },
                                @endforeach
                                @endif
                            ],
                            get filtered() {
                                if (!this.search) return this.items;
                                return this.items.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            openDropdown() { this.isOpen = true; this.highlightedIndex = -1; },
                            closeDropdown() { this.isOpen = false; this.search = ''; this.highlightedIndex = -1; },
                            select(item) {
                                this.selectedValue = item.value;
                                this.selectedLabel = item.label;
                                this.closeDropdown();
                                this.$nextTick(() => this.$refs.form.submit());
                            },
                            clear() {
                                this.selectedValue = '';
                                this.selectedLabel = '';
                                this.closeDropdown();
                                this.$nextTick(() => this.$refs.form.submit());
                            },
                            onArrowDown() { if (this.highlightedIndex < this.filtered.length - 1) this.highlightedIndex++; },
                            onArrowUp() { if (this.highlightedIndex > 0) this.highlightedIndex--; },
                            onEnter() {
                                if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filtered.length) {
                                    this.select(this.filtered[this.highlightedIndex]);
                                } else if (this.filtered.length === 1) {
                                    this.select(this.filtered[0]);
                                }
                            }
                        }" x-init="$refs.form = $el.closest('form')" class="relative">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Jenis Lahan</label>
                            <input type="hidden" name="jenis_lahan" :value="selectedValue">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text"
                                    x-show="!selectedLabel || isOpen"
                                    x-model="search"
                                    @focus="openDropdown()"
                                    @click.away="closeDropdown()"
                                    @keydown.arrow-down.prevent="onArrowDown()"
                                    @keydown.arrow-up.prevent="onArrowUp()"
                                    @keydown.enter.prevent="onEnter()"
                                    @keydown.escape.prevent="closeDropdown()"
                                    placeholder="Cari jenis lahan..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="selectedLabel && !isOpen"
                                    @click="openDropdown()"
                                    class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="selectedLabel"></span>
                                </div>
                                <button type="button" x-show="selectedValue" @click.stop="clear()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="isOpen && filtered.length > 0"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <template x-for="(item, index) in filtered" :key="item.value">
                                    <div @mousedown.prevent="select(item)"
                                        class="px-3.5 py-2.5 text-sm cursor-pointer transition-colors border-b border-slate-50 last:border-0 hover:bg-emerald-50 hover:text-emerald-700"
                                        :class="highlightedIndex === index ? 'bg-emerald-100 text-emerald-800 font-semibold' : (selectedValue === item.value ? 'bg-emerald-50/40 text-emerald-600 font-medium' : 'text-slate-700')"
                                        x-text="item.label">
                                    </div>
                                </template>
                            </div>
                            <div x-show="isOpen && search && filtered.length === 0" x-transition.opacity.duration.150ms
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl p-4 text-center">
                                <p class="text-xs text-slate-400 font-medium">Tidak ditemukan</p>
                            </div>
                        </div>

                        {{-- Komoditi Searchable --}}
                        <div x-data="{
                            isOpen: false,
                            search: '',
                            highlightedIndex: -1,
                            selectedValue: '{{ request('komoditi', '') }}',
                            selectedLabel: '{{ request('komoditi') ? optional(($komoditiList ?? collect())->firstWhere('id_komoditi', request('komoditi')))->nama_komoditi : '' }}',
                            items: [
                                @foreach($komoditiList ?? [] as $km)
                                { value: '{{ $km->id_komoditi }}', label: '{{ addslashes($km->nama_komoditi) }}' },
                                @endforeach
                            ],
                            get filtered() {
                                if (!this.search) return this.items;
                                return this.items.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            openDropdown() { this.isOpen = true; this.highlightedIndex = -1; },
                            closeDropdown() { this.isOpen = false; this.search = ''; this.highlightedIndex = -1; },
                            select(item) {
                                this.selectedValue = item.value;
                                this.selectedLabel = item.label;
                                this.closeDropdown();
                                this.$nextTick(() => this.$refs.form.submit());
                            },
                            clear() {
                                this.selectedValue = '';
                                this.selectedLabel = '';
                                this.closeDropdown();
                                this.$nextTick(() => this.$refs.form.submit());
                            },
                            onArrowDown() { if (this.highlightedIndex < this.filtered.length - 1) this.highlightedIndex++; },
                            onArrowUp() { if (this.highlightedIndex > 0) this.highlightedIndex--; },
                            onEnter() {
                                if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filtered.length) {
                                    this.select(this.filtered[this.highlightedIndex]);
                                } else if (this.filtered.length === 1) {
                                    this.select(this.filtered[0]);
                                }
                            }
                        }" x-init="$refs.form = $el.closest('form')" class="relative">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Komoditi</label>
                            <input type="hidden" name="komoditi" :value="selectedValue">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text"
                                    x-show="!selectedLabel || isOpen"
                                    x-model="search"
                                    @focus="openDropdown()"
                                    @click.away="closeDropdown()"
                                    @keydown.arrow-down.prevent="onArrowDown()"
                                    @keydown.arrow-up.prevent="onArrowUp()"
                                    @keydown.enter.prevent="onEnter()"
                                    @keydown.escape.prevent="closeDropdown()"
                                    placeholder="Cari komoditi..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="selectedLabel && !isOpen"
                                    @click="openDropdown()"
                                    class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="selectedLabel"></span>
                                </div>
                                <button type="button" x-show="selectedValue" @click.stop="clear()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div x-show="isOpen && filtered.length > 0"
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                <template x-for="(item, index) in filtered" :key="item.value">
                                    <div @mousedown.prevent="select(item)"
                                        class="px-3.5 py-2.5 text-sm cursor-pointer transition-colors border-b border-slate-50 last:border-0 hover:bg-emerald-50 hover:text-emerald-700"
                                        :class="highlightedIndex === index ? 'bg-emerald-100 text-emerald-800 font-semibold' : (selectedValue === item.value ? 'bg-emerald-50/40 text-emerald-600 font-medium' : 'text-slate-700')"
                                        x-text="item.label">
                                    </div>
                                </template>
                            </div>
                            <div x-show="isOpen && search && filtered.length === 0" x-transition.opacity.duration.150ms
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl p-4 text-center">
                                <p class="text-xs text-slate-400 font-medium">Tidak ditemukan</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Periode Laporan --}}
                <div class="space-y-4" x-data="{ filterType: '{{ request('periode', 'tahun') }}' }">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Periode Laporan</h4>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-4">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="periode" value="tahun" x-model="filterType" onchange="this.form.submit()"
                                    class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                                <span :class="filterType === 'tahun' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'" class="text-xs group-hover:text-slate-900 transition-colors uppercase">TAHUN</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="periode" value="kwartal" x-model="filterType" onchange="this.form.submit()"
                                    class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                                <span :class="filterType === 'kwartal' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'" class="text-xs group-hover:text-slate-900 transition-colors uppercase">KWARTAL</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="periode" value="tanggal" x-model="filterType" onchange="this.form.submit()"
                                    class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                                <span :class="filterType === 'tanggal' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'" class="text-xs group-hover:text-slate-900 transition-colors uppercase">TANGGAL</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div x-show="filterType === 'tahun' || filterType === 'kwartal'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="grid grid-cols-2 gap-3">
                                <input type="number" name="tahun" value="{{ request('tahun', date('Y')) }}" placeholder="Tahun" onchange="this.form.submit()"
                                    class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">

                                <select name="bulan" x-show="filterType === 'tahun'" onchange="this.form.submit()"
                                    class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                    <option value="">SEMUA BULAN</option>
                                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                                    <option value="{{ $bulan }}" {{ request('bulan') == $bulan ? 'selected' : '' }}>{{ $bulan }}</option>
                                    @endforeach
                                </select>

                                <select name="kwartal" x-show="filterType === 'kwartal'" onchange="this.form.submit()"
                                    class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                    <option value="">PILIH KWARTAL</option>
                                    <option value="KWARTAL I (Jan-Mar)" {{ request('kwartal') == 'KWARTAL I (Jan-Mar)' ? 'selected' : '' }}>KWARTAL I (Jan-Mar)</option>
                                    <option value="KWARTAL II (Apr-Jun)" {{ request('kwartal') == 'KWARTAL II (Apr-Jun)' ? 'selected' : '' }}>KWARTAL II (Apr-Jun)</option>
                                    <option value="KWARTAL III (Jul-Sep)" {{ request('kwartal') == 'KWARTAL III (Jul-Sep)' ? 'selected' : '' }}>KWARTAL III (Jul-Sep)</option>
                                    <option value="KWARTAL IV (Okt-Des)" {{ request('kwartal') == 'KWARTAL IV (Okt-Des)' ? 'selected' : '' }}>KWARTAL IV (Okt-Des)</option>
                                </select>
                            </div>

                            <div x-show="filterType === 'tanggal'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0">
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="date" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}" onchange="this.form.submit()"
                                        class="w-full h-10 pl-10 pr-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- [SEC 4] - DATA TABLE --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-2xl shadow-slate-200/50 overflow-hidden relative z-20 mx-2 mt-12 bg-clip-padding backdrop-filter backdrop-blur-3xl bg-opacity-70">
        <!-- Header Panel -->
        <div class="px-8 py-6 bg-gradient-to-r from-slate-900 to-slate-800 flex justify-between items-center relative overflow-hidden">
            <svg class="absolute right-0 top-0 h-full w-48 text-white opacity-5 transform translate-x-12 -rotate-12" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 7.5L5.5 6.5 12 3.25l6.5 3.25L12 9.5zm0 12.5l-10-5 v-6l10 5 10-5v6l-10 5z"></path></svg>
            <div class="flex items-center gap-4 relative z-10 w-full">
                <div class="w-1.5 h-8 bg-emerald-500 rounded-full"></div>
                <h3 class="text-sm font-black text-white uppercase tracking-widest">RINCIAN PRODUKSI WILAYAH</h3>
                <span class="text-[10px] font-black text-emerald-300 bg-emerald-500/20 px-2.5 py-1 rounded-lg border border-emerald-400/30">
                    {{ $dataRekap->total() }} BARIS
                </span>
            </div>
            <div class="hidden md:block relative z-10 text-xs font-black text-emerald-400 bg-emerald-400/20 px-3 py-1.5 rounded-lg border border-emerald-400/30 whitespace-nowrap">
                LIVE DATA
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <th rowspan="2" class="px-8 py-4 border-b-2 border-slate-200 text-left font-bold">
                            Wilayah / Satuan Kerja
                        </th>
                        <th rowspan="2" class="px-6 py-4 text-right border-b-2 border-slate-200 whitespace-nowrap">
                            Potensi Lahan
                        </th>
                        <th rowspan="2" class="px-6 py-4 text-right border-b-2 border-slate-200 whitespace-nowrap">
                            Aktual Tanam
                        </th>
                        <th colspan="2" class="px-6 py-2 text-center border-b border-l border-slate-200 bg-blue-50/50 text-blue-600">
                            Hasil Produksi
                        </th>
                    </tr>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-3 text-right border-b-2 border-l border-slate-200 whitespace-nowrap">Panen Ha / Ton</th>
                        <th class="px-6 py-3 text-right border-b-2 border-l border-slate-200 whitespace-nowrap">Serapan %</th>
                    </tr>
                </thead>

                {{-- === DATA ROWS === --}}
                @php
                $allItems = collect($dataRekap->items() ?? []);
                $groupedByPolres = $allItems->groupBy('nama_polres');
                @endphp

                @forelse($groupedByPolres as $polresName => $polseksCollection)
                <tbody x-data="{ openPolres: true, openPolsek: {} }" class="text-sm">
                    @php
                    $totalPolresHA = $polseksCollection->sum('kapasitas_lahan_ha');
                    $totalPolresTanam = $polseksCollection->sum('aktual_tanam_ha');
                    @endphp

                    {{-- ====== BARIS POLRES ====== --}}
                    <tr @click="openPolres = !openPolres"
                        class="bg-gradient-to-r from-emerald-50 to-emerald-50/30 border-y-2 border-emerald-100 cursor-pointer hover:from-emerald-100/60 transition-all duration-200 group/polres">
                        <td colspan="5" class="px-8 py-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-300"
                                        :class="openPolres ? 'animate-pulse' : ''"></div>
                                    <span class="text-[11px] font-black text-emerald-900 uppercase tracking-[0.15em]">
                                        {{ $polresName ?: 'POLRES TIDAK DIKETAHUI' }}
                                    </span>
                                    <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-bold rounded-full">
                                        {{ number_format($totalPolresHA, 2) }} HA &nbsp;·&nbsp; {{ $polseksCollection->count() }} entri
                                    </span>
                                </div>
                                <svg :class="openPolres ? 'rotate-180' : ''"
                                    class="w-4 h-4 text-emerald-500 transition-transform duration-300 flex-shrink-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </td>
                    </tr>

                    @php
                    $groupedByPolsek = $polseksCollection->groupBy('nama_polsek');
                    @endphp

                    @foreach($groupedByPolsek as $polsekName => $desaCollection)
                    @php
                    $psKey = 'ps_' . md5(($polsekName ?: 'x') . $polresName);
                    $subtotalHA = $desaCollection->sum('kapasitas_lahan_ha');
                    $subtotalTanam = $desaCollection->sum('aktual_tanam_ha');
                    $subtotalPanen = $desaCollection->sum('aktual_panen_ha');
                    $subtotalProd = $desaCollection->sum('total_produksi_panen');
                    $subtotalSerap = $subtotalHA > 0 ? round(($subtotalTanam / $subtotalHA) * 100, 2) : 0;
                    $jumlahDesa = $desaCollection->count();
                    @endphp

                    <tr x-show="openPolres"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        @click="openPolsek['{{ $psKey }}'] = !openPolsek['{{ $psKey }}']"
                        class="bg-blue-50/40 border-b border-blue-100/70 cursor-pointer hover:bg-blue-100/40 transition-colors duration-150 group/polsek">

                        <td class="px-8 py-3 pl-14">
                            <div class="flex items-center gap-2">
                                <svg :class="openPolsek['{{ $psKey }}'] ? '' : 'rotate-90'"
                                    class="w-3 h-3 text-blue-400 transition-transform duration-200 flex-shrink-0"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                </svg>
                                <span class="text-[11px] font-bold text-blue-900 uppercase tracking-wider">
                                    {{ $polsekName ?: 'POLSEK TIDAK DIKETAHUI' }}
                                </span>
                                <span class="px-1.5 py-0.5 bg-blue-100/80 text-blue-600 text-[9px] font-bold rounded">
                                    {{ $jumlahDesa }} desa
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="text-[11px] font-bold text-blue-700">{{ number_format($subtotalHA, 2) }}</span>
                            <span class="text-[9px] text-blue-400 ml-0.5">HA</span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="text-[11px] font-semibold text-slate-500">{{ number_format($subtotalTanam, 2) }}</span>
                            <span class="text-[9px] text-slate-400 ml-0.5">HA</span>
                        </td>
                        <td class="px-6 py-3 text-right border-l border-blue-100/50">
                            <span class="text-[11px] font-semibold text-slate-500 italic">
                                {{ number_format($subtotalPanen, 2) }} / {{ number_format($subtotalProd, 2) }}
                            </span>
                            <span class="text-[9px] text-slate-400 ml-0.5">TON</span>
                        </td>
                        <td class="px-6 py-3 text-right border-l border-blue-100/50">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                {{ $subtotalSerap >= 75 ? 'bg-emerald-50 text-emerald-700' : ($subtotalSerap >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ number_format($subtotalSerap, 1) }}%
                            </span>
                        </td>
                    </tr>

                    @foreach($desaCollection as $row)
                    @php
                    $hasData = ($row->total_titik_lahan ?? 0) > 0;
                    $serapan = $row->persentase_serapan ?? 0;
                    @endphp
                    <tr x-show="openPolres && !openPolsek['{{ $psKey }}']"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="border-b border-slate-50 transition-colors duration-100 {{ $hasData ? 'bg-white hover:bg-slate-50/60' : 'bg-slate-50/20 opacity-60 hover:opacity-80' }}">

                        {{-- Nama Desa --}}
                        <td class="px-8 py-3.5 pl-20">
                            <div class="flex items-start gap-3">
                                <div class="w-[3px] h-6 rounded-full flex-shrink-0 mt-0.5
                                    {{ $hasData ? 'bg-emerald-300' : 'bg-slate-200' }}">
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-semibold capitalize leading-tight
                                        {{ $hasData ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ strtolower($row->nama_desa ?? 'Desa Tidak Diketahui') }}
                                    </span>
                                    @if($hasData)
                                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wide mt-0.5 leading-none">
                                        {{ $row->nama_jenis_lahan ?? '' }}
                                        @if($row->nama_komoditi) &bull; {{ $row->nama_komoditi }} @endif
                                        @if($row->tahun_lahan)
                                        <span class="text-slate-300">&middot;</span> {{ $row->tahun_lahan }}
                                        @endif
                                    </span>
                                    @else
                                    <span class="text-[10px] text-rose-300 font-semibold uppercase tracking-wide mt-0.5 leading-none">
                                        Belum Ada Data Produksi
                                    </span>
                                    @endif
                                </div>
                                @if($hasData && $row->total_titik_lahan > 0)
                                <span class="ml-auto flex-shrink-0 px-1.5 py-0.5 bg-emerald-50 text-emerald-600 text-[9px] font-bold rounded-full border border-emerald-100">
                                    {{ $row->total_titik_lahan }} titik
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- Potensi Lahan --}}
                        <td class="px-6 py-3.5 text-right">
                            @if($hasData)
                            <span class="font-bold text-sm text-slate-900 tracking-tight">{{ number_format($row->kapasitas_lahan_ha ?? 0, 2) }}</span>
                            <span class="text-[10px] text-slate-400 ml-0.5">HA</span>
                            @else
                            <span class="text-sm font-bold text-slate-200">—</span>
                            @endif
                        </td>

                        {{-- Aktual Tanam --}}
                        <td class="px-6 py-3.5 text-right">
                            @if($hasData)
                            <span class="font-bold text-sm text-rose-500 tracking-tight">{{ number_format($row->aktual_tanam_ha ?? 0, 2) }}</span>
                            <span class="text-[10px] text-slate-400 ml-0.5">HA</span>
                            @else
                            <span class="text-sm font-bold text-slate-200">—</span>
                            @endif
                        </td>

                        {{-- Panen Ha / Ton --}}
                        <td class="px-6 py-3.5 text-right border-l border-slate-100/50">
                            @if($hasData)
                            <span class="font-bold text-sm text-rose-500 tracking-tight italic">
                                {{ number_format($row->aktual_panen_ha ?? 0, 2) }} / {{ number_format($row->total_produksi_panen ?? 0, 2) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-normal ml-0.5">TON</span>
                            @else
                            <span class="text-sm font-bold text-slate-200">—</span>
                            @endif
                        </td>

                        {{-- Serapan % --}}
                        <td class="px-6 py-3.5 text-right border-l border-slate-100/50">
                            @if($hasData)
                            <span class="inline-flex items-center gap-0.5 px-2.5 py-1 rounded-full text-[11px] font-bold
                                    {{ $serapan >= 75 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                        : ($serapan >= 40 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                                        : 'bg-rose-50 text-rose-600 ring-1 ring-rose-200') }}">
                                {{ number_format($serapan, 2) }}%
                            </span>
                            @else
                            <span class="text-[11px] font-bold text-slate-200">0.00%</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    {{-- END DESA LOOP --}}

                    @endforeach
                    {{-- END POLSEK LOOP --}}

                </tbody>
                {{-- END POLRES TBODY --}}

                @empty
                <tbody>
                    <tr>
                        <td colspan="5" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="text-sm font-bold text-slate-500">Tidak ada data ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Coba ubah filter atau pilih parameter berbeda</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                @endforelse
            </table>
        </div>

        {{-- Pagination --}}
        @if($dataRekap->hasPages())
        <div class="px-6 py-5 border-t border-slate-200/60 bg-slate-50/80 flex flex-col sm:flex-row justify-between items-center gap-4 rounded-b-[2.5rem]">
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                Data ke <span class="text-emerald-600">{{ $dataRekap->firstItem() }}</span> - <span class="text-emerald-600">{{ $dataRekap->lastItem() }}</span> dari total <span class="text-slate-800">{{ $dataRekap->total() }}</span>
            </div>
            
            <div class="flex items-center gap-1 sm:gap-2">
                {{-- Previous --}}
                @if ($dataRekap->onFirstPage())
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Mundur</span>
                @else
                    <a href="{{ $dataRekap->previousPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-white border border-slate-200 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-300 text-slate-600 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-sm active:scale-95">Mundur</a>
                @endif

                {{-- Pages --}}
                <div class="hidden sm:flex items-center gap-1 mx-2">
                    @php
                        $startPage = max($dataRekap->currentPage() - 2, 1);
                        $endPage = min($startPage + 4, $dataRekap->lastPage());
                        if ($endPage - $startPage < 4) {
                            $startPage = max($endPage - 4, 1);
                        }
                    @endphp

                    @if($startPage > 1)
                        <a href="{{ $dataRekap->url(1) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-xl text-xs font-black transition-all">1</a>
                        @if($startPage > 2)
                            <span class="w-9 h-9 flex items-center justify-center text-slate-400 text-xs font-black">...</span>
                        @endif
                    @endif

                    @for ($page = $startPage; $page <= $endPage; $page++)
                        @if ($page == $dataRekap->currentPage())
                            <span class="w-9 h-9 flex items-center justify-center bg-emerald-600 text-white rounded-xl text-xs font-black shadow-md shadow-emerald-500/30">{{ $page }}</span>
                        @else
                            <a href="{{ $dataRekap->url($page) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-300 rounded-xl text-xs font-black transition-all">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($endPage < $dataRekap->lastPage())
                        @if($endPage < $dataRekap->lastPage() - 1)
                            <span class="w-9 h-9 flex items-center justify-center text-slate-400 text-xs font-black">...</span>
                        @endif
                        <a href="{{ $dataRekap->url($dataRekap->lastPage()) }}" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 rounded-xl text-xs font-black transition-all">{{ $dataRekap->lastPage() }}</a>
                    @endif
                </div>

                {{-- Next --}}
                @if ($dataRekap->hasMorePages())
                    <a href="{{ $dataRekap->nextPageUrl() }}" class="px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-emerald-500/30 active:scale-95">Next</a>
                @else
                    <span class="px-3 sm:px-4 py-2 sm:py-2.5 bg-slate-100 text-slate-400 rounded-xl text-[10px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed border border-slate-200/50">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- [SEC 5] - MOBILE CARDS --}}
    <div class="sm:hidden px-4 space-y-5">
        @forelse(collect($dataRekap->items() ?? [])->groupBy('nama_polres') as $polresName => $polseksCol)
        <div x-data="{ openPolres: true, openPolsek: {} }" class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm">
            {{-- Header Polres --}}
            <div @click="openPolres = !openPolres"
                class="flex justify-between items-center px-4 py-3.5 bg-gradient-to-r from-emerald-50 to-white border-b border-emerald-100 cursor-pointer">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500" :class="openPolres ? 'animate-pulse' : ''"></div>
                    <h3 class="text-xs font-black text-emerald-800 uppercase tracking-widest">
                        {{ $polresName ?: 'POLRES TIDAK DIKETAHUI' }}
                    </h3>
                </div>
                <svg :class="{ 'rotate-180': openPolres }" class="w-4 h-4 text-emerald-500 transition-transform duration-300"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>

            <div x-show="openPolres" class="divide-y divide-slate-100">
                @foreach($polseksCol->groupBy('nama_polsek') as $polsekName => $desaRows)
                @php $psKeyM = 'mob_' . md5(($polsekName ?: 'x') . $polresName); @endphp
                <div>
                    {{-- Header Polsek --}}
                    <div @click="openPolsek['{{ $psKeyM }}'] = !openPolsek['{{ $psKeyM }}']"
                        class="flex items-center gap-2.5 px-4 py-3 bg-blue-50/50 border-b border-blue-50 cursor-pointer hover:bg-blue-100/30 transition-colors">
                        <svg :class="openPolsek['{{ $psKeyM }}'] ? '' : 'rotate-90'"
                            class="w-3 h-3 text-blue-500 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <h4 class="text-[11px] font-bold text-blue-800 uppercase tracking-wider flex-1">
                            {{ $polsekName ?: 'POLSEK TIDAK DIKETAHUI' }}
                        </h4>
                        <span class="text-[9px] font-bold text-blue-500 bg-blue-100 px-1.5 py-0.5 rounded">
                            {{ $desaRows->count() }}
                        </span>
                    </div>

                    {{-- Desa Cards --}}
                    <div x-show="!openPolsek['{{ $psKeyM }}']" class="p-3 space-y-2.5">
                        @foreach($desaRows as $row)
                        @php $hasDataM = ($row->total_titik_lahan ?? 0) > 0; @endphp
                        <div class="rounded-xl border p-3.5 space-y-3
                            {{ $hasDataM ? 'bg-white border-slate-200 shadow-sm' : 'bg-slate-50/50 border-slate-100 opacity-60' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="text-[13px] font-black capitalize {{ $hasDataM ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ strtolower($row->nama_desa ?? 'Desa Tidak Diketahui') }}
                                    </h5>
                                    @if($hasDataM)
                                    <p class="text-[9px] font-semibold text-slate-400 uppercase mt-0.5">
                                        {{ $row->nama_jenis_lahan ?? '-' }} &bull; {{ $row->nama_komoditi ?? '-' }}
                                    </p>
                                    @else
                                    <p class="text-[9px] font-bold text-rose-300 uppercase mt-0.5">Belum Ada Data</p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-[9px] font-black rounded-full
                                    {{ $hasDataM ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $hasDataM ? 'AKTIF' : 'KOSONG' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase mb-0.5">Potensi</p>
                                    <p class="text-xs font-bold {{ $hasDataM ? 'text-slate-800' : 'text-slate-300' }}">
                                        {{ number_format($row->kapasitas_lahan_ha ?? 0, 2) }} HA
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase mb-0.5">Tanam</p>
                                    <p class="text-xs font-bold {{ $hasDataM ? 'text-rose-500' : 'text-slate-300' }}">
                                        {{ number_format($row->aktual_tanam_ha ?? 0, 2) }} HA
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase mb-0.5">Serapan</p>
                                    <p class="text-xs font-bold {{ $hasDataM ? 'text-emerald-600' : 'text-slate-300' }}">
                                        {{ number_format($row->persentase_serapan ?? 0, 2) }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white p-10 rounded-2xl border border-slate-200 shadow-sm text-center">
            <p class="text-sm text-slate-500 font-medium">Tidak ada data rekapitulasi.</p>
        </div>
        @endforelse

        {{-- Mobile Pagination --}}
        @if($dataRekap->hasPages())
        <div class="py-6 flex flex-col items-center gap-3">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                Hal {{ $dataRekap->currentPage() }} dari {{ $dataRekap->lastPage() }}
            </p>
            <div class="flex items-center gap-3">
                @if (!$dataRekap->onFirstPage())
                <a href="{{ $dataRekap->previousPageUrl() }}"
                    class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-[11px] font-bold text-slate-600 shadow-sm active:scale-95 transition-all">← Sebelumnya</a>
                @endif
                @if ($dataRekap->hasMorePages())
                <a href="{{ $dataRekap->nextPageUrl() }}"
                    class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-[11px] font-bold shadow-md shadow-emerald-200 active:scale-95 transition-all">Berikutnya →</a>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection