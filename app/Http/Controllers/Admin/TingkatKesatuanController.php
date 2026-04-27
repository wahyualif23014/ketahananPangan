<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TingkatKesatuanController extends Controller
{
    public function index(Request $request) {
        $search = $request->input('search', '');

        // 1. Ambil data penanggung jawab (lookup map)
        $tingkatWilayah = DB::table('tingkatwilayah')
            ->join('anggota', 'tingkatwilayah.id_anggota', '=', 'anggota.id_anggota')
            ->select('tingkatwilayah.id_tingkat', 'anggota.nama_anggota', 'anggota.no_telp_anggota')
            ->get()
            ->keyBy('id_tingkat');

        // 2. Query Utama (untuk Roots: Polda level 1 atau Polres level 2)
        $query = DB::table('tingkat')
            ->where(function($q) {
                // Hanya ambil level 1 (X) atau level 2 (X.XX)
                $q->whereRaw("LENGTH(id_tingkat) - LENGTH(REPLACE(id_tingkat, '.', '')) <= 1");
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_tingkat', 'like', "%{$search}%")
                  ->orWhere('id_tingkat', 'like', "%{$search}%");
            });
        }

        // Paginasi Roots
        $kategoriList = $query->orderBy('id_tingkat')->paginate(6)->withQueryString();

        // 3. Ambil ALL data tingkat untuk lookup Polsek di bawahnya (tanpa filter page)
        $allTingkatFull = DB::table('tingkat')->get();

        return view('admin.data-utama.tingkat-kesatuan.index', compact('kategoriList', 'allTingkatFull', 'tingkatWilayah', 'search'));
    }
}
