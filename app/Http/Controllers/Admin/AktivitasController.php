<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use Illuminate\Http\Request;

class AktivitasController extends Controller
{
    public function index(Request $request)
    {
        $bulan  = $request->input('bulan', (int) now()->format('n'));
        $tahun  = $request->input('tahun', (int) now()->format('Y'));
        $modul  = $request->input('modul', 'semua');
        $aksi   = $request->input('aksi', 'semua');
        $search = $request->input('search');

        $query = AktivitasLog::query()
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->orderByDesc('created_at');

        if ($modul !== 'semua') {
            $query->where('modul', $modul);
        }

        if ($aksi !== 'semua') {
            $query->where('aksi', $aksi);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_user', 'LIKE', "%$search%")
                  ->orWhere('username', 'LIKE', "%$search%")
                  ->orWhere('keterangan', 'LIKE', "%$search%")
                  ->orWhere('label_modul', 'LIKE', "%$search%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Stats untuk bulan ini
        $stats = AktivitasLog::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->selectRaw('aksi, COUNT(*) as total')
            ->groupBy('aksi')
            ->pluck('total', 'aksi');

        $modulList = AktivitasLog::selectRaw('DISTINCT modul')->pluck('modul');

        // Semua modul yang tersedia (selalu tampil di filter walau belum ada log)
        $allModuls = collect([
            // Data Utama
            'komoditi'         => 'Komoditi',
            'jabatan'          => 'Jabatan',
            'wilayah'          => 'Wilayah',
            'tingkat_kesatuan' => 'Tingkat Kesatuan',
            // Data Personel
            'anggota'          => 'Data Personel',
            // Kelola Lahan - Potensi
            'potensi_lahan'    => 'Data Potensi Lahan',
            // Kelola Lahan - Daftar Kelola
            'tanam'            => 'Data Tanam',
            'panen'            => 'Data Panen',
            'serapan'          => 'Data Serapan',
        ]);

        // Merge: tampilkan semua modul statis + modul dari DB yang belum terdaftar
        $modulList = $allModuls->keys()->merge(
            $modulList->reject(fn($m) => $allModuls->has($m))
        );

        $tahunList = AktivitasLog::selectRaw('DISTINCT tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('admin.aktivitas.index', compact(
            'logs', 'bulan', 'tahun', 'modul', 'aksi', 'search',
            'stats', 'modulList', 'tahunList', 'allModuls'
        ));
    }

    public function show($id)
    {
        $log = AktivitasLog::findOrFail($id);

        $dataLama = $log->data_lama ? json_decode($log->data_lama, true) : null;
        $dataBaru = $log->data_baru ? json_decode($log->data_baru, true) : null;

        return view('admin.aktivitas.show', compact('log', 'dataLama', 'dataBaru'));
    }
}
