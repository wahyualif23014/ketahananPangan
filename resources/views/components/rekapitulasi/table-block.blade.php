    <div id="rekap-table-block" class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-slate-800">Rincian Produksi Wilayah</h3>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-full uppercase">{{ $dataRekap->total() }} baris</span>
            </div>
            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded uppercase tracking-wider border border-emerald-100">Live Data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <th rowspan="2" class="px-6 py-4 border-b-2 border-slate-200 sticky left-0 z-10 bg-slate-50/95 backdrop-blur">Wilayah / Satker</th>
                        <th rowspan="2" class="px-4 py-4 text-center border-b-2 border-l border-slate-200">Titik Lahan</th>
                        <th rowspan="2" class="px-4 py-4 text-right border-b-2 border-l border-slate-200">Potensi (HA)</th>
                        <th rowspan="2" class="px-4 py-4 text-right border-b-2 border-l border-slate-200">Tanam (HA)</th>
                        <th colspan="2" class="px-4 py-2 text-center border-b border-l border-slate-200 bg-blue-50/50 text-blue-600">Hasil Produksi</th>
                        <th colspan="5" class="px-4 py-2 text-center border-b border-l border-slate-200 bg-emerald-50/50 text-emerald-700">Detail Serapan (Ton)</th>
                    </tr>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200">Panen (HA)</th>
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200">Total (Ton)</th>
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200 bg-emerald-50/20 text-emerald-600">Bulog</th>
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200 bg-emerald-50/20 text-emerald-600">Pabrik</th>
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200 bg-emerald-50/20 text-emerald-600">Tengkulak</th>
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200 bg-emerald-50/20 text-emerald-600">Konsumsi</th>
                        <th class="px-4 py-3 text-right border-b-2 border-l border-slate-200 bg-emerald-100/40 text-emerald-700">Total %</th>
                    </tr>
                </thead>

                @forelse($groupedData as $polresName => $polseksCollection)
                <tbody x-data="{ openPolres: true, openPolsek: {} }" class="text-sm">
                    @php
                        $totalPolresHA = $polseksCollection->sum('kapasitas_lahan_ha');
                        $totalPolresTitik = $polseksCollection->sum('total_titik_lahan');
                        $polseksGrouped = $polseksCollection->groupBy('nama_polsek');
                    @endphp
                    <tr @click="openPolres = !openPolres" class="bg-gradient-to-r from-slate-100 to-slate-50/50 border-y-2 border-slate-200 cursor-pointer hover:from-slate-200 transition-all group">
                        <td colspan="11" class="px-6 py-3 sticky left-0 z-10 bg-inherit">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-500 shadow-sm" :class="openPolres ? 'animate-pulse' : ''"></div>
                                    <span class="text-[11px] font-black text-slate-800 uppercase tracking-widest">{{ $polresName ?: 'POLRES TIDAK DIKETAHUI' }}</span>
                                    <span class="hidden sm:inline-flex px-2 py-0.5 bg-slate-200 text-slate-700 text-[9px] font-bold rounded-full">
                                        {{ number_format($totalPolresHA, 2) }} HA &nbsp;·&nbsp; {{ number_format($totalPolresTitik) }} Titik
                                    </span>
                                </div>
                                <svg :class="openPolres ? 'rotate-180' : ''" class="w-4 h-4 text-slate-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </td>
                    </tr>

                    @foreach($polseksGrouped as $polsekName => $desaCollection)
                    @php
                        $psKey = 'ps_' . md5($polsekName . $polresName);
                        $sTitik = $desaCollection->sum('total_titik_lahan');
                        $sHA = $desaCollection->sum('kapasitas_lahan_ha');
                        $sTanam = $desaCollection->sum('aktual_tanam_ha');
                        $sPanen = $desaCollection->sum('aktual_panen_ha');
                        $sProd = $desaCollection->sum('total_produksi_panen');
                        $sBulog = $desaCollection->sum('serapan_bulog');
                        $sPabrik = $desaCollection->sum('serapan_pabrik');
                        $sTengkulak = $desaCollection->sum('serapan_tengkulak');
                        $sKonsumsi = $desaCollection->sum('serapan_konsumsi');
                        
                        $sTotalSerapan = $sBulog + $sPabrik + $sTengkulak + $sKonsumsi;
                        $sSerapPersen = $sProd > 0 ? ($sTotalSerapan / $sProd) * 100 : 0;
                    @endphp
                    <tr x-show="openPolres" @click="openPolsek['{{ $psKey }}'] = !openPolsek['{{ $psKey }}']" class="bg-blue-50/40 border-b border-blue-100/70 cursor-pointer hover:bg-blue-100/40 transition-colors">
                        <td class="px-6 py-3 pl-12 sticky left-0 z-10 bg-blue-50/90 backdrop-blur border-r border-blue-100/50">
                            <div class="flex items-center gap-2">
                                <svg :class="openPolsek['{{ $psKey }}'] ? '' : 'rotate-90'" class="w-3 h-3 text-blue-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                <span class="text-[11px] font-bold text-blue-900 uppercase">{{ $polsekName ?: 'POLSEK TIDAK DIKETAHUI' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center border-l border-blue-100/50"><span class="text-[11px] font-bold text-slate-700">{{ number_format($sTitik) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-blue-100/50"><span class="text-[11px] font-bold text-blue-700">{{ number_format($sHA, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-blue-100/50"><span class="text-[11px] font-semibold text-slate-600">{{ number_format($sTanam, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-blue-100/50"><span class="text-[11px] font-semibold text-rose-600">{{ number_format($sPanen, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-blue-100/50"><span class="text-[11px] font-bold text-rose-600">{{ number_format($sProd, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-emerald-100/50 bg-emerald-50/20"><span class="text-[11px] font-medium text-slate-600">{{ number_format($sBulog, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-emerald-100/50 bg-emerald-50/20"><span class="text-[11px] font-medium text-slate-600">{{ number_format($sPabrik, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-emerald-100/50 bg-emerald-50/20"><span class="text-[11px] font-medium text-slate-600">{{ number_format($sTengkulak, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-emerald-100/50 bg-emerald-50/20"><span class="text-[11px] font-medium text-slate-600">{{ number_format($sKonsumsi, 2) }}</span></td>
                        <td class="px-4 py-3 text-right border-l border-emerald-100/50 bg-emerald-100/30"><span class="inline-flex px-1.5 py-0.5 rounded-sm text-[10px] font-bold {{ $sSerapPersen >= 75 ? 'text-emerald-700' : 'text-slate-600' }}">{{ number_format($sSerapPersen, 1) }}%</span></td>
                    </tr>

                    @foreach($desaCollection as $row)
                    @php 
                        $hasData = ($row->total_titik_lahan ?? 0) > 0;
                        $rProd = $row->total_produksi_panen ?? 0;
                        $rBulog = $row->serapan_bulog ?? 0;
                        $rPabrik = $row->serapan_pabrik ?? 0;
                        $rTengkulak = $row->serapan_tengkulak ?? 0;
                        $rKonsumsi = $row->serapan_konsumsi ?? 0;
                        $rTotalSerapan = $rBulog + $rPabrik + $rTengkulak + $rKonsumsi;
                        $rSerapPersen = $rProd > 0 ? ($rTotalSerapan / $rProd) * 100 : 0;
                    @endphp
                    <tr x-show="openPolres && !openPolsek['{{ $psKey }}']" class="border-b border-slate-50 {{ $hasData ? 'bg-white hover:bg-slate-50/50' : 'bg-slate-50/20 opacity-60' }} transition-colors">
                        <td class="px-6 py-3 pl-16 sticky left-0 z-10 {{ $hasData ? 'bg-white' : 'bg-slate-50' }} border-r border-slate-100">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold capitalize {{ $hasData ? 'text-slate-800' : 'text-slate-400' }}">{{ strtolower($row->nama_desa) }}</span>
                                <span class="text-[9px] text-slate-400 uppercase tracking-tight">{{ $row->nama_jenis_lahan }} &bull; {{ $row->nama_komoditi }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center border-l border-slate-50 font-medium text-slate-700">{{ number_format($row->total_titik_lahan) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 font-bold text-slate-800">{{ number_format($row->kapasitas_lahan_ha, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 font-medium text-slate-600">{{ number_format($row->aktual_tanam_ha, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 font-medium text-rose-500">{{ number_format($row->aktual_panen_ha, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 font-bold text-rose-500">{{ number_format($rProd, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 text-slate-500 text-[11px]">{{ number_format($rBulog, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 text-slate-500 text-[11px]">{{ number_format($rPabrik, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 text-slate-500 text-[11px]">{{ number_format($rTengkulak, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50 text-slate-500 text-[11px]">{{ number_format($rKonsumsi, 2) }}</td>
                        <td class="px-4 py-3 text-right border-l border-slate-50">
                            <span class="text-[11px] font-bold {{ $rSerapPersen >= 75 ? 'text-emerald-600' : 'text-slate-500' }}">
                                {{ number_format($rSerapPersen, 2) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
                @empty
                <tbody><tr><td colspan="11" class="px-8 py-16 text-center text-slate-500 font-bold">Data tidak ditemukan.</td></tr></tbody>
                @endforelse
            </table>
        </div>

        {{-- Pagination --}}
        @if($dataRekap->hasPages())
        <div id="rekap-pagination" class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Hal {{ $dataRekap->currentPage() }} dari {{ $dataRekap->lastPage() }} &bull; Total {{ number_format($dataRekap->total()) }} data</div>
            <div class="flex items-center gap-2">
                {{ $dataRekap->links() }}
            </div>
        </div>
        @endif
    </div>
