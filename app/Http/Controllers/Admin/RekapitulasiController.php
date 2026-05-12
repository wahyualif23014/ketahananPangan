<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiLahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exports\RekapitulasiExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapitulasiController extends Controller
{
    /**
     * Menampilkan halaman utama rekapitulasi dengan optimasi pembacaan data.
     * Flow: Request -> Cache/DB -> Controller -> View
     */
    public function index(Request $request)
    {
        set_time_limit(120);

        // 1. Optimasi Dropdown List menggunakan Cache (TTL: 1 Jam)
        // Ini mengurangi beban query berulang pada tabel master 'tingkat', 'jenislahan', dan 'komoditi'
        $polresList = Cache::remember('rekap_polres_list', 3600, function () {
            return DB::table('tingkat')
                ->select('id_tingkat', 'nama_tingkat')
                ->whereRaw('LENGTH(TRIM(id_tingkat)) = 5')
                ->get();
        });

        $jenisLahanList = Cache::remember('rekap_jenis_lahan_list', 3600, function () {
            return DB::table('jenislahan')
                ->select('id_jenis_lahan', 'nama_jenis_lahan')
                ->distinct()
                ->orderBy('id_jenis_lahan', 'asc')
                ->get();
        });

        $komoditiList = Cache::remember('rekap_komoditi_list', 3600, function () {
            return DB::table('komoditi')
                ->select('id_komoditi', 'nama_komoditi')
                ->get();
        });

        // 2. Optimasi Query Utama: Explicit Selection & Result Caching
        // Caching selama 60 detik untuk mempercepat navigasi filter yang sama
        $cacheKey = 'rekap_admin_data_' . md5(serialize($request->all()) . '_page_' . $request->get('page', 1));

        $dataRekap = Cache::remember($cacheKey, 60, function () use ($request) {
            return RekapitulasiLahan::select([
                'nama_polres',
                'nama_polsek',
                'nama_desa',
                'kapasitas_lahan_ha',
                'aktual_tanam_ha',
                'aktual_panen_ha',
                'total_produksi_panen',
                'total_titik_lahan',
                'persentase_serapan',
                'nama_jenis_lahan',
                'nama_komoditi',
                'tahun_lahan'
            ])
                ->filter($request->all())
                ->paginate(100);
        });

        $dataRekap->withQueryString();

        $polsekList = [];
        if ($request->filled('polres')) {
            $polsekList = DB::table('tingkat')
                ->select('id_tingkat', 'nama_tingkat')
                ->where('id_tingkat', 'like', $request->polres . '.%')
                ->get();
        }

        return view('admin.rekapitulasi.index', compact(
            'dataRekap',
            'polresList',
            'polsekList',
            'jenisLahanList',
            'komoditiList'
        ));
    }

    public function getPolsek(Request $request)
    {
        if (!$request->filled('polres')) {
            return response()->json([]);
        }

        $cacheKey = 'polsek_of_' . $request->polres;
        $polsekList = Cache::remember($cacheKey, 300, function () use ($request) {
            return DB::table('tingkat')
                ->select('id_tingkat', 'nama_tingkat')
                ->where('id_tingkat', 'like', $request->polres . '.%')
                ->orderBy('nama_tingkat')
                ->get()
                ->map(fn($item) => ['value' => $item->id_tingkat, 'label' => $item->nama_tingkat]);
        });

        return response()->json($polsekList);
    }

    /**
     * Export Excel berdasarkan filter aktif.
     */
    public function export(Request $request)
    {
        $fileName = 'Rekap_Lahan_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new RekapitulasiExport($request->all()), $fileName);
    }
}
