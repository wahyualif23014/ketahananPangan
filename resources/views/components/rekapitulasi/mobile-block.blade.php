    <div id="rekap-mobile-wrapper" class="sm:hidden px-4 space-y-5">
        @forelse($groupedData as $polresName => $polseksCol)
        <div x-data="{ openPolres: true, openPolsek: {} }" class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm" x-cloak>
            <div @click="openPolres = !openPolres" class="px-4 py-3.5 bg-gradient-to-r from-emerald-50 to-white flex justify-between items-center cursor-pointer">
                <h3 class="text-xs font-black text-emerald-800 uppercase tracking-widest">{{ $polresName ?: 'UNKNOWN' }}</h3>
                <svg :class="{ 'rotate-180': openPolres }" class="w-4 h-4 text-emerald-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
            <div x-show="openPolres" class="divide-y divide-slate-100">
                @foreach($polseksCol->groupBy('nama_polsek') as $polsekName => $desaRows)
                <div @click="openPolsek['{{ $polsekName }}'] = !openPolsek['{{ $polsekName }}']" class="px-4 py-3 bg-blue-50/50 text-[11px] font-bold text-blue-800 uppercase flex justify-between cursor-pointer">
                    <span>{{ $polsekName ?: 'UNKNOWN' }}</span>
                    <span class="bg-blue-100 px-1.5 rounded">{{ $desaRows->count() }}</span>
                </div>
                <div x-show="!openPolsek['{{ $polsekName }}']" class="p-3 space-y-2">
                    @foreach($desaRows as $row)
                    <div class="rounded-xl border p-3 bg-white space-y-2">
                        <div class="flex justify-between font-black text-[13px] capitalize"><span>{{ strtolower($row->nama_desa) }}</span> <span class="text-[9px] text-emerald-600">AKTIF</span></div>
                        <div class="grid grid-cols-3 gap-2 text-center text-[10px]">
                            <div class="bg-slate-50 p-1 rounded">Potensi<br><b>{{ number_format($row->kapasitas_lahan_ha, 1) }}</b></div>
                            <div class="bg-slate-50 p-1 rounded">Tanam<br><b class="text-rose-500">{{ number_format($row->aktual_tanam_ha, 1) }}</b></div>
                            <div class="bg-slate-50 p-1 rounded">Hasil<br><b class="text-emerald-600">{{ number_format($row->persentase_serapan, 1) }}%</b></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="text-center p-10 text-slate-500">Kosong.</div>
        @endforelse
