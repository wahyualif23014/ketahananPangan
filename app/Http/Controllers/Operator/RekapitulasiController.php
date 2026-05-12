<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiLahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // Import Cache
use App\Exports\RekapitulasiExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapitulasiController extends Controller
{
    /**
     * Buat closure scope yang membatasi query ke jurisdiksi operator
     * berdasarkan kolom id_polres (format prefix matching).
     */
    private function makeScopeClosure(): \Closure
    {
        $scope = auth()->user()->id_tugas ?? '0';

        return function ($query) use ($scope) {
            if ($scope && $scope !== '0') {
                $parts = explode('.', $scope);
                $levelCount = count($parts);
                
                if ($levelCount == 2) { // Polres
                    $query->where('id_polres', $scope);
                } elseif ($levelCount >= 3) { // Polsek or lower
                    $polsekScope = implode('.', array_slice($parts, 0, 3));
                    $query->where('id_polsek', $polsekScope);
                } else { // Polda
                    $query->where('id_polres', 'LIKE', $parts[0] . '.%');
                }
            }
            return $query;
        };
    }

    /**
     * Buat scope untuk tabel `tingkat` (id_tingkat bukan id_polres).
     */
    private function makeTingkatScope(): \Closure
    {
        $scope = auth()->user()->id_tugas ?? '0';

        return function ($query) use ($scope) {
            if ($scope && $scope !== '0') {
                $parts = explode('.', $scope);
                $levelCount = count($parts);

                if ($levelCount == 2) {
                    $query->where(function($q) use ($scope) { 
                        $q->where('id_tingkat', $scope)->orWhere('id_tingkat', 'LIKE', $scope . '.%'); 
                    });
                } elseif ($levelCount >= 3) {
                    $polsekScope = implode('.', array_slice($parts, 0, 3));
                    $query->where(function($q) use ($polsekScope) { 
                        $q->where('id_tingkat', $polsekScope)->orWhere('id_tingkat', 'LIKE', $polsekScope . '.%'); 
                    });
                } else {
                    $poldaPrefix = $parts[0];
                    $query->where('id_tingkat', 'LIKE', $poldaPrefix . '.%');
                }
            }
            return $query;
        };
    }

    public function index(Request $request)
    {
        set_time_limit(120);

        $applyScope    = $this->makeScopeClosure();
        $applyTingkat  = $this->makeTingkatScope();
        $userId        = auth()->id(); // Untuk unique cache key per user operator
        $scope         = auth()->user()->id_tugas ?? '0';

        $userLevel = 0;
        if ($scope !== '0' && !empty($scope)) {
            $userLevel = count(explode('.', $scope));
        }

        // 1. OPTIMASI: Cache list Polres (Hanya jika userLevel < 2, misal Polda)
        $polresList = collect();
        if ($userLevel < 2) {
            $polresList = Cache::remember("op_polres_list_{$userId}", 3600, function() use ($applyTingkat) {
                return $applyTingkat(DB::table('tingkat'))
                    ->select('id_tingkat', 'nama_tingkat') // Explicit Select
                    ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
                    ->orderBy('id_tingkat')
                    ->get();
            });
        }

        // 2. OPTIMASI: Polsek List (Cache 5 Menit jika sedang memfilter polres tertentu)
        $polsekList = collect();
        if ($userLevel == 2) {
            // Operator Polres: Load polsek di bawah yurisdiksi polres-nya
            $polresReq = $scope;
            $polsekList = Cache::remember("op_polsek_list_{$userId}_{$polresReq}", 300, function() use ($applyTingkat, $polresReq) {
                return $applyTingkat(DB::table('tingkat'))
                    ->select('id_tingkat', 'nama_tingkat') // Explicit Select
                    ->where('id_tingkat', 'like', $polresReq . '.%')
                    ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                    ->orderBy('nama_tingkat')
                    ->get();
            });
        } elseif ($userLevel < 2 && $request->filled('polres')) {
            $polresReq = $request->polres;
            $polsekList = Cache::remember("op_polsek_list_{$userId}_{$polresReq}", 300, function() use ($applyTingkat, $polresReq) {
                return $applyTingkat(DB::table('tingkat'))
                    ->select('id_tingkat', 'nama_tingkat') // Explicit Select
                    ->where('id_tingkat', 'like', $polresReq . '.%')
                    ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                    ->orderBy('nama_tingkat')
                    ->get();
            });
        }

        // 3. OPTIMASI: Cache Data Lahan & Komoditi (TTL 1 Jam)
        $jenisLahanList = Cache::remember('global_jenis_lahan', 3600, function() {
            return DB::table('jenislahan')
                ->select('id_jenis_lahan', 'nama_jenis_lahan')
                ->distinct()
                ->orderBy('id_jenis_lahan', 'asc')
                ->get();
        });

        $komoditiList = Cache::remember('global_komoditi_list', 3600, function() {
            return DB::table('komoditi')
                ->select('id_komoditi', 'nama_komoditi')
                ->get();
        });

        // 4. OPTIMASI: Explicit Selection & Result Caching
        // Caching per user selama 60 detik untuk mempercepat navigasi
        $cacheKey = "rekap_op_data_{$userId}_" . md5(serialize($request->all()) . '_p' . $request->get('page', 1));

        $dataRekap = Cache::remember($cacheKey, 60, function() use ($request, $applyScope) {
            return $applyScope(RekapitulasiLahan::select([
                'nama_polres', 'nama_polsek', 'nama_desa', 'kapasitas_lahan_ha',
                'aktual_tanam_ha', 'aktual_panen_ha', 'total_produksi_panen',
                'total_titik_lahan', 'persentase_serapan', 'nama_jenis_lahan',
                'nama_komoditi', 'tahun_lahan'
            ]))
            ->filter($request->all())
            ->paginate(100);
        });

        $dataRekap->withQueryString();

        return view('operator.rekapitulasi.operator_rekap', compact(
            'dataRekap',
            'polresList',
            'polsekList',
            'jenisLahanList',
            'komoditiList',
            'userLevel',
            'scope'
        ));
    }

    public function getPolsek(Request $request)
    {
        if (!$request->filled('polres')) {
            return response()->json([]);
        }

        $userId       = auth()->id();
        $polresReq    = $request->polres;
        $scope        = auth()->user()->id_tugas ?? '0';
        
        $userLevel = 0;
        if ($scope !== '0' && !empty($scope)) {
            $userLevel = count(explode('.', $scope));
        }

        if ($userLevel >= 2) {
             return response()->json([]);
        }

        $applyTingkat = $this->makeTingkatScope();

        // Optimasi: AJAX Caching (5 Menit)
        $polsekList = Cache::remember("ajax_op_polsek_{$userId}_{$polresReq}", 300, function() use ($applyTingkat, $polresReq) {
            return $applyTingkat(DB::table('tingkat'))
                ->select('id_tingkat', 'nama_tingkat')
                ->where('id_tingkat', 'like', $polresReq . '.%')
                ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                ->orderBy('nama_tingkat')
                ->get()
                ->map(fn($item) => ['value' => $item->id_tingkat, 'label' => $item->nama_tingkat]);
        });

        return response()->json($polsekList);
    }

    public function export(Request $request)
    {
        $applyScope = $this->makeScopeClosure();
        $fileName   = 'Rekap_Operator_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new RekapitulasiExport($request->all(), $applyScope),
            $fileName
        );
    }
}