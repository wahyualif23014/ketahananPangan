<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RekapitulasiLahan;
use Illuminate\Support\Facades\DB;

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
}
