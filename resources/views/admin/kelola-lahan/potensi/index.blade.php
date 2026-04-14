@extends('layouts.app')

@section('header', 'Data Potensi Lahan')

@section('content')
    <div class="pb-24 font-sans text-slate-900 antialiased" style="font-family: 'Figtree', sans-serif;">

        <div
            class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 px-4 mb-10 transition-all duration-700 animate-in fade-in slide-in-from-top-4">
            <div>
                <nav
                    class="flex items-center gap-2 text-[11px] font-semibold tracking-widest uppercase text-slate-400 mb-2">
                    <span>Data Utama</span>
                    <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-emerald-600">Potensi Lahan</span>
                </nav>
                <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-900 uppercase"
                    style="font-family: 'Ramabhadra', sans-serif;">
                    Potensi <span class="text-emerald-600 italic">Lahan</span>
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="relative group">
                    <div
                        class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" placeholder="Cari data lokasi..."
                        class="block w-full md:w-72 pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm transition-all outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-sm">
                </div>
                <button onclick="window.location.reload()"
                    class="p-3 bg-white text-slate-500 rounded-xl border border-slate-200 hover:bg-slate-50 hover:text-emerald-600 transition-all shadow-sm active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                </button>
                <button
                    class="flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-xl shadow-md shadow-emerald-600/20 hover:bg-emerald-700 transition-all active:scale-95 font-bold text-sm tracking-wide">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Data
                </button>
            </div>
        </div>

        <div x-data="{ filter_resor: '', filter_sektor: '', filter_lahan: '', is_validated: false }" class="px-4 mb-8">
            <div
                class="bg-white/50 backdrop-blur-md p-2 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row gap-2">
                <select x-model="filter_resor"
                    class="flex-1 bg-transparent border-none rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 focus:ring-0 cursor-pointer py-3 px-4">
                    <option value="">Pilih Resor</option>
                </select>
                <div class="hidden lg:block w-px h-8 bg-slate-200 self-center"></div>
                <select x-model="filter_sektor"
                    class="flex-1 bg-transparent border-none rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 focus:ring-0 cursor-pointer py-3 px-4">
                    <option value="">Pilih Sektor</option>
                </select>
                <div class="hidden lg:block w-px h-8 bg-slate-200 self-center"></div>
                <select x-model="filter_lahan"
                    class="flex-1 bg-transparent border-none rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 focus:ring-0 cursor-pointer py-3 px-4">
                    <option value="">Jenis Lahan</option>
                </select>
                <button @click="is_validated = !is_validated"
                    :class="is_validated ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white text-slate-400 border-slate-200'"
                    class="flex-1 flex items-center justify-between px-5 py-3 border rounded-xl transition-all shadow-sm">
                    <span class="text-[10px] font-black uppercase tracking-widest"
                        x-text="is_validated ? 'Tervalidasi' : 'Belum Validasi'"></span>
                    <div :class="is_validated ? 'bg-emerald-500' : 'bg-slate-300'"
                        class="w-2 h-2 rounded-full shadow-sm transition-colors"></div>
                </button>
            </div>
        </div>

        <div class="px-4 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div
                class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative group transition-all hover:shadow-xl hover:shadow-slate-200/50">
                <div class="p-8 relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
                        <div class="flex items-center gap-6">
                            <div
                                class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center border border-emerald-100 shadow-inner group-hover:scale-110 transition-transform duration-500">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.3em] mb-1">Capaian Luas
                                    Area</p>
                                <h1 class="text-5xl font-black text-slate-900 tracking-tighter">170,969<span
                                        class="text-emerald-500 text-3xl">.02</span> <span
                                        class="text-base font-medium text-slate-400 uppercase tracking-normal">Ha</span>
                                </h1>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span
                                class="px-4 py-1.5 bg-slate-900 text-white rounded-full text-[10px] font-black tracking-widest uppercase mb-2 shadow-lg">5,547
                                Lokasi</span>
                            <div class="flex items-center gap-1 text-emerald-600 font-bold text-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span>+12.5% MoM</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @php
                            $cats = [
                                ['label' => 'Milik Polri', 'val' => '9.63', 'color' => 'bg-blue-500'],
                                ['label' => 'Poktan Binaan', 'val' => '34,903.96', 'color' => 'bg-emerald-500'],
                                ['label' => 'Masyarakat', 'val' => '27,320.94', 'color' => 'bg-indigo-500'],
                                ['label' => 'Hutan Sosial', 'val' => '20,690.15', 'color' => 'bg-amber-500'],
                                ['label' => 'LBS (Sawah)', 'val' => '65,013.95', 'color' => 'bg-rose-500'],
                                ['label' => 'Lainnya', 'val' => '108.02', 'color' => 'bg-slate-400'],
                            ];
                        @endphp
                        @foreach($cats as $c)
                            <div
                                class="p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-emerald-200 transition-all">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-2 h-2 rounded-full {{ $c['color'] }}"></div>
                                    <span
                                        class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $c['label'] }}</span>
                                </div>
                                <p class="text-lg font-bold text-slate-800">{{ $c['val'] }} <span
                                        class="text-[10px] text-slate-400 font-normal italic">Ha</span></p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div
                    class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-emerald-400 to-emerald-600 opacity-20">
                </div>
            </div>

            <div class="lg:col-span-4 flex flex-col gap-6">
                <div
                    class="bg-rose-50 p-6 rounded-3xl border border-rose-100 relative overflow-hidden group hover:bg-rose-100 transition-colors">
                    <div class="relative z-10 flex items-start gap-4">
                        <div class="w-12 h-12 bg-white text-rose-500 rounded-xl flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-rose-600 uppercase tracking-widest mb-1">Sistem Peringatan
                            </p>
                            <h3 class="text-xl font-black text-slate-800 uppercase italic leading-none">0 <span
                                    class="text-sm font-bold text-rose-400">POLRES NIHIL</span></h3>
                            <div class="mt-4 flex gap-4">
                                <div class="text-center">
                                    <span class="block text-sm font-bold text-slate-700">66</span>
                                    <span class="text-[9px] text-slate-400 uppercase">Polsek</span>
                                </div>
                                <div class="text-center">
                                    <span class="block text-sm font-bold text-slate-700">5,607</span>
                                    <span class="text-[9px] text-slate-400 uppercase">Desa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-slate-900 p-6 rounded-3xl relative overflow-hidden flex-1 flex flex-col justify-between shadow-xl shadow-slate-900/20 group">
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-4">
                            <span
                                class="px-3 py-1 bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase tracking-widest rounded-full border border-emerald-500/30">Pending
                                Validation</span>
                            <span class="text-2xl font-black text-white italic opacity-20">0.29%</span>
                        </div>
                        <p class="text-slate-300 text-sm mb-1 uppercase tracking-tighter">Potensi Pengembangan</p>
                        <h2 class="text-3xl font-black text-white leading-none">10.31 <span
                                class="text-sm text-emerald-400 uppercase">Ha</span></h2>
                    </div>
                    <button
                        class="relative z-10 mt-6 w-full py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all border border-white/10">Lihat
                        Detail Menunggu</button>

                    <div
                        class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-700">
                    </div>
                </div>
            </div>
        </div>

        <div id="scroll-trigger-area"
            class="flex justify-center relative z-50 pointer-events-none transition-all duration-500"
            style="margin-top: -30px;">
            <button id="btn-scroll-table"
                onclick="document.querySelector('main').scrollTo({ top: 400, behavior: 'smooth' })"
                class="group pointer-events-auto flex items-center gap-3 px-8 py-3.5 bg-white border border-slate-200 text-slate-900 rounded-full shadow-xl hover:shadow-emerald-500/10 hover:border-emerald-300 transition-all duration-500 active:scale-95">
                <div
                    class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <svg class="w-3 h-3 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 13l-7 7-7-7"></path>
                    </svg>
                </div>
                <span class="text-[11px] font-black uppercase tracking-[0.3em]">Jelajahi Tabel</span>
            </button>
        </div>

        <div id="tabel-potensi" class="px-4 mt-16 transition-all duration-700 animate-in fade-in slide-in-from-bottom-10">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-5">Polisi Penggerak</th>
                                <th class="px-8 py-5">Penanggung Jawab</th>
                                <th class="px-8 py-5 text-center">Luas (HA)</th>
                                <th class="px-8 py-5">Status & Validasi</th>
                                <th class="px-8 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="bg-emerald-50/30 border-y border-emerald-100/50">
                                <td colspan="5"
                                    class="px-8 py-3 font-bold text-emerald-800 uppercase tracking-tight text-[11px]">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Kab. Bangkalan, Kec. Arosbaya, Desa Dlemer
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors border-b border-slate-50">
                                <td class="px-8 py-6">
                                    <p class="font-bold text-slate-800 uppercase text-xs">Bambang Priono</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">+62 878-4523-7310</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="font-bold text-slate-800 uppercase text-xs">Rohmatulloh</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Dusun Ronceh</p>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="text-base font-black text-slate-800">3.50</span>
                                    <span class="block text-[9px] text-emerald-500 font-bold uppercase">Produktif</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-[10px] font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md w-fit uppercase">Achmad
                                            Furkon</span>
                                        <span
                                            class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md w-fit uppercase">Validated:
                                            Dwi Achmat</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            class="p-2.5 bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all"><svg
                                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg></button>
                                        <button
                                            class="p-2.5 bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"><svg
                                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <button id="btn-back-to-top" onclick="document.querySelector('main').scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-8 right-8 z-[100] p-4 bg-slate-900 text-white rounded-2xl shadow-2xl opacity-0 translate-y-10 pointer-events-none transition-all duration-500 hover:bg-emerald-600 hover:-translate-y-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 11l7-7 7 7M5 19l7-7 7 7"></path>
        </svg>
    </button>
@endsection

@push('scripts')
    <script>
        const scrollContainer = document.querySelector('main');
        const scrollTriggerArea = document.getElementById('scroll-trigger-area');
        const backToTopBtn = document.getElementById('btn-back-to-top');

        if (scrollContainer) {
            scrollContainer.addEventListener('scroll', () => {
                const scrollPos = scrollContainer.scrollTop;

                if (scrollPos > 100) {
                    scrollTriggerArea.classList.add('opacity-0', 'pointer-events-none', '-translate-y-10');
                } else {
                    scrollTriggerArea.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-10');
                }

                if (scrollPos > 400) {
                    backToTopBtn.classList.remove('opacity-0', 'translate-y-10', 'pointer-events-none');
                } else {
                    backToTopBtn.classList.add('opacity-0', 'translate-y-10', 'pointer-events-none');
                }
            });
        }
    </script>
@endpush