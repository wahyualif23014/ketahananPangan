@extends('layouts.app')

@section('header', 'Log Aktivitas Sistem')

@section('content')
@php
$bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$aksiConfig = [
    'create'     => ['label'=>'Tambah Data',    'color'=>'emerald', 'icon'=>'M12 4v16m8-8H4'],
    'update'     => ['label'=>'Edit Data',      'color'=>'blue',    'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
    'delete'     => ['label'=>'Hapus Data',     'color'=>'rose',    'icon'=>'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
    'validasi'   => ['label'=>'Validasi',       'color'=>'teal',    'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    'unvalidasi' => ['label'=>'Batalkan Validasi','color'=>'amber','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    'verify'     => ['label'=>'Verifikasi',     'color'=>'indigo',  'icon'=>'M5 13l4 4L19 7'],
];

$modulLabels = [
    // Data Utama
    'komoditi'         => 'Komoditi',
    'jabatan'          => 'Jabatan',
    'wilayah'          => 'Wilayah (Koordinat)',
    'tingkat_kesatuan' => 'Tingkat Kesatuan',
    // Data Personel
    'anggota'          => 'Data Personel',
    // Kelola Lahan
    'potensi_lahan'    => 'Data Potensi Lahan',
    'tanam'            => 'Data Tanam',
    'panen'            => 'Data Panen',
    'serapan'          => 'Data Serapan / Distribusi',
];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    .aktivitas-container { font-family: 'Outfit', sans-serif; }
    .log-card { transition: all .25s ease; }
    .log-card:hover { transform: translateY(-2px); }
</style>

<div class="space-y-6 pb-24 aktivitas-container max-w-7xl mx-auto px-2">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 animate-in fade-in slide-in-from-top-6 duration-500">
        <div>
            <nav class="flex items-center gap-2 font-black tracking-[0.2em] uppercase text-slate-400 mb-2 text-[10px]">
                <span class="border-b-2 border-slate-300 pb-0.5">ADMIN</span>
                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                <span class="text-violet-600 border-b-2 border-violet-600 pb-0.5">Log Aktivitas</span>
            </nav>
            <h2 class="text-3xl lg:text-4xl font-black text-slate-800 tracking-tight uppercase leading-none drop-shadow-sm">
                LOG <span class="bg-clip-text text-transparent bg-gradient-to-r from-violet-500 to-purple-600">AKTIVITAS</span>
            </h2>
            <p class="mt-2 text-sm text-slate-500 font-medium">Riwayat semua aksi operator &amp; admin &mdash; dikelompokkan per bulan</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[11px] text-slate-400 font-medium hidden sm:block">Terakhir dimuat: <strong class="text-slate-600">{{ now()->format('H:i:s') }}</strong></span>
            <button onclick="window.location.reload()" title="Refresh Data"
                class="group flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-violet-600 text-violet-400 hover:text-white border border-slate-700 hover:border-violet-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95 shadow-lg shadow-slate-900/20">
                <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach($aksiConfig as $key => $cfg)
        @php
        $count = $stats[$key] ?? 0;
        $colors = [
            'emerald' => ['bg'=>'bg-emerald-50','border'=>'border-emerald-100','text'=>'text-emerald-700','icon'=>'bg-emerald-500'],
            'blue'    => ['bg'=>'bg-blue-50',   'border'=>'border-blue-100',   'text'=>'text-blue-700',   'icon'=>'bg-blue-500'],
            'rose'    => ['bg'=>'bg-rose-50',   'border'=>'border-rose-100',   'text'=>'text-rose-700',   'icon'=>'bg-rose-500'],
            'teal'    => ['bg'=>'bg-teal-50',   'border'=>'border-teal-100',   'text'=>'text-teal-700',   'icon'=>'bg-teal-500'],
            'amber'   => ['bg'=>'bg-amber-50',  'border'=>'border-amber-100',  'text'=>'text-amber-700',  'icon'=>'bg-amber-500'],
            'indigo'  => ['bg'=>'bg-indigo-50', 'border'=>'border-indigo-100', 'text'=>'text-indigo-700', 'icon'=>'bg-indigo-500'],
        ];
        $c = $colors[$cfg['color']];
        @endphp
        <div class="log-card {{ $c['bg'] }} border {{ $c['border'] }} rounded-2xl p-4 flex items-center gap-3 shadow-sm">
            <div class="w-9 h-9 {{ $c['icon'] }} rounded-xl flex items-center justify-center flex-shrink-0 shadow">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $cfg['icon'] }}"/>
                </svg>
            </div>
            <div>
                <p class="text-[10px] font-black {{ $c['text'] }} uppercase tracking-widest leading-none">{{ $cfg['label'] }}</p>
                <p class="text-xl font-black text-slate-800 mt-0.5">{{ $count }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FILTER FORM --}}
    <form method="GET" action="{{ route('admin.aktivitas.index') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">

            {{-- Bulan --}}
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Bulan</label>
                <select name="bulan" class="w-full h-10 px-3 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 outline-none">
                    @foreach($bulanNames as $num => $nama)
                        @if($num > 0)
                        <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Tahun --}}
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Tahun</label>
                <select name="tahun" class="w-full h-10 px-3 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 outline-none">
                    @foreach($tahunList as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                    @if($tahunList->isEmpty())
                    <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                    @endif
                </select>
            </div>

            {{-- Modul --}}
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modul</label>
                <select name="modul" class="w-full h-10 px-3 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 outline-none">
                    <option value="semua" {{ $modul === 'semua' ? 'selected' : '' }}>Semua Modul</option>
                    {{-- Grup: Data Utama --}}
                    <optgroup label="── Data Utama">
                        <option value="komoditi"         {{ $modul === 'komoditi'         ? 'selected' : '' }}>Komoditi</option>
                        <option value="jabatan"          {{ $modul === 'jabatan'          ? 'selected' : '' }}>Jabatan</option>
                        <option value="wilayah"          {{ $modul === 'wilayah'          ? 'selected' : '' }}>Wilayah (Koordinat)</option>
                        <option value="tingkat_kesatuan" {{ $modul === 'tingkat_kesatuan' ? 'selected' : '' }}>Tingkat Kesatuan</option>
                    </optgroup>
                    {{-- Grup: Data Personel --}}
                    <optgroup label="── Data Personel">
                        <option value="anggota" {{ $modul === 'anggota' ? 'selected' : '' }}>Data Personel</option>
                    </optgroup>
                    {{-- Grup: Kelola Lahan --}}
                    <optgroup label="── Kelola Lahan">
                        <option value="potensi_lahan" {{ $modul === 'potensi_lahan' ? 'selected' : '' }}>Data Potensi Lahan</option>
                        <option value="tanam"         {{ $modul === 'tanam'         ? 'selected' : '' }}>Data Tanam</option>
                        <option value="panen"         {{ $modul === 'panen'         ? 'selected' : '' }}>Data Panen</option>
                        <option value="serapan"       {{ $modul === 'serapan'       ? 'selected' : '' }}>Data Serapan</option>
                    </optgroup>
                    {{-- Modul lain dari DB yang tidak terdaftar --}}
                    @foreach($modulList->reject(fn($m) => in_array($m, ['komoditi','jabatan','wilayah','tingkat_kesatuan','anggota','potensi_lahan','tanam','panen','serapan'])) as $m)
                    <option value="{{ $m }}" {{ $modul === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Aksi --}}
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Aksi</label>
                <select name="aksi" class="w-full h-10 px-3 text-sm font-medium bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 outline-none">
                    <option value="semua" {{ $aksi === 'semua' ? 'selected' : '' }}>Semua Aksi</option>
                    @foreach($aksiConfig as $key => $cfg)
                    <option value="{{ $key }}" {{ $aksi === $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cari + Tombol --}}
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Cari</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama / Keterangan..."
                        class="w-full h-10 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-violet-400/30 focus:border-violet-400 outline-none">
                </div>
                <div class="flex flex-col justify-end">
                    <button type="submit" class="h-10 px-4 bg-violet-600 hover:bg-violet-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-sm active:scale-95">
                        Filter
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 bg-violet-400 rounded-full"></div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Aktivitas {{ $bulanNames[$bulan] ?? '' }} {{ $tahun }}</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $logs->total() }} entri ditemukan</p>
                </div>
            </div>
            <button onclick="window.location.reload()" title="Refresh"
                class="group flex items-center gap-2 px-3 py-1.5 bg-white/10 hover:bg-violet-500 text-violet-300 hover:text-white border border-white/10 hover:border-violet-500 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 active:scale-95">
                <svg class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>

        @if($logs->isEmpty())
        <div class="py-20 flex flex-col items-center justify-center text-slate-400">
            <svg class="w-16 h-16 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="font-semibold text-slate-500">Tidak ada aktivitas ditemukan</p>
            <p class="text-sm mt-1">pada periode {{ $bulanNames[$bulan] ?? '' }} {{ $tahun }}</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">#</th>
                        <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Waktu</th>
                        <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Operator/Admin</th>
                        <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Aksi</th>
                        <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Modul</th>
                        <th class="text-left px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Keterangan</th>
                        <th class="text-center px-5 py-3.5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($logs as $i => $log)
                    @php
                    $cfg = $aksiConfig[$log->aksi] ?? ['label'=>$log->aksi,'color'=>'slate','icon'=>'M13 16h-1v-4h-1m1-4h.01'];
                    $badgeColors = [
                        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'blue'    => 'bg-blue-50 text-blue-700 border-blue-200',
                        'rose'    => 'bg-rose-50 text-rose-700 border-rose-200',
                        'teal'    => 'bg-teal-50 text-teal-700 border-teal-200',
                        'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
                        'indigo'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'slate'   => 'bg-slate-50 text-slate-700 border-slate-200',
                    ];
                    $badgeCls = $badgeColors[$cfg['color']] ?? $badgeColors['slate'];
                    $modulLabel = $modulLabels[$log->modul] ?? ucfirst(str_replace('_', ' ', $log->modul));
                    @endphp
                    <tr class="hover:bg-violet-50/30 transition-colors group">
                        <td class="px-5 py-3.5 text-[11px] text-slate-400 font-medium">{{ $logs->firstItem() + $i }}</td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <p class="text-xs font-semibold text-slate-700">{{ $log->created_at->format('d M Y') }}</p>
                            <p class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i:s') }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-xs font-bold text-slate-800">{{ $log->nama_user ?? '-' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $log->username }} &bull;
                                <span class="{{ $log->role === 'admin' ? 'text-emerald-600' : 'text-amber-600' }} font-semibold uppercase">{{ $log->role }}</span>
                            </p>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $badgeCls }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $cfg['icon'] }}"/>
                                </svg>
                                {{ $cfg['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <p class="text-xs font-bold text-slate-700">{{ $modulLabel }}</p>
                            @if($log->label_modul)
                            <p class="text-[11px] text-slate-400 truncate max-w-[160px]">{{ $log->label_modul }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 max-w-xs">
                            <p class="text-[12px] text-slate-600 line-clamp-2">{{ $log->keterangan ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <a href="{{ route('admin.aktivitas.show', $log->id) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-[10px] font-black uppercase tracking-widest rounded-lg transition-all shadow-sm active:scale-95 group-hover:shadow-violet-500/30">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan {{ $logs->firstItem() }}&ndash;{{ $logs->lastItem() }} dari {{ $logs->total() }} entri</p>
            <div class="flex gap-1">
                @if($logs->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-200 rounded-lg">‹ Prev</span>
                @else
                <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">‹ Prev</a>
                @endif

                @foreach($logs->getUrlRange(max(1,$logs->currentPage()-2), min($logs->lastPage(),$logs->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="px-3 py-1.5 text-xs rounded-lg border transition-colors {{ $page === $logs->currentPage() ? 'bg-violet-600 text-white border-violet-600' : 'text-slate-600 border-slate-200 hover:bg-slate-50' }}">{{ $page }}</a>
                @endforeach

                @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">Next ›</a>
                @else
                <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-200 rounded-lg">Next ›</span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
