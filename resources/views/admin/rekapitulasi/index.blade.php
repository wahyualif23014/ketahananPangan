@extends('layouts.app')

@php
    $routeName = request()->route()->getName();
    $routePrefix = explode('.', $routeName)[0] ?? 'admin';

    // OPTIMASI: Pre-process grouping & calculation agar tidak membebani rendering loop
    $dataCollection = collect($dataRekap->items() ?? []);
    $groupedData = $dataCollection->groupBy('nama_polres');
@endphp

@section('header', 'Laporan Rekapitulasi Produksi')

@section('content')
<div class="space-y-8 pb-20 antialiased text-slate-900" style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">

    {{-- [SEC 1] - HEADER SECTION --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 px-4 transition-all mb-10 duration-700 animate-in fade-in slide-in-from-top-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <span>Rekapitulasi Data</span>
                <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-emerald-600 font-semibold">Produksi Lahan</span>
            </nav>
            <h2 class="text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900">
                Laporan <span class="text-slate-400 font-normal">Rekapitulasi</span>
            </h2>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
            <form action="{{ route($routePrefix . '.rekapitulasi.index') }}" method="GET" id="form-filter">
                <div class="relative group w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}" placeholder="Cari data lokasi..."
                        class="block w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm shadow-sm transition-all outline-none focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500/40">
                </div>
                @foreach(request()->except('search') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button onclick="window.location.reload()" title="Refresh"
                    class="p-2.5 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-emerald-600 transition-all active:scale-95 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
                <button type="submit" form="form-filter" formaction="{{ route($routePrefix . '.rekapitulasi.export') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm shadow-emerald-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>

    {{-- [SEC 3] - FILTER SECTION --}}
    <form action="{{ url()->current() }}" method="GET" class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm" x-data="{ open: true }">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <span class="text-sm font-bold text-slate-700 uppercase tracking-tight">Filter Laporan</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                    Terapkan Filter
                </button>
                <button type="button" @click="open = !open" class="text-xs font-bold text-emerald-600 hover:underline transition-all">
                    <span x-text="open ? 'Sembunyikan' : 'Tampilkan Filter'"></span>
                </button>
            </div>
        </div>

        <div x-show="open" x-collapse x-cloak class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Kategori Lokasi --}}
                <div class="space-y-4" x-data="{
                    formEl: null,
                    polresOpen: false,
                    polresSearch: '',
                    polresHighlight: -1,
                    polresValue: '{{ request('polres', '') }}',
                    polresLabel: '{{ request('polres') ? optional(($polresList ?? collect())->firstWhere('id_tingkat', request('polres')))->nama_tingkat : '' }}',
                    polresItems: @js($polresList->map(fn($p) => ['value' => $p->id_tingkat, 'label' => $p->nama_tingkat])),
                    polsekOpen: false,
                    polsekSearch: '',
                    polsekHighlight: -1,
                    polsekLoading: false,
                    polsekValue: '{{ request('polsek', '') }}',
                    polsekLabel: '{{ request('polsek') ? optional(($polsekList ?? collect())->firstWhere('id_tingkat', request('polsek')))->nama_tingkat : '' }}',
                    polsekItems: @js($polsekList ? $polsekList->map(fn($ps) => ['value' => $ps->id_tingkat, 'label' => $ps->nama_tingkat]) : []),
                    
                    get polresFiltered() {
                        return this.polresSearch === '' ? this.polresItems : this.polresItems.filter(i => i.label.toLowerCase().includes(this.polresSearch.toLowerCase()));
                    },
                    get polsekFiltered() {
                        return this.polsekSearch === '' ? this.polsekItems : this.polsekItems.filter(i => i.label.toLowerCase().includes(this.polsekSearch.toLowerCase()));
                    },
                    selectPolres(item) {
                        this.polresValue = item.value; this.polresLabel = item.label; this.polresOpen = false; this.polresSearch = '';
                        this.polsekValue = ''; this.polsekLabel = ''; this.polsekItems = [];
                        this.fetchPolsek(item.value);
                    },
                    async fetchPolsek(polresId) {
                        this.polsekLoading = true;
                        try {
                            const res = await fetch(`{{ route($routePrefix . '.rekapitulasi.polsek') }}?polres=${encodeURIComponent(polresId)}`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            this.polsekItems = await res.json();
                        } finally { this.polsekLoading = false; }
                    }
                }" x-init="formEl = $el.closest('form')">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Kategori Lokasi</h4>
                    <div class="space-y-3">
                        {{-- Input Polres --}}
                        <div class="relative">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Polres / Satwil</label>
                            <input type="hidden" name="polres" :value="polresValue">
                            <div class="relative">
                                <input type="text" x-show="!polresLabel || polresOpen" x-model="polresSearch" @focus="polresOpen = true" @click.away="polresOpen = false" placeholder="Cari polres..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="polresLabel && !polresOpen" @click="polresOpen = true" class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="polresLabel"></span>
                                </div>
                            </div>
                            <div x-show="polresOpen && polresFiltered.length > 0" class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto" x-cloak>
                                <template x-for="item in polresFiltered" :key="item.value">
                                    <div @mousedown.prevent="selectPolres(item)" class="px-3.5 py-2.5 text-sm cursor-pointer hover:bg-emerald-50 hover:text-emerald-700" x-text="item.label"></div>
                                </template>
                            </div>
                        </div>
                        {{-- Input Polsek --}}
                        <div class="relative">
                            <label class="block text-xs font-semibold mb-1.5 ml-1" :class="polresValue ? 'text-slate-600' : 'text-slate-400'">Polsek / Sektor</label>
                            <input type="hidden" name="polsek" :value="polsekValue">
                            <div class="relative">
                                <input type="text" x-show="polresValue && (!polsekLabel || polsekOpen)" x-model="polsekSearch" @focus="polsekOpen = true" @click.away="polsekOpen = false" :disabled="polsekLoading || !polresValue"
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="polsekLabel && !polsekOpen" @click="polsekOpen = true" class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="polsekLabel"></span>
                                </div>
                            </div>
                            <div x-show="polsekOpen && polsekFiltered.length > 0" class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto" x-cloak>
                                <template x-for="item in polsekFiltered" :key="item.value">
                                    <div @mousedown.prevent="polsekValue = item.value; polsekLabel = item.label; polsekOpen = false; $nextTick(() => formEl.submit())" class="px-3.5 py-2.5 text-sm cursor-pointer hover:bg-emerald-50 hover:text-emerald-700" x-text="item.label"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Spesifikasi Lahan --}}
                <div class="space-y-4">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Spesifikasi Lahan</h4>
                    <div class="space-y-3">
                        {{-- Reusable Searchable Select Component --}}
                        @php
                            $searchableConfigs = [
                                ['label' => 'Jenis Lahan', 'name' => 'jenis_lahan', 'items' => $jenisLahanList->map(fn($i) => ['value' => $i->id_jenis_lahan, 'label' => $i->nama_jenis_lahan]), 'req' => 'jenis_lahan'],
                                ['label' => 'Komoditi', 'name' => 'komoditi', 'items' => $komoditiList->map(fn($i) => ['value' => $i->id_komoditi, 'label' => $i->nama_komoditi]), 'req' => 'komoditi'],
                            ];
                        @endphp
                        @foreach($searchableConfigs as $conf)
                        <div x-data="{
                            isOpen: false, search: '', selectedValue: '{{ request($conf['req'], '') }}',
                            selectedLabel: '{{ request($conf['req']) ? optional($conf['items']->firstWhere('value', request($conf['req'])))['label'] : '' }}',
                            items: @js($conf['items']),
                            get filtered() { return this.search === '' ? this.items : this.items.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase())) }
                        }" class="relative">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">{{ $conf['label'] }}</label>
                            <input type="hidden" name="{{ $conf['name'] }}" :value="selectedValue">
                            <div class="relative">
                                <input type="text" x-show="!selectedLabel || isOpen" x-model="search" @focus="isOpen = true" @click.away="isOpen = false" placeholder="Cari {{ strtolower($conf['label']) }}..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="selectedLabel && !isOpen" @click="isOpen = true" class="w-full h-10 pl-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="selectedLabel"></span>
                                </div>
                            </div>
                            <div x-show="isOpen && filtered.length > 0" class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto" x-cloak>
                                <template x-for="item in filtered" :key="item.value">
                                    <div @mousedown.prevent="selectedValue = item.value; selectedLabel = item.label; isOpen = false; $nextTick(() => $el.closest('form').submit())" class="px-3.5 py-2.5 text-sm cursor-pointer hover:bg-emerald-50 hover:text-emerald-700" x-text="item.label"></div>
                                </template>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Periode Laporan --}}
                <div class="space-y-4" x-data="{ filterType: '{{ request('periode', 'tahun') }}' }">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Periode Laporan</h4>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-4">
                        <div class="flex items-center gap-4">
                            @foreach(['tahun', 'kwartal', 'tanggal'] as $p)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="periode" value="{{ $p }}" x-model="filterType" onchange="this.form.submit()" class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                <span :class="filterType === '{{ $p }}' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'" class="text-xs uppercase tracking-tighter">{{ $p }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div x-show="filterType === 'tahun' || filterType === 'kwartal'" class="grid grid-cols-2 gap-3" x-cloak>
                                <input type="number" name="tahun" value="{{ request('tahun', date('Y')) }}" onchange="this.form.submit()" class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                <select name="{{ request('periode') === 'kwartal' ? 'kwartal' : 'bulan' }}" onchange="this.form.submit()" class="h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:ring-4 focus:ring-emerald-500/5 transition-all">
                                    @if(request('periode') === 'kwartal')
                                        <option value="">PILIH KWARTAL</option>
                                        @foreach(['KWARTAL I (Jan-Mar)', 'KWARTAL II (Apr-Jun)', 'KWARTAL III (Jul-Sep)', 'KWARTAL IV (Okt-Des)'] as $kw)
                                            <option value="{{ $kw }}" {{ request('kwartal') == $kw ? 'selected' : '' }}>{{ $kw }}</option>
                                        @endforeach
                                    @else
                                        <option value="">SEMUA BULAN</option>
                                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bln)
                                            <option value="{{ $bln }}" {{ request('bulan') == $bln ? 'selected' : '' }}>{{ $bln }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div x-show="filterType === 'tanggal'" x-cloak>
                                <input type="date" name="tanggal" value="{{ request('tanggal', date('Y-m-d')) }}" onchange="this.form.submit()" class="w-full h-10 px-3 bg-white border border-slate-200 rounded-lg text-sm font-semibold outline-none focus:ring-4 focus:ring-emerald-500/5 transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- [SEC 4] - DATA TABLE --}}
    <div class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-slate-800">Rincian Produksi Wilayah</h3>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-full uppercase">{{ $dataRekap->total() }} baris</span>
            </div>
            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded uppercase tracking-wider border border-emerald-100">Live Data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <th rowspan="2" class="px-8 py-4 border-b-2 border-slate-200">Wilayah / Satker</th>
                        <th rowspan="2" class="px-6 py-4 text-right border-b-2 border-slate-200">Potensi Lahan</th>
                        <th rowspan="2" class="px-6 py-4 text-right border-b-2 border-slate-200">Aktual Tanam</th>
                        <th colspan="2" class="px-6 py-2 text-center border-b border-l border-slate-200 bg-blue-50/50 text-blue-600">Hasil Produksi</th>
                    </tr>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-3 text-right border-b-2 border-l border-slate-200">Panen Ha / Ton</th>
                        <th class="px-6 py-3 text-right border-b-2 border-l border-slate-200">Serapan %</th>
                    </tr>
                </thead>

                @forelse($groupedData as $polresName => $polseksCollection)
                <tbody x-data="{ openPolres: true, openPolsek: {} }" class="text-sm">
                    @php
                        $totalPolresHA = $polseksCollection->sum('kapasitas_lahan_ha');
                        $polseksGrouped = $polseksCollection->groupBy('nama_polsek');
                    @endphp
                    <tr @click="openPolres = !openPolres" class="bg-gradient-to-r from-emerald-50 to-emerald-50/30 border-y-2 border-emerald-100 cursor-pointer hover:from-emerald-100/60 transition-all group">
                        <td colspan="5" class="px-8 py-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm" :class="openPolres ? 'animate-pulse' : ''"></div>
                                    <span class="text-[11px] font-black text-emerald-900 uppercase tracking-widest">{{ $polresName ?: 'POLRES TIDAK DIKETAHUI' }}</span>
                                    <span class="hidden sm:inline-flex px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-bold rounded-full">
                                        {{ number_format($totalPolresHA, 2) }} HA &nbsp;·&nbsp; {{ $polseksCollection->count() }} entri
                                    </span>
                                </div>
                                <svg :class="openPolres ? 'rotate-180' : ''" class="w-4 h-4 text-emerald-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </td>
                    </tr>

                    @foreach($polseksGrouped as $polsekName => $desaCollection)
                    @php
                        $psKey = 'ps_' . md5($polsekName . $polresName);
                        $sHA = $desaCollection->sum('kapasitas_lahan_ha');
                        $sTanam = $desaCollection->sum('aktual_tanam_ha');
                        $sPanen = $desaCollection->sum('aktual_panen_ha');
                        $sProd = $desaCollection->sum('total_produksi_panen');
                        $sSerap = $sHA > 0 ? ($sTanam / $sHA) * 100 : 0;
                    @endphp
                    <tr x-show="openPolres" @click="openPolsek['{{ $psKey }}'] = !openPolsek['{{ $psKey }}']" class="bg-blue-50/40 border-b border-blue-100/70 cursor-pointer hover:bg-blue-100/40 transition-colors">
                        <td class="px-8 py-3 pl-14">
                            <div class="flex items-center gap-2">
                                <svg :class="openPolsek['{{ $psKey }}'] ? '' : 'rotate-90'" class="w-3 h-3 text-blue-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                <span class="text-[11px] font-bold text-blue-900 uppercase">{{ $polsekName ?: 'POLSEK TIDAK DIKETAHUI' }}</span>
                                <span class="px-1.5 py-0.5 bg-blue-100/80 text-blue-600 text-[9px] font-bold rounded">{{ $desaCollection->count() }} desa</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right"><span class="text-[11px] font-bold text-blue-700">{{ number_format($sHA, 2) }}</span> <span class="text-[9px] text-blue-400">HA</span></td>
                        <td class="px-6 py-3 text-right"><span class="text-[11px] font-semibold text-slate-500">{{ number_format($sTanam, 2) }}</span> <span class="text-[9px] text-slate-400">HA</span></td>
                        <td class="px-6 py-3 text-right border-l border-blue-100/50"><span class="text-[11px] font-semibold text-slate-500 italic">{{ number_format($sPanen, 2) }} / {{ number_format($sProd, 2) }}</span> <span class="text-[9px] text-slate-400">TON</span></td>
                        <td class="px-6 py-3 text-right border-l border-blue-100/50"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sSerap >= 75 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ number_format($sSerap, 1) }}%</span></td>
                    </tr>

                    @foreach($desaCollection as $row)
                    @php $hasData = ($row->total_titik_lahan ?? 0) > 0; @endphp
                    <tr x-show="openPolres && !openPolsek['{{ $psKey }}']" class="border-b border-slate-50 {{ $hasData ? 'bg-white' : 'bg-slate-50/20 opacity-60' }}">
                        <td class="px-8 py-3.5 pl-20">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold capitalize {{ $hasData ? 'text-slate-800' : 'text-slate-400' }}">{{ strtolower($row->nama_desa) }}</span>
                                <span class="text-[10px] text-slate-400 uppercase tracking-tight">{{ $row->nama_jenis_lahan }} &bull; {{ $row->nama_komoditi }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-900">{{ number_format($row->kapasitas_lahan_ha, 2) }}</td>
                        <td class="px-6 py-3.5 text-right font-bold text-rose-500">{{ number_format($row->aktual_tanam_ha, 2) }}</td>
                        <td class="px-6 py-3.5 text-right border-l border-slate-100/50 italic text-rose-500">{{ number_format($row->aktual_panen_ha, 2) }} / {{ number_format($row->total_produksi_panen, 2) }}</td>
                        <td class="px-6 py-3.5 text-right border-l border-slate-100/50">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $row->persentase_serapan >= 75 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                                {{ number_format($row->persentase_serapan, 2) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
                @empty
                <tbody><tr><td colspan="5" class="px-8 py-16 text-center text-slate-500 font-bold">Data tidak ditemukan.</td></tr></tbody>
                @endforelse
            </table>
        </div>

        {{-- Pagination --}}
        @if($dataRekap->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Hal {{ $dataRekap->currentPage() }} dari {{ $dataRekap->lastPage() }} &bull; Total {{ number_format($dataRekap->total()) }} data</div>
            <div class="flex items-center gap-2">
                {{ $dataRekap->links() }}
            </div>
        </div>
        @endif
    </div>

    {{-- [SEC 5] - MOBILE CARDS --}}
    <div class="sm:hidden px-4 space-y-5">
        @forelse($groupedData as $polresName => $polseksCol)
        <div x-data="{ openPolres: true, openPolsek: {} }" class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm" x-cloak>
            <div @click="openPolres = !openPolres" class="px-4 py-3.5 bg-gradient-to-r from-emerald-50 to-white flex justify-between items-center cursor-pointer">
                <h3 class="text-xs font-black text-emerald-800 uppercase tracking-widest">{{ $polresName ?: 'UNKNOWN' }}</h3>
                <svg :class="{ 'rotate-180': openPolres }" class="w-4 h-4 text-emerald-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
            <div x-show="openPolres" class="divide-y divide-slate-100">
                @foreach($polseksCol->groupBy('nama_polsek') as $polsekName => $desaRows)
                <div @click="openPolsek['{{ $polsekName }}'] = !openPolsek['{{ $polsekName }}']" class="px-4 py-3 bg-blue-50/50 text-[11px] font-bold text-blue-800 uppercase flex justify-between cursor-pointer">
                    <span>{{ $polsekName ?: 'UNKNOWN' }}</span>
                    <span class="bg-blue-100 px-1.5 rounded">{{ $desaRows->count() }}</span>
                </div>
                <div x-show="!openPolsek['{{ $polsekName }}']" class="p-3 space-y-2">
                    @foreach($desaRows as $row)
                    <div class="rounded-xl border p-3 bg-white space-y-2">
                        <div class="flex justify-between font-black text-[13px] capitalize"><span>{{ strtolower($row->nama_desa) }}</span> <span class="text-[9px] text-emerald-600">AKTIF</span></div>
                        <div class="grid grid-cols-3 gap-2 text-center text-[10px]">
                            <div class="bg-slate-50 p-1 rounded">Potensi<br><b>{{ number_format($row->kapasitas_lahan_ha, 1) }}</b></div>
                            <div class="bg-slate-50 p-1 rounded">Tanam<br><b class="text-rose-500">{{ number_format($row->aktual_tanam_ha, 1) }}</b></div>
                            <div class="bg-slate-50 p-1 rounded">Hasil<br><b class="text-emerald-600">{{ number_format($row->persentase_serapan, 1) }}%</b></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="text-center p-10 text-slate-500">Kosong.</div>
        @endforelse
    </div>
</div>
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection