<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiLahan;
use Illuminate\Support\Facades\DB;
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

        return function ($query, string $column = 'id_polres') use ($scope) {
            if ($scope && $scope !== '0') {
                // Polres-level operator  → scope = "XX.YY"
                // Polsek-level operator  → scope = "XX.YY.ZZ"
                // Both can use LIKE prefix on id_polres (first 2 segments)
                $polresPrefix = implode('.', array_slice(explode('.', $scope), 0, 2));
                $query->where($column, 'LIKE', $polresPrefix . '%');
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
                $polresPrefix = implode('.', array_slice(explode('.', $scope), 0, 2));
                // Izinkan parent Polres muncul di dropdown
                $query->where(function ($q) use ($polresPrefix, $scope) {
                    $q->where('id_tingkat', 'LIKE', $polresPrefix . '%')
                      ->orWhereRaw("? LIKE CONCAT(id_tingkat, '%')", [$scope]);
                });
            }
            return $query;
        };
    }

    public function index(Request $request)
    {
        set_time_limit(120);

        $applyScope    = $this->makeScopeClosure();
        $applyTingkat  = $this->makeTingkatScope();

        // ── Polres scoped ke jurisdiksi operator ──────────────────────
        $polresList = $applyTingkat(DB::table('tingkat'))
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        // ── Polsek (cascading dari request) ───────────────────────────
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

        // ── Data Rekapitulasi — scope jurisdiksi dulu, filter user kemudian ──
        $dataRekap = $applyScope(RekapitulasiLahan::query())
            ->filter($request->all())
            ->paginate(100)
            ->withQueryString();

        return view('operator.rekapitulasi.operator_rekap', compact(
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
        $fileName   = 'Rekap_Operator_' . now()->format('Y-m-d_His') . '.xlsx';

        // Kirim scope ke Export agar file Excel juga terfilter per operator
        return Excel::download(
            new RekapitulasiExport($request->all(), $applyScope),
            $fileName
        );
    }
}

