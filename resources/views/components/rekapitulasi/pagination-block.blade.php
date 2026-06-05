        {{-- Pagination --}}
        @if($dataRekap->hasPages())
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Hal {{ $dataRekap->currentPage() }} dari {{ $dataRekap->lastPage() }} &bull; Total {{ number_format($dataRekap->total()) }} data</div>
            <div class="flex items-center gap-2">
                {{ $dataRekap->links() }}
            </div>
        </div>
        @endif
