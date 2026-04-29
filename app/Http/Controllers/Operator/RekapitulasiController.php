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
        $dataRekap = RekapitulasiLahan::filter($request->all())->paginate(25)->withQueryString();

        $polresList = DB::table('tingkat')->whereRaw('LENGTH(id_tingkat) = 5')->get();
        $polsekList = [];
        if ($request->polres) {
            $polsekList = DB::table('tingkat')
                ->where('id_tingkat', 'like', $request->polres . '.%')
                ->get();
        }
        $desaList = [];
        if ($request->polsek) {
            $mapping = DB::table('tingkatwilayah')
                ->where('id_tingkat', $request->polsek)
                ->first();

            if ($mapping) {
                $desaList = DB::table('wilayah')
                    ->where('id_wilayah', 'like', $mapping->id_wilayah . '.%')
                    ->orderBy('nama_wilayah', 'ASC')
                    ->get();
            }
        }

        $jenisLahanList = DB::table('jenislahan')->get();
        $komoditiList = DB::table('komoditi')->get();

        return view('operator.rekapitulasi.operator_rekap', compact(
            'dataRekap',
            'polresList',
            'polsekList',
            'desaList',
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
