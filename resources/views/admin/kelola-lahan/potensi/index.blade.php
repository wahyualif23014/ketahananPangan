@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
<div x-data="potensiLahanManager()" class="min-h-screen antialiased text-slate-900 pb-20"
    style="font-family: 'Inter', system-ui, -apple-system, sans-serif;">

    <div class="max-w-[1400px] mx-auto space-y-6 pt-4">

        {{-- [SEC 1] - HEADER SECTION --}}
        <div class="px-4 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-1">
                <nav class="flex items-center gap-2 text-[11px] font-medium uppercase tracking-wider text-slate-400">
                    <span>Data Utama</span>
                    <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600">Potensi Lahan</span>
                </nav>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    Potensi Lahan <span class="text-slate-400 font-normal ml-1">Statistik</span>
                </h2>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
                <div class="relative w-full sm:w-64 group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari lokasi atau personel..."
                        class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none">
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button onclick="window.location.reload()" title="Refresh Data"
                        class="p-2 bg-white text-slate-500 rounded-lg border border-slate-200 hover:bg-slate-50 hover:text-emerald-600 transition-colors shadow-sm active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                    {{-- Trigger Modal Tambah --}}
                    <button @click="openModal()"
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg shadow-sm hover:bg-slate-800 transition-all active:scale-95 font-semibold text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Tambah Data
                    </button>
                </div>
            </div>
        </div>

        {{-- [SEC 2] - COMPACT FILTER BAR --}}
        <div class="px-4">
            <div class="bg-white p-1 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-1">
                <div class="grid grid-cols-1 sm:grid-cols-3 flex-1 gap-1">
                    <select
                        class="bg-transparent border-none text-sm font-medium text-slate-600 focus:ring-0 cursor-pointer py-2 px-4 rounded-lg hover:bg-slate-50">
                        <option value="">Semua Resor</option>
                    </select>
                    <select
                        class="bg-transparent border-none text-sm font-medium text-slate-600 focus:ring-0 cursor-pointer py-2 px-4 rounded-lg hover:bg-slate-50">
                        <option value="">Semua Sektor</option>
                    </select>
                    <select
                        class="bg-transparent border-none text-sm font-medium text-slate-600 focus:ring-0 cursor-pointer py-2 px-4 rounded-lg hover:bg-slate-50">
                        <option value="">Jenis Lahan</option>
                    </select>
                </div>

                <button @click="is_validated = !is_validated"
                    :class="is_validated ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-50 text-slate-500 border-transparent'"
                    class="flex items-center justify-between px-4 py-2 border rounded-lg transition-all min-w-[180px]">
                    <span class="text-[11px] font-bold uppercase tracking-tight"
                        x-text="is_validated ? 'Tervalidasi' : 'Belum Validasi'"></span>
                    <div :class="is_validated ? 'bg-emerald-500' : 'bg-slate-300'" class="w-1.5 h-1.5 rounded-full">
                    </div>
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
        <div class="px-4 grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Statistik Utama --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden h-full">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Capaian
                                    Luas Area</p>
                                <div class="flex items-baseline gap-2">
                                    <h1 class="text-4xl font-bold text-slate-900 tracking-tighter">170,969<span
                                            class="text-slate-300">.02</span></h1>
                                    <span class="text-slate-400 font-medium">Ha</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 rounded-md text-xs font-bold border border-emerald-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    12.5%
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 uppercase font-medium">MoM Growth</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">
                                Distribusi Kategori Lahan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1">
                                @foreach($cats ?? [] as $c)
                                <div
                                    class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors px-2 rounded-md group">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-1.5 h-1.5 rounded-full bg-slate-200 group-hover:bg-emerald-500 transition-colors">
                                        </div>
                                        <span class="text-sm text-slate-600 font-medium">{{ $c['label'] }}</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">{{ $c['val'] }} <small
                                            class="text-slate-400 font-normal ml-0.5">Ha</small></span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Side Panels --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-7 h-7 bg-amber-50 text-amber-600 rounded flex items-center justify-center border border-amber-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Status Operasional</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <span class="block text-2xl font-bold text-slate-900">66</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Polsek Aktif</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg">
                            <span class="block text-2xl font-bold text-slate-900">5.6k</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Desa Binaan</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-slate-900 p-5 rounded-xl shadow-lg shadow-slate-900/10 text-white relative overflow-hidden group">
                    <div class="relative z-10 space-y-4">
                        <div>
                            <p class="text-blue-300 text-[10px] font-bold uppercase tracking-widest mb-1">Validasi
                                Tertunda</p>
                            <h4 class="text-3xl font-bold">10.31 <span
                                    class="text-sm font-normal text-blue-300/60 uppercase">Ha</span></h4>
                        </div>
                        <button
                            class="w-full py-2 bg-white/10 hover:bg-white/20 border border-white/10 rounded-lg text-xs font-semibold transition-all">
                            Proses Verifikasi Sekarang
                        </button>
                    </div>
                    <svg class="absolute right-[-20%] bottom-[-20%] w-48 h-48 opacity-10 group-hover:scale-110 transition-transform"
                        fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- [SEC 4] - RESPONSIVE DATA TABLE --}}
        <div id="tabel-potensi" class="px-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    Polisi Penggerak</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    Penanggung Jawab</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">
                                    Luas (HA)</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    Status Validasi</th>
                                <th
                                    class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-50">
                            {{-- Group Header --}}
                            <tr class="bg-emerald-50/20">
                                <td colspan="5" class="px-6 py-2">
                                    <div
                                        class="flex items-center gap-2 text-[10px] font-bold text-emerald-700 uppercase">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Kab. Bangkalan, Kec. Arosbaya, Desa Dlemer
                                    </div>
                                </td>
                            </tr>
                            {{-- Row Loop (Static for UI example, update with @foreach) --}}
                            @forelse($lahans as $lahan)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4" data-label="Personel">
                                    <div class="font-semibold text-slate-900">Bambang Priono</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">+62 878-4523-7310</div>
                                </td>
                                <td class="px-6 py-4" data-label="PJ">
                                    <div class="text-slate-700">Rohmatulloh</div>
                                    <div class="text-[10px] text-slate-400">Dusun Ronceh</div>
                                </td>
                                <td class="px-6 py-4 text-center" data-label="Luas">
                                    <div class="text-base font-bold text-slate-900 tracking-tighter">3.50</div>
                                    <span
                                        class="inline-block px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-bold rounded uppercase">Produktif</span>
                                </td>
                                <td class="px-6 py-4" data-label="Validasi">
                                    <div class="space-y-1">
                                        <span
                                            class="block text-[10px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded w-fit">Achmad
                                            Furkon (Polda)</span>
                                        <span class="block text-[10px] font-bold text-emerald-600">Terverifikasi: Dwi
                                            Achmat</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right" data-label="Aksi">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Trigger Modal Edit --}}
                                        <button
                                            @click="openModal({id: 1, nama_personel: 'Bambang Priono', pj_lahan: 'Rohmatulloh', luas: '3.50', jenis_lahan: 'Produktif'})"
                                            class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-all"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteItem(1)"
                                            class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-all"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
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

    {{-- [MODAL] - PROGRESSIVE STEP FORM --}}
    <template x-teleport="body">
        <div x-show="isOpen" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>

            <div class="flex items-center justify-center min-h-screen p-4">
                {{-- Overlay --}}
                <div @click="closeModal()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity">
                </div>

                {{-- Modal Content --}}
                <div x-show="isOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col max-h-[90vh]">

                    {{-- Header & Step Indicator --}}
                    <div class="bg-white border-b border-slate-100 px-8 py-6">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M9 20l-5.447-2.724A2 2 0 013 15.382V6.418a2 2 0 011.106-1.789L9 2m0 18l6-3m-6 3V7.5m6 9.5l5.447 2.724A2 2 0 0021 17.618V8.582a2 2 0 00-1.106-1.789L15 4m0 13V4m0 0L9 7.5"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight"
                                        x-text="isEdit ? 'Sunting Potensi Lahan' : 'Tambah Potensi Lahan'"></h3>
                                    <p class="text-xs text-slate-500">Lengkapi informasi aset ketahanan pangan secara
                                        bertahap.</p>
                                </div>
                            </div>
                            <button @click="closeModal()"
                                class="text-slate-400 hover:text-slate-600 p-2 hover:bg-slate-50 rounded-full transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" />
                                </svg>
                            </button>
                        </div>

                        {{-- Step Progress Bar --}}
                        <div class="flex items-center justify-between max-w-3xl mx-auto relative">
                            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
                            <div class="absolute top-1/2 left-0 h-0.5 bg-emerald-500 -translate-y-1/2 z-0 transition-all duration-500"
                                :style="`width: ${(currentStep - 1) / (totalSteps - 1) * 100}%` "></div>

                            <template x-for="step in totalSteps">
                                <div class="relative z-10 flex flex-col items-center gap-2">
                                    <div :class="currentStep >= step ? 'bg-emerald-600 text-white scale-110 shadow-lg shadow-emerald-100' : 'bg-white text-slate-400 border-2 border-slate-100'"
                                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300">
                                        <template x-if="currentStep > step">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                        </template>
                                        <span x-show="currentStep <= step" x-text="step"></span>
                                    </div>
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider transition-colors duration-300"
                                        :class="currentStep >= step ? 'text-emerald-700' : 'text-slate-400'"
                                        x-text="['Institusi', 'Personel', 'Teknis', 'Lokasi'][step-1]"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Main Form Body --}}
                    <form @submit.prevent="saveData()" class="flex flex-col md:flex-row flex-2 overflow-hidden">

                        {{-- LEFT: Form Inputs --}}
                        <div class="flex-1 overflow-y-auto p-8 space-y-8 scroll-smooth">

                            {{-- STEP 1: Institusi & Klasifikasi --}}
                            <div x-show="isStep(1)" x-transition.opacity.duration.400ms class="space-y-6">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Institusi &
                                        Klasifikasi</h4>
                                    <p class="text-xs text-slate-500">Tentukan kesatuan kepolisian dan kategori lahan.
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600 ml-1">Kepolisian
                                            Resor</label>
                                        <select x-model="formData.id_resor"
                                            class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-600 outline-none transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%2364748B%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:20px_20px] bg-[right_12px_center] bg-no-repeat">
                                            <option value="">PILIH KEPOLISIAN RESOR</option>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600 ml-1">Kepolisian
                                            Sektor</label>
                                        <select x-model="formData.id_sektor"
                                            class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-600 outline-none transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%2364748B%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:20px_20px] bg-[right_12px_center] bg-no-repeat">
                                            <option value="">PILIH KEPOLISIAN SEKTOR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-600 ml-1">Jenis Lahan</label>
                                    <select x-model="formData.jenis_lahan"
                                        class="w-full h-11 px-4 bg-white border border-emerald-200 rounded-xl text-sm font-bold text-emerald-800 focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-600 outline-none transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207.5L10%2012.5L15%207.5%22%20stroke%3D%22%23059669%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22/%3E%3C/svg%3E')] bg-[length:20px_20px] bg-[right_12px_center] bg-no-repeat shadow-sm">
                                        <option value="">PILIH JENIS LAHAN</option>
                                    </select>
                                    <p class="text-[10px] text-slate-400 italic">* Klasifikasi lahan menentukan proses
                                        validasi lanjutan.</p>
                                </div>
                            </div>

                            {{-- STEP 2: Data Personel --}}
                            <div x-show="isStep(2)" x-transition.opacity.duration.400ms class="space-y-8">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Data Personel</h4>
                                    <p class="text-xs text-slate-500">Informasi polisi penggerak dan penanggung jawab di
                                        lapangan.</p>
                                </div>

                                {{-- Polisi Penggerak --}}
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                                    <h5
                                        class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                stroke-width="2" />
                                        </svg>
                                        Polisi Penggerak
                                    </h5>
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-7 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Nama Lengkap
                                                Personel</label>
                                            <input type="text" x-model="formData.nama_personel"
                                                class="w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all"
                                                placeholder="Contoh: Aiptu John Doe">
                                        </div>
                                        <div class="col-span-12 md:col-span-5 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Nomor
                                                WhatsApp</label>
                                            <div class="flex">
                                                <span
                                                    class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-slate-500 text-xs font-bold">+62</span>
                                                <input type="text" x-model="formData.hp_personel"
                                                    class="w-full h-10 px-4 bg-white border border-slate-200 rounded-r-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Penanggung Jawab --}}
                                <div class="p-6 bg-white rounded-2xl border border-slate-200 space-y-4">
                                    <h5
                                        class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                stroke-width="2" />
                                        </svg>
                                        Penanggung Jawab Lahan
                                    </h5>
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-7 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Nama Penanggung
                                                Jawab</label>
                                            <input type="text" x-model="formData.pj_lahan"
                                                class="w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all"
                                                placeholder="Nama Pemilik / Ketua Poktan">
                                        </div>
                                        <div class="col-span-12 md:col-span-5 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Kontak Person</label>
                                            <div class="flex">
                                                <span
                                                    class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-slate-500 text-xs font-bold">+62</span>
                                                <input type="text" x-model="formData.hp_pj"
                                                    class="w-full h-10 px-4 bg-white border border-slate-200 rounded-r-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all">
                                            </div>
                                        </div>
                                        <div class="col-span-12 space-y-1.5">
                                            <label class="text-xs font-medium text-slate-500 ml-1">Keterangan
                                                Peran</label>
                                            <input type="text" x-model="formData.ket_pj"
                                                class="w-full h-10 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all"
                                                placeholder="Contoh: Ketua Kelompok Tani Mulyo">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 3: Data Teknis Lahan --}}
                            <div x-show="isStep(3)" x-transition.opacity.duration.400ms class="space-y-6">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Data Teknis Lahan</h4>
                                    <p class="text-xs text-slate-500">Detail produktivitas dan kapasitas lahan yang
                                        didaftarkan.</p>
                                </div>

                                <div class="grid grid-cols-3 gap-6">
                                    <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-3">
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center block">Jml.
                                            Poktan</label>
                                        <input type="number" x-model="formData.jml_poktan"
                                            class="w-full text-center text-xl font-bold bg-slate-50 border-none rounded-lg h-12 focus:ring-0"
                                            placeholder="0">
                                        <p class="text-[9px] text-slate-400 text-center font-medium leading-tight">Total
                                            kelompok tani terdaftar</p>
                                    </div>
                                    <div
                                        class="p-5 bg-emerald-50 border border-emerald-100 rounded-2xl shadow-sm space-y-3">
                                        <label
                                            class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest text-center block">Luas
                                            Lahan (HA)</label>
                                        <input type="number" step="0.01" x-model="formData.luas"
                                            class="w-full text-center text-xl font-bold bg-white border-none rounded-lg h-12 text-emerald-700 focus:ring-2 focus:ring-emerald-200"
                                            placeholder="0.00">
                                        <p
                                            class="text-[9px] text-emerald-500 text-center font-medium leading-tight tracking-tighter">
                                            Luas total area dalam Hektar</p>
                                    </div>
                                    <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-3">
                                        <label
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center block">Jml.
                                            Petani</label>
                                        <input type="number" x-model="formData.jml_petani"
                                            class="w-full text-center text-xl font-bold bg-slate-50 border-none rounded-lg h-12 focus:ring-0"
                                            placeholder="0">
                                        <p class="text-[9px] text-slate-400 text-center font-medium leading-tight">Total
                                            anggota petani aktif</p>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-600 ml-1">Komoditi Utama</label>
                                    <select x-model="formData.komoditi"
                                        class="w-full h-11 px-4 bg-white border border-slate-200 rounded-xl text-sm font-medium outline-none focus:border-emerald-600 transition-all">
                                        <option value="AKASIA">AKASIA</option>
                                        <option value="JAGUNG">JAGUNG</option>
                                        <option value="PADI">PADI</option>
                                        <option value="SINGKONG">SINGKONG</option>
                                    </select>
                                </div>
                            </div>

                            {{-- STEP 4: Lokasi & Dokumentasi --}}
                            <div x-show="isStep(4)" x-transition.opacity.duration.400ms class="space-y-6">
                                <div class="space-y-1 border-l-4 border-emerald-500 pl-4 py-1">
                                    <h4 class="text-base font-bold text-slate-800 tracking-tight">Lokasi & Dokumentasi
                                    </h4>
                                    <p class="text-xs text-slate-500">Koordinat geospasial dan bukti foto lokasi lahan.
                                    </p>
                                </div>


                                <div class="space-y-4">
                                    {{-- Alamat --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Alamat Lengkap Lahan</label>
                                        <textarea x-model="formData.alamat" rows="2"
                                            class="w-full p-4 bg-white border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-600 transition-all placeholder:text-slate-300"
                                            placeholder="Contoh: Jl. Raya Kediri No. 12, RT/RW..."></textarea>
                                    </div>

                                    {{-- Wilayah Selects --}}
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-400 uppercase ml-1">Kabupaten</label>
                                            <select
                                                class="w-full h-10 px-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-emerald-500">
                                                <option>PILIH KABUPATEN</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-400 uppercase ml-1">Kecamatan</label>
                                            <select
                                                class="w-full h-10 px-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-emerald-500">
                                                <option>PILIH KECAMATAN</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-400 uppercase ml-1">Desa</label>
                                            <select
                                                class="w-full h-10 px-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:border-emerald-500">
                                                <option>PILIH DESA</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Catatan --}}
                                    <div class="space-y-1.5">
                                        <label
                                            class="text-xs font-semibold text-slate-700 uppercase tracking-tighter">Catatan
                                            Tambahan</label>
                                        <textarea x-model="formData.keterangan_lain" rows="2"
                                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:bg-white focus:border-emerald-600 transition-all"
                                            placeholder="Catatan mengenai akses jalan atau kondisi tanah..."></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Visuals (Peta & Dokumentasi) --}}
                            <div
                                :class="currentStep === 4 ? 'flex' : 'hidden md:flex'"
                                class="flex-col w-full lg:w-[650px] xl:w-[700px] bg-slate-50/50 md:border-l border-t md:border-t-0 border-slate-100 overflow-y-auto shrink-0">
                                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                                    {{-- Map Section --}}
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Titik
                                                Koordinat</h4>
                                            <button type="button" @click="getCurrentLocation()"
                                                class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                                        stroke-width="2.5" />
                                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2.5" />
                                                </svg>
                                                Gunakan GPS
                                            </button>
                                        </div>

                                        {{-- Map Display --}}
                                        <div
                                            class="relative w-full h-64 bg-slate-200 rounded-2xl overflow-hidden border border-slate-200 shadow-inner group">
                                            <div id="map" class="w-full h-full z-0"></div> {{-- ID PENTING UNTUK LEAFLET
                                                --}}

                                            {{-- Overlay info --}}
                                            <div
                                                class="absolute bottom-2 left-2 right-2 flex justify-between items-center z-[10] pointer-events-none">
                                                <div
                                                    class="bg-white/90 backdrop-blur px-2 py-1 rounded shadow-sm border border-slate-200 text-[9px] font-bold text-slate-500 uppercase tracking-tighter">
                                                    Geser marker ke lokasi lahan
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Lat/Lng Inputs --}}
                                        <div class="grid grid-cols-2 gap-3">
                                            <div
                                                class="bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:border-emerald-500">
                                                <span
                                                    class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Latitude</span>
                                                <input type="text" x-model="formData.lat"
                                                    class="text-sm font-bold text-slate-800 bg-transparent border-none p-0 w-full focus:ring-0 outline-none"
                                                    placeholder="-0.0000">
                                            </div>
                                            <div
                                                class="bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:border-emerald-500">
                                                <span
                                                    class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Longitude</span>
                                                <input type="text" x-model="formData.lng"
                                                    class="text-sm font-bold text-slate-800 bg-transparent border-none p-0 w-full focus:ring-0 outline-none"
                                                    placeholder="000.0000">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Photo Section --}}
                                    <div class="space-y-4">
                                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                                            Dokumentasi Foto</h4>

                                        <div class="relative group cursor-pointer">
                                            <input type="file" class="hidden" id="land_photo"
                                                @change="handleFileUpload">
                                            <label for="land_photo"
                                                class="block w-full aspect-video bg-white border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center text-slate-400 group-hover:border-emerald-400 group-hover:bg-emerald-50/30 transition-all cursor-pointer overflow-hidden">
                                                <template x-if="!imagePreview">
                                                    <div class="flex flex-col items-center">
                                                        <svg class="w-8 h-8 mb-2 group-hover:scale-110 group-hover:text-emerald-500 transition-all"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                                stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" />
                                                        </svg>
                                                        <span class="text-[10px] font-bold uppercase">Upload Foto
                                                            Lahan</span>
                                                    </div>
                                                </template>
                                                <template x-if="imagePreview">
                                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                                </template>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Navigation --}}
                            <div
                                class="px-8 py-4 bg-white border-t border-slate-100 flex items-center justify-between sticky bottom-0 z-10">
                                <div>
                                    <button type="button" @click="closeModal()"
                                        class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest">
                                        Batal
                                    </button>
                                </div>

                                <div class="flex items-center gap-3">
                                    {{-- Back Button --}}
                                    <button type="button" x-show="currentStep > 1" @click="prevStep()"
                                        class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M15 19l-7-7 7-7" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        Kembali
                                    </button>

                                    {{-- Next Button --}}
                                    <button type="button" x-show="currentStep < totalSteps" @click="nextStep()"
                                        class="px-8 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg shadow-slate-200 active:scale-95">
                                        Lanjut
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>

                                    {{-- Final Submit Button --}}
                                    <button type="submit" x-show="currentStep === totalSteps" :disabled="isLoading"
                                        class="px-10 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-200 active:scale-95 disabled:opacity-50">
                                        <template x-if="isLoading">
                                            <svg class="animate-spin h-3 w-3 text-white" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                        </template>
                                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Selesaikan Pendaftaran'"></span>
                                        <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
    </template>

    <script>
        function potensiLahanManager() {
            return {
                currentStep: 1,
                totalSteps: 4,
                isOpen: false,
                isEdit: false,
                isLoading: false,
                is_validated: false,
                imagePreview: null,
                mapInstance: null,
                markerInstance: null,
                formData: {
                    id: null,
                    id_resor: '',
                    id_sektor: '',
                    jenis_lahan: 'Produktif',
                    luas: '',
                    nama_personel: '',
                    pj_lahan: '',
                    lat: '',
                    lng: '',
                    alamat: '',
                    keterangan_lain: '',
                    jml_poktan: '',
                    jml_petani: '',
                    komoditi: '',
                    hp_personel: '',
                    hp_pj: '',
                    ket_pj: ''
                },

                init() {
                    this.$watch('formData.lat', () => this.updateMarkerFromInput());
                    this.$watch('formData.lng', () => this.updateMarkerFromInput());
                },

                nextStep() {
                    if (this.currentStep < this.totalSteps) {
                        this.currentStep++;
                        if (this.currentStep === 4) {
                            setTimeout(() => {
                                this.initMap();
                                if (this.mapInstance) this.mapInstance.invalidateSize();
                            }, 500);
                        }
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) this.currentStep--;
                },

                isStep(step) {
                    return this.currentStep === step;
                },

                initMap() {
                    if (this.mapInstance) {
                        setTimeout(() => this.mapInstance.invalidateSize(), 400);
                        return;
                    }

                    setTimeout(() => {
                        const defaultLat = -7.250445;
                        const defaultLng = 112.768845;
                        const initialLat = this.formData.lat ? parseFloat(this.formData.lat) : defaultLat;
                        const initialLng = this.formData.lng ? parseFloat(this.formData.lng) : defaultLng;

                        this.mapInstance = L.map('map').setView([initialLat, initialLng], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.mapInstance);

                        this.markerInstance = L.marker([initialLat, initialLng], {
                            draggable: true
                        }).addTo(this.mapInstance);

                        this.markerInstance.on('dragend', (e) => {
                            const position = e.target.getLatLng();
                            this.formData.lat = position.lat.toFixed(6);
                            this.formData.lng = position.lng.toFixed(6);
                        });

                        this.mapInstance.on('click', (e) => {
                            const {
                                lat,
                                lng
                            } = e.latlng;
                            this.markerInstance.setLatLng([lat, lng]);
                            this.formData.lat = lat.toFixed(6);
                            this.formData.lng = lng.toFixed(6);
                        });

                        this.mapInstance.invalidateSize();
                    }, 200);
                },

                updateMarkerFromInput() {
                    const lat = parseFloat(this.formData.lat);
                    const lng = parseFloat(this.formData.lng);
                    if (!isNaN(lat) && !isNaN(lng) && this.mapInstance && this.markerInstance) {
                        const newLatLng = new L.LatLng(lat, lng);
                        this.markerInstance.setLatLng(newLatLng);
                        this.mapInstance.panTo(newLatLng);
                    }
                },

                getCurrentLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;

                                this.formData.lat = lat.toFixed(6);
                                this.formData.lng = lng.toFixed(6);

                                if (this.mapInstance && this.markerInstance) {
                                    const newLatLng = new L.LatLng(lat, lng);
                                    this.markerInstance.setLatLng(newLatLng);
                                    this.mapInstance.setView(newLatLng, 15);
                                }
                            },
                            (error) => {
                                alert('Gagal mendapatkan lokasi GPS: ' + error.message);
                            }, {
                                enableHighAccuracy: true
                            }
                        );
                    } else {
                        alert('Browser Anda tidak mendukung Geolocation.');
                    }
                },

                handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                openModal(item = null) {
                    if (item) {
                        this.isEdit = true;
                        this.formData = {
                            ...this.formData,
                            ...item
                        };
                    } else {
                        this.isEdit = false;
                        this.resetForm();
                    }
                    this.isOpen = true;
                    this.initMap();
                },

                closeModal() {
                    this.isOpen = false;
                    this.resetForm();
                },

                resetForm() {
                    this.formData = {
                        id: null,
                        id_resor: '',
                        id_sektor: '',
                        jenis_lahan: 'Produktif',
                        luas: '',
                        nama_personel: '',
                        pj_lahan: '',
                        lat: '',
                        lng: '',
                        alamat: '',
                        keterangan_lain: '',
                        jml_poktan: '',
                        jml_petani: '',
                        komoditi: '',
                        hp_personel: '',
                        hp_pj: '',
                        ket_pj: ''
                    };
                    this.currentStep = 1;
                    this.imagePreview = null;
                    if (this.mapInstance && this.markerInstance) {
                        const defaultLat = -7.250445;
                        const defaultLng = 112.768845;
                        this.markerInstance.setLatLng([defaultLat, defaultLng]);
                        this.mapInstance.setView([defaultLat, defaultLng], 13);
                    }
                },

                async saveData() {
                    this.isLoading = true;

                    // Method & URL Selection
                    const method = this.isEdit ? 'PUT' : 'POST';
                    const url = this.isEdit ? `/api/potensi-lahan/${this.formData.id}` : '/api/potensi-lahan';

                    try {
                        // Logic Fetch API (Aktifkan jika backend sudah siap)
                        /*
                        const response = await fetch(url, {
                            method: method,
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.formData)
                        });
                        */

                        // Simulasi Proses
                        await new Promise(r => setTimeout(r, 1000));

                        console.log('Success:', this.formData);
                        this.closeModal();
                        // window.location.reload(); 
                    } catch (e) {
                        alert('Gagal menyimpan data.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                deleteItem(id) {
                    if (confirm('Apakah Anda yakin ingin menghapus data lahan ini?')) {
                        console.log('Menghapus ID:', id);
                        // Tambahkan logic fetch DELETE di sini
                    }
                }
            }
        }
    </script>

    @endsection
