<?php

namespace App\Http\Controllers\view;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DeliversRekapitulasiAjax;
use Illuminate\Http\Request;
use App\Models\RekapitulasiLahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // Import Cache untuk optimasi
use App\Exports\RekapitulasiExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapitulasiController extends Controller
{
    use DeliversRekapitulasiAjax;

    /**
     * Scope closure untuk tabel RekapitulasiLahan (kolom id_polres).
     */
    private function makeScopeClosure(): \Closure
    {
        $scope = auth()->user()->id_tugas ?? '0';

        return function ($query, string $column = 'id_polres') use ($scope) {
            if ($scope && $scope !== '0') {
                $polresPrefix = implode('.', array_slice(explode('.', $scope), 0, 2));
                $query->where(function($q) use ($column, $polresPrefix) { 
                    $q->where($column, $polresPrefix)->orWhere($column, 'LIKE', $polresPrefix . '.%'); 
                });
            }
            return $query;
        };
    }

    /**
     * Scope closure untuk tabel `tingkat` (kolom id_tingkat).
     */
    private function makeTingkatScope(): \Closure
    {
        $scope = auth()->user()->id_tugas ?? '0';

        return function ($query) use ($scope) {
            if ($scope && $scope !== '0') {
                $polresPrefix = implode('.', array_slice(explode('.', $scope), 0, 2));
                $query->where(function ($q) use ($polresPrefix, $scope) {
                    $q->where(function($q) use ($polresPrefix) { 
                        $q->where('id_tingkat', $polresPrefix)->orWhere('id_tingkat', 'LIKE', $polresPrefix . '.%'); 
                    })
                    ->orWhereRaw("? = id_tingkat OR ? LIKE CONCAT(id_tingkat, '.%')", [$scope, $scope]);
                });
            }
            return $query;
        };
    }

    public function index(Request $request)
    {
        set_time_limit(120);

        $scope = auth()->user()->id_tugas ?? '0';
        $userPolresId = '0';
        if ($scope && $scope !== '0') {
            $userPolresId = implode('.', array_slice(explode('.', $scope), 0, 2));
            $request->merge(['polres' => $userPolresId]);
        }

        $applyScope   = $this->makeScopeClosure();
        $applyTingkat = $this->makeTingkatScope();
        $userId       = auth()->id(); // Digunakan untuk unique cache key

        // 1. OPTIMASI: Cache list Polres (TTL 1 Jam) - Spesifik per User Scope
        $polresList = Cache::remember("view_polres_list_{$userId}", 3600, function() use ($applyTingkat, $userPolresId) {
            $query = DB::table('tingkat')->select('id_tingkat', 'nama_tingkat');
            if ($userPolresId && $userPolresId !== '0') {
                $query->where('id_tingkat', $userPolresId);
            } else {
                $query = $applyTingkat($query)
                    ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'");
            }
            return $query->orderBy('id_tingkat')->get();
        });

        // 2. OPTIMASI: Polsek List (Cache 5 Menit jika sedang memfilter polres tertentu)
        $polsekList = collect();
        if ($request->filled('polres')) {
            $polresReq = $request->polres;
            $polsekList = Cache::remember("view_polsek_list_{$userId}_{$polresReq}", 300, function() use ($applyTingkat, $polresReq) {
                return $applyTingkat(DB::table('tingkat'))
                    ->select('id_tingkat', 'nama_tingkat') // Explicit Select
                    ->where('id_tingkat', 'like', $polresReq . '.%')
                    ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                    ->orderBy('nama_tingkat')
                    ->get();
            });
        }

        // 3. OPTIMASI: Cache Global untuk Jenis Lahan & Komoditi (TTL 1 Jam)
        $jenisLahanList = Cache::remember('global_jenis_lahan_view', 3600, function() {
            return DB::table('jenislahan')
                ->select('id_jenis_lahan', 'nama_jenis_lahan')
                ->distinct()
                ->orderBy('id_jenis_lahan', 'asc')
                ->get();
        });

        $komoditiList = Cache::remember('global_komoditi_list_view', 3600, function() {
            return DB::table('komoditi')
                ->select('id_komoditi', 'nama_komoditi')
                ->get();
        });

        $dataRekap = $this->fetchRekapitulasiPaginated($request, $applyScope, $userId);

        return view('view.rekapitulasi.view_rekapitulasi', compact(
            'dataRekap',
            'polresList',
            'polsekList',
            'jenisLahanList',
            'komoditiList'
        ));
    }

    public function data(Request $request)
    {
        set_time_limit(120);

        if (! $request->ajax() && ! $request->wantsJson()) {
            return redirect()->route('view.rekapitulasi.index', $request->query());
        }

        $scope = auth()->user()->id_tugas ?? '0';
        if ($scope && $scope !== '0') {
            $userPolresId = implode('.', array_slice(explode('.', $scope), 0, 2));
            $request->merge(['polres' => $userPolresId]);
        }

        $applyScope = $this->makeScopeClosure();
        $userId = auth()->id();

        return $this->rekapitulasiAjaxResponse(
            $this->fetchRekapitulasiPaginated($request, $applyScope, $userId)
        );
    }

    private function fetchRekapitulasiPaginated(Request $request, \Closure $applyScope, int $userId)
    {
        $cacheKey = "rekap_view_data_{$userId}_" . md5(serialize($request->all()) . '_p' . $request->get('page', 1));

        $dataRekap = Cache::remember($cacheKey, 60, function () use ($request, $applyScope) {
            return $applyScope(RekapitulasiLahan::select([
                'nama_polres', 'nama_polsek', 'nama_desa', 'kapasitas_lahan_ha',
                'aktual_tanam_ha', 'aktual_panen_ha', 'total_produksi_panen',
                'total_titik_lahan', 'persentase_serapan', 'nama_jenis_lahan',
                'nama_komoditi', 'tahun_lahan',
            ]))
                ->filter($request->all())
                ->withSerapanDetails($request->all())
                ->paginate(100);
        });

        $dataRekap->withQueryString();

        return $dataRekap;
    }

    public function getPolsek(Request $request)
    {
        $scope = auth()->user()->id_tugas ?? '0';
        if ($scope && $scope !== '0') {
            $userPolresId = implode('.', array_slice(explode('.', $scope), 0, 2));
            $request->merge(['polres' => $userPolresId]);
        }

        if (!$request->filled('polres')) {
            return response()->json([]);
        }

        $applyTingkat = $this->makeTingkatScope();
        $userId       = auth()->id();
        $polresReq    = $request->polres;

        // Optimasi: AJAX Response Caching (5 Menit)
        $cacheKey = "ajax_view_polsek_{$userId}_{$polresReq}";
        $polsekList = Cache::remember($cacheKey, 300, function() use ($applyTingkat, $polresReq) {
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
        $scope = auth()->user()->id_tugas ?? '0';
        if ($scope && $scope !== '0') {
            $userPolresId = implode('.', array_slice(explode('.', $scope), 0, 2));
            $request->merge(['polres' => $userPolresId]);
        }

        $applyScope = $this->makeScopeClosure();
        $fileName   = 'Rekap_View_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new RekapitulasiExport($request->all(), $applyScope),
            $fileName
        );
    }
}