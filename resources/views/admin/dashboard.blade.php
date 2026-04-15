@extends('layouts.app')

@section('header', 'Dashboard Utama Administrator')

@section('content')
    <div class="space-y-6 pb-12 font-sans" style="font-family: 'Ramabhadra', sans-serif;">
        {{-- header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-slate-200 pb-4 mb-2">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-slate-800 uppercase tracking-tighter leading-none">
                    Dashboard
                </h1>
                <p class="text-[10px] md:text-[11px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-2 italic">
                    Welcome back, <span
                        class="text-emerald-500 border-b border-emerald-200">{{ Auth::user()->nama_anggota }}</span>
                </p>
            </div>

            <div class="mt-4 md:mt-0 text-right hidden md:block">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Sistem</p>
                <div class="flex items-center gap-2 justify-end mt-1">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-tighter italic">Live Monitoring
                        Active</span>
                </div>
            </div>
        </div>

        {{-- section2 --}}
        <div class="space-y-6 pt-2 antialiased text-slate-900">
            {{-- potensi --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">

                    <div
                        class="lg:col-span-4 p-6 md:p-8 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-slate-100">
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Total Potensi Lahan</p>
                            <div class="flex items-baseline gap-2">
                                <h1 class="text-4xl md:text-5xl font-semibold text-slate-900 tracking-tight">170,715.11</h1>
                                <span class="text-sm font-medium text-slate-400 uppercase">Ha</span>
                            </div>
                            <div class="pt-4 flex">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider">
                                    Periode 2026
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-8 p-6 md:p-8 bg-slate-50/30">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Distribusi Kategori
                            Lahan</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-3">
                            @php
                                $potensi1 = ['Milik Polri' => '9.63', 'Produktif (Poktan)' => '34,882.86', 'Produktif (Masyarakat)' => '27,316.49', 'Produktif (Tumpang Sari)' => '27,316.49'];
                                $potensi2 = ['Hutan (Perhutani)' => '22,573.23', 'Luas Baku Sawah' => '64,792.29', 'Pesantren' => '64,792.9', 'Lainnya' => '107.52'];
                            @endphp

                            <div class="space-y-3">
                                @foreach($potensi1 as $label => $val)
                                    <div class="flex justify-between items-center group transition-all">
                                        <span
                                            class="text-xs font-medium text-slate-500 group-hover:text-slate-900 transition-colors">{{ $label }}</span>
                                        <span class="text-sm font-semibold text-slate-900">{{ $val }} <small
                                                class="text-slate-400 font-normal uppercase text-[10px] ml-0.5">Ha</small></span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="space-y-3">
                                @foreach($potensi2 as $label => $val)
                                    <div class="flex justify-between items-center group transition-all">
                                        <span
                                            class="text-xs font-medium text-slate-500 group-hover:text-slate-900 transition-colors">{{ $label }}</span>
                                        <span class="text-sm font-semibold text-slate-900">{{ $val }} <small
                                                class="text-slate-400 font-normal uppercase text-[10px] ml-0.5">Ha</small></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- panen tanam --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div
                    class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col transition-all hover:border-blue-200">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Planting Analytics</p>
                            <div class="flex items-baseline gap-2">
                                <h3 class="text-3xl font-semibold text-slate-900 tracking-tight">242.74</h3>
                                <span class="text-xs font-medium text-slate-400 uppercase">Ha</span>
                            </div>
                            <p class="text-xs font-medium text-slate-500">Total Lahan Tanam (2026)</p>
                        </div>
                        <div
                            class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded-md border border-blue-100 text-[10px] font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M5 10l7-7m0 0l7 7m-7-7v18" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                            0%
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/30 flex-1 space-y-3">
                        @foreach(['Milik Polri' => '0', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.1'] as $label => $val)
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-slate-500">{{ $label }}</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $val }} <small
                                        class="text-slate-400 font-normal uppercase text-[10px] ml-0.5">Ha</small></span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col transition-all hover:border-emerald-200">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Harvesting Analytics
                            </p>
                            <div class="flex items-baseline gap-2">
                                <h3 class="text-3xl font-semibold text-slate-900 tracking-tight">243.72</h3>
                                <span class="text-xs font-medium text-slate-400 uppercase">Ha</span>
                            </div>
                            <p class="text-xs font-medium text-slate-500">Total Lahan Panen (2026)</p>
                        </div>
                        <div
                            class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-100 text-[10px] font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M5 10l7-7m0 0l7 7m-7-7v18" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                            0%
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50/30 flex-1 space-y-3">
                        @foreach(['Milik Polri' => '0.98', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.1'] as $label => $val)
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-slate-500">{{ $label }}</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $val }} <small
                                        class="text-slate-400 font-normal uppercase text-[10px] ml-0.5">Ha</small></span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- ==============================================================================
        4. SECTION CHART, LOGS, KWARTAL & MAPS
        ============================================================================== --}}

        {{-- Tren & Real-time Logs --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 my-14">
            <div
                class="lg:col-span-2 bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10 relative z-10">
                    <div>
                        <h3 class="font-black text-slate-800 uppercase tracking-tighter text-2xl italic leading-none">Tren
                            <span class="text-blue-600">Hasil</span> Panen
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Statistik luasan
                            produktif per-periode</p>
                    </div>
                    <div class="flex bg-slate-100 p-1.5 rounded-2xl border border-slate-200 shadow-inner">
                        <button
                            class="px-5 py-2 text-[9px] font-black bg-white rounded-xl shadow-md text-slate-800 uppercase tracking-widest">Bulanan</button>
                        <button
                            class="px-5 py-2 text-[9px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest">Tahunan</button>
                    </div>
                </div>
                <div class="h-72 relative z-10"><canvas id="productivityChart"></canvas></div>
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center gap-6 relative z-10">
                    <div class="flex flex-col"><span
                            class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Total Yield
                            2026</span><span class="text-sm font-black text-slate-800 italic">128,429.00 Ha</span></div>
                    <div class="flex flex-col"><span
                            class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Growth Rate</span><span
                            class="text-sm font-black text-emerald-500 italic">+12.4% ↑</span></div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl flex flex-col">
                <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg mb-8">Log Aktivitas <span
                        class="text-emerald-500">Real-time</span></h3>
                <div class="flex-1 space-y-6 overflow-y-auto pr-2 custom-scrollbar">
                    @php
                        $logs = [['title' => 'Input Lahan Baru', 'user' => 'Bripka Ahmad', 'satwil' => 'Polres Gresik', 'time' => '2 mnt lalu', 'color' => 'emerald'], ['title' => 'Update Panen', 'user' => 'Bripda Bayu', 'satwil' => 'Polres Malang', 'time' => '15 mnt lalu', 'color' => 'blue'], ['title' => 'Validasi Data', 'user' => 'Iptu Sanjaya', 'satwil' => 'Polres Kediri', 'time' => '32 mnt lalu', 'color' => 'amber']];
                    @endphp
                    @foreach($logs as $log)
                        <div class="group flex items-start gap-4 relative">
                            <div class="relative">
                                <div class="w-2.5 h-2.5 mt-1.5 rounded-full bg-{{ $log['color'] }}-500 z-10 relative"></div>
                                <div
                                    class="absolute top-1.5 left-1/2 -translate-x-1/2 w-[1px] h-full bg-slate-100 group-last:hidden">
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight">{{ $log['title'] }}
                                </p>
                                <p class="text-[9px] text-slate-400 font-bold uppercase mt-0.5">{{ $log['user'] }} —
                                    {{ $log['satwil'] }}
                                </p>
                                <p class="text-[8px] text-{{ $log['color'] }}-500 font-black mt-1 uppercase">{{ $log['time'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button
                    class="w-full mt-6 py-3.5 bg-slate-900 text-[10px] font-black text-white hover:bg-emerald-600 rounded-2xl transition-all uppercase tracking-widest shadow-lg shadow-slate-900/20 active:scale-95">Lihat
                    Seluruh Aktivitas</button>
            </div>
        </div>

        {{-- Kwartal Performance --}}
        <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl my-10">
            <div class="mb-10 relative z-10">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] mb-2">Quarterly Performance</p>
                <h3 class="font-black text-slate-800 uppercase tracking-tighter text-2xl italic leading-none">Monitoring
                    Target & Hasil <span class="text-emerald-500">Kwartal</span></h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Rangkuman progres capaian
                    per-triwulan tahun 2026</p>
            </div>
            <div class="grid grid-cols-1 gap-6 relative z-10">
                @php
                    $kwartalData = [['category' => 'Milik Polri', 'unit' => 'Ha', 'q1' => '9.63', 'q2' => '0', 'q3' => '0', 'q4' => '0', 'color' => 'blue'], ['category' => 'Produktif (Poktan Binaan)', 'unit' => 'Ha', 'q1' => '34,882.86', 'q2' => '107.08', 'q3' => '0', 'q4' => '0', 'color' => 'emerald'], ['category' => 'Hasil Panen Kwartal', 'unit' => 'Ton', 'q1' => '988.92', 'q2' => '0', 'q3' => '0', 'q4' => '0', 'color' => 'orange']];
                @endphp
                @foreach($kwartalData as $row)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                        @for($i = 1; $i <= 4; $i++)
                            <div
                                class="relative overflow-hidden bg-slate-50/50 p-5 rounded-[2rem] border border-slate-100 transition-all duration-500 hover:bg-{{ $row['color'] }}-50/80 hover:border-{{ $row['color'] }}-200 hover:shadow-lg group">
                                <div class="relative z-10 flex flex-col h-full justify-between">
                                    <div>
                                        <p
                                            class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-{{ $row['color'] }}-600">
                                            {{ $row['category'] }}
                                        </p>
                                        <h4 class="text-xl font-black text-slate-800 tracking-tighter leading-none">
                                            {{ $row['q' . $i] }} <span
                                                class="text-[9px] font-normal uppercase">{{ $row['unit'] }}</span>
                                        </h4>
                                    </div>
                                    <p
                                        class="text-[9px] font-black text-{{ $row['color'] }}-500 uppercase tracking-widest mt-4 flex items-center gap-1.5 italic">
                                        Q{{ $i }} 2026</p>
                                </div>
                                <span
                                    class="absolute right-1 -bottom-3 text-7xl font-black text-{{ $row['color'] }}-900/10 italic select-none group-hover:scale-105 transition-all">{{ $i }}</span>
                            </div>
                        @endfor
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Operational Detail Widgets --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 my-10">
            @php
                $opStatsData = [
                    ['label' => 'Panen Normal', 'val' => $harvestStats['normal'] ?? '0', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0', 'theme' => ['base_bg' => 'hover:bg-emerald-50/90', 'base_brd' => 'hover:border-emerald-200', 'icon_bg' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'icon_hv' => 'group-hover:bg-emerald-500', 'text_hv' => 'group-hover:text-emerald-700', 'glow' => 'bg-emerald-500/10', 'bar' => 'bg-emerald-500', 'shadow' => 'hover:shadow-emerald-500/20']],
                    ['label' => 'Gagal Panen', 'val' => $harvestStats['failed'] ?? '0', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'theme' => ['base_bg' => 'hover:bg-rose-50/90', 'base_brd' => 'hover:border-rose-200', 'icon_bg' => 'bg-rose-50 text-rose-600 border-rose-100', 'icon_hv' => 'group-hover:bg-rose-500', 'text_hv' => 'group-hover:text-rose-700', 'glow' => 'bg-rose-500/10', 'bar' => 'bg-rose-500', 'shadow' => 'hover:shadow-rose-500/20']],
                    ['label' => 'Panen Dini', 'val' => $harvestStats['early'] ?? '0', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0', 'theme' => ['base_bg' => 'hover:bg-amber-50/90', 'base_brd' => 'hover:border-amber-200', 'icon_bg' => 'bg-amber-50 text-amber-600 border-amber-100', 'icon_hv' => 'group-hover:bg-amber-500', 'text_hv' => 'group-hover:text-amber-700', 'glow' => 'bg-amber-500/10', 'bar' => 'bg-amber-500', 'shadow' => 'hover:shadow-amber-500/20']],
                    ['label' => 'Panen Tahunan', 'val' => $harvestStats['yearly'] ?? '0', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'theme' => ['base_bg' => 'hover:bg-blue-50/90', 'base_brd' => 'hover:border-blue-200', 'icon_bg' => 'bg-blue-50 text-blue-600 border-blue-100', 'icon_hv' => 'group-hover:bg-blue-500', 'text_hv' => 'group-hover:text-blue-700', 'glow' => 'bg-blue-500/10', 'bar' => 'bg-blue-500', 'shadow' => 'hover:shadow-blue-500/20']]
                ];
            @endphp
            @foreach($opStatsData as $op)
                <div
                    class="group relative overflow-hidden bg-white/80 backdrop-blur-xl p-6 rounded-[2.2rem] border border-white shadow-xl shadow-slate-200/40 transition-all duration-500 hover:-translate-y-2 {{ $op['theme']['base_bg'] }} {{ $op['theme']['base_brd'] }} {{ $op['theme']['shadow'] }}">
                    <div
                        class="absolute -right-6 -top-6 w-24 h-24 {{ $op['theme']['glow'] }} rounded-full blur-3xl transition-all duration-700 group-hover:scale-150">
                    </div>
                    <div class="relative z-10 flex flex-col items-center text-center">
                        <div
                            class="p-3.5 {{ $op['theme']['icon_bg'] }} rounded-2xl mb-5 border shadow-sm transition-all duration-500 group-hover:scale-110 {{ $op['theme']['icon_hv'] }} group-hover:text-white group-hover:rotate-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $op['icon'] }}">
                                </path>
                            </svg>
                        </div>
                        <h4
                            class="text-3xl font-black text-slate-800 tracking-tighter leading-none transition-all {{ $op['theme']['text_hv'] }}">
                            {{ $op['val'] }} <span
                                class="text-xs font-bold text-slate-400 uppercase ml-1 group-hover:opacity-70">Ha</span>
                        </h4>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-4">{{ $op['label'] }}</p>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1.5 bg-slate-50">
                        <div
                            class="h-full w-0 {{ $op['theme']['bar'] }} transition-all duration-700 ease-out group-hover:w-full">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tactical Geospatial Monitoring --}}
        <div class="space-y-8 my-14">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div
                    class="lg:col-span-8 bg-white/90 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 overflow-hidden group">
                    <div
                        class="p-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/30 relative z-10">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] mb-1">Geographic
                                Distribution</p>
                            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg italic leading-none">
                                Peta Penyebaran Potensi Lahan <span class="text-blue-600">2026</span></h3>
                        </div>
                        <div
                            class="px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-[9px] font-black uppercase border border-emerald-100 flex items-center gap-2">
                            <span class="relative flex h-2 w-2"><span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span
                                    class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>Live
                            Tracking
                        </div>
                    </div>
                    <div id="map"
                        class="h-[550px] w-full z-0 grayscale group-hover:grayscale-0 transition-all duration-1000"></div>
                </div>
                <div class="lg:col-span-4 flex flex-col gap-6">
                    <div
                        class="bg-white/90 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl flex-1 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Total Titik Lahan
                        </h4>
                        <div class="relative w-48 h-48"><canvas id="totalTitikChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center"><span
                                    class="text-3xl font-black text-slate-800 tracking-tighter leading-none">5,528</span><span
                                    class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Lahan</span>
                            </div>
                        </div>
                        <div
                            class="absolute -right-4 -bottom-4 w-20 h-20 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors">
                        </div>
                    </div>
                    <div
                        class="bg-white/90 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl flex-1 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">Pengelolaan Polsek
                        </h4>
                        <div class="relative w-48 h-48"><canvas id="pengelolaanPolsekChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center"><span
                                    class="text-3xl font-black text-slate-800 tracking-tighter leading-none">659</span><span
                                    class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1">Polsek</span>
                            </div>
                        </div>
                        <div
                            class="absolute -left-4 -top-4 w-20 h-20 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 6: SERAPAN HASIL (Identic Icon & High-Contrast) --}}
        <div
            class="bg-white/90 backdrop-blur-xl p-8 lg:p-12 rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 relative overflow-hidden my-14 font-sans">

            {{-- Decorative Background Glow --}}
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-slate-50 rounded-full blur-3xl opacity-50"></div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12 relative z-10">
                @php
                    $serapanData = [
                        [
                            'label' => 'SERAPAN BULOG',
                            'val' => '565.42',
                            'unit' => 'TON',
                            'year' => '2026',
                            // Ikon Gudang Garis (BULOG) - Sesuai Gambar
                            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                            'theme' => [
                                'color' => 'blue',
                                'icon_bg' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'hover_bg' => 'group-hover:bg-blue-500',
                                'shadow' => 'group-hover:shadow-blue-500/30'
                            ]
                        ],
                        [
                            'label' => 'SERAPAN PABRIK PAKAN',
                            'val' => '0',
                            'unit' => 'TON',
                            'year' => '2026',
                            // Ikon Sapi Silhouette (Pabrik Pakan) - Sesuai Gambar
                            'icon' => 'M15 11l4.553 2.276A1 1 0 0120 14.17V17a2 2 0 01-2 2h-1v-2a1 1 0 00-1-1H6a1 1 0 00-1 1v2H4a2 2 0 01-2-2v-2.83a1 1 0 01.553-.894L7 11V7a3 3 0 013-3h4a3 3 0 013 3v4z',
                            'theme' => [
                                'color' => 'indigo',
                                'icon_bg' => 'bg-indigo-50 text-indigo-400 border-indigo-100',
                                'hover_bg' => 'group-hover:bg-indigo-500',
                                'shadow' => 'group-hover:shadow-indigo-500/30'
                            ]
                        ],
                        [
                            'label' => 'SERAPAN TENGKULAK',
                            'val' => '516.92',
                            'unit' => 'TON',
                            'year' => '2026',
                            // Ikon Group People (Tengkulak) - Sesuai Gambar
                            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197',
                            'theme' => [
                                'color' => 'amber',
                                'icon_bg' => 'bg-amber-50 text-amber-500 border-amber-100',
                                'hover_bg' => 'group-hover:bg-amber-500',
                                'shadow' => 'group-hover:shadow-amber-500/30'
                            ]
                        ],
                        [
                            'label' => 'KONSUMSI SENDIRI',
                            'val' => '0',
                            'unit' => 'TON',
                            'year' => '2026',
                            // Ikon Isometric Cube (Konsumsi Sendiri) - Sesuai Gambar
                            'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14v4m0 0l8 4m-8-4l-8 4m0 0v10l8 4',
                            'theme' => [
                                'color' => 'emerald',
                                'icon_bg' => 'bg-emerald-50 text-emerald-500 border-emerald-100',
                                'hover_bg' => 'group-hover:bg-emerald-500',
                                'shadow' => 'group-hover:shadow-emerald-500/30'
                            ]
                        ],
                    ];
                @endphp

                @foreach($serapanData as $idx => $s)
                    <div
                        class="flex flex-col items-center text-center group {{ $idx < 3 ? 'lg:border-r border-slate-100/60' : '' }}">

                        {{-- Icon Badge with Smooth Transitions --}}
                        <div class="p-6 {{ $s['theme']['icon_bg'] }} rounded-[2.2rem] mb-8
                                                            {{ $s['theme']['hover_bg'] }} group-hover:text-white
                                                            transition-all duration-500 group-hover:scale-110 group-hover:-rotate-3
                                                            shadow-sm {{ $s['theme']['shadow'] }} relative overflow-hidden">

                            {{-- Decorative inner glow --}}
                            <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>

                            <svg class="w-10 h-10 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $s['icon'] }}">
                                </path>
                            </svg>
                        </div>

                        {{-- Data Angka --}}
                        <div class="flex items-baseline gap-2">
                            <h4
                                class="text-4xl lg:text-5xl font-black text-slate-800 tracking-tighter leading-none group-hover:scale-105 transition-transform duration-300">
                                {{ $s['val'] }}
                            </h4>
                            <span class="text-[10px] font-black text-slate-400 uppercase italic">{{ $s['unit'] }}</span>
                        </div>

                        {{-- Label Deskripsi --}}
                        <p
                            class="text-[11px] font-black text-slate-500 uppercase tracking-[0.15em] mt-5 mb-3 group-hover:text-slate-800 transition-colors">
                            {{ $s['label'] }}
                        </p>

                        {{-- Pill Year Badge --}}
                        <div
                            class="flex items-center gap-2 px-4 py-1.5 bg-slate-50 rounded-full border border-slate-100 group-hover:border-{{ $s['theme']['color'] }}-200 group-hover:bg-{{ $s['theme']['color'] }}-50 transition-all duration-300">
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-{{ $s['theme']['color'] }}-500 animate-pulse"></span>
                            <span
                                class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-{{ $s['theme']['color'] }}-600">TAHUN
                                {{ $s['year'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Validation Info --}}
        {{-- SECTION: TACTICAL VALIDATION STATUS (Dark Command Center Style) --}}
        <div
            class="relative overflow-hidden bg-slate-900 p-8 md:p-10 rounded-[2.5rem] shadow-2xl shadow-slate-900/50 my-14 border border-slate-800">

            {{-- Decorative Background Elements --}}
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -left-20 -bottom-20 w-60 h-60 bg-blue-500/5 rounded-full blur-3xl"></div>

            {{-- Tactical Icon Accent (Watermark style) --}}
            <div class="absolute right-10 bottom-10 text-white/[0.03] select-none pointer-events-none">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L1 21h22L12 2zm0 3.45l8.28 14.55H3.72L12 5.45zM11 16h2v2h-2v-2zm0-7h2v5h-2V9z" />
                </svg>
            </div>

            <div class="relative z-10">
                {{-- Header dengan Spacing Taktis --}}
                <div class="flex items-center gap-3 mb-8">
                    <div class="h-[1px] w-8 bg-emerald-500/50"></div>
                    <h4 class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.4em] italic">
                        Sistem Validasi Satwil Terintegrasi
                    </h4>
                </div>

                <div class="mb-8">
                    <h3 class="text-xl md:text-2xl font-black text-white uppercase tracking-tighter leading-none">
                        Laporan <span class="text-emerald-500">Pending</span> Validasi
                    </h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">
                        Daftar Satuan Wilayah yang belum melakukan sinkronisasi data final
                    </p>
                </div>

                {{-- Grid Items dengan Glassmorphism Dark --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $pendingSatwil = ['Polres Jember', 'Polres Jombang', 'Polres Malang', 'Polres Pasuruan Kota'];
                    @endphp

                    @foreach($pendingSatwil as $polres)
                        <div
                            class="group flex items-center p-4 bg-white/[0.03] border border-white/[0.05] rounded-2xl hover:bg-white/[0.06] hover:border-emerald-500/30 transition-all duration-300">
                            {{-- Status Indicator --}}
                            <div class="relative flex h-3 w-3 mr-4">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-20"></span>
                                <span
                                    class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500/80 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                            </div>

                            {{-- Text Info --}}
                            <div class="flex flex-col">
                                <span
                                    class="text-[11px] font-black text-slate-100 uppercase tracking-tight group-hover:text-emerald-400 transition-colors">
                                    {{ $polres }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-tighter mt-0.5">
                                    Status: Menunggu Validasi Pendataan
                                </span>
                            </div>

                            {{-- Micro-action Icon --}}
                            <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer Action / Info --}}
                <div
                    class="mt-10 flex flex-col md:flex-row md:items-center justify-between gap-4 pt-6 border-t border-white/[0.05]">
                    <div class="flex items-center gap-4">
                        <div class="flex -space-x-2">
                            @for($i = 0; $i < 3; $i++)
                                <div
                                    class="w-6 h-6 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center">
                                    <span class="text-[8px] font-bold text-slate-500 uppercase">P</span>
                                </div>
                            @endfor
                        </div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total 4 Satwil memerlukan
                            tindakan segera</p>
                    </div>
                    <button
                        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-[9px] font-black uppercase tracking-[0.2em] rounded-xl transition-all shadow-lg shadow-emerald-900/20 active:scale-95">
                        Kirim Notifikasi Massal
                    </button>
                </div>
            </div>

            {{-- ==============================================================================
            5. CONSOLIDATED JAVASCRIPT INITIALIZATION
            ============================================================================== --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function () {

                    // --- 1. PRODUCTIVITY LINE CHART ---
                    const lineCtx = document.getElementById('productivityChart').getContext('2d');
                    const grad = lineCtx.createLinearGradient(0, 0, 0, 300);
                    grad.addColorStop(0, 'rgba(37, 99, 235, 0.15)');
                    grad.addColorStop(1, 'rgba(37, 99, 235, 0)');

                    new Chart(lineCtx, {
                        type: 'line',
                        data: {
                            labels: ['2021', '2022', '2023', '2024', '2025', '2026'],
                            datasets: [{
                                label: 'Luas Lahan',
                                data: [85000, 105000, 95000, 140000, 165000, 170715],
                                borderColor: '#2563eb',
                                backgroundColor: grad,
                                borderWidth: 4,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointBackgroundColor: '#fff',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { display: false },
                                x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' } }
                            }
                        }
                    });

                    // --- 2. LEAFLET MAP INITIALIZATION ---
                    var map = L.map('map', {
                        zoomControl: false,
                        scrollWheelZoom: false
                    }).setView([-7.5360, 112.2384], 8);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    L.control.zoom({ position: 'bottomright' }).addTo(map);

                    // Sample Markers
                    var sampleData = [
                        { lat: -7.2504, lng: 112.7688, title: "Polrestabes Surabaya", status: "Produktif" },
                        { lat: -7.9839, lng: 112.6214, title: "Polres Malang", status: "Panen" }
                    ];

                    sampleData.forEach(function (point) {
                        L.circleMarker([point.lat, point.lng], {
                            radius: 8, fillColor: "#3b82f6", color: "#fff", weight: 2, opacity: 1, fillOpacity: 0.8
                        }).addTo(map).bindPopup("<b class='uppercase'>" + point.title + "</b>");
                    });

                    // --- 3. DONUT ANALYTICS CHARTS ---
                    const donutConfig = {
                        cutout: '80%',
                        plugins: { legend: { display: false } },
                        animation: { animateScale: true, animateRotate: true }
                    };

                    new Chart(document.getElementById('totalTitikChart'), {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [5528, 1200],
                                backgroundColor: ['#3b82f6', '#f1f5f9'],
                                borderWidth: 0
                            }]
                        },
                        options: donutConfig
                    });

                    new Chart(document.getElementById('pengelolaanPolsekChart'), {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [659, 200],
                                backgroundColor: ['#10b981', '#f1f5f9'],
                                borderWidth: 0
                            }]
                        },
                        options: donutConfig
                    });
                });
            </script>
@endsection