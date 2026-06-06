@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 md:p-8 flex flex-col gap-6 sm:gap-8 bg-slate-50 min-h-screen" x-data="poktanData()">
    
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-1 bg-emerald-500/10 text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">KELOLA LAHAN</span>
                <span class="text-slate-300">/</span>
                <span class="text-slate-500 text-[10px] font-black uppercase tracking-widest">DATA POKTAN</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Data Poktan</h1>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 via-transparent to-transparent pointer-events-none"></div>
        <div class="relative z-10 flex flex-col gap-6">
            @php
                $user = auth()->user();
                $idTugas = (string)($user->id_tugas ?? '0');
                $isAdmin = $user->role === 'admin' || $idTugas === '0';
                $isPolsek = !$isAdmin && substr_count($idTugas, '.') >= 2;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                {{-- Search Filter --}}
                <div class="space-y-2 lg:col-span-1">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">PENCARIAN POKTAN</label>
                    <div class="relative group">
                        <input type="text" id="searchInput" x-model="search" @keydown.enter="submitFilters()" placeholder="Cari nama poktan..." class="w-full h-12 text-[11px] font-bold px-4 pl-11 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 placeholder:text-slate-400 uppercase tracking-wider">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>

                @if($isAdmin)
                {{-- Resor Filter --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">KESATUAN WILAYAH (RESOR)</label>
                    <div class="relative">
                        <select id="resorFilter" x-model="selectedResor" @change="submitFilters()" class="appearance-none bg-none w-full h-12 text-[11px] font-bold px-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 uppercase tracking-wider cursor-pointer">
                            <option value="">SEMUA POLRES</option>
                            @foreach($polresList as $polres)
                                <option value="{{ $polres->id_tingkat }}">{{ $polres->nama_tingkat }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                @endif

                @if(!$isPolsek)
                {{-- Sektor Filter --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">KESATUAN WILAYAH (SEKTOR)</label>
                    <div class="relative">
                        <select id="sektorFilter" x-model="selectedSektor" @change="submitFilters()" class="appearance-none bg-none w-full h-12 text-[11px] font-bold px-4 bg-slate-50/50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all text-slate-700 uppercase tracking-wider cursor-pointer">
                            <option value="">SEMUA POLSEK</option>
                            @foreach($polsekList as $polsek)
                                <option value="{{ $polsek->id_tingkat }}">{{ $polsek->nama_tingkat }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button @click="resetFilters()" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all">Reset Filter</button>
            </div>
        </div>
    </div>

    {{-- Data Table Section --}}
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden flex flex-col flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest w-16 text-center">NO</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest w-48">POLDA</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest w-48">POLRES</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest w-48">POLSEK</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest">NAMA POKTAN</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center w-36">LUAS (HA)</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center w-48">KOORDINAT</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-center w-32">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($data as $index => $row)
                        @php
                            $poldaName = $tingkatMap[$row->id_polda] ?? 'POLDA JAWA TIMUR';
                            $polresName = $tingkatMap[$row->id_polres] ?? '-';
                            $polsekName = $tingkatMap[$row->id_polsek] ?? $row->id_polsek;
                        @endphp
                        <tr class="group hover:bg-emerald-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm font-bold text-slate-500 text-center">{{ $data->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-slate-700 tracking-tight uppercase">{{ str_replace('POLDA ', '', $poldaName) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-slate-700 tracking-tight uppercase">{{ str_replace('POLRES ', '', $polresName) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-emerald-700 tracking-tight uppercase">{{ str_replace('POLSEK ', '', $polsekName) }}</span>
                                @if(isset($row->jumlah_lokasi) && $row->jumlah_lokasi > 1)
                                    <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-black rounded shadow-sm">
                                        +{{ $row->jumlah_lokasi - 1 }} LOKASI
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0 shadow-inner">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                    <span class="text-sm font-black text-slate-900 tracking-tight uppercase">{{ $row->nama_poktan }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 text-[11px] font-black rounded-lg shadow-sm">
                                    {{ number_format((float)$row->luas_lahan, 2) }} HA
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center justify-center gap-0.5">
                                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $row->latitude ?? '-' }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $row->longitude ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="openModal('{{ $row->nama_poktan }}')" class="px-4 py-2 bg-slate-900 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest transition-all shadow-md shadow-slate-900/20">
                                    Detail Kategori
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-1">TIDAK ADA DATA</h3>
                                    <p class="text-xs text-slate-500 font-medium">Belum ada data poktan yang sesuai dengan kriteria pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($data->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 mt-auto">
            {{ $data->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div x-show="showModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
         style="display: none;">

        <!-- Backdrop -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-250"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showModal = false"
             class="absolute inset-0"
             style="background: rgba(15,23,42,0.62); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"></div>

        <!-- Dialog -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative z-10 w-full max-w-6xl bg-white rounded-2xl overflow-hidden flex flex-col max-h-[90vh]"
             style="box-shadow: 0 32px 80px -16px rgba(0,0,0,0.32), 0 0 0 1px rgba(0,0,0,0.05);">
             
            {{-- Modal Header --}}
            <div class="flex-shrink-0">
                <div class="h-1 bg-gradient-to-r from-emerald-400 to-teal-500"></div>
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-slate-900 tracking-tight" x-text="activePoktanName"></h2>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">DETAIL SEBARAN LOKASI</p>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-slate-200 text-slate-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto flex-1 bg-white">
                <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-5 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest w-12 text-center">NO</th>
                                <th class="px-5 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">KESATUAN (POLSEK)</th>
                                <th class="px-5 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">LUAS (HA)</th>
                                <th class="px-5 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-center">KOORDINAT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in activeDetails" :key="index">
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4 text-sm font-bold text-slate-400 text-center" x-text="index + 1"></td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-emerald-700 uppercase" x-text="item.nama_tingkat"></span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="item.id_polsek"></span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-black rounded-md" x-text="item.luas_lahan + ' HA'"></span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded" x-text="item.latitude || '-'"></span>
                                            <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded" x-text="item.longitude || '-'"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="activeDetails.length === 0">
                                <td colspan="4" class="px-5 py-8 text-center text-sm font-medium text-slate-400">
                                    Tidak ada rincian lokasi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end shrink-0">
                <button @click="showModal = false" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[11px] font-black uppercase tracking-widest transition-all shadow-lg shadow-slate-900/20">
                    Tutup
                </button>
            </div>
        </div>
    </div>


</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const rawDetails = {!! json_encode($details ?? []) !!};
        const tingkatMap = {!! json_encode($tingkatMap ?? []) !!};

        Alpine.data('poktanData', () => ({
            selectedResor: '{{ $filters['resor'] ?? '' }}',
            selectedSektor: '{{ $filters['sektor'] ?? '' }}',
            search: '{{ $filters['search'] ?? '' }}',
            
            showModal: false,
            activePoktanName: '',
            activeDetails: [],

            submitFilters() {
                const params = new URLSearchParams();
                if (this.selectedResor) params.set('resor', this.selectedResor);
                if (this.selectedSektor) params.set('sektor', this.selectedSektor);
                if (this.search) params.set('search', this.search);
                
                window.location.href = '?' + params.toString();
            },

            resetFilters() {
                this.selectedResor = '';
                this.selectedSektor = '';
                this.search = '';
                this.submitFilters();
            },

            openModal(poktanName) {
                this.activePoktanName = poktanName;
                const items = rawDetails[poktanName] || [];
                
                // Map the id_polsek to nama_tingkat using tingkatMap
                this.activeDetails = items.map(item => {
                    return {
                        ...item,
                        nama_tingkat: tingkatMap[item.id_polsek] || item.id_polsek
                    };
                });
                
                this.showModal = true;
            }
        }));
    });
</script>
@endpush
@endsection
