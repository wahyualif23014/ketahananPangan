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
    public function index(Request $request)
    {
        set_time_limit(120);
        $fileName = 'Rekap_Lahan_' . now()->format('Y-m-d_His') . '.xlsx';

        $dataRekap = RekapitulasiLahan::filter($request->all())
            ->paginate(100)
            ->withQueryString();

        $polresList = DB::table('tingkat')
            ->select('id_tingkat', 'nama_tingkat')
            ->whereRaw('LENGTH(TRIM(id_tingkat)) = 5')
            ->get();

        $polsekList = [];
        if ($request->filled('polres')) {
            $polsekList = DB::table('tingkat')
                ->select('id_tingkat', 'nama_tingkat')
                ->where('id_tingkat', 'like', $request->polres . '.%')
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

        $polsekList = DB::table('tingkat')
            ->select('id_tingkat', 'nama_tingkat')
            ->where('id_tingkat', 'like', $request->polres . '.%')
            ->orderBy('nama_tingkat')
            ->get()
            ->map(fn($item) => ['value' => $item->id_tingkat, 'label' => $item->nama_tingkat]);

        return response()->json($polsekList);
    }

    public function export(Request $request)
    {
        $fileName = 'Rekap_Lahan_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RekapitulasiExport($request->all()), $fileName);
    }
}
