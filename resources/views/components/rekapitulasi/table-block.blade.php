    <div id="rekap-table-block" class="mx-4 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h3 class="font-semibold text-slate-800">Rincian Produksi Wilayah</h3>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-full uppercase">{{ $dataRekap->total() }} baris</span>
            </div>
            <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded uppercase tracking-wider border border-emerald-100">Live Data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        <th rowspan="2" class="px-8 py-4 border-b-2 border-slate-200">Wilayah / Satker</th>
                        <th rowspan="2" class="px-6 py-4 text-right border-b-2 border-slate-200">Potensi Lahan</th>
                        <th rowspan="2" class="px-6 py-4 text-right border-b-2 border-slate-200">Aktual Tanam</th>
                        <th colspan="2" class="px-6 py-2 text-center border-b border-l border-slate-200 bg-blue-50/50 text-blue-600">Hasil Produksi</th>
                    </tr>
                    <tr class="bg-slate-50/80 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <th class="px-6 py-3 text-right border-b-2 border-l border-slate-200">Panen Ha / Ton</th>
                        <th class="px-6 py-3 text-right border-b-2 border-l border-slate-200">Serapan %</th>
                    </tr>
                </thead>

                @forelse($groupedData as $polresName => $polseksCollection)
                <tbody x-data="{ openPolres: true, openPolsek: {} }" class="text-sm">
                    @php
                        $totalPolresHA = $polseksCollection->sum('kapasitas_lahan_ha');
                        $polseksGrouped = $polseksCollection->groupBy('nama_polsek');
                    @endphp
                    <tr @click="openPolres = !openPolres" class="bg-gradient-to-r from-emerald-50 to-emerald-50/30 border-y-2 border-emerald-100 cursor-pointer hover:from-emerald-100/60 transition-all group">
                        <td colspan="5" class="px-8 py-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm" :class="openPolres ? 'animate-pulse' : ''"></div>
                                    <span class="text-[11px] font-black text-emerald-900 uppercase tracking-widest">{{ $polresName ?: 'POLRES TIDAK DIKETAHUI' }}</span>
                                    <span class="hidden sm:inline-flex px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-bold rounded-full">
                                        {{ number_format($totalPolresHA, 2) }} HA &nbsp;·&nbsp; {{ $polseksCollection->count() }} entri
                                    </span>
                                </div>
                                <svg :class="openPolres ? 'rotate-180' : ''" class="w-4 h-4 text-emerald-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </td>
                    </tr>

                    @foreach($polseksGrouped as $polsekName => $desaCollection)
                    @php
                        $psKey = 'ps_' . md5($polsekName . $polresName);
                        $sHA = $desaCollection->sum('kapasitas_lahan_ha');
                        $sTanam = $desaCollection->sum('aktual_tanam_ha');
                        $sPanen = $desaCollection->sum('aktual_panen_ha');
                        $sProd = $desaCollection->sum('total_produksi_panen');
                        $sSerap = $sHA > 0 ? ($sTanam / $sHA) * 100 : 0;
                    @endphp
                    <tr x-show="openPolres" @click="openPolsek['{{ $psKey }}'] = !openPolsek['{{ $psKey }}']" class="bg-blue-50/40 border-b border-blue-100/70 cursor-pointer hover:bg-blue-100/40 transition-colors">
                        <td class="px-8 py-3 pl-14">
                            <div class="flex items-center gap-2">
                                <svg :class="openPolsek['{{ $psKey }}'] ? '' : 'rotate-90'" class="w-3 h-3 text-blue-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                <span class="text-[11px] font-bold text-blue-900 uppercase">{{ $polsekName ?: 'POLSEK TIDAK DIKETAHUI' }}</span>
                                <span class="px-1.5 py-0.5 bg-blue-100/80 text-blue-600 text-[9px] font-bold rounded">{{ $desaCollection->count() }} desa</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right"><span class="text-[11px] font-bold text-blue-700">{{ number_format($sHA, 2) }}</span> <span class="text-[9px] text-blue-400">HA</span></td>
                        <td class="px-6 py-3 text-right"><span class="text-[11px] font-semibold text-slate-500">{{ number_format($sTanam, 2) }}</span> <span class="text-[9px] text-slate-400">HA</span></td>
                        <td class="px-6 py-3 text-right border-l border-blue-100/50"><span class="text-[11px] font-semibold text-slate-500 italic">{{ number_format($sPanen, 2) }} / {{ number_format($sProd, 2) }}</span> <span class="text-[9px] text-slate-400">TON</span></td>
                        <td class="px-6 py-3 text-right border-l border-blue-100/50"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $sSerap >= 75 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ number_format($sSerap, 1) }}%</span></td>
                    </tr>

                    @foreach($desaCollection as $row)
                    @php $hasData = ($row->total_titik_lahan ?? 0) > 0; @endphp
                    <tr x-show="openPolres && !openPolsek['{{ $psKey }}']" class="border-b border-slate-50 {{ $hasData ? 'bg-white' : 'bg-slate-50/20 opacity-60' }}">
                        <td class="px-8 py-3.5 pl-20">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold capitalize {{ $hasData ? 'text-slate-800' : 'text-slate-400' }}">{{ strtolower($row->nama_desa) }}</span>
                                <span class="text-[10px] text-slate-400 uppercase tracking-tight">{{ $row->nama_jenis_lahan }} &bull; {{ $row->nama_komoditi }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-900">{{ number_format($row->kapasitas_lahan_ha, 2) }}</td>
                        <td class="px-6 py-3.5 text-right font-bold text-rose-500">{{ number_format($row->aktual_tanam_ha, 2) }}</td>
                        <td class="px-6 py-3.5 text-right border-l border-slate-100/50 italic text-rose-500">{{ number_format($row->aktual_panen_ha, 2) }} / {{ number_format($row->total_produksi_panen, 2) }}</td>
                        <td class="px-6 py-3.5 text-right border-l border-slate-100/50">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $row->persentase_serapan >= 75 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                                {{ number_format($row->persentase_serapan, 2) }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
                @empty
                <tbody><tr><td colspan="5" class="px-8 py-16 text-center text-slate-500 font-bold">Data tidak ditemukan.</td></tr></tbody>
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
