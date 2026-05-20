<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center font-black shadow-inner shadow-blue-700/50 group-hover/p:rotate-6 transition-all duration-300 border border-blue-400 flex-shrink-0">
            {{ strtoupper(substr($p->nama_anggota, 0, 2)) }}
        </div>
        <div class="space-y-1">
            <h4 class="text-base font-black text-slate-800 uppercase tracking-wide group-hover/p:text-blue-600 transition-colors">
                {{ $p->nama_anggota }}
            </h4>
            <div class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-500 tracking-wider">
                <span>NRP:</span> <span class="text-slate-700 font-black">{{ $p->username }}</span>
                <span class="mx-1 text-slate-300">•</span>
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 004.815 4.815l.773-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
                <span class="text-slate-700 font-bold">{{ $p->no_telp_anggota ?? 'BELUM DIATUR' }}</span>
            </div>
        </div>
    </div>
    
    <div class="flex flex-wrap items-center gap-3">
        <div class="bg-indigo-50 border border-indigo-100 text-indigo-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
            {{ $p->jabatan->nama_jabatan ?? 'STAFF' }}
        </div>
        <div class="bg-blue-50 border border-blue-100 text-blue-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-sm flex items-center gap-1.5">
            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></div>
            {{ $p->role }}
        </div>
        
        <div class="flex gap-2 opacity-0 group-hover/p:opacity-100 transition-opacity duration-300 delay-100">
            <button 
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-modal-personel', { detail: {
                    mode: 'edit',
                    data: {
                        id_anggota: '{{ $p->id_anggota }}',
                        nama_anggota: '{{ addslashes($p->nama_anggota) }}',
                        username: '{{ addslashes($p->username) }}',
                        id_jabatan: '{{ $p->id_jabatan }}',
                        role: '{{ $p->role }}',
                        id_tugas: '{{ addslashes($p->id_tugas) }}',
                        no_telp_anggota: '{{ addslashes($p->no_telp_anggota) }}'
                    }
                }}))"
                class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white shadow-sm transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            </button>
            <button 
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-modal-personel', { detail: {
                    mode: 'delete',
                    data: {
                        id_anggota: '{{ $p->id_anggota }}',
                        nama_anggota: '{{ addslashes($p->nama_anggota) }}',
                        username: '{{ $p->username }}',
                        id_jabatan: '{{ $p->id_jabatan }}',
                        role: '{{ $p->role }}',
                        id_tugas: '{{ $p->id_tugas }}',
                        no_telp_anggota: '{{ $p->no_telp_anggota }}'
                    }
                }}))"
                class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white shadow-sm transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
    </div>
</div>
