<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PotensiLahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLahanController extends Controller {
    public function index(Request $request) {
        // ===========================
        // DATA STATISTIK (di blade)
        // ===========================
        $summary = [ 'total_ha' => '0' ];
        $cats    = [];

        // ===========================
        // DATA TABEL: DAFTAR LAHAN
        // Grouped per Kabupaten
        // ===========================

        // 1. Ambil semua anggota untuk lookup nama
        $anggotaMap = DB::table('anggota')->pluck('nama_anggota', 'id_anggota');

        // 3. Ambil semua wilayah untuk lookup nama kab/kec/desa
        $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');

        $search = $request->input('search', '');

        // 2. Ambil semua data lahan (aktif) dipaginasi dengan filter search
        $lahanQuery = DB::table('lahan')
            ->where('deletestatus', '!=', '0')
            ->orderBy('id_wilayah');

        if ($search) {
            $lahanQuery->where(function($q) use ($search, $wilayahMap) {
                $q->where('alamat_lahan', 'like', "%{$search}%")
                  ->orWhere('cp_polisi', 'like', "%{$search}%")
                  ->orWhere('cp_lahan', 'like', "%{$search}%")
                  ->orWhere('id_wilayah', 'like', "%{$search}%")
                  ->orWhereIn('id_wilayah', $wilayahMap->filter(fn($nama) =>
                      stripos($nama, $search) !== false
                  )->keys());
            });
        }

        $lahanList = $lahanQuery->paginate(25)->withQueryString();

        // Lookup nama tingkat dan komoditi
        $tingkatMap  = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $komoditiMap = DB::table('komoditi')->get()->keyBy('id_komoditi');

        // 4. Transform data untuk view
        $lahanList->getCollection()->transform(function ($lahan) use ($wilayahMap, $anggotaMap, $tingkatMap, $komoditiMap) {
            $parts = explode('.', $lahan->id_wilayah);

            // Resolve Kabupaten (format: XX.XX)
            $kabId   = count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $lahan->id_wilayah;
            $kabNama = $wilayahMap[$kabId] ?? ('Wilayah ' . $kabId);

            // Resolve Kecamatan (format: XX.XX.XXX)
            $kecId   = count($parts) >= 3 ? $parts[0] . '.' . $parts[1] . '.' . $parts[2] : ($kabId . '.000');
            $kecNama = $wilayahMap[$kecId] ?? ('Kec. ' . $kecId);

            // Resolve Desa (full id_wilayah)
            $desaNama = $wilayahMap[$lahan->id_wilayah] ?? $lahan->id_wilayah;

            // Resolve edit_oleh & valid_oleh
            $editNama  = $lahan->edit_oleh  ? ($anggotaMap[$lahan->edit_oleh]  ?? $lahan->edit_oleh)  : null;
            $validNama = $lahan->valid_oleh ? ($anggotaMap[$lahan->valid_oleh] ?? $lahan->valid_oleh) : null;

            // Resolve Polres & Polsek dari id_tingkat
            $idTingkat  = $lahan->id_tingkat ?? '';
            $dotCount   = substr_count($idTingkat, '.');
            if ($dotCount >= 2) {
                // Polsek: X.XX.XX → Polres: X.XX
                $parts2 = explode('.', $idTingkat);
                $polresId   = $parts2[0] . '.' . $parts2[1];
                $polsekId   = $idTingkat;
            } else {
                $polresId   = $idTingkat;
                $polsekId   = null;
            }
            $namaPolres = $tingkatMap[$polresId] ?? $polresId;
            $namaPolsek = $polsekId ? ($tingkatMap[$polsekId] ?? $polsekId) : '-';

            // Resolve Komoditi
            $km = $komoditiMap[$lahan->id_komoditi] ?? null;
            $namaKomoditi = $km ? ($km->jenis_komoditi . ' - ' . $km->nama_komoditi) : '-';

            return [
                'id_lahan'           => $lahan->id_lahan,
                'id_tingkat'         => $lahan->id_tingkat,
                'nama_polres'        => $namaPolres,
                'nama_polsek'        => $namaPolsek,
                'cp_lahan'           => $lahan->cp_lahan,
                'no_cp_lahan'        => $lahan->no_cp_lahan,
                'cp_polisi'          => $lahan->cp_polisi,
                'no_cp_polisi'       => $lahan->no_cp_polisi,
                'ket_polisi'         => $lahan->ket_polisi,
                'alamat_lahan'       => $lahan->alamat_lahan,
                'longitude'          => $lahan->longitude,
                'latitude'           => $lahan->latitude,
                'luas_lahan'         => $lahan->luas_lahan,
                'poktan'             => $lahan->poktan,
                'jml_petani'         => $lahan->jml_petani,
                'id_jenis_lahan'     => $lahan->id_jenis_lahan,
                'nama_komoditi'      => $namaKomoditi,
                'keterangan_lahan'   => $lahan->keterangan_lahan,
                'ket_polisi'         => $lahan->ket_polisi,
                'dokumentasi_lahan'  => $lahan->dokumentasi_lahan,
                'status_lahan'       => $lahan->status_lahan,
                'edit_oleh'          => $editNama,
                'tgl_edit'           => $lahan->tgl_edit,
                'valid_oleh'         => $validNama,
                'tgl_valid'          => $lahan->tgl_valid,
                'kec_nama'           => $kecNama,
                'desa_nama'          => $desaNama,
                'kab_nama'           => $kabNama,
                'wilayah_label'      => 'Desa ' . $desaNama . ' Kecamatan ' . $kecNama . ' Kabupaten ' . $kabNama,
            ];
        });

        $tingkatSemua = DB::table('tingkat')->where('id_tingkat', 'like', '11.%')->get();
        $polresList = $tingkatSemua->filter(function($t) { return substr_count($t->id_tingkat, '.') == 1; })->values();
        $polsekList = $tingkatSemua->filter(function($t) { return substr_count($t->id_tingkat, '.') == 2; })->values();

        $kategoriMapping = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',
            2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',
            4 => 'PESANTREN',
            5 => 'MILIK POLRI',
            6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',
            8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA'
        ];

        $komoditiList = DB::table('komoditi')->where('deletestatus', '!=', '0')->get();
        
        $wilayahSemua = DB::table('wilayah')->get();
        // Format Kabupaten: 35.XX (1 dot)
        $kabupatenList = $wilayahSemua->filter(function($w) { return substr_count($w->id_wilayah, '.') == 1; })->values();
        // Format Kecamatan: 35.XX.XX (2 dots)
        $kecamatanList = $wilayahSemua->filter(function($w) { return substr_count($w->id_wilayah, '.') == 2; })->values();
        // Format Desa: 35.XX.XX.XXXX (3 dots)
        $desaList = $wilayahSemua->filter(function($w) { return substr_count($w->id_wilayah, '.') == 3; })->values();

        $anggotaList = DB::table('anggota')
            ->where('deletestatus', '!=', '0')
            ->select('id_anggota', 'nama_anggota', 'no_telp_anggota')
            ->get();

        return view('admin.kelola-lahan.potensi.index', compact('summary', 'cats', 'lahanList', 'polresList', 'polsekList', 'kategoriMapping', 'komoditiList', 'kabupatenList', 'kecamatanList', 'desaList', 'anggotaList'));
    }

    public function store(Request $request) {
        $data = [
            'id_tingkat'        => $request->id_sektor ?? $request->id_resor,
            'id_wilayah'        => $request->id_desa,
            'id_jenis_lahan'    => $request->id_jenis_lahan,
            'luas_lahan'        => $request->luas_lahan,
            'id_anggota'        => $request->id_anggota,
            'cp_lahan'          => $request->cp_lahan,
            'no_cp_lahan'       => $request->no_cp_lahan,
            'cp_polisi'         => $request->cp_polisi,  // nama polisi penggerak
            'no_cp_polisi'      => $request->no_cp_polisi,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'alamat_lahan'      => $request->alamat_lahan,
            'keterangan_lahan'  => $request->ket_pj,
            'poktan'            => $request->jml_poktan,
            'jml_petani'        => $request->jml_petani,
            'id_komoditi'       => $request->id_komoditi,
            'ket_polisi'        => $request->keterangan_lain,
            'edit_oleh'         => auth()->user() ? auth()->user()->id : null,
            'tgl_edit'          => Carbon::now()
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Move file to public/storage/dokumentasi (or public/dokumentasi)
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        $data['id_lahan'] = DB::table('lahan')->max('id_lahan') + 1;

        DB::table('lahan')->insert($data);

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    }
}