@extends('layouts.app')

@section('header', 'Detail Aktivitas')

@section('content')
@php
$aksiConfig = [
    'create'     => ['label'=>'Tambah Data',       'color'=>'emerald', 'icon'=>'M12 4v16m8-8H4'],
    'update'     => ['label'=>'Edit / Update Data', 'color'=>'blue',    'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
    'delete'     => ['label'=>'Hapus Data',         'color'=>'rose',    'icon'=>'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
    'validasi'   => ['label'=>'Validasi Data',      'color'=>'teal',    'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    'unvalidasi' => ['label'=>'Batalkan Validasi',  'color'=>'amber',   'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    'verify'     => ['label'=>'Verifikasi',         'color'=>'indigo',  'icon'=>'M5 13l4 4L19 7'],
];

$modulLabels = [
    'komoditi'         => 'Komoditi',
    'jabatan'          => 'Jabatan',
    'wilayah'          => 'Wilayah (Koordinat)',
    'tingkat_kesatuan' => 'Tingkat Kesatuan',
    'anggota'          => 'Data Personel',
    'potensi_lahan'    => 'Data Potensi Lahan',
    'tanam'            => 'Data Tanam',
    'panen'            => 'Data Panen',
    'serapan'          => 'Data Serapan / Distribusi',
];

$cfg = $aksiConfig[$log->aksi] ?? ['label'=>$log->aksi,'color'=>'slate','icon'=>'M13 16h-1v-4h-1m1-4h.01'];
$colors = [
    'emerald' => ['bg'=>'bg-emerald-500','badge'=>'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'blue'    => ['bg'=>'bg-blue-500',   'badge'=>'bg-blue-50 text-blue-700 border-blue-200'],
    'rose'    => ['bg'=>'bg-rose-500',   'badge'=>'bg-rose-50 text-rose-700 border-rose-200'],
    'teal'    => ['bg'=>'bg-teal-500',   'badge'=>'bg-teal-50 text-teal-700 border-teal-200'],
    'amber'   => ['bg'=>'bg-amber-500',  'badge'=>'bg-amber-50 text-amber-700 border-amber-200'],
    'indigo'  => ['bg'=>'bg-indigo-500', 'badge'=>'bg-indigo-50 text-indigo-700 border-indigo-200'],
    'slate'   => ['bg'=>'bg-slate-500',  'badge'=>'bg-slate-50 text-slate-700 border-slate-200'],
];
$c          = $colors[$cfg['color']] ?? $colors['slate'];
$modulLabel = $modulLabels[$log->modul] ?? ucfirst(str_replace('_', ' ', $log->modul));

// Hitung field yang berubah
$changedKeys = [];
if ($dataLama && $dataBaru) {
    foreach ($dataBaru as $k => $v) {
        if (isset($dataLama[$k]) && (string)$dataLama[$k] !== (string)$v) {
            $changedKeys[] = $k;
        }
    }
}
$allKeys = array_unique(array_merge(
    $dataLama ? array_keys($dataLama) : [],
    $dataBaru ? array_keys($dataBaru) : []
));
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');
    body, .detail-container { font-family: 'Outfit', sans-serif; }

    .field-card { transition: all .2s ease; }
    .field-card:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(0,0,0,.06); }

    .val-chip {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        word-break: break-all;
        line-height: 1.4;
    }
    .val-chip.neutral  { background: #f1f5f9; color: #334155; }
    .val-chip.removed  { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
    .val-chip.added    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .val-chip.null-val { background: #f8fafc; color: #94a3b8; font-style: italic; border: 1px dashed #cbd5e1; font-size: 11px; }

    .changed-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 7px;
        background: #fef9c3;
        border: 1px solid #fde047;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
        color: #854d0e;
        text-transform: uppercase;
        letter-spacing: .1em;
        white-space: nowrap;
    }
    .topo-dots {
        background-image: radial-gradient(rgba(255,255,255,.1) 1px, transparent 1px);
        background-size: 18px 18px;
    }
    .data-table tr:last-child td { border-bottom: none; }
</style>

<div class="space-y-7 pb-28 detail-container max-w-6xl mx-auto px-2">

    {{-- Back --}}
    <a href="{{ route('admin.aktivitas.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-violet-600 font-semibold transition-colors group">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Log Aktivitas
    </a>

    {{-- ═══ HEADER CARD ═══ --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-8 py-7 flex flex-col sm:flex-row sm:items-center gap-5 relative overflow-hidden topo-dots">
            <div class="absolute -right-10 -top-10 w-40 h-40 {{ $c['bg'] }} opacity-10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="w-16 h-16 {{ $c['bg'] }} rounded-2xl flex items-center justify-center shadow-2xl flex-shrink-0 relative z-10">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}"/>
                </svg>
            </div>
            <div class="relative z-10 flex-1 min-w-0">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em] mb-1">Detail Aktivitas &mdash; ID #{{ $log->id }}</p>
                <h2 class="text-2xl font-black text-white leading-tight truncate">{{ $cfg['label'] }} &mdash; {{ $modulLabel }}</h2>
                @if($log->label_modul)
                <p class="text-sm text-slate-300 mt-1 font-medium">{{ $log->label_modul }}</p>
                @endif
            </div>
            @if(count($changedKeys) > 0)
            <div class="relative z-10 flex-shrink-0 bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-center">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Field Berubah</p>
                <p class="text-4xl font-black text-yellow-400 leading-none">{{ count($changedKeys) }}</p>
                <p class="text-[9px] font-bold text-slate-500 mt-0.5">dari {{ count($allKeys) }} field</p>
            </div>
            @endif
        </div>

        {{-- Meta grid --}}
        <div class="p-6 md:p-8 grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Waktu</p>
                <p class="text-sm font-black text-slate-800">{{ $log->created_at->format('d F Y') }}</p>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">{{ $log->created_at->format('H:i:s') }} WIB</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Operator</p>
                <p class="text-sm font-black text-slate-800 truncate">{{ $log->nama_user ?? '-' }}</p>
                <p class="text-xs font-medium mt-0.5">
                    <span class="text-slate-400">{{ $log->username }}</span>
                    &bull;
                    <span class="{{ $log->role === 'admin' ? 'text-emerald-600' : 'text-amber-600' }} font-black uppercase">{{ $log->role }}</span>
                </p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Aksi</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $c['badge'] }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $cfg['icon'] }}"/></svg>
                    {{ $cfg['label'] }}
                </span>
                <p class="text-xs text-slate-500 font-semibold mt-2">{{ $modulLabel }}</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">IP Address</p>
                <p class="text-sm font-black text-slate-800 font-mono">{{ $log->ip_address ?? '-' }}</p>
                @if($log->record_id)
                <p class="text-xs text-violet-600 font-black mt-1">Record #{{ $log->record_id }}</p>
                @endif
            </div>
            @if($log->keterangan)
            <div class="bg-violet-50 rounded-2xl p-4 border border-violet-100 col-span-2 lg:col-span-4">
                <p class="text-[9px] font-black text-violet-500 uppercase tracking-widest mb-1.5">Keterangan</p>
                <p class="text-sm font-semibold text-slate-700">{{ $log->keterangan }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ═══ DATA COMPARISON ═══ --}}
    @if($dataLama || $dataBaru)

    {{-- ── MODE UPDATE: tabel perbandingan berdampingan ── --}}
    @if($dataLama && $dataBaru && $log->aksi === 'update')
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-500 delay-100">
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 topo-dots">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-8 bg-blue-400 rounded-full"></div>
                <div>
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Perbandingan Data</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ count($allKeys) }} field &bull; <span class="text-yellow-400 font-black">{{ count($changedKeys) }} perubahan</span> terdeteksi</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-xl text-[10px] font-black uppercase tracking-widest">
                    <span class="w-2 h-2 bg-rose-400 rounded-full"></span>Sebelum
                </span>
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                <span class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-xl text-[10px] font-black uppercase tracking-widest">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>Sesudah
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full data-table min-w-[640px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="text-left px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width:20%">Field</th>
                        <th class="text-left px-6 py-4 text-[10px] font-black text-rose-500 uppercase tracking-widest" style="width:38%">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-rose-500 rounded-full inline-block"></span>
                                Data Sebelum
                            </div>
                        </th>
                        <th class="text-left px-6 py-4 text-[10px] font-black text-emerald-600 uppercase tracking-widest" style="width:42%">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full inline-block"></span>
                                Data Sesudah (Terkini)
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allKeys as $idx => $key)
                    @php
                    $oldVal  = $dataLama[$key] ?? null;
                    $newVal  = $dataBaru[$key] ?? null;
                    $changed = (string)$oldVal !== (string)$newVal;
                    $rowBg   = $changed ? 'bg-amber-50/50' : ($idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/40');
                    @endphp
                    <tr class="{{ $rowBg }} border-b border-slate-100/80 hover:bg-blue-50/20 transition-colors">
                        <td class="px-6 py-4 align-middle">
                            <div class="flex flex-col gap-1">
                                <span class="text-[11px] font-black text-slate-700 font-mono uppercase tracking-wide">{{ $key }}</span>
                                @if($changed)
                                <span class="changed-badge">&#x26A1; Berubah</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 align-middle">
                            @if(is_null($oldVal) || $oldVal === '')
                            <span class="val-chip null-val">— kosong —</span>
                            @else
                            <span class="val-chip {{ $changed ? 'removed' : 'neutral' }}">{{ is_array($oldVal) ? json_encode($oldVal, JSON_UNESCAPED_UNICODE) : $oldVal }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 align-middle">
                            <div class="flex items-center gap-3 flex-wrap">
                                @if(is_null($newVal) || $newVal === '')
                                <span class="val-chip null-val">— kosong —</span>
                                @else
                                <span class="val-chip {{ $changed ? 'added' : 'neutral' }}">{{ is_array($newVal) ? json_encode($newVal, JSON_UNESCAPED_UNICODE) : $newVal }}</span>
                                @endif
                                @if($changed)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    Updated
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary footer --}}
        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-rose-400 rounded-full"></span>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Data lama (sebelum diubah)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-emerald-500 rounded-full"></span>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Data baru (terkini)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="changed-badge">&#x26A1; Berubah</span>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Field yang mengalami perubahan nilai</span>
            </div>
        </div>
    </div>

    {{-- ── MODE CREATE / DELETE: card per-field ── --}}
    @else
    <div class="grid grid-cols-1 {{ ($dataLama && $dataBaru) ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} gap-6">

        {{-- Data Lama (DELETE / before) --}}
        @if($dataLama)
        <div class="bg-white rounded-3xl border border-rose-100 shadow-xl overflow-hidden animate-in fade-in slide-in-from-left-4 duration-500 delay-100">
            <div class="bg-gradient-to-r from-rose-900 to-rose-800 px-8 py-5 flex items-center justify-between topo-dots relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-28 h-28 bg-rose-500 opacity-20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 bg-rose-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-white text-sm uppercase tracking-widest">Data Sebelum</h3>
                        <p class="text-[10px] text-rose-300 mt-0.5">{{ count($dataLama) }} field tercatat</p>
                    </div>
                </div>
                <span class="relative z-10 px-3 py-1.5 bg-rose-500/20 text-rose-200 border border-rose-400/30 rounded-xl text-[10px] font-black uppercase tracking-widest">Data Lama</span>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($dataLama as $key => $val)
                <div class="field-card bg-rose-50/60 border border-rose-100 rounded-2xl p-4">
                    <p class="text-[9px] font-black text-rose-400 uppercase tracking-widest font-mono mb-2">{{ $key }}</p>
                    <span class="val-chip removed">{{ is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : ($val !== null && $val !== '' ? $val : '— kosong —') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Data Baru (CREATE / after) --}}
        @if($dataBaru)
        <div class="bg-white rounded-3xl border border-emerald-100 shadow-xl overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500 delay-200">
            <div class="bg-gradient-to-r from-emerald-900 to-emerald-800 px-8 py-5 flex items-center justify-between topo-dots relative overflow-hidden">
                <div class="absolute -right-6 -top-6 w-28 h-28 bg-emerald-500 opacity-20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-white text-sm uppercase tracking-widest">Data Sesudah</h3>
                        <p class="text-[10px] text-emerald-300 mt-0.5">{{ count($dataBaru) }} field tercatat</p>
                    </div>
                </div>
                <span class="relative z-10 px-3 py-1.5 bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 rounded-xl text-[10px] font-black uppercase tracking-widest">Data Baru</span>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($dataBaru as $key => $val)
                @php $changed = $dataLama && isset($dataLama[$key]) && (string)$dataLama[$key] !== (string)$val; @endphp
                <div class="field-card {{ $changed ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50/60 border-slate-100' }} border rounded-2xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <p class="text-[9px] font-black {{ $changed ? 'text-emerald-600' : 'text-slate-400' }} uppercase tracking-widest font-mono">{{ $key }}</p>
                        @if($changed)<span class="changed-badge">&#x26A1; Berubah</span>@endif
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="val-chip {{ $changed ? 'added' : 'neutral' }}">{{ is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : ($val !== null && $val !== '' ? $val : '— kosong —') }}</span>
                        @if($changed)
                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @endif

    @else
    <div class="bg-slate-50 rounded-3xl border border-slate-200 p-12 text-center">
        <svg class="w-12 h-12 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-slate-500 font-bold text-sm uppercase tracking-widest">Tidak ada snapshot data</p>
        <p class="text-slate-400 text-xs mt-1">Aktivitas ini tidak menyimpan perbandingan data</p>
    </div>
    @endif

</div>
@endsection
