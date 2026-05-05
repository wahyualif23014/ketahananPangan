<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PotensiLahanController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $scope = $user->id_tugas ?? '0';

        // Jurisdictional scope: allows seeing parent Resor header too
        $applyScope = function ($query, $column = 'id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where($column, 'LIKE', $scope . '%');
            }
            return $query;
        };

        // Tingkat scope (for polresList / polsekList dropdowns):
        // allows parent Resor to appear for Polsek-level operators
        $applyTingkatScope = function ($query) use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function ($q) use ($scope) {
                    $q->where('id_tingkat', 'LIKE', $scope . '%')
                      ->orWhereRaw("? LIKE CONCAT(id_tingkat, '%')", [$scope]);
                });
            }
            return $query;
        };

        // ──────────────────────────────────────────
        // Capture filter parameters
        // ──────────────────────────────────────────
        $filters = [
            'search'     => $request->input('search', ''),
            'resor'      => $request->input('resor', ''),
            'sektor'     => $request->input('sektor', ''),
            'jenis'      => $request->input('jenis', ''),
            'validasi'   => $request->input('validasi', ''),
            'start_date' => $request->input('start_date', ''),
            'end_date'   => $request->input('end_date', ''),
        ];

        // ──────────────────────────────────────────
        // Wilayah lookup maps
        // ──────────────────────────────────────────
        $anggotaMap  = DB::table('anggota')->pluck('nama_anggota', 'id_anggota');
        $wilayahMap  = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');

        // ──────────────────────────────────────────
        // Build lahan query with all filters
        // ──────────────────────────────────────────
        $lahanQuery = DB::table('lahan')
            ->where('deletestatus', '!=', '0')
            ->orderBy('id_wilayah');

        // Jurisdictional scope (operator's wilayah)
        $lahanQuery = $applyScope($lahanQuery, 'id_tingkat');

        // Filter: Sektor takes priority over Resor
        if ($filters['sektor']) {
            $lahanQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $lahanQuery->where('id_tingkat', 'LIKE', $filters['resor'] . '%');
        }

        // Filter: Jenis Lahan
        if ($filters['jenis']) {
            $lahanQuery->where('id_jenis_lahan', $filters['jenis']);
        }

        // Filter: Status Validasi
        if ($filters['validasi'] === 'sudah') {
            $lahanQuery->whereNotNull('valid_oleh');
        } elseif ($filters['validasi'] === 'belum') {
            $lahanQuery->whereNull('valid_oleh');
        }

        // Filter: Periode Tanggal (berdasarkan tgl_edit)
        if ($filters['start_date']) {
            $lahanQuery->whereDate('datetransaction', '>=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $lahanQuery->whereDate('datetransaction', '<=', $filters['end_date']);
        }

        // Filter: Search
        if ($filters['search']) {
            $searchStr = $filters['search'];
            $lahanQuery->where(function ($q) use ($searchStr, $wilayahMap) {
                $q->where('id_lahan', $searchStr)
                  ->orWhere('alamat_lahan', 'like', "%{$searchStr}%")
                  ->orWhere('cp_polisi', 'like', "%{$searchStr}%")
                  ->orWhere('cp_lahan', 'like', "%{$searchStr}%")
                  ->orWhere('poktan', 'like', "%{$searchStr}%")
                  ->orWhere('id_wilayah', 'like', "%{$searchStr}%");

                foreach ($wilayahMap as $wId => $wNama) {
                    if (stripos($wNama, $searchStr) !== false) {
                        $q->orWhere('id_wilayah', 'like', "{$wId}%");
                    }
                }
            });
        }

        $lahanList = $lahanQuery->paginate(25)->withQueryString();

        // ──────────────────────────────────────────
        // Lookup maps for transform
        // ──────────────────────────────────────────
        $tingkatMap  = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $komoditiMap = DB::table('komoditi')->get()->keyBy('id_komoditi');

        $lahanList->getCollection()->transform(function ($lahan) use ($wilayahMap, $anggotaMap, $tingkatMap, $komoditiMap) {
            $parts = explode('.', $lahan->id_wilayah);

            $kabId    = count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $lahan->id_wilayah;
            $kabNama  = $wilayahMap[$kabId] ?? ('Wilayah ' . $kabId);

            $kecId    = count($parts) >= 3 ? $parts[0] . '.' . $parts[1] . '.' . $parts[2] : ($kabId . '.000');
            $kecNama  = $wilayahMap[$kecId] ?? ('Kec. ' . $kecId);

            $desaNama = $wilayahMap[$lahan->id_wilayah] ?? $lahan->id_wilayah;

            $editNama  = $lahan->edit_oleh  ? ($anggotaMap[$lahan->edit_oleh]  ?? $lahan->edit_oleh)  : null;
            $validNama = $lahan->valid_oleh ? ($anggotaMap[$lahan->valid_oleh] ?? $lahan->valid_oleh) : null;

            $idTingkat = $lahan->id_tingkat ?? '';
            $dotCount  = substr_count($idTingkat, '.');
            if ($dotCount >= 2) {
                $parts2   = explode('.', $idTingkat);
                $polresId = $parts2[0] . '.' . $parts2[1];
                $polsekId = $idTingkat;
            } else {
                $polresId = $idTingkat;
                $polsekId = null;
            }
            $namaPolres   = $tingkatMap[$polresId] ?? $polresId;
            $namaPolsek   = $polsekId ? ($tingkatMap[$polsekId] ?? $polsekId) : '-';

            $km           = $komoditiMap[$lahan->id_komoditi] ?? null;
            $namaKomoditi = $km ? ($km->jenis_komoditi . ' - ' . $km->nama_komoditi) : '-';

            return [
                'id_lahan'          => $lahan->id_lahan,
                'id_tingkat'        => $lahan->id_tingkat,
                'nama_polres'       => $namaPolres,
                'nama_polsek'       => $namaPolsek,
                'cp_lahan'          => $lahan->cp_lahan,
                'no_cp_lahan'       => $lahan->no_cp_lahan,
                'cp_polisi'         => $lahan->cp_polisi,
                'no_cp_polisi'      => $lahan->no_cp_polisi,
                'ket_polisi'        => $lahan->ket_polisi,
                'alamat_lahan'      => $lahan->alamat_lahan,
                'longitude'         => $lahan->longitude,
                'latitude'          => $lahan->latitude,
                'luas_lahan'        => $lahan->luas_lahan,
                'poktan'            => $lahan->poktan,
                'jml_petani'        => $lahan->jml_petani,
                'id_jenis_lahan'    => $lahan->id_jenis_lahan,
                'nama_komoditi'     => $namaKomoditi,
                'keterangan_lahan'  => $lahan->keterangan_lahan,
                'dokumentasi_lahan' => $lahan->dokumentasi_lahan,
                'status_lahan'      => $lahan->status_lahan,
                'edit_oleh'         => $editNama,
                'tgl_edit'          => $lahan->tgl_edit,
                'valid_oleh'        => $validNama,
                'tgl_valid'         => $lahan->tgl_valid,
                'kec_nama'          => $kecNama,
                'desa_nama'         => $desaNama,
                'kab_nama'          => $kabNama,
                'id_wilayah'        => $lahan->id_wilayah,
                'id_komoditi'       => $lahan->id_komoditi,
                'wilayah_label'     => 'Desa ' . $desaNama . ' Kecamatan ' . $kecNama . ' Kabupaten ' . $kabNama,
            ];
        });

        // ──────────────────────────────────────────
        // Dropdown lists (scoped to operator's jurisdiction)
        // ──────────────────────────────────────────
        $tingkatSemua = $applyTingkatScope(DB::table('tingkat'))->get();
        $polresList   = $tingkatSemua->filter(fn($t) => substr_count($t->id_tingkat, '.') == 1)->values();
        $polsekList   = $tingkatSemua->filter(fn($t) => substr_count($t->id_tingkat, '.') == 2)->values();

        $kategoriMapping = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',
            2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',
            4 => 'PESANTREN',
            5 => 'MILIK POLRI',
            6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',
            8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA',
        ];

        $komoditiList  = DB::table('komoditi')->where('deletestatus', '!=', '0')->get();

        $wilayahSemua  = DB::table('wilayah')->get();
        $kabupatenList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 1)->values();
        $kecamatanList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 2)->values();
        $desaList      = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 3)->values();

        $anggotaList   = DB::table('anggota')
            ->where('deletestatus', '!=', '0')
            ->select('id_anggota', 'nama_anggota', 'no_telp_anggota', 'id_tugas')
            ->get();

        // ──────────────────────────────────────────
        // Statistics — scoped to operator's jurisdiction
        // ──────────────────────────────────────────
        $allLahanScoped = $applyScope(DB::table('lahan')->where('deletestatus', '!=', '0'))->get();

        $totalLuasLahan    = 0;
        $luasBelumValidasi = 0;
        $countBelumValidasi = 0;
        $totalCount         = count($allLahanScoped);
        $unikLokasi         = [];

        $breakdownByJenis = [];
        foreach ($kategoriMapping as $k => $v) {
            $breakdownByJenis[$k] = ['nama' => $v, 'luas' => 0, 'lokasi' => []];
        }

        $distinctPolsek    = [];
        $distinctKabKota   = [];
        $distinctKecamatan = [];
        $distinctDesa      = [];

        foreach ($allLahanScoped as $lahan) {
            // Distribusi tingkatan dari id_tingkat
            $idT = (string) ($lahan->id_tingkat ?? '');
            if (mb_substr_count($idT, '.') >= 2) {
                $distinctPolsek[$idT] = true;
            }

            // Wilayah unik dari id_wilayah
            $idW   = (string) ($lahan->id_wilayah ?? '');
            $parts = explode('.', $idW);
            $dotsW = count($parts) - 1;
            if ($dotsW >= 1) { $distinctKabKota[$parts[0] . '.' . $parts[1]] = true; }
            if ($dotsW >= 2) { $distinctKecamatan[$parts[0] . '.' . $parts[1] . '.' . $parts[2]] = true; }
            if ($dotsW >= 3) { $distinctDesa[$idW] = true; }

            // Luas & breakdown hanya dari data tervalidasi (status_lahan == '1')
            if ($lahan->status_lahan == '1') {
                $luas = (float) $lahan->luas_lahan;
                $totalLuasLahan += $luas;
                $unikLokasi[$lahan->id_wilayah] = true;

                $jId = $lahan->id_jenis_lahan;
                if (isset($breakdownByJenis[$jId])) {
                    $breakdownByJenis[$jId]['luas'] += $luas;
                    $breakdownByJenis[$jId]['lokasi'][$lahan->id_wilayah] = true;
                }
            } else {
                $luasBelumValidasi  += (float) $lahan->luas_lahan;
                $countBelumValidasi++;
            }
        }

        $totalLokasiLahan   = count($unikLokasi);
        $persenBelumValidasi = $totalCount > 0 ? round(($countBelumValidasi / $totalCount) * 100, 2) : 0;

        $submissionByKategori = [
            'POLSEK'    => count($distinctPolsek),
            'KAB_KOTA'  => count($distinctKabKota),
            'KECAMATAN' => count($distinctKecamatan),
            'DESA'      => count($distinctDesa),
        ];

        return view('operator.kelola-lahan.operator_potensi.operator_kelola_index', compact(
            'lahanList',
            'filters',
            'polresList',
            'polsekList',
            'kategoriMapping',
            'komoditiList',
            'kabupatenList',
            'kecamatanList',
            'desaList',
            'anggotaList',
            'totalLuasLahan',
            'totalLokasiLahan',
            'breakdownByJenis',
            'submissionByKategori',
            'persenBelumValidasi',
            'luasBelumValidasi'
        ));
    }

    public function store(Request $request)
    {
        $data = [
            'id_tingkat'       => $request->id_sektor ?: $request->id_resor,
            'id_wilayah'       => $request->id_desa,
            'id_jenis_lahan'   => $request->id_jenis_lahan,
            'luas_lahan'       => $request->luas_lahan,
            'id_anggota'       => $request->id_anggota,
            'cp_lahan'         => $request->cp_lahan,
            'no_cp_lahan'      => $request->no_cp_lahan,
            'cp_polisi'        => $request->cp_polisi,
            'no_cp_polisi'     => $request->no_cp_polisi,
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'alamat_lahan'     => $request->alamat_lahan,
            'keterangan_lahan' => $request->ket_pj,
            'poktan'           => $request->jml_poktan,
            'jml_petani'       => $request->jml_petani,
            'id_komoditi'      => $request->id_komoditi,
            'ket_polisi'       => $request->keterangan_lain,
            'edit_oleh'        => auth()->user()->username ?? auth()->id(),
            'tgl_edit'         => Carbon::now(),
            'datetransaction'  => Carbon::now(),
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file     = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        $data['id_lahan'] = DB::table('lahan')->max('id_lahan') + 1;
        DB::table('lahan')->insert($data);

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $data = [
            'id_tingkat'       => $request->id_sektor ?: $request->id_resor,
            'id_wilayah'       => $request->id_desa,
            'id_jenis_lahan'   => $request->id_jenis_lahan,
            'luas_lahan'       => $request->luas_lahan,
            'id_anggota'       => $request->id_anggota,
            'cp_lahan'         => $request->cp_lahan,
            'no_cp_lahan'      => $request->no_cp_lahan,
            'cp_polisi'        => $request->cp_polisi,
            'no_cp_polisi'     => $request->no_cp_polisi,
            'latitude'         => $request->latitude,
            'longitude'        => $request->longitude,
            'alamat_lahan'     => $request->alamat_lahan,
            'keterangan_lahan' => $request->ket_pj,
            'poktan'           => $request->jml_poktan,
            'jml_petani'       => $request->jml_petani,
            'id_komoditi'      => $request->id_komoditi,
            'ket_polisi'       => $request->keterangan_lain,
            'edit_oleh'        => auth()->user()->username ?? auth()->id(),
            'tgl_edit'         => Carbon::now(),
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file     = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        DB::table('lahan')->where('id_lahan', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui']);
    }

    public function validasi($id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        DB::table('lahan')->where('id_lahan', $id)->update([
            'valid_oleh' => $user->username ?? auth()->id(),
            'tgl_valid'  => Carbon::now(),
            'status_lahan' => '1',
        ]);
        return back()->with('success', 'Data berhasil divalidasi');
    }

    public function unvalidasi($id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return back()->with('error', 'Akses ditolak. Pembatalan validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        DB::table('lahan')->where('id_lahan', $id)->update([
            'valid_oleh'   => null,
            'tgl_valid'    => null,
            'status_lahan' => '0',
        ]);
        return back()->with('success', 'Validasi data dibatalkan');
    }

    public function destroy($id)
    {
        DB::table('lahan')->where('id_lahan', $id)->update([
            'deletestatus' => '0',
        ]);
        return back()->with('success', 'Data berhasil dihapus');
    }
}