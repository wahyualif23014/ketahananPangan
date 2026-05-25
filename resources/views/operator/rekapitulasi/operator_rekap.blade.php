@extends('layouts.app')

@php
$routeName = request()->route()->getName();
$routePrefix = explode('.', $routeName)[0] ?? 'admin';
$allItems = collect($dataRekap->items() ?? []);
$groupedData = $allItems->groupBy('nama_polres');
@endphp

@section('header', 'Laporan Rekapitulasi Produksi')

@section('content')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>

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
                @if($userLevel < 3)
                    {{-- Kategori Lokasi --}}
                    <div class="space-y-4" x-data="{
                    formEl: null,
                    polresOpen: false, polresSearch: '', polresHighlight: -1,
                    polresValue: '{{ request('polres', '') }}',
                    polresLabel: '{{ request('polres') ? optional(($polresList ?? collect())->firstWhere('id_tingkat', request('polres')))->nama_tingkat : '' }}',
                    polresItems: @js($polresList->map(fn($p) => ['value' => $p->id_tingkat, 'label' => $p->nama_tingkat])),
                    polsekOpen: false, polsekSearch: '', polsekHighlight: -1, polsekLoading: false,
                    polsekValue: '{{ request('polsek', '') }}',
                    polsekLabel: '{{ request('polsek') ? optional(($polsekList ?? collect())->firstWhere('id_tingkat', request('polsek')))->nama_tingkat : '' }}',
                    polsekItems: @js($polsekList ? collect($polsekList)->map(fn($ps) => ['value' => $ps->id_tingkat, 'label' => $ps->nama_tingkat]) : []),
                    
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
                        } catch(e) { this.polsekItems = []; } finally { this.polsekLoading = false; }
                    }
                }" x-init="formEl = $el.closest('form')">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Kategori Lokasi</h4>
                    <div class="space-y-3">
                        @if($userLevel < 2)
                            <div class="relative">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">Polres / Satwil</label>
                            <input type="hidden" name="polres" :value="polresValue">
                            <div class="relative">
                                <input type="text" x-ref="polresInput" x-show="!polresLabel || polresOpen" x-model="polresSearch" @focus="polresOpen = true" @click.away="polresOpen = false" placeholder="Cari polres..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                                <div x-show="polresLabel && !polresOpen" @click="polresOpen = true; $nextTick(() => $refs.polresInput.focus())" class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
                                    <span class="truncate text-slate-800 font-medium" x-text="polresLabel"></span>
                                </div>
                                <button type="button" x-show="polresValue" @click="polresValue=''; polresLabel=''; polsekValue=''; polsekLabel='';" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-rose-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg></button>
                            </div>
                            <div x-show="polresOpen && polresFiltered.length > 0" class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto" x-cloak>
                                <template x-for="item in polresFiltered" :key="item.value">
                                    <div @mousedown.prevent="selectPolres(item)" class="px-3.5 py-2.5 text-sm cursor-pointer hover:bg-emerald-50 hover:text-emerald-700" x-text="item.label"></div>
                                </template>
                            </div>
                    </div>
                    @endif

                    <div class="relative">
                        <label class="block text-xs font-semibold mb-1.5 ml-1" :class="polresValue || {{ $userLevel == 2 ? 'true' : 'false' }} ? 'text-slate-600' : 'text-slate-400'">Polsek / Sektor</label>
                        <input type="hidden" name="polsek" :value="polsekValue">
                        <div class="relative">
                            <input type="text" x-ref="polsekInput" x-show="(polresValue || {{ $userLevel == 2 ? 'true' : 'false' }}) && (!polsekLabel || polsekOpen)" x-model="polsekSearch" @focus="polsekOpen = true" @click.away="polsekOpen = false" :disabled="polsekLoading || !(polresValue || {{ $userLevel == 2 ? 'true' : 'false' }})" placeholder="Cari polsek..."
                                class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                            <div x-show="polsekLabel && !polsekOpen" @click="polsekOpen = true; $nextTick(() => $refs.polsekInput.focus())" class="w-full h-10 pl-9 pr-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
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
        @endif

        {{-- Spesifikasi Lahan --}}
        <div class="space-y-4">
            <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Spesifikasi Lahan</h4>
            <div class="space-y-3">
                @php
                $specs = [
                ['label' => 'Jenis Lahan', 'name' => 'jenis_lahan', 'items' => $jenisLahanList->map(fn($i) => ['value' => $i->id_jenis_lahan, 'label' => $i->nama_jenis_lahan]), 'req' => 'jenis_lahan'],
                ['label' => 'Komoditi', 'name' => 'komoditi', 'items' => $komoditiList->map(fn($i) => ['value' => $i->id_komoditi, 'label' => $i->nama_komoditi]), 'req' => 'komoditi'],
                ];
                @endphp
                @foreach($specs as $spec)
                <div x-data="{
                            isOpen: false, search: '', selectedValue: '{{ request($spec['req'], '') }}',
                            selectedLabel: '{{ request($spec['req']) ? optional($spec['items']->firstWhere('value', request($spec['req'])))['label'] : '' }}',
                            items: @js($spec['items']),
                            get filtered() { return this.search === '' ? this.items : this.items.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase())) }
                        }" class="relative">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 ml-1">{{ $spec['label'] }}</label>
                    <input type="hidden" name="{{ $spec['name'] }}" :value="selectedValue">
                    <div class="relative">
                        <input type="text" x-ref="specInput{{ $loop->index }}" x-show="!selectedLabel || isOpen" x-model="search" @focus="isOpen = true" @click.away="isOpen = false" placeholder="Cari..."
                            class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:bg-white transition-all outline-none focus:border-emerald-500/50 focus:ring-4 focus:ring-emerald-500/5">
                        <div x-show="selectedLabel && !isOpen" @click="isOpen = true; $nextTick(() => $refs.specInput{{ $loop->index }}.focus())" class="w-full h-10 pl-9 bg-slate-50 border border-slate-200 rounded-lg text-sm flex items-center cursor-pointer hover:bg-white transition-all">
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
                        <input type="radio" name="periode" value="{{ $p }}" x-model="filterType" onchange="this.form.submit()" class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 transition-all">
                        <span :class="filterType === '{{ $p }}' ? 'text-emerald-700 font-bold' : 'text-slate-500 font-medium'" class="text-xs uppercase">{{ $p }}</span>
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


    {{-- [SEC 4] - DATA TABLE (AJAX) --}}
    <div id="rekap-table-wrapper">
        @include('components.rekapitulasi.table-block', ['dataRekap' => $dataRekap, 'groupedData' => $groupedData])
    </div>

    @include('components.rekapitulasi.mobile-block', ['dataRekap' => $dataRekap, 'groupedData' => $groupedData])

</div><script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
@endsection