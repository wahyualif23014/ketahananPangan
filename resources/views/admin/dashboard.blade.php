@extends('layouts.app')

@section('header', 'Dashboard Utama Administrator')

@section('content')
<div class="space-y-6 pb-12">
    
    {{-- Section 1: Greeting Hero --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900/90 backdrop-blur-xl p-10 shadow-2xl border border-slate-700/50">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h4 class="text-emerald-400 font-black uppercase text-[10px] tracking-[0.4em] mb-3 opacity-80">Sistem Informasi Ketahanan Pangan</h4>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase  leading-none">
                    KOMPOL <span class="text-emerald-500">{{ Auth::user()->name }}</span>
                </h1>
                <p class="text-slate-400 text-xs mt-4 font-medium max-w-xl leading-relaxed uppercase tracking-widest opacity-70">
                    Monitoring 38 Satwil Jawa Timur. Data terpusat, valid, dan terintegrasi dalam satu komando.
                </p>
            </div>
            <div class="flex gap-3">
                <div class="px-6 py-3 bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl text-center">
                    <p class="text-[9px] text-slate-500 font-black uppercase">Status Sistem</p>
                    <p class="text-xs text-emerald-400 font-bold uppercase tracking-tighter">● Online</p>
                </div>
            </div>
        </div>
        <div class="absolute -right-10 -top-20 h-64 w-64 rounded-full bg-emerald-500/20 blur-[80px]"></div>
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-blue-500/10 blur-[60px]"></div>
    </div>

    {{-- Section 2: Detail Stats Cards (Updated based on image_dc1861 & image_dc1884) --}}
    <div class="space-y-6">
        
        {{-- Card 2.1: TOTAL POTENSI LAHAN (image_dc1861) --}}
        <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50 flex flex-col lg:flex-row gap-8 items-center">
            <div class="lg:w-1/3 text-center lg:text-left border-b lg:border-b-0 lg:border-r border-slate-200/50 pb-6 lg:pb-0 lg:pr-8">
                <h1 class="text-5xl font-black text-slate-800 tracking-tighter leading-none">170,715.11 <span class="text-sm font-normal text-slate-400 font-sans uppercase">Ha</span></h1>
                <p class="text-[11px] font-black text-slate-400 uppercase mt-4 tracking-[0.2em] ">Total Potensi Lahan Sampai Tahun 2026</p>
            </div>
            <div class="lg:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-3">
                <div class="space-y-3">
                    <div class="flex justify-between text-[11px] font-bold border-b border-slate-100 pb-1">
                        <span class="text-slate-500 uppercase">➤ Milik Polri :</span>
                        <span class="text-emerald-600 font-black">9.63 <small class="text-slate-400 uppercase">Ha</small></span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold border-b border-slate-100 pb-1">
                        <span class="text-slate-500 uppercase">➤ Produktif (Poktan) :</span>
                        <span class="text-emerald-600 font-black">34,882.86 <small class="text-slate-400 uppercase">Ha</small></span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold border-b border-slate-100 pb-1">
                        <span class="text-slate-500 uppercase">➤ Produktif (Masyarakat) :</span>
                        <span class="text-emerald-600 font-black">27,316.49 <small class="text-slate-400 uppercase">Ha</small></span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-[11px] font-bold border-b border-slate-100 pb-1">
                        <span class="text-slate-500 uppercase">➤ Hutan (Perhutani) :</span>
                        <span class="text-emerald-600 font-black">22,573.23 <small class="text-slate-400 uppercase">Ha</small></span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold border-b border-slate-100 pb-1">
                        <span class="text-slate-500 uppercase">➤ Luas Baku Sawah :</span>
                        <span class="text-emerald-600 font-black">64,792.29 <small class="text-slate-400 uppercase">Ha</small></span>
                    </div>
                    <div class="flex justify-between text-[11px] font-bold border-b border-slate-100 pb-1">
                        <span class="text-slate-500 uppercase">➤ Lainnya :</span>
                        <span class="text-emerald-600 font-black">107.52 <small class="text-slate-400 uppercase">Ha</small></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Card 2.2: TOTAL LAHAN TANAM (image_dc1884 - Top) --}}
            <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-4xl font-black text-slate-800 tracking-tighter ">242.74 <span class="text-xs font-normal text-slate-400 uppercase font-sans">Ha</span></h1>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest  mt-2">Total Lahan Tanam Tahun 2026</p>
                    </div>
                    <div class="p-2 bg-blue-50 text-blue-500 rounded-lg flex items-center gap-1">
                        <span class="text-[10px] font-black ">0%</span>
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"></path></svg>
                    </div>
                </div>
                <div class="space-y-2 border-t border-slate-200/50 pt-4">
                    @foreach(['Milik Polri' => '0', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.1'] as $label => $val)
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-400 uppercase">➤ {{ $label }}</span>
                        <span class="text-slate-700 font-black">{{ $val }} <small class="text-slate-400">Ha</small></span>
                    </div>
                    @endforeach
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-colors"></div>
            </div>

            {{-- Card 2.3: TOTAL LAHAN PANEN (image_dc1884 - Bottom) --}}
            <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-4xl font-black text-slate-800 tracking-tighter ">243.72 <span class="text-xs font-normal text-slate-400 uppercase font-sans">Ha</span></h1>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest  mt-2">Total Lahan Panen Tahun 2026</p>
                    </div>
                    <div class="p-2 bg-emerald-50 text-emerald-500 rounded-lg flex items-center gap-1">
                        <span class="text-[10px] font-black ">0%</span>
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"></path></svg>
                    </div>
                </div>
                <div class="space-y-2 border-t border-slate-200/50 pt-4">
                    @foreach(['Milik Polri' => '0.98', 'Produktif (Poktan)' => '107.08', 'Masyarakat Binaan' => '39.31', 'Hutan (Sosial)' => '83.25', 'LBS' => '12.1'] as $label => $val)
                    <div class="flex justify-between text-[10px] font-bold">
                        <span class="text-slate-400 uppercase">➤ {{ $label }}</span>
                        <span class="text-emerald-600 font-black">{{ $val }} <small class="text-slate-400">Ha</small></span>
                    </div>
                    @endforeach
                </div>
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-colors"></div>
            </div>
        </div>
    </div>

    {{-- Section 3: Chart & Logs --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg ">Tren Produktivitas Lahan</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 ">Statistik pergerakan luasan lahan produktif</p>
                </div>
                <div class="flex bg-slate-100/50 p-1 rounded-xl border border-slate-200/50">
                    <button class="px-4 py-1.5 text-[9px] font-black bg-white rounded-lg shadow-sm text-slate-800 uppercase tracking-widest">Bulanan</button>
                    <button class="px-4 py-1.5 text-[9px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest">Tahunan</button>
                </div>
            </div>
            <div class="h-80">
                <canvas id="productivityChart"></canvas>
            </div>
        </div>

        <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50 flex flex-col">
            <h3 class="font-black text-slate-800 uppercase tracking-tighter text-lg  mb-8">Log Aktivitas <span class="text-emerald-500">Real-time</span></h3>
            <div class="flex-1 space-y-8 overflow-y-auto pr-2 custom-scrollbar">
                @php
                    $logs = [
                        ['title' => 'Input Lahan Baru', 'user' => 'Bripka Ahmad', 'satwil' => 'Polres Gresik', 'time' => '2 menit lalu', 'color' => 'emerald'],
                        ['title' => 'Update Panen', 'user' => 'Bripda Bayu', 'satwil' => 'Polres Malang', 'time' => '15 menit lalu', 'color' => 'blue'],
                    ];
                @endphp
                @foreach($logs as $log)
                <div class="group flex items-start gap-4 relative">
                    <div class="relative">
                        <div class="w-3 h-3 mt-1.5 rounded-full bg-{{ $log['color'] }}-500 shadow-[0_0_15px_rgba(var(--tw-color-{{ $log['color'] }}-500),0.5)] z-10 relative"></div>
                        <div class="absolute top-1.5 left-1/2 -translate-x-1/2 w-[1px] h-20 bg-slate-200 group-last:hidden"></div>
                    </div>
                    <div class="bg-white/30 backdrop-blur-md p-4 rounded-2xl border border-white/40 flex-1 hover:bg-white/50 transition-colors">
                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight">{{ $log['title'] }}</p>
                        <p class="text-[9px] text-slate-500 mt-1 font-bold uppercase tracking-tighter">{{ $log['user'] }} — <span class="text-slate-400">{{ $log['satwil'] }}</span></p>
                        <p class="text-[8px] text-{{ $log['color'] }}-500 font-mono  mt-2 uppercase font-black">{{ $log['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <button class="w-full mt-8 py-4 bg-slate-900 text-[10px] font-black text-white hover:bg-emerald-600 rounded-2xl transition-all uppercase tracking-[0.2em] shadow-lg shadow-slate-900/20">Lihat Seluruh Aktivitas</button>
        </div>
    </div>

    {{-- Section 4: Monitoring Kwartal Lahan --}}
    <div class="bg-white/40 backdrop-blur-xl p-10 rounded-[2.5rem] border border-white/60 shadow-xl shadow-slate-200/50">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h3 class="font-black text-slate-800 uppercase tracking-tighter text-xl ">Monitoring Target & Hasil <span class="text-emerald-500 ">Kwartal</span></h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 ">Rangkuman progres capaian per-triwulan tahun 2026</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @php
                $kwartalData = [
                    ['category' => 'Milik Polri', 'unit' => 'Ha', 'q1' => '9.63', 'q2' => '0', 'q3' => '0', 'q4' => '0', 'color' => 'blue'],
                    ['category' => 'Produktif (Poktan Binaan)', 'unit' => 'Ha', 'q1' => '34,882.86', 'q2' => '107.08', 'q3' => '0', 'q4' => '0', 'color' => 'emerald'],
                    ['category' => 'Hasil Panen Kwartal', 'unit' => 'Ton', 'q1' => '988.92', 'q2' => '0', 'q3' => '0', 'q4' => '0', 'color' => 'orange'],
                ];
            @endphp

            @foreach($kwartalData as $row)
            <div class="grid grid-cols-4 gap-4 group">
                @for($i = 1; $i <= 4; $i++)
                @php $val = 'q'.$i; @endphp
                <div class="relative overflow-hidden bg-white/40 backdrop-blur-lg p-5 rounded-3xl border border-white/60 shadow-sm transition-all duration-300 hover:bg-white/60 hover:-translate-y-1">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $row['category'] }}</p>
                            <h4 class="text-xl font-black text-slate-800 tracking-tighter leading-none">{{ $row[$val] }} <span class="text-[9px] font-normal text-slate-400 font-sans">{{ $row['unit'] }}</span></h4>
                        </div>
                        <p class="text-[10px] font-black text-{{ $row['color'] }}-500 uppercase tracking-widest mt-4">Kwartal {{ $i }}</p>
                    </div>
                    <span class="absolute -right-2 -bottom-2 text-7xl font-black text-slate-900/5  select-none group-hover:text-{{ $row['color'] }}-500/10 transition-colors">{{ $i }}</span>
                </div>
                @endfor
            </div>
            @endforeach
        </div>
    </div>

    {{-- Section 5: Operational Detail Widgets (image_dbabc2) --}}
    <div class="space-y-6">
        {{-- Row 5.1: Harvest Types --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $opStats = [
                    ['label' => 'Panen Normal', 'val' => '0', 'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3'],
                    ['label' => 'Gagal Panen', 'val' => '0', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856'],
                    ['label' => 'Panen Dini', 'val' => '0', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4'],
                    ['label' => 'Panen Tahunan', 'val' => '0', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0'],
                ];
            @endphp
            @foreach($opStats as $op)
            <div class="bg-white/40 backdrop-blur-xl p-5 rounded-3xl border border-white/60 shadow-lg text-center flex flex-col items-center">
                <div class="p-2 bg-slate-100 rounded-xl mb-3 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $op['icon'] }}"></path></svg>
                </div>
                <h4 class="text-xl font-black text-slate-800">{{ $op['val'] }} <small class="text-slate-400 font-normal">Ha</small></h4>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $op['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Row 5.2: Map & Pie --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white/40 backdrop-blur-xl p-6 rounded-[2.5rem] border border-white/60 shadow-xl min-h-[400px] flex flex-col">
                <h3 class="font-black text-slate-800 uppercase text-sm mb-4  tracking-widest">Peta Penyebaran Potensi Lahan 2026</h3>
                <div class="flex-1 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center opacity-30  font-black text-slate-400 uppercase">Interactive Map Container</div>
            </div>
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white/40 backdrop-blur-xl p-6 rounded-[2.5rem] border border-white/60 shadow-xl flex flex-col items-center">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest">Total Titik Lahan</h4>
                    <div class="w-40 h-40"><canvas id="totalTitikChart"></canvas></div>
                    <p class="mt-4 text-lg font-black text-slate-800  uppercase">5498 <span class="text-xs text-slate-400">Lahan</span></p>
                </div>
                <div class="bg-white/40 backdrop-blur-xl p-6 rounded-[2.5rem] border border-white/60 shadow-xl flex flex-col items-center">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase mb-4 tracking-widest">Pengelolaan Lahan Polsek</h4>
                    <div class="w-40 h-40"><canvas id="pengelolaanPolsekChart"></canvas></div>
                    <p class="mt-4 text-lg font-black text-slate-800  uppercase">659 <span class="text-xs text-slate-400">Polsek</span></p>
                </div>
            </div>
        </div>

        {{-- Row 5.3: Absorption --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach(['Serapan Gudang' => '565.42', 'Pabrik Pakan' => '0', 'Tengkulak' => '423.5', 'Konsumsi Sendiri' => '0'] as $label => $val)
            <div class="bg-white/40 backdrop-blur-xl p-5 rounded-3xl border border-white/60 shadow-lg text-center flex flex-col items-center">
                <div class="p-2 bg-slate-800 text-white rounded-xl mb-3"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"></path></svg></div>
                <h4 class="text-xl font-black text-slate-800">{{ $val }} <small class="text-slate-400 font-normal ">Ton</small></h4>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>

        {{-- Row 5.4: Info Lainnya --}}
        <div class="bg-white/40 backdrop-blur-xl p-8 rounded-[2.5rem] border border-white/60 shadow-xl">
            <h4 class="text-xs font-black text-slate-800 uppercase  mb-4 tracking-widest">Informasi Lainnya</h4>
            <div class="space-y-2">
                @foreach(['Polres Jember', 'Polres Jombang', 'Polres Malang', 'Polres Pasuruan Kota'] as $polres)
                <div class="flex items-center text-[10px] font-bold text-slate-500 uppercase tracking-tighter"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-3"></span> {{ $polres }} Belum Melaksanakan Validasi Pada Tahapan Pendataan Potensi Lahan.</div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Productivity Chart
    const lineCtx = document.getElementById('productivityChart').getContext('2d');
    const gradient = lineCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            datasets: [{ label: 'Luas Lahan', data: [12000, 19000, 15000, 25000, 22000, 30000], borderColor: '#10b981', backgroundColor: gradient, borderWidth: 4, fill: true, tension: 0.4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.03)' } }, x: { grid: { display: false } } } }
    });

    // Pie Charts
    const pieOptions = { cutout: '75%', plugins: { legend: { display: false } } };
    new Chart(document.getElementById('totalTitikChart'), { type: 'doughnut', data: { datasets: [{ data: [5498, 1000], backgroundColor: ['#3b82f6', '#f1f5f9'], borderWidth: 0 }] }, options: pieOptions });
    new Chart(document.getElementById('pengelolaanPolsekChart'), { type: 'doughnut', data: { datasets: [{ data: [659, 200], backgroundColor: ['#10b981', '#f1f5f9'], borderWidth: 0 }] }, options: pieOptions });
</script>
@endsection