@extends('layouts.app')

@section('header', 'Dashboard Utama Administrator')

@section('content')
    <div class="space-y-2 pb-12 font-sans" style="font-family: 'Ramabhadra', sans-serif;">

        <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-slate-200 pb-4 mb-2 font-sans"
            style="font-family: 'Ramabhadra', sans-serif;">
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

        {{-- Section Utama: Statistik Lahan --}}
        <div class="space-y-6">
            <div
                class="relative overflow-hidden bg-white/90 backdrop-blur-xl p-6 md:p-8 lg:p-10 rounded-[2rem] md:rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 flex flex-col lg:flex-row gap-8 lg:gap-12 items-center">

                <div
                    class="absolute -left-10 -top-10 w-32 h-32 bg-slate-50 rounded-full blur-3xl opacity-50 hidden md:block">
                </div>

                <div
                    class="w-full lg:w-1/3 text-center lg:text-left border-b lg:border-b-0 lg:border-r border-slate-100 pb-8 lg:pb-0 lg:pr-12 relative z-10">
                    <div class="space-y-1">
                        <h1
                            class="text-4xl sm:text-5xl lg:text-5xl font-black text-slate-800 tracking-tighter leading-none flex items-baseline justify-center lg:justify-start gap-2">
                            170,715.11
                            <span
                                class="text-[10px] md:text-xs font-black text-slate-400 font-sans uppercase tracking-widest italic">Ha</span>
                        </h1>

                        <p
                            class="text-[11px] md:text-xs font-black text-slate-500 uppercase mt-4 tracking-[0.1em] leading-tight">
                            Total Potensi Lahan
                        </p>

                        {{-- FOOTER JUDUL: Info Periode (Minimalist Badge) --}}
                        <div class="mt-5 flex items-center justify-center lg:justify-start gap-3">
                            <div
                                class="px-3 py-1.5 bg-slate-600 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-slate-900/20">
                                Tahun 2026
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Kanan: Grid Breakdown Data (1 kolom di Mobile, 2 kolom di Tablet ke atas) --}}
                <div class="w-full lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-4 relative z-10">
                    <div class="space-y-3">
                        @php
                            $potensi1 = [
                                'Milik Polri' => '9.63',
                                'Produktif (Poktan)' => '34,882.86',
                                'Produktif (Masyarakat)' => '27,316.49',
                                'Produktif (Tumpang Sari)' => '27,316.49'
                            ];
                        @endphp
                        @foreach($potensi1 as $label => $val)
                            <div
                                class="group flex justify-between items-center text-[10px] md:text-[11px] font-bold border-b border-slate-50 pb-2.5 hover:border-emerald-100 transition-all duration-300">
                                <span
                                    class="text-slate-400 uppercase tracking-tighter group-hover:text-slate-600 transition-colors">➤
                                    {{ $label }}</span>
                                <span
                                    class="text-slate-800 font-black tracking-tight bg-slate-50 px-2 py-0.5 rounded-md group-hover:bg-emerald-50 group-hover:text-emerald-700 transition-all">
                                    {{ $val }} <small class="text-slate-400 font-normal uppercase ml-0.5">Ha</small>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Grup Potensi 2 --}}
                    <div class="space-y-3">
                        @php
                            $potensi2 = [
                                'Hutan (Perhutani)' => '22,573.23',
                                'Luas Baku Sawah' => '64,792.29',
                                'Pesantren' => '64,792.9',
                                'Lainnya' => '107.52'
                            ];
                        @endphp
                        @foreach($potensi2 as $label => $val)
                            <div
                                class="group flex justify-between items-center text-[10px] md:text-[11px] font-bold border-b border-slate-50 pb-2.5 hover:border-emerald-100 transition-all duration-300">
                                <span
                                    class="text-slate-400 uppercase tracking-tighter group-hover:text-slate-600 transition-colors">➤
                                    {{ $label }}</span>
                                <span
                                    class="text-slate-800 font-black tracking-tight bg-slate-50 px-2 py-0.5 rounded-md group-hover:bg-emerald-100 group-hover:text-emerald-700 transition-all">
                                    {{ $val }} <small class="text-slate-400 font-normal uppercase ml-0.5">Ha</small>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Grid Tanam & Panen (Responsive & Tactical Edition) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 my-10">

                {{-- Card 2.2: TOTAL LAHAN TANAM --}}
                <div
                    class="relative overflow-hidden bg-white/90 backdrop-blur-xl p-7 md:p-8 rounded-[2rem] md:rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/30 group">
                    {{-- Header Card --}}
                    <div class="flex justify-between items-start mb-8 relative z-10">
                        <div class="space-y-1">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] mb-2">Planting
                                Database</p>
                            <h1
                                class="text-4xl sm:text-5xl font-black text-slate-800 tracking-tighter leading-none flex items-baseline gap-2">
                                242.74
                                <span class="text-[10px] font-black text-slate-400 font-sans uppercase italic">Ha</span>
                            </h1>
                            <p class="text-[11px] font-black text-slate-500 uppercase mt-4 tracking-[0.1em]">Total Lahan
                                Tanam</p>

                            {{-- Konsistensi: Year Badge --}}
                            <div class="mt-4 flex items-center gap-2">
                                <div
                                    class="px-2.5 py-1 bg-slate-600 text-white rounded-lg text-[8px] font-black uppercase tracking-tighter">
                                    Tahun 2026
                                </div>
                                <div class="h-[1px] w-6 bg-slate-200"></div>
                                {{-- Percentage Badge --}}
                                <div
                                    class="p-1.5 bg-blue-100 text-blue-600 rounded-lg flex items-center gap-1 border border-blue-100 shadow-sm">
                                    <span class="text-[9px] font-black uppercase">0%</span>
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Breakdown Data (Responsive List) --}}
                    <div class="space-y-2 border-t border-slate-100 pt-6 relative z-10">
                        @php
                            $tanamData = ['Milik Polri' => '0', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.1'];
                        @endphp
                        @foreach($tanamData as $label => $val)
                            <div
                                class="group/item flex justify-between items-center text-[10px] md:text-[11px] font-bold border-b border-slate-50 pb-2.5 hover:border-blue-100 transition-all duration-300">
                                <span class="text-slate-400 uppercase tracking-tighter group-hover/item:text-slate-600">➤
                                    {{ $label }}</span>
                                <span
                                    class="text-slate-800 font-black tracking-tight bg-slate-50 px-2 py-0.5 rounded-md group-hover/item:bg-blue-100 group-hover/item:text-blue-700 transition-all">
                                    {{ $val }} <small class="text-slate-400 font-normal uppercase ml-0.5">Ha</small>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Background Decoration --}}
                    <div
                        class="absolute -right-10 -bottom-10 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/10 transition-colors duration-700">
                    </div>
                </div>

                {{-- Card 2.3: TOTAL LAHAN PANEN --}}
                <div
                    class="relative overflow-hidden bg-white/90 backdrop-blur-xl p-7 md:p-8 rounded-[2rem] md:rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/30 group">
                    {{-- Header Card --}}
                    <div class="flex justify-between items-start mb-8 relative z-10">
                        <div class="space-y-1">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] mb-2">Harvesting
                                Database</p>
                            <h1
                                class="text-4xl sm:text-5xl font-black text-slate-800 tracking-tighter leading-none flex items-baseline gap-2">
                                243.72
                                <span class="text-[10px] font-black text-slate-400 font-sans uppercase italic">Ha</span>
                            </h1>
                            <p class="text-[11px] font-black text-slate-500 uppercase mt-4 tracking-[0.1em]">Total Lahan
                                Panen</p>

                            {{-- Konsistensi: Year Badge --}}
                            <div class="mt-4 flex items-center gap-2">
                                <div
                                    class="px-2.5 py-1 bg-slate-600 text-white rounded-lg text-[8px] font-black uppercase tracking-tighter">
                                    Tahun 2026
                                </div>
                                <div class="h-[1px] w-6 bg-slate-200"></div>
                                {{-- Percentage Badge --}}
                                <div
                                    class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg flex items-center gap-1 border border-emerald-100 shadow-sm">
                                    <span class="text-[9px] font-black uppercase">0%</span>
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Breakdown Data (Responsive List) --}}
                    <div class="space-y-2 border-t border-slate-100 pt-6 relative z-10">
                        @php
                            $panenData = ['Milik Polri' => '0.98', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.1'];
                        @endphp
                        @foreach($panenData as $label => $val)
                            <div
                                class="group/item flex justify-between items-center text-[10px] md:text-[11px] font-bold border-b border-slate-50 pb-2.5 hover:border-emerald-100 transition-all duration-300">
                                <span class="text-slate-400 uppercase tracking-tighter group-hover/item:text-slate-600">➤
                                    {{ $label }}</span>
                                <span
                                    class="text-slate-800 font-black tracking-tight bg-slate-50 px-2 py-0.5 rounded-md group-hover/item:bg-emerald-100 group-hover/item:text-emerald-700 transition-all">
                                    {{ $val }} <small class="text-slate-400 font-normal uppercase ml-0.5">Ha</small>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Background Decoration --}}
                    <div
                        class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-colors duration-700">
                    </div>
                </div>
            </div>

            {{-- Section 3: Chart & Logs (Optimized Year Visibility) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 my-14">

                {{-- Card 3.1: TREN PRODUKTIVITAS --}}
                <div
                    class="lg:col-span-2 bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                    {{-- Background Decoration --}}
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-50"></div>

                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-10 relative z-10">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em]">Performance
                                    Monitor</p>

                            </div>
                            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-2xl leading-none">
                                Total <span class="text-blue-600">Hasil</span> Panen
                            </h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Statistik luasan
                                produktif per-periode</p>
                        </div>

                        {{-- Optimized Toggle Switch --}}
                        <div class="flex bg-slate-100 p-1.5 rounded-2xl border border-slate-200 shadow-inner">
                            <button
                                class="px-5 py-2 text-[9px] font-black bg-white rounded-xl shadow-md text-slate-800 uppercase tracking-widest transition-all">
                                Bulanan
                            </button>
                            <button
                                class="px-5 py-2 text-[9px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">
                                Tahunan
                            </button>
                        </div>
                    </div>

                    {{-- Chart Container --}}
                    <div class="h-72 relative z-10">
                        <canvas id="productivityChart"></canvas>
                    </div>

                    {{-- Section Footer: Quick Yearly Summary --}}
                    <div class="mt-8 pt-6 border-t border-slate-100 flex items-center gap-6 relative z-10">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Total Yield
                                2026</span>
                            <span class="text-sm font-black text-slate-800 uppercase italic">128,429.00 <small
                                    class="text-[10px] text-slate-400 font-normal">Ha</small></span>
                        </div>
                        <div class="h-8 w-[1px] bg-slate-100"></div>
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Growth Rate</span>
                            <span class="text-sm font-black text-emerald-500 uppercase italic">+12.4% ↑</span>
                        </div>
                    </div>
                </div>

                {{-- Card 3.2: LOG AKTIVITAS --}}
                <div
                    class="bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/40 flex flex-col group">
                    <div class="mb-8">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] mb-2">System Logs</p>
                        <h3 class="font-black text-slate-800 uppercase tracking-tighter text-2xl  leading-none">
                            Log <span class="text-emerald-500">Real-time</span>
                        </h3>
                    </div>

                    <div class="flex-1 space-y-8 overflow-y-auto pr-2 custom-scrollbar">
                        @php
                            $logs = [
                                ['title' => 'Input Lahan Baru', 'user' => 'Bripka Ahmad', 'satwil' => 'Polres Gresik', 'time' => '2 mnt lalu', 'color' => 'emerald'],
                                ['title' => 'Update Panen', 'user' => 'Bripda Bayu', 'satwil' => 'Polres Malang', 'time' => '15 mnt lalu', 'color' => 'blue'],
                                ['title' => 'Validasi Data', 'user' => 'Iptu Sanjaya', 'satwil' => 'Polres Kediri', 'time' => '32 mnt lalu', 'color' => 'amber'],
                                ['title' => 'Export Laporan', 'user' => 'Admin Polda', 'satwil' => 'Polda Jatim', 'time' => '1 jam lalu', 'color' => 'slate'],
                            ];
                        @endphp
                        @foreach($logs as $log)
                            <div class="group/item flex items-start gap-5 relative">
                                {{-- Timeline Line & Dot --}}
                                <div class="relative flex flex-col items-center">
                                    <div
                                        class="w-3.5 h-3.5 rounded-full bg-white border-2 border-{{ $log['color'] }}-500 z-10 relative shadow-sm group-hover/item:scale-125 transition-transform">
                                    </div>
                                    <div class="absolute top-3.5 w-[1px] h-[calc(100%+2rem)] bg-slate-100 group-last:hidden">
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 pb-2">
                                    <div class="flex justify-between items-start">
                                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight">
                                            {{ $log['title'] }}
                                        </p>
                                        <span
                                            class="text-[8px] font-black text-{{ $log['color'] }}-500 uppercase bg-{{ $log['color'] }}-50 transition-colors px-2 py-0.5 rounded-md border border-{{ $log['color'] }}-100">{{ $log['time'] }}</span>
                                    </div>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                        {{ $log['user'] }} <span class="mx-1 text-slate-200">|</span> {{ $log['satwil'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button
                        class="w-full mt-8 py-4 bg-slate-900 text-[10px] font-black text-white hover:bg-emerald-600 rounded-2xl transition-all uppercase tracking-[0.2em] shadow-lg shadow-slate-900/20 active:scale-95">
                        Lihat Seluruh Aktivitas
                    </button>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const lineCtx = document.getElementById('productivityChart').getContext('2d');
                const grad = lineCtx.createLinearGradient(0, 0, 0, 300);
                grad.addColorStop(0, 'rgba(37, 99, 235, 0.15)'); // Blue subtle tint
                grad.addColorStop(1, 'rgba(37, 99, 235, 0)');

                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: ['2021', '2022', '2023', '2024', '2025', '2026'],
                        datasets: [{
                            label: 'Luas Lahan',
                            data: [85000, 105000, 95000, 140000, 165000, 170715],
                            borderColor: '#2563eb', // Blue tactical
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
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: 'bold' }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            </script>

            {{-- Section 4: Monitoring Kwartal (Optimized Contrast & Elegant Hover) --}}
            <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white shadow-xl my-10 font-sans">
                <div class="mb-10 relative z-10">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] mb-2">Quarterly Performance
                    </p>
                    <h3 class="font-black text-slate-800 uppercase tracking-tighter text-2xl leading-none">
                        Monitoring Target & Hasil <span class="text-emerald-500">Kwartal</span>
                    </h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Rangkuman progres capaian
                        per-triwulan tahun 2026</p>
                </div>

                <div class="grid grid-cols-1 gap-6 relative z-10">
                    @php
                        $kwartalData = [
                            ['category' => 'Milik Polri', 'unit' => 'Ha', 'q1' => '9.63', 'q2' => '0', 'q3' => '0', 'q4' => '0', 'color' => 'blue'],
                            ['category' => 'Produktif (Poktan Binaan)', 'unit' => 'Ha', 'q1' => '34,882.86', 'q2' => '107.08', 'q3' => '0', 'q4' => '0', 'color' => 'emerald'],
                            ['category' => 'Hasil Panen Kwartal', 'unit' => 'Ton', 'q1' => '988.92', 'q2' => '0', 'q3' => '0', 'q4' => '0', 'color' => 'orange'],
                        ];
                    @endphp

                    @foreach($kwartalData as $row)
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 group/row">
                            @for($i = 1; $i <= 4; $i++)
                                <div
                                    class="relative overflow-hidden bg-slate-50/50 p-5 rounded-[2rem] border border-slate-100 transition-all duration-500 
                                                                                                hover:bg-{{ $row['color'] }}-50/80 hover:border-{{ $row['color'] }}-200 hover:shadow-lg hover:shadow-{{ $row['color'] }}-500/10 hover:-translate-y-1 group">

                                    <div class="relative z-10 flex flex-col h-full justify-between">
                                        <div>
                                            <p
                                                class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1 group-hover:text-{{ $row['color'] }}-600 transition-colors">
                                                {{ $row['category'] }}
                                            </p>
                                            <h4 class="text-xl font-black text-slate-800 tracking-tighter leading-none">
                                                {{ $row['q' . $i] }}
                                                <span
                                                    class="text-[9px] font-normal text-slate-400 font-sans uppercase">{{ $row['unit'] }}</span>
                                            </h4>
                                        </div>

                                        <p
                                            class="text-[9px] font-black text-{{ $row['color'] }}-500 uppercase tracking-widest mt-4 flex items-center gap-1.5">
                                            <span
                                                class="w-1 h-1 rounded-full bg-{{ $row['color'] }}-500 shadow-[0_0_8px_rgba(0,0,0,0.1)]"></span>
                                            Q{{ $i }} 2026
                                        </p>
                                    </div>

                                    {{-- Background Number: Lebih Kontras (15% Opacity) & Mengikuti Warna Tema --}}
                                    <span
                                        class="absolute right-1 -bottom-3 text-7xl font-black text-{{ $row['color'] }}-900/10 italic select-none 
                                                                                                    group-hover:text-{{ $row['color'] }}-600/20 group-hover:scale-100 transition-all duration-500">
                                        {{ $i }}
                                    </span>
                                </div>
                            @endfor
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Section 5: Operational Detail Widgets (Fixed Dynamic Colors) --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 my-10 font-sans">
                @php
                    $opStatsData = [
                        [
                            'label' => 'Panen Normal',
                            'val' => $harvestStats['normal'] ?? '0',
                            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0',
                            'theme' => [
                                'base_bg' => 'hover:bg-emerald-50/90',
                                'base_brd' => 'hover:border-emerald-200',
                                'icon_bg' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'icon_hv' => 'group-hover:bg-emerald-500',
                                'text_hv' => 'group-hover:text-emerald-700',
                                'glow' => 'bg-emerald-500/10',
                                'bar' => 'bg-emerald-500',
                                'shadow' => 'hover:shadow-emerald-500/20'
                            ]
                        ],
                        [
                            'label' => 'Gagal Panen',
                            'val' => $harvestStats['failed'] ?? '0',
                            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                            'theme' => [
                                'base_bg' => 'hover:bg-rose-50/90',
                                'base_brd' => 'hover:border-rose-200',
                                'icon_bg' => 'bg-rose-50 text-rose-600 border-rose-100',
                                'icon_hv' => 'group-hover:bg-rose-500',
                                'text_hv' => 'group-hover:text-rose-700',
                                'glow' => 'bg-rose-500/10',
                                'bar' => 'bg-rose-500',
                                'shadow' => 'hover:shadow-rose-500/20'
                            ]
                        ],
                        [
                            'label' => 'Panen Dini',
                            'val' => $harvestStats['early'] ?? '0',
                            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0',
                            'theme' => [
                                'base_bg' => 'hover:bg-amber-50/90',
                                'base_brd' => 'hover:border-amber-200',
                                'icon_bg' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'icon_hv' => 'group-hover:bg-amber-500',
                                'text_hv' => 'group-hover:text-amber-700',
                                'glow' => 'bg-amber-500/10',
                                'bar' => 'bg-amber-500',
                                'shadow' => 'hover:shadow-amber-500/20'
                            ]
                        ],
                        [
                            'label' => 'Panen Tahunan',
                            'val' => $harvestStats['yearly'] ?? '0',
                            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'theme' => [
                                'base_bg' => 'hover:bg-blue-50/90',
                                'base_brd' => 'hover:border-blue-200',
                                'icon_bg' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'icon_hv' => 'group-hover:bg-blue-500',
                                'text_hv' => 'group-hover:text-blue-700',
                                'glow' => 'bg-blue-500/10',
                                'bar' => 'bg-blue-500',
                                'shadow' => 'hover:shadow-blue-500/20'
                            ]
                        ],
                    ];
                @endphp

                @foreach($opStatsData as $op)
                    {{-- Card Utama --}}
                    <div
                        class="group relative overflow-hidden bg-white/80 backdrop-blur-xl p-6 rounded-[2.2rem] border border-white shadow-xl shadow-slate-200/40 transition-all duration-500 
                    hover:-translate-y-2 {{ $op['theme']['base_bg'] }} {{ $op['theme']['base_brd'] }} {{ $op['theme']['shadow'] }}">

                        {{-- Glowing Background Circle --}}
                        <div
                            class="absolute -right-6 -top-6 w-24 h-24 {{ $op['theme']['glow'] }} rounded-full blur-3xl transition-all duration-700 group-hover:scale-150">
                        </div>

                        <div class="relative z-10 flex flex-col items-center text-center">
                            {{-- Icon Badge --}}
                            <div
                                class="p-3.5 {{ $op['theme']['icon_bg'] }} rounded-2xl mb-5 border shadow-sm transition-all duration-500 
                            group-hover:scale-110 {{ $op['theme']['icon_hv'] }} group-hover:text-white group-hover:rotate-6 group-hover:shadow-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="{{ $op['icon'] }}"></path>
                                </svg>
                            </div>

                            {{-- Value --}}
                            <h4
                                class="text-3xl font-black text-slate-800 tracking-tighter leading-none transition-all duration-500 {{ $op['theme']['text_hv'] }}">
                                {{ $op['val'] }}
                                <span
                                    class="text-xs font-bold text-slate-400 uppercase italic ml-1 group-hover:opacity-70">Ha</span>
                            </h4>

                            {{-- Label --}}
                            <p
                                class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-4 transition-all duration-500 group-hover:text-slate-600">
                                {{ $op['label'] }}
                            </p>
                        </div>

                        {{-- Animated Bottom Bar --}}
                        <div class="absolute bottom-0 left-0 w-full h-1.5 bg-slate-50 transition-all duration-500">
                            <div
                                class="h-full w-0 {{ $op['theme']['bar'] }} transition-all duration-700 ease-out group-hover:w-full">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- MAPP DURUNG, TOTAL TITIK LAHAN --}}

            {{-- Footer Info --}}
            <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <h4 class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] mb-4">Informasi Validasi
                        Satwil</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach(['Polres Jember', 'Polres Jombang', 'Polres Malang', 'Polres Pasuruan Kota'] as $polres)
                            <div class="flex items-center text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-3 animate-pulse"></span> {{ $polres }}
                                Belum Melaksanakan Validasi Pendataan.
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl"></div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Productivity Chart Optimized
            const lineCtx = document.getElementById('productivityChart').getContext('2d');
            const gradient = lineCtx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                    datasets: [{ label: 'Luas Lahan', data: [12000, 19000, 15000, 25000, 22000, 30000], borderColor: '#10b981', backgroundColor: gradient, borderWidth: 3, fill: true, tension: 0.4, pointRadius: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' }, color: '#94a3b8' } } } }
            });
        </script>
@endsection