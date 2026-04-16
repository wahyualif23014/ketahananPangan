@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
<div class="min-h-screen bg-[#f8fafc] antialiased text-slate-900 pb-20" 
     style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">
    
    <div class="max-w-[1400px] mx-auto space-y-6 pt-2">

        {{-- [SEC 1] - HEADER SECTION (Rapat & Sejajar) --}}
        <div class="px-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 transition-all duration-700 animate-in fade-in slide-in-from-top-4">
            <div class="space-y-0.5">
                <nav class="flex items-center gap-2 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">
                    <span class="bg-indigo-50 text-indigo-500 px-2.5 py-1 rounded-md border border-indigo-100">Data Utama</span>
                    <svg class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600 tracking-wide bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">Potensi Lahan</span>
                </nav>
                <h2 class="text-3xl lg:text-5xl font-black tracking-tight text-slate-900 leading-tight">
                    Potensi <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-400">Lahan</span>
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

        @php
            // -- LOGIKA PENGAMBILAN DATA (Lahan dengan status_lahan = 2) -- //
            
            // 1. Distribusi per Jenis Lahan & Total Luas Area
            $kategoriMapping = [
                1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',
                2 => 'HUTAN (PERHUTANAN SOSIAL)',
                3 => 'LUAS BAKU SAWAH (LBS)',
                4 => 'PESANTREN',
                5 => 'MILIK POLRI',
                6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
                7 => 'PRODUKTIF (TUMPANG SARI)',
                8 => 'HUTAN (PERHUTANI/INHUTANI)',
                9 => 'LAHAN LAINNYA'
            ];

            $lahanGroups = DB::table('lahan')
                ->where('status_lahan', '1')
                ->where('deletestatus', '!=', '0')
                ->select(
                    'id_jenis_lahan', 
                    DB::raw('SUM(CAST(luas_lahan AS DECIMAL(15,2))) as total_luas'),
                    DB::raw('COUNT(DISTINCT id_wilayah) as total_lokasi')
                )
                ->groupBy('id_jenis_lahan')
                ->get()
                ->keyBy('id_jenis_lahan');

            $totalLuasGlobal = $lahanGroups->sum('total_luas');

            // 2. Sistem Peringatan: Ekstraksi Data id_wilayah & id_tingkat
            $lahanWilayahList = DB::table('lahan')
                ->where('status_lahan', '1')
                ->where('deletestatus', '!=', '0')
                ->pluck('id_wilayah')
                ->filter();

            $lahanTingkatList = DB::table('lahan')
                ->where('status_lahan', '1')
                ->where('deletestatus', '!=', '0')
                ->pluck('id_tingkat')
                ->filter()
                ->unique();
                
            $countPolsek = $lahanTingkatList->count();

            // Dekonstruksi id_wilayah untuk menghitung Kabupaten, Kecamatan, dan Kel/Desa secara unik (terlepas dari level mana ID itu disimpan)
            $uniqueKab = [];
            $uniqueKec = [];
            $uniqueDesa = [];

            foreach($lahanWilayahList as $idWilayah) {
                $parts = explode('.', $idWilayah);
                if(count($parts) >= 2) {
                    $uniqueKab[$parts[0].'.'.$parts[1]] = true;
                }
                if(count($parts) >= 3) {
                    $uniqueKec[$parts[0].'.'.$parts[1].'.'.$parts[2]] = true;
                }
                if(count($parts) >= 4) {
                    $uniqueDesa[$idWilayah] = true;
                }
            }
        @endphp

        {{-- [SEC 3] - MAIN CONTENT GRID --}}
        <div class="px-4 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- LEFT: Metric Highlight --}}
            <div class="lg:col-span-8">
                <div class="h-full bg-gradient-to-br from-emerald-600 to-teal-700 rounded-[2rem] border border-emerald-500 shadow-xl shadow-emerald-600/20 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-110 transition-transform duration-700">
                        <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 4.5l6.5 13h-13L12 6.5z"/></svg>
                    </div>
                    <div class="relative z-10 p-8 md:p-10">
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-10">
                            <div class="space-y-1">
                                <p class="text-[11px] font-black text-emerald-100/80 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                                    Capaian Luas Area
                                </p>
                                <div class="flex items-baseline gap-3">
                                    <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter leading-none drop-shadow-md">
                                        {{ number_format((float)$totalLuasGlobal, 2, '.', ',') }}
                                    </h1>
                                    <span class="text-lg font-black uppercase text-emerald-200">Ha</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/10">
                            <h4 class="text-[10px] font-black text-emerald-100 uppercase tracking-widest border-b border-emerald-500/30 pb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Distribusi Jenis Lahan
                            </h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                                @foreach($kategoriMapping as $id => $label)
                                    @php
                                        $valNode = $lahanGroups[$id] ?? null;
                                        $valLuas = $valNode ? $valNode->total_luas : 0;
                                        $valLokasi = $valNode ? $valNode->total_lokasi : 0;
                                    @endphp
                                    <div class="flex items-center justify-between py-3 border-b border-white/5 last:border-0 hover:bg-white/5 transition-colors px-3 rounded-xl group cursor-default">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full {{ $valLuas > 0 ? 'bg-emerald-300 shadow-[0_0_8px_rgba(110,231,183,0.8)]' : 'bg-white/20' }} transition-colors"></div>
                                            <span class="text-[10px] font-black uppercase tracking-wider {{ $valLuas > 0 ? 'text-white' : 'text-emerald-100/50' }} max-w-[130px] truncate" title="{{ $label }}">{{ $label }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-right flex-shrink-0">
                                            <div class="flex flex-col items-end leading-none gap-1">
                                                <div class="flex items-baseline gap-1">
                                                    <span class="text-[13px] font-black {{ $valLuas > 0 ? 'text-white' : 'text-emerald-100/30' }}">{{ number_format((float)$valLuas, 2, '.', ',') }}</span>
                                                    <span class="text-[9px] font-black uppercase tracking-wider {{ $valLuas > 0 ? 'text-emerald-200' : 'text-emerald-100/30' }}">Ha</span>
                                                </div>
                                                <span class="text-[9px] font-bold text-emerald-200/70 bg-black/20 px-1.5 py-0.5 rounded uppercase">{{ $valLokasi }} Titik Lokasi</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                {{-- Card 1: Peringatan & Info Jangkauan --}}
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm space-y-6 transition-all hover:border-blue-300 hover:shadow-blue-500/10 hover:shadow-xl relative overflow-hidden group">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-50 rounded-full blur-2xl group-hover:bg-blue-100 transition-colors"></div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Radar Jangkauan</h3>
                            <p class="text-sm font-bold text-slate-800">Wilayah Terdaftar</p>
                        </div>
                    </div>
                    
                    <div class="relative z-10 grid grid-cols-2 gap-4">
                        <!-- Kab/Kota -->
                        <div class="space-y-1 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center transition-all hover:bg-white hover:shadow-md hover:border-slate-200">
                            <span class="block text-2xl font-black text-slate-800 tracking-tight">{{ count($uniqueKab) }}</span>
                            <span class="block text-[9px] font-black text-slate-500 uppercase tracking-tighter">Kab/Kota</span>
                        </div>
                        <!-- Kecamatan -->
                        <div class="space-y-1 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center transition-all hover:bg-white hover:shadow-md hover:border-slate-200">
                            <span class="block text-2xl font-black text-slate-800 tracking-tight">{{ count($uniqueKec) }}</span>
                            <span class="block text-[9px] font-black text-slate-500 uppercase tracking-tighter">Kecamatan</span>
                        </div>
                        <!-- Desa -->
                        <div class="space-y-1 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center transition-all hover:bg-white hover:shadow-md hover:border-slate-200">
                            <span class="block text-2xl font-black text-slate-800 tracking-tight">{{ count($uniqueDesa) }}</span>
                            <span class="block text-[9px] font-black text-slate-500 uppercase tracking-tighter">Desa/Kelurahan</span>
                        </div>
                        <!-- Polsek -->
                        <div class="space-y-1 p-4 bg-slate-50 border border-slate-100 rounded-2xl text-center transition-all hover:bg-white hover:shadow-md hover:border-slate-200">
                            <span class="block text-2xl font-black text-slate-800 tracking-tight">{{ $countPolsek }}</span>
                            <span class="block text-[9px] font-black text-slate-500 uppercase tracking-tighter">Kesatuan</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Validasi --}}
                <div class="bg-indigo-600 p-8 rounded-[2rem] border border-indigo-500 shadow-lg shadow-indigo-600/20 flex flex-col justify-between group transition-all hover:bg-indigo-700 relative overflow-hidden">
                    <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-4 translate-y-4">
                        <svg class="w-32 h-32 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-indigo-300 uppercase tracking-widest leading-none">Menunggu Validasi</p>
                                <h4 class="text-3xl font-black text-white leading-tight">10.31 <span class="text-xs font-bold text-indigo-200 uppercase">Ha</span></h4>
                            </div>
                            <span class="px-2.5 py-1 bg-white/20 text-white text-[10px] font-black rounded-lg border border-white/10 uppercase tracking-widest backdrop-blur-md">0.29% Total</span>
                        </div>
                        <p class="text-xs text-indigo-200 leading-relaxed font-medium">Beberapa pendaftaran potensi lahan baru memerlukan verifikasi tingkat administrator.</p>
                    </div>
                    <button class="relative z-10 mt-6 w-full py-3.5 bg-white text-indigo-700 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-indigo-50 transition-all active:scale-[0.98] shadow-md">
                        Tinjau Validasi Lahan
                    </button>
                </div>
            </div>
        </div>

        {{-- [SEC 4] - DATA TABLE --}}
        <div id="tabel-potensi" x-data="potensiCRUD()" class="px-4 transition-all duration-700 animate-in fade-in slide-in-from-bottom-6">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100/80 border-b border-slate-200 text-xs font-black text-slate-700 uppercase tracking-wider text-left shadow-sm">
                                <th class="px-6 py-5 rounded-tl-2xl">Polisi Penggerak</th>
                                <th class="px-6 py-5">Penanggung Jawab</th>
                                <th class="px-6 py-5 text-center">Luas (HA)</th>
                                <th class="px-6 py-5">Proses</th>
                                <th class="px-6 py-5">Validasi</th>
                                <th class="px-6 py-5 text-center rounded-tr-2xl">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm bg-slate-50/30">
                            @forelse($lahans as $row)
                            <tr class="bg-white border-b border-slate-100 hover:bg-slate-50/80 transition-all group relative drop-shadow-sm hover:drop-shadow-md z-0 hover:z-10">
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 uppercase text-sm tracking-tight leading-none">{{ $row->cp_lahan ?? 'BELUM DIISI' }}</p>
                                            <p class="text-xs font-bold text-slate-500 mt-1 tracking-wider">{{ $row->no_cp_lahan ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800 uppercase text-sm tracking-tight leading-none">{{ $row->cp_polisi ?? 'BELUM DIISI' }}</p>
                                            <p class="text-xs font-bold text-slate-500 mt-1 tracking-wider">{{ $row->no_cp_polisi ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center align-middle">
                                    <span class="block text-base font-black text-slate-900 tracking-tighter">{{ number_format((float)$row->luas_lahan, 2) }}</span>
                                    <span class="block text-[10px] text-emerald-600 font-black uppercase tracking-widest mt-1.5 bg-emerald-50 px-2 py-1 rounded border border-emerald-200/50 w-fit mx-auto" title="{{ $kategoriMapping[$row->id_jenis_lahan] ?? 'Lainnya' }}">
                                        {{ Str::limit($kategoriMapping[$row->id_jenis_lahan] ?? 'LAINNYA', 15) }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-50 group-hover:border-indigo-200 group-hover:text-indigo-500 text-slate-400 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-700 uppercase tracking-tight">{{ $row->nama_editor ?? 'SISTEM' }}</span>
                                            @if($row->tgl_edit)
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">{{ \Carbon\Carbon::parse($row->tgl_edit)->format('d M Y - H:i') }}</span>
                                            @else
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Belum ada riwayat</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    @if($row->tgl_valid)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-emerald-50 border border-emerald-200 flex items-center justify-center flex-shrink-0 text-emerald-500 shadow-sm shadow-emerald-500/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-emerald-700 uppercase tracking-tight">{{ $row->nama_validator ?? 'ADMIN' }}</span>
                                            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-0.5">{{ \Carbon\Carbon::parse($row->tgl_valid)->format('d M Y - H:i') }}</span>
                                        </div>
                                    </div>
                                    @else
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0 text-amber-500 shadow-sm shadow-amber-500/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-amber-700 uppercase tracking-tight">Belum Tervalidasi</span>
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Menunggu respon</span>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center align-middle">
                                    <div class="flex items-center justify-center gap-1.5 opacity-40 group-hover:opacity-100 transition-opacity">
                                        <!-- View -->
                                        <button @click="openView({{ json_encode($row) }}, '{{ $kategoriMapping[$row->id_jenis_lahan] ?? 'Lainnya' }}')" title="Detail Profile" class="p-2 bg-slate-50 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all border border-slate-200 hover:scale-105 active:scale-95 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        
                                        <!-- Tog Valid -->
                                        <form action="{{ route('admin.kelola-lahan.potensi.verify', $row->id_lahan) }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <button type="submit" title="{{ $row->tgl_valid ? 'Cabut Validasi' : 'Setujui Validasi' }}" class="p-2 bg-slate-50 {{ $row->tgl_valid ? 'text-amber-500 hover:bg-amber-50 border-amber-200' : 'text-emerald-500 hover:bg-emerald-50 border-emerald-200' }} rounded-lg transition-all border hover:scale-105 active:scale-95 shadow-sm">
                                                @if($row->tgl_valid)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @endif
                                            </button>
                                        </form>

                                        <!-- Edit -->
                                        <button @click="openEdit({{ json_encode($row) }})" title="Ubah Data" class="p-2 bg-slate-50 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all border border-slate-200 hover:scale-105 active:scale-95 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        
                                        <!-- Delete -->
                                        <button @click="openDelete({{ json_encode($row) }})" title="Hapus Lahan" class="p-2 bg-rose-50 text-rose-400 hover:bg-rose-500 hover:text-white rounded-lg transition-all border border-rose-100 hover:scale-105 active:scale-95 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 bg-slate-50 border border-slate-100 shadow-inner rounded-full mb-4 group-hover:scale-110 transition-transform">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight mb-2">Data Lahan Nihil</h3>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest max-w-sm mx-auto">Sistem belum menemukan pendaftaran lahan baru dari kelompok tani manapun di seluruh wilayah.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($lahans->hasPages())
                <div class="px-6 py-5 border-t border-slate-100 bg-slate-50/50">
                    {{ $lahans->links() }}
                </div>
                @endif
            </div>

            {{-- ----------------------------- --}}
            {{-- MODALS SECTION (ALPINE JS)    --}}
            {{-- ----------------------------- --}}

            <!-- 1. VIEW MODAL -->
            <div x-show="isViewOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div @click.outside="isViewOpen = false" x-show="isViewOpen" x-transition.opacity.duration.300ms class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                    <div class="px-8 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Detail Potensi Lahan
                        </h3>
                        <button @click="isViewOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="p-8 overflow-y-auto custom-scrollbar space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Penggerak</p>
                                <p class="text-sm font-bold text-slate-800" x-text="activeData?.cp_lahan || '-'"></p>
                                <p class="text-xs text-slate-500" x-text="activeData?.no_cp_lahan || '-'"></p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Polisi T. Jawab</p>
                                <p class="text-sm font-bold text-slate-800" x-text="activeData?.cp_polisi || '-'"></p>
                                <p class="text-xs text-slate-500" x-text="activeData?.no_cp_polisi || '-'"></p>
                            </div>
                        </div>
                        <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500 mb-1" x-text="activeLabel"></p>
                            <h4 class="text-3xl font-black text-emerald-700"><span x-text="activeData?.luas_lahan || '0'"></span><span class="text-sm text-emerald-600 ml-1">HA</span></h4>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 border-b pb-2">Informasi Validasi</p>
                            <div class="text-xs font-medium text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-100" x-html="
                                activeData?.tgl_valid 
                                ? `<span class='text-emerald-500 font-bold'>✓ Tervalidasi</span> oleh ${activeData?.nama_validator || 'Admin'} pada ${activeData?.tgl_valid}` 
                                : `<span class='text-amber-500 font-bold'>⏳ Menunggu Validasi</span>` 
                            "></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. EDIT MODAL -->
            <div x-show="isEditOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div @click.outside="isEditOpen = false" x-show="isEditOpen" x-transition.opacity.duration.300ms class="bg-white rounded-[2rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
                    <div class="px-8 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
                        <h3 class="text-sm font-black text-white uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Ubah Data Lahan
                        </h3>
                        <button @click="isEditOpen = false" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form :action="`/admin/kelola-lahan/potensi/update/${activeData?.id_lahan}`" method="POST" class="flex-1 overflow-y-auto custom-scrollbar">
                        @csrf @method('PUT')
                        <div class="p-8 space-y-6">
                            
                            <!-- Penggerak -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nama Penggerak</label>
                                    <input type="text" name="cp_lahan" :value="activeData?.cp_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kontak Penggerak</label>
                                    <input type="text" name="no_cp_lahan" :value="activeData?.no_cp_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                            </div>
                            <!-- Penanggung Jawab -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nama T. Jawab</label>
                                    <input type="text" name="cp_polisi" :value="activeData?.cp_polisi" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Kontak T. Jawab</label>
                                    <input type="text" name="no_cp_polisi" :value="activeData?.no_cp_polisi" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                            </div>
                            <!-- Lahan Detail -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Luas Lahan (HA)</label>
                                    <input type="number" step="0.01" name="luas_lahan" :value="activeData?.luas_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Jenis Lahan</label>
                                    <select name="id_jenis_lahan" class="w-full text-xs font-bold bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none uppercase" x-model="activeData.id_jenis_lahan">
                                        @foreach($kategoriMapping as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="pt-4 flex gap-3">
                                <button type="button" @click="isEditOpen = false" class="flex-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl transition-all">Batal</button>
                                <button type="submit" class="flex-1 bg-indigo-600 text-white hover:bg-indigo-700 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. DELETE MODAL -->
            <div x-show="isDeleteOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
                <div @click.outside="isDeleteOpen = false" x-show="isDeleteOpen" x-transition.opacity.duration.300ms class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden border border-rose-100 flex flex-col items-center text-center p-8">
                    <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-2 uppercase">Hapus Data?</h3>
                    <p class="text-xs text-slate-500 font-medium mb-8">Data lahan seluas <strong class="text-rose-500" x-text="activeData?.luas_lahan + ' HA'"></strong> milik <strong class="text-slate-700 uppercase" x-text="activeData?.cp_lahan"></strong> akan dihapus sementara dari sistem.</p>
                    
                    <form :action="`/admin/kelola-lahan/potensi/destroy/${activeData?.id_lahan}`" method="POST" class="w-full flex gap-3">
                        @csrf @method('DELETE')
                        <button type="button" @click="isDeleteOpen = false" class="flex-1 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl transition-all">Batal</button>
                        <button type="submit" class="flex-1 bg-rose-500 text-white hover:bg-rose-600 uppercase tracking-widest text-[10px] font-black py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">Ya, Hapus</button>
                    </form>
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

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('potensiCRUD', () => ({
            isViewOpen: false,
            isEditOpen: false,
            isDeleteOpen: false,
            activeData: null,
            activeLabel: '',

            openView(data, label) {
                this.activeData = data;
                this.activeLabel = label;
                this.isViewOpen = true;
            },
            openEdit(data) {
                this.activeData = data || {};
                this.isEditOpen = true;
            },
            openDelete(data) {
                this.activeData = data;
                this.isDeleteOpen = true;
            }
        }))
    });
</script>
@endsection