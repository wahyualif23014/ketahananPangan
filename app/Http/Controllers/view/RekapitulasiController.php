<?php

namespace App\Http\Controllers\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiLahan;
use Illuminate\Support\Facades\DB;
use App\Exports\RekapitulasiExport;
use Maatwebsite\Excel\Facades\Excel;

class RekapitulasiController extends Controller
{
    /**
     * Scope closure untuk tabel RekapitulasiLahan (kolom id_polres).
     * Menggunakan pola yang sama dengan Operator\RekapitulasiController.
     */
    private function makeScopeClosure(): \Closure
    {
        $scope = auth()->user()->id_tugas ?? '0';

        return function ($query, string $column = 'id_polres') use ($scope) {
            if ($scope && $scope !== '0') {
                $polresPrefix = implode('.', array_slice(explode('.', $scope), 0, 2));
                $query->where(function($q) use ($column, $polresPrefix) { $q->where($column, $polresPrefix)->orWhere($column, \'LIKE\', $polresPrefix . \'.%\'); });
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
                    $q->where(function($q) use ($polresPrefix) { $q->where(\'id_tingkat\', $polresPrefix)->orWhere(\'id_tingkat\', \'LIKE\', $polresPrefix . \'.%\'); })
                      ->orWhereRaw("? = id_tingkat OR ? LIKE CONCAT(id_tingkat, \'.%\')", [$scope, $scope]);
                });
            }
            return $query;
        };
    }

    public function index(Request $request)
    {
        set_time_limit(120);

        $applyScope   = $this->makeScopeClosure();
        $applyTingkat = $this->makeTingkatScope();

        // ── Polres — scoped ke jurisdiksi user view ───────────────────────
        $polresList = $applyTingkat(DB::table('tingkat'))
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        // ── Polsek — cascading, hanya jika polres dipilih ────────────────
        $polsekList = collect();
        if ($request->filled('polres')) {
            $polsekList = $applyTingkat(DB::table('tingkat'))
                ->where('id_tingkat', 'like', $request->polres . '.%')
                ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                ->orderBy('nama_tingkat')
                ->get();
        }

        $jenisLahanList = DB::table('jenislahan')
            ->select('id_jenis_lahan', 'nama_jenis_lahan')
            ->distinct()
            ->orderBy('id_jenis_lahan', 'asc')
            ->get();

        $komoditiList = DB::table('komoditi')
            ->select('id_komoditi', 'nama_komoditi')
            ->get();

        // ── Data Rekapitulasi — scope jurisdiksi dulu, lalu filter user ──
        $dataRekap = $applyScope(RekapitulasiLahan::query())
            ->filter($request->all())
            ->paginate(100)
            ->withQueryString();

        return view('view.rekapitulasi.view_rekapitulasi', compact(
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

        $applyTingkat = $this->makeTingkatScope();

        $polsekList = $applyTingkat(DB::table('tingkat'))
            ->where('id_tingkat', 'like', $request->polres . '.%')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
            ->orderBy('nama_tingkat')
            ->get()
            ->map(fn($item) => ['value' => $item->id_tingkat, 'label' => $item->nama_tingkat]);

        return response()->json($polsekList);
    }

    public function export(Request $request)
    {
        $applyScope = $this->makeScopeClosure();
        $fileName   = 'Rekap_View_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new RekapitulasiExport($request->all(), $applyScope),
            $fileName
        );
    }
}
