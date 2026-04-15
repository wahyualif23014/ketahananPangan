@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
<div class="min-h-screen bg-[#f8fafc] antialiased text-slate-900 pb-20" 
     style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">
    
    <div class="max-w-[1400px] mx-auto space-y-6 pt-2">

        {{-- [SEC 1] - HEADER SECTION (Rapat & Sejajar) --}}
        <div class="px-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all duration-700 animate-in fade-in slide-in-from-top-4">
            <div class="space-y-0.5">
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <span>Data Utama</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600 font-semibold tracking-wide">Potensi Lahan</span>
                </nav>
                <h2 class="text-3xl lg:text-4xl font-medium tracking-tight text-slate-900 leading-tight">
                    Potensi <span class="text-emerald-500 font-normal">Lahan</span>
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari data lokasi..."
                        class="block w-full md:w-72 pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm shadow-sm transition-all outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500">
                </div>
                
                <button onclick="window.location.reload()" title="Refresh"
                    class="p-2.5 bg-white text-slate-500 rounded-xl border border-slate-200 hover:bg-slate-50 hover:text-emerald-600 transition-all shadow-sm active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>

                <button class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition-all active:scale-95 font-bold text-sm tracking-wide">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Data
                </button>
            </div>
        </div>

        {{-- [SEC 2] - FILTER BAR --}}
        <div x-data="{ filter_resor: '', filter_sektor: '', filter_lahan: '', is_validated: false }" class="px-4">
            <div class="bg-white p-1.5 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-center gap-1.5">
                <select x-model="filter_resor"
                    class="w-full lg:flex-1 bg-transparent border-none rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 focus:ring-0 cursor-pointer py-2.5 px-4">
                    <option value="">Pilih Resor</option>
                </select>
                <div class="hidden lg:block w-px h-6 bg-slate-200 self-center"></div>
                <select x-model="filter_sektor"
                    class="w-full lg:flex-1 bg-transparent border-none rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 focus:ring-0 cursor-pointer py-2.5 px-4">
                    <option value="">Pilih Sektor</option>
                </select>
                <div class="hidden lg:block w-px h-6 bg-slate-200 self-center"></div>
                <select x-model="filter_lahan"
                    class="w-full lg:flex-1 bg-transparent border-none rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 focus:ring-0 cursor-pointer py-2.5 px-4">
                    <option value="">Jenis Lahan</option>
                </select>
                
                <button @click="is_validated = !is_validated"
                    :class="is_validated ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white text-slate-400 border-slate-200'"
                    class="w-full lg:w-56 flex items-center justify-between px-4 py-2.5 border rounded-xl transition-all shadow-sm group">
                    <span class="text-[10px] font-black uppercase tracking-widest" x-text="is_validated ? 'Tervalidasi' : 'Belum Validasi'"></span>
                    <div :class="is_validated ? 'bg-emerald-500' : 'bg-slate-300'" class="w-1.5 h-1.5 rounded-full transition-colors"></div>
                </button>
            </div>
        </div>

        {{-- [SEC 3] - MAIN CONTENT GRID --}}
        <div class="px-4 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- LEFT: Metric Highlight --}}
            <div class="lg:col-span-8">
                <div class="h-full bg-white rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-slate-300">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-8">
                            <div class="space-y-0.5">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">Capaian Luas Area</p>
                                <div class="flex items-baseline gap-2">
                                    <h1 class="text-4xl md:text-5xl font-semibold text-slate-900 tracking-tight leading-none">
                                        170,969<span class="text-slate-300">.02</span>
                                    </h1>
                                    <span class="text-lg font-medium text-slate-400">Ha</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:items-end gap-1">
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100/50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    <span class="text-xs font-bold">12.5%</span>
                                </div>
                                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-tighter">vs bulan lalu</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Distribusi Kategori Lahan</h4>
                            
                            @php
                                $cats = [
                                    ['label' => 'Milik Polri', 'val' => '9.63'],
                                    ['label' => 'Poktan Binaan', 'val' => '34,903.96'],
                                    ['label' => 'Masyarakat', 'val' => '27,320.94'],
                                    ['label' => 'Hutan Sosial', 'val' => '20,690.15'],
                                    ['label' => 'LBS (Sawah)', 'val' => '65,013.95'],
                                    ['label' => 'Lainnya', 'val' => '108.02'],
                                ];
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                                @foreach($cats as $c)
                                    <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors px-2 rounded-lg group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-1 h-1 rounded-full bg-slate-300 group-hover:bg-emerald-500 transition-colors"></div>
                                            <span class="text-sm font-medium text-slate-500">{{ $c['label'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 font-semibold">
                                            <span class="text-sm text-slate-800">{{ $m['val'] ?? $c['val'] }}</span>
                                            <span class="text-[10px] text-slate-400 font-medium uppercase">Ha</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Side Panels --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Card 1: Peringatan --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3 transition-all hover:border-slate-300">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center border border-amber-100/50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sistem Peringatan</h3>
                    </div>
                    <div class="space-y-0">
                        <h4 class="text-xl font-semibold text-slate-800 leading-tight">0 Polres Nihil</h4>
                        <p class="text-[10px] font-medium text-slate-400 uppercase">Status Saat Ini</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                        <div class="space-y-0">
                            <span class="text-lg font-semibold text-slate-800 tracking-tight">66</span>
                            <span class="block text-[10px] font-medium text-slate-400 uppercase tracking-tighter">Polsek Aktif</span>
                        </div>
                        <div class="space-y-0">
                            <span class="text-lg font-semibold text-slate-800 tracking-tight">5,607</span>
                            <span class="block text-[10px] font-medium text-slate-400 uppercase tracking-tighter">Desa Binaan</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Validasi --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between group transition-all hover:border-slate-300">
                    <div class="space-y-3">
                        <div class="flex justify-between items-start">
                            <div class="space-y-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Menunggu Validasi</p>
                                <h4 class="text-2xl font-semibold text-slate-800 leading-tight">10.31 <span class="text-sm font-medium text-slate-400 uppercase">Ha</span></h4>
                            </div>
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[9px] font-bold rounded-full border border-blue-100 uppercase tracking-tighter">0.29% Total</span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">Potensi lahan baru yang memerlukan verifikasi tingkat Polda.</p>
                    </div>
                    <button class="mt-4 w-full py-2.5 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-all active:scale-[0.98]">
                        Lihat Detail
                    </button>
                </div>
            </div>
        </div>

        {{-- [SEC 4] - DATA TABLE --}}
        <div id="tabel-potensi" class="px-4 transition-all duration-700 animate-in fade-in slide-in-from-bottom-6">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-5">Polisi Penggerak</th>
                                <th class="px-8 py-5">Penanggung Jawab</th>
                                <th class="px-8 py-5 text-center">Luas (HA)</th>
                                <th class="px-8 py-5">Status & Validasi</th>
                                <th class="px-8 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="bg-emerald-50/30 border-y border-emerald-100/50">
                                <td colspan="5" class="px-8 py-4 font-bold text-emerald-800 uppercase tracking-tight text-[11px]">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Kab. Bangkalan, Kec. Arosbaya, Desa Dlemer
                                    </span>
                                </td>
                            </tr>
                            {{-- Row Loop --}}
                            <tr class="hover:bg-slate-50/50 transition-colors border-b border-slate-50">
                                <td class="px-8 py-6">
                                    <p class="font-bold text-slate-800 uppercase text-xs">Bambang Priono</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">+62 878-4523-7310</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="font-bold text-slate-800 uppercase text-xs">Rohmatulloh</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Dusun Ronceh</p>
                                </td>
                                <td class="px-8 py-6 text-center font-semibold">
                                    <span class="text-base text-slate-900 tracking-tighter">3.50</span>
                                    <span class="block text-[9px] text-emerald-500 font-bold uppercase tracking-wider">Produktif</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="text-[10px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md w-fit uppercase">Achmad Furkon</span>
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md w-fit uppercase border border-emerald-100">Validated: Dwi Achmat</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button class="p-2.5 bg-white text-slate-400 border border-slate-100 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all shadow-sm active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button class="p-2.5 bg-white text-slate-400 border border-slate-100 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all shadow-sm active:scale-95">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        #tabel-potensi thead { display: none; }
        #tabel-potensi table, #tabel-potensi tbody, #tabel-potensi tr, #tabel-potensi td { display: block; width: 100%; }
        #tabel-potensi tr { margin-bottom: 1.5rem; background: white; border: 1px solid #e2e8f0; border-radius: 1.5rem; padding: 1rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        #tabel-potensi td { border: none; padding: 0.75rem 0.5rem; display: flex; justify-content: space-between; align-items: center; }
        #tabel-potensi td:before { content: attr(data-label); font-weight: 800; text-transform: uppercase; font-size: 10px; color: #94a3b8; letter-spacing: 0.1em; }
    }
</style>
@endsection