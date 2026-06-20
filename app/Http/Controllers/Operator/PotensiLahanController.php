<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                return $query->where(function($q) use ($column, $scope) { $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%'); });
            }
            return $query;
        };

        // Tingkat scope (for polresList / polsekList dropdowns):
        // allows parent Resor to appear for Polsek-level operators
        $applyTingkatScope = function ($query) use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function ($q) use ($scope) {
                    $q->where(function($q) use ($scope) { $q->where('id_tingkat', $scope)->orWhere('id_tingkat', 'LIKE', $scope . '.%'); })
                      ->orWhereRaw("? = id_tingkat OR ? LIKE CONCAT(id_tingkat, '.%')", [$scope, $scope]);
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
            ->leftJoin('poktan', 'lahan.id_poktan', '=', 'poktan.id_poktan')
            ->select('lahan.*', 'poktan.nama_poktan')
            ->where('lahan.deletestatus', '!=', '0')
            ->orderBy('lahan.id_wilayah');

        // Jurisdictional scope (operator's wilayah)
        $lahanQuery = $applyScope($lahanQuery, 'id_tingkat');

        // Filter: Sektor takes priority over Resor
        if ($filters['sektor']) {
            $lahanQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $lahanQuery->where(function($q) use ($filters) { $q->where('id_tingkat', $filters['resor'])->orWhere('id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
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
                'id_poktan'         => $lahan->id_poktan,
                'poktan'            => $lahan->nama_poktan,
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
        $kabupatenList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 1);
        $kecamatanList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 2)->values();
        $desaList      = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 3)->values();

        if ($scope && $scope != '0') {
            $allowedWilayahIds = DB::table('tingkatwilayah')
                ->where('id_tingkat', 'LIKE', $scope . '%')
                ->pluck('id_wilayah')
                ->toArray();
                
            $allowedKabupatenIds = [];
            foreach ($allowedWilayahIds as $idW) {
                $parts = explode('.', $idW);
                if (count($parts) >= 2) {
                    $allowedKabupatenIds[] = $parts[0] . '.' . $parts[1];
                }
            }
            $allowedKabupatenIds = array_unique($allowedKabupatenIds);
            
            if (!empty($allowedKabupatenIds)) {
                $kabupatenList = $kabupatenList->filter(function($k) use ($allowedKabupatenIds) {
                    return in_array($k->id_wilayah, $allowedKabupatenIds);
                });
            }
        }
        $kabupatenList = $kabupatenList->values();

        $anggotaList   = DB::table('anggota')
            ->where('deletestatus', '!=', '0')
            ->select('id_anggota', 'nama_anggota', 'no_telp_anggota', 'id_tugas')
            ->get();

        $tingkatWilayahList = DB::table('tingkatwilayah')->get();

        $poktanQuery = \App\Models\Poktan::query()
            ->select('poktan.*')
            ->addSelect(['id_wilayah' => DB::table('lahan')
                ->whereColumn('lahan.id_poktan', 'poktan.id_poktan')
                ->where('deletestatus', '!=', '0')
                ->select('id_wilayah')
                ->limit(1)
            ]);

        if ($scope && $scope != '0') {
            $parts = explode('.', $scope);
            $levelCount = count($parts);
            if ($levelCount == 2) {
                $poktanQuery->where('id_polres', $scope);
            } elseif ($levelCount >= 3) {
                $polsekScope = implode('.', array_slice($parts, 0, 3));
                $poktanQuery->where('id_polsek', $polsekScope);
            } else {
                $poktanQuery->where('id_polda', $parts[0]);
            }
        }
        
        $tingkatMapPoktan = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $wilayahMapPoktan = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');
        $poktanList = $poktanQuery->get()->map(function($p) use ($tingkatMapPoktan, $wilayahMapPoktan) {
            $parts = [];
            if (!empty($p->id_wilayah) && isset($wilayahMapPoktan[$p->id_wilayah])) {
                $parts[] = 'Desa: ' . $wilayahMapPoktan[$p->id_wilayah];
            }
            if (!empty($p->id_polsek) && isset($tingkatMapPoktan[$p->id_polsek])) {
                $parts[] = 'Polsek: ' . $tingkatMapPoktan[$p->id_polsek];
            }
            if (!empty($p->id_polres) && isset($tingkatMapPoktan[$p->id_polres])) {
                $parts[] = 'Polres: ' . $tingkatMapPoktan[$p->id_polres];
            }
            
            $p->nama_tingkatan = !empty($parts) ? implode(' | ', $parts) : 'Umum';
            return $p;
        });

        // ──────────────────────────────────────────
        // Statistics — scoped to operator's jurisdiction
        // ──────────────────────────────────────────
        $allLahanData = $applyScope(DB::table('lahan')->where('deletestatus', '!=', '0'))->get();

        $totalLuasLahan    = 0;
        $luasBelumValidasi = 0;
        $countBelumValidasi = 0;
        $totalCount         = count($allLahanData);
        $unikLokasi         = [];

        $breakdownByJenis = [];
        foreach ($kategoriMapping as $k => $v) {
            $breakdownByJenis[$k] = ['nama' => $v, 'luas' => 0, 'lokasi' => []];
        }

        $distinctPolsek    = [];
        $distinctKabKota   = [];
        $distinctKecamatan = [];
        $distinctDesa      = [];

        foreach ($allLahanData as $lahan) {
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

        return view('admin.kelola-lahan.potensi.index', compact(
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
            'tingkatWilayahList',
            'totalLuasLahan',
            'totalLokasiLahan',
            'breakdownByJenis',
            'submissionByKategori',
            'persenBelumValidasi',
            'luasBelumValidasi',
            'allLahanData',
            'poktanList'
        ));
    }

    private function resolvePoktan(Request $request)
    {
        $idPoktan = $request->id_poktan;
        $namaPoktan = $request->jml_poktan ? strtoupper($request->jml_poktan) : null;
        
        if (empty($idPoktan) || $idPoktan === 'new') {
            if (!empty($namaPoktan)) {
                $idSektor = $request->id_sektor;
                $idResor = $request->id_resor;
                $idPolda = null;
                
                if ($idResor) {
                    $parts = explode('.', $idResor);
                    if (count($parts) > 0) $idPolda = $parts[0];
                }
                
                $namaDesa = $request->id_desa;
                
                $newPoktan = \App\Models\Poktan::create([
                    'id_polda' => $idPolda,
                    'id_polres' => $idResor,
                    'id_polsek' => $idSektor,
                    'nama_poktan' => $namaPoktan,
                    'luas_lahan' => $request->luas_lahan,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
                return $newPoktan->id_poktan;
            } else {
                return null;
            }
        }
        return $idPoktan;
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_sektor'        => 'nullable|string',
            'id_resor'         => 'nullable|string',
            'id_desa'          => 'required|string',
            'id_jenis_lahan'   => 'required|integer',
            'luas_lahan'       => 'required|numeric|min:0',
            'id_anggota'       => 'nullable|integer',
            'cp_lahan'         => 'nullable|string|max:255',
            'no_cp_lahan'      => 'nullable|string|max:50',
            'cp_polisi'        => 'nullable|string|max:255',
            'no_cp_polisi'     => 'nullable|string|max:50',
            'latitude'         => 'nullable|string|max:50',
            'longitude'        => 'nullable|string|max:50',
            'alamat_lahan'     => 'required|string|max:500',
            'ket_pj'           => 'nullable|string|max:1000',
            'jml_poktan'       => 'nullable|string|max:255',
            'jml_petani'       => 'nullable|integer|min:0',
            'id_komoditi'      => 'nullable|integer',
            'keterangan_lain'  => 'nullable|string|max:1000',
            'dokumentasi_lahan'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'id_poktan'        => 'nullable|string',
        ]);

        $idPoktan = $this->resolvePoktan($request);

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
            'poktan'           => 1,
            'id_poktan'        => $idPoktan,
            'jml_petani'       => $request->jml_petani,
            'id_komoditi'      => $request->id_komoditi,
            'ket_polisi'       => $request->keterangan_lain,
            'edit_oleh'        => auth()->user()->username ?? auth()->id(),
            'tgl_edit'         => Carbon::now(),
            'datetransaction'  => Carbon::now(),
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file     = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->hashName(); // Security: use hashName
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        $data['id_lahan'] = DB::table('lahan')->max('id_lahan') + 1;
        DB::table('lahan')->insert($data);

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_sektor'        => 'nullable|string',
            'id_resor'         => 'nullable|string',
            'id_desa'          => 'required|string',
            'id_jenis_lahan'   => 'required|integer',
            'luas_lahan'       => 'required|numeric|min:0',
            'id_anggota'       => 'nullable|integer',
            'cp_lahan'         => 'nullable|string|max:255',
            'no_cp_lahan'      => 'nullable|string|max:50',
            'cp_polisi'        => 'nullable|string|max:255',
            'no_cp_polisi'     => 'nullable|string|max:50',
            'latitude'         => 'nullable|string|max:50',
            'longitude'        => 'nullable|string|max:50',
            'alamat_lahan'     => 'required|string|max:500',
            'ket_pj'           => 'nullable|string|max:1000',
            'jml_poktan'       => 'nullable|string|max:255',
            'jml_petani'       => 'nullable|integer|min:0',
            'id_komoditi'      => 'nullable|integer',
            'keterangan_lain'  => 'nullable|string|max:1000',
            'dokumentasi_lahan'=> 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'id_poktan'        => 'nullable|string',
        ]);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();
        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        if ($user && substr_count((string)$user->id_tugas, '.') < 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya Polsek yang dapat mengubah data.'], 403);
        }

        if ($lahan->status_lahan != '2') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Data hanya dapat diubah jika berstatus ditolak.'], 403);
        }

        $idPoktan = $this->resolvePoktan($request);

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
            'poktan'           => 1,
            'id_poktan'        => $idPoktan,
            'jml_petani'       => $request->jml_petani,
            'id_komoditi'      => $request->id_komoditi,
            'ket_polisi'       => $request->keterangan_lain,
            'edit_oleh'        => auth()->user()->username ?? auth()->id(),
            'tgl_edit'         => Carbon::now(),
            'valid_oleh'       => null,
            'tgl_valid'        => null,
            'status_lahan'     => '0',
        ];

        if ($request->hasFile('dokumentasi_lahan')) {
            $file     = $request->file('dokumentasi_lahan');
            $filename = time() . '_' . $file->hashName(); // Security: use hashName
            $file->move(public_path('storage/dokumentasi'), $filename);
            $data['dokumentasi_lahan'] = 'storage/dokumentasi/' . $filename;
        }

        $wasDitolak = $lahan->status_lahan == '2';
        $oldIdPoktan = $lahan->id_poktan;

        DB::table('lahan')->where('id_lahan', $id)->update($data);

        // Bersihkan poktan yatim jika id_poktan berubah
        if ($oldIdPoktan && $oldIdPoktan != $idPoktan) {
            $stillUsed = DB::table('lahan')->where('id_poktan', $oldIdPoktan)->where('deletestatus', '!=', '0')->exists();
            if (!$stillUsed) {
                DB::table('poktan')->where('id_poktan', $oldIdPoktan)->delete();
            }
        }

        // Jika sebelumnya ditolak, kirim notifikasi ke Polres bahwa data sudah diperbaiki
        if ($wasDitolak) {
            $idTingkatLahan = $lahan->id_tingkat ?? '';
            // Cari Polres dari prefix id_tingkat (misal: "1.12.01" → polres "1.12")
            $parts = explode('.', $idTingkatLahan);
            $polresId = count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $idTingkatLahan;

            // Cari operator Polres (role = 'operator' dengan id_tugas = polresId)
            $polresOperator = DB::table('anggota')
                ->where('id_tugas', $polresId)
                ->where('role', 'operator')
                ->first();

            if ($polresOperator) {
                $namaPengirim = $user->nama_anggota ?? $user->username ?? 'Operator';
                $alamat       = $request->alamat_lahan ?? $lahan->alamat_lahan ?? 'Lahan #' . $id;

                Pesan::create([
                    'id_pesan'     => Str::uuid(),
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $polresOperator->id_anggota,
                    'judul'        => '🔄 Data Lahan Telah Diperbaiki - Mohon Validasi Ulang #' . $id,
                    'isi_pesan'    =>
                        "Data potensi lahan yang sebelumnya **DITOLAK** telah **DIPERBAIKI** oleh {$namaPengirim}.\n\n" .
                        "📍 **Lokasi Lahan:** {$alamat}\n" .
                        "🆔 **ID Lahan:** #{$id}\n\n" .
                        "Silakan tinjau kembali data tersebut dan lakukan validasi jika sudah sesuai.",
                    'is_read'      => false,
                ]);
            }

            AktivitasLog::catat('perbaiki_data', 'potensi_lahan', [
                'record_id'   => $id,
                'label_modul' => 'Lahan #' . $id,
                'keterangan'  => 'Perbaikan data potensi lahan #' . $id . ' setelah penolakan.',
            ]);
        }

        return response()->json(['success' => true, 'message' => $wasDitolak ? 'Data berhasil diperbaiki. Operator Polres telah diberitahu untuk validasi ulang.' : 'Data berhasil diperbarui']);
    }

    public function tolakValidasi(Request $request, $id)
    {
        $user  = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();

        // Scope check
        if (!$lahan || ($scope && $scope != '0' && (
            (string)$lahan->id_tingkat !== (string)$scope &&
            !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')
        ))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        // Hanya Polres yang bisa menolak
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Penolakan hanya dapat dilakukan oleh tingkat Polres.'], 403);
        }

        $request->validate([
            'alasan_penolakan' => 'required|string|max:1000',
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan harus diisi.',
        ]);

        $alasan  = $request->input('alasan_penolakan');
        $penolak = $user->nama_anggota ?? $user->username ?? 'Operator';

        // Update status lahan menjadi '2' (Ditolak)
        DB::table('lahan')->where('id_lahan', $id)->update([
            'status_lahan' => '2',
            'valid_oleh'   => null,
            'tgl_valid'    => null,
            'ket_polisi'   => '[DITOLAK] ' . $alasan,
            'tgl_edit'     => Carbon::now(),
        ]);

        // Kirim pesan otomatis ke pembuat laporan
        $pembuatId = $lahan->edit_oleh;
        if ($pembuatId) {
            $pembuat = DB::table('anggota')
                ->where('id_anggota', $pembuatId)
                ->orWhere('username', $pembuatId)
                ->first();

            if ($pembuat) {
                $alamat = $lahan->alamat_lahan ?? 'Lahan #' . $id;
                Pesan::create([
                    'id_pesan'     => Str::uuid(),
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $pembuat->id_anggota,
                    'judul'        => '❌ Penolakan Validasi Potensi Lahan #' . $id,
                    'isi_pesan'    =>
                        "Potensi lahan yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n" .
                        "📍 **Lokasi Lahan:** {$alamat}\n" .
                        "🆔 **ID Lahan:** #{$id}\n\n" .
                        "📝 **Alasan Penolakan:**\n{$alasan}\n\n" .
                        "Silakan perbaiki data dan ajukan kembali laporan potensi lahan Anda.",
                    'is_read'      => false,
                ]);
            }
        }

        AktivitasLog::catat('tolak_validasi', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id,
            'keterangan'  => 'Tolak validasi potensi lahan #' . $id . '. Alasan: ' . $alasan,
        ]);

        return response()->json(['success' => true, 'message' => 'Validasi lahan berhasil ditolak dan notifikasi telah dikirim.']);
    }

    public function validasi($id)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();

        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return back()->with('error', 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!');
        }

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
        if ($user->role !== 'admin') {
            return back()->with('error', 'Akses ditolak. Pembatalan validasi hanya dapat dilakukan oleh Admin.');
        }

        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();

        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return back()->with('error', 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!');
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
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return back()->with('error', 'Akses ditolak: Hanya Admin yang dapat menghapus data.');
        }

        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $id)->first();

        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return back()->with('error', 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!');
        }

        DB::transaction(function () use ($id, $lahan) {
            $tanamIds = DB::table('tanam')->where('id_lahan', $id)->pluck('id_tanam');
            if ($tanamIds->isNotEmpty()) {
                $panenIds = DB::table('panen')->whereIn('id_tanam', $tanamIds)->pluck('id_panen')->toArray();
                if (!empty($panenIds)) {
                    DB::table('distribusi')->whereIn('id_panen', $panenIds)->delete();
                }
                DB::table('panen')->whereIn('id_tanam', $tanamIds)->delete();
                DB::table('tanam')->where('id_lahan', $id)->delete();
            }
            DB::table('lahan')->where('id_lahan', $id)->delete();

            // Hapus poktan yatim jika tidak ada lahan lain yang menggunakannya
            if ($lahan && $lahan->id_poktan) {
                $stillUsed = DB::table('lahan')->where('id_poktan', $lahan->id_poktan)->where('deletestatus', '!=', '0')->exists();
                if (!$stillUsed) {
                    DB::table('poktan')->where('id_poktan', $lahan->id_poktan)->delete();
                }
            }
        });
        
        AktivitasLog::catat('delete', 'potensi_lahan', [
            'record_id'   => $id,
            'label_modul' => 'Lahan #' . $id,
            'keterangan'  => 'Hapus potensi lahan #' . $id . ' beserta siklus tanam terkait',
        ]);
        return back()->with('success', 'Data berhasil dihapus');
    }
}