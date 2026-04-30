<?php

namespace App\Http\Controllers\view;

use App\Http\Controllers\Controller;
use App\Models\Komoditi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelolaLahanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Filters Data (Dropdowns)
        $polresList = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        $polsekList = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        $komoditiList = Komoditi::orderBy('jenis_komoditi')
            ->orderBy('nama_komoditi')
            ->get(['id_komoditi', 'jenis_komoditi', 'nama_komoditi']);

        // 2. Capture Filter Parameters
        $filters = [
            'resor'     => $request->resor,
            'sektor'    => $request->sektor,
            'jenis'     => $request->jenis,
            'komoditi'  => $request->komoditi,
            'start'     => $request->start_date,
            'end'       => $request->end_date,
            'kategori'  => $request->kategori ?? 'semua',
            'search'    => $request->search
        ];

        // 3. Build Base Data Query (Applying Filters)
        $dataQuery = DB::table('lahan')
            ->leftJoin('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->leftJoin('anggota', 'lahan.id_anggota', '=', 'anggota.id_anggota')
            ->leftJoin('komoditi', 'lahan.id_komoditi', '=', 'komoditi.id_komoditi');

        // Apply Common Filters
        if ($filters['sektor']) {
            $dataQuery->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $dataQuery->where('lahan.id_tingkat', 'LIKE', $filters['resor'] . '%');
        }

        if ($filters['jenis']) {
            $dataQuery->where('lahan.id_jenis_lahan', $filters['jenis']);
        }

        if ($filters['komoditi']) {
            $dataQuery->where('lahan.id_komoditi', $filters['komoditi']);
        }        
        
        $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam GROUP BY id_lahan)) as t');
        $latestPanen = DB::raw('(SELECT * FROM panen WHERE id_panen IN (SELECT MAX(id_panen) FROM panen GROUP BY id_tanam)) as p');
        $latestDistribusi = DB::raw('(SELECT * FROM distribusi WHERE id_distribusi IN (SELECT MAX(id_distribusi) FROM distribusi GROUP BY id_tanam)) as d');

        $dataQuery = DB::table('lahan')
            ->leftJoin('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->leftJoin('anggota', 'lahan.id_anggota', '=', 'anggota.id_anggota')
            ->leftJoin('komoditi', 'lahan.id_komoditi', '=', 'komoditi.id_komoditi')
            ->leftJoin($latestTanam, 'lahan.id_lahan', '=', 't.id_lahan')
            ->leftJoin($latestPanen, 't.id_tanam', '=', 'p.id_tanam')
            ->leftJoin($latestDistribusi, 't.id_tanam', '=', 'd.id_tanam');

        // Category-Specific Joins & Date Filters
        if ($filters['kategori'] === 'panen') {
            $dateField = 'p.tgl_panen';
        } elseif ($filters['kategori'] === 'serapan') {
            $dateField = 'd.tgl_distribusi';
        } else {
            $dateField = 't.tgl_tanam';
        }

        if ($filters['kategori'] !== 'semua') {
            $targetStage = $filters['kategori'] === 'tanam' ? 0 : ($filters['kategori'] === 'panen' ? 1 : 2);
            $dataQuery->whereRaw("
                CASE 
                    WHEN t.id_tanam IS NULL THEN 0
                    WHEN p.id_panen IS NULL THEN 1
                    WHEN d.id_distribusi IS NULL THEN 2
                    ELSE 0
                END = ?
            ", [$targetStage]);
        }

        if ($filters['start']) {
            $dataQuery->where($dateField, '>=', $filters['start']);
        }
        if ($filters['end']) {
            $dataQuery->where($dateField, '<=', $filters['end']);
        }

        if ($filters['search']) {
            $searchStr = $filters['search'];
            $matchingWilayahIds = DB::table('wilayah')
                ->where('nama_wilayah', 'LIKE', '%' . $searchStr . '%')
                ->pluck('id_wilayah')
                ->toArray();

            $dataQuery->where(function($q) use ($searchStr, $matchingWilayahIds) {
                $q->where('wilayah.nama_wilayah', 'LIKE', '%' . $searchStr . '%')
                  ->orWhere('tingkat.nama_tingkat', 'LIKE', '%' . $searchStr . '%')
                  ->orWhere('lahan.alamat_lahan', 'LIKE', '%' . $searchStr . '%')
                  ->orWhere('lahan.cp_polisi', 'LIKE', '%' . $searchStr . '%')
                  ->orWhere('lahan.cp_lahan', 'LIKE', '%' . $searchStr . '%')
                  ->orWhere('lahan.poktan', 'LIKE', '%' . $searchStr . '%');

                foreach ($matchingWilayahIds as $wId) {
                    $q->orWhere('lahan.id_wilayah', 'LIKE', $wId . '%');
                }
            });
        }

        // 4. Hierarchical Pagination: Paginate Polres (Resor)
        $resorBaseQuery = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'");
        
        // If filters are active, limit Polres to those present in the filtered data
        $matchingResors = (clone $dataQuery)
            ->selectRaw("LEFT(lahan.id_tingkat, 5) as resor_id")
            ->distinct()
            ->pluck('resor_id')
            ->toArray();

        $lahanStagesMap = [];

        if (empty($matchingResors) && collect($filters)->filter()->isNotEmpty()) {
            $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        } else {
            if (!empty($matchingResors)) {
                $resorBaseQuery->whereIn('id_tingkat', $matchingResors);
            }
            
            $paginator = $resorBaseQuery->orderBy('id_tingkat')->paginate(5)->appends(request()->query());
            $resorIds = collect($paginator->items())->pluck('id_tingkat')->toArray();

            // Fetch Sektors (Polsek) for these Resors
            $allSektors = DB::table('tingkat')
                ->where(function($q) use ($resorIds) {
                    foreach($resorIds as $id) {
                        $q->orWhere('id_tingkat', 'LIKE', $id . '.%');
                    }
                })
                ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                ->get();

            // Fetch Individual Land Records for these Resors
            $allRecordsQuery = (clone $dataQuery)
                ->select(
                    'lahan.*',
                    'tingkat.nama_tingkat',
                    'wilayah.nama_wilayah',
                    'anggota.nama_anggota',
                    'komoditi.nama_komoditi',
                    'komoditi.jenis_komoditi',
                    't.id_tanam', 't.luas_tanam', 't.tgl_tanam', 't.est_awal_panen', 't.est_akhir_panen',
                    'p.id_panen', 'p.total_panen', 'p.tgl_panen', 'p.status_panen',
                    'd.id_distribusi', 'd.total_distribusi', 'd.tgl_distribusi', 'd.distribusi_ke', 'd.valid_oleh as serapan_valid_oleh'
                )
                ->where(function($q) use ($resorIds) {
                    foreach($resorIds as $id) {
                        $q->orWhere('lahan.id_tingkat', 'LIKE', $id . '%');
                    }
                });
            
            $recordsCollection = $allRecordsQuery->get();

            // Resolve Kecamatan for each record
            $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');
            $recordsCollection->transform(function($row) use ($wilayahMap) {
                $idW = $row->id_wilayah ?? '';
                $wParts = explode('.', $idW);
                $kecId = (count($wParts) >= 3) ? $wParts[0] . '.' . $wParts[1] . '.' . $wParts[2] : null;
                $row->nama_kecamatan = $kecId ? ($wilayahMap[$kecId] ?? $kecId) : '-';
                return $row;
            });

            // Build Hierarchy
            $groupedItems = collect($paginator->items())->map(function($resor) use ($allSektors, $recordsCollection) {
                $resor->sektors = $allSektors->filter(function($s) use ($resor) {
                    return str_starts_with($s->id_tingkat, $resor->id_tingkat . '.');
                })->map(function($sektor) use ($recordsCollection) {
                    $sektor->lahans = $recordsCollection->filter(function($l) use ($sektor) {
                        return $l->id_tingkat === $sektor->id_tingkat;
                    });
                    return $sektor;
                })->filter(function($sektor) {
                    return $sektor->lahans->isNotEmpty();
                });
                return $resor;
            })->filter(function($resor) {
                return $resor->sektors->isNotEmpty();
            });

            // Swap the items in the paginator with the grouped items
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $paginator->setCollection($groupedItems);
            $data = $paginator;

            // Build Lahan Stages based on latest cycles
            $lahanIds = $recordsCollection->pluck('id_lahan')->unique()->toArray();
            if (!empty($lahanIds)) {
                $latestTanams = DB::table('tanam')
                    ->select('id_lahan', DB::raw('MAX(id_tanam) as max_id_tanam'))
                    ->whereIn('id_lahan', $lahanIds)
                    ->groupBy('id_lahan')
                    ->get()
                    ->keyBy('id_lahan');

                $tanamIds = $latestTanams->pluck('max_id_tanam')->toArray();

                $panens = DB::table('panen')->whereIn('id_tanam', $tanamIds)->pluck('id_panen', 'id_tanam');
                $distribusis = DB::table('distribusi')->whereIn('id_tanam', $tanamIds)->pluck('id_distribusi', 'id_tanam');

                foreach($lahanIds as $idLahan) {
                    if (!isset($latestTanams[$idLahan])) {
                        $lahanStagesMap[$idLahan] = 0; // Tanam
                    } else {
                        $idTanam = $latestTanams[$idLahan]->max_id_tanam;
                        if (!isset($panens[$idTanam])) {
                            $lahanStagesMap[$idLahan] = 1; // Panen
                        } else {
                            if (!isset($distribusis[$idTanam])) {
                                $lahanStagesMap[$idLahan] = 2; // Serapan
                            } else {
                                $lahanStagesMap[$idLahan] = 0; // Selesai serapan -> Reset ke Tanam lagi!
                            }
                        }
                    }
                }
            }
        }

        // 5. Calculate Stats (Aggregated)
        $statsData = DB::table('lahan');
        if ($filters['sektor']) {
            $statsData->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $statsData->where('id_tingkat', 'LIKE', $filters['resor'] . '%');
        }

        $potensiTotal = (clone $statsData)->sum('luas_lahan');
        $potensiDetails = (clone $statsData)->selectRaw('id_jenis_lahan, SUM(luas_lahan) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');
        
        // Tanam Stats
        $tanamQuery = DB::table('view_tanam');
        if ($filters['sektor']) {
            $tanamQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $tanamQuery->where('id_tingkat', 'LIKE', $filters['resor'] . '%');
        }
        $tanamTotal = (clone $tanamQuery)->sum('luas_tanam') ?? 0;
        $tanamDetails = (clone $tanamQuery)->selectRaw('id_jenis_lahan, SUM(luas_tanam) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Panen Stats
        $panenQuery = DB::table('view_panen');
        if ($filters['sektor']) {
            $panenQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $panenQuery->where('id_tingkat', 'LIKE', $filters['resor'] . '%');
        }
        $panenTotal = (clone $panenQuery)->sum('luas_panen_ha') ?? 0;
        $panenDetails = (clone $panenQuery)->selectRaw('id_jenis_lahan, SUM(luas_panen_ha) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Serapan Stats
        $serapanQuery = DB::table('view_serapan');
        if ($filters['sektor']) {
            $serapanQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $serapanQuery->where('id_tingkat', 'LIKE', $filters['resor'] . '%');
        }
        $serapanTotal = (clone $serapanQuery)->sum('total_serapan_ton') ?? 0;
        $serapanDetails = (clone $serapanQuery)->selectRaw('id_jenis_lahan, SUM(total_serapan_ton) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        $jenisLahanList = [
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

        $stats = [
            'potensi' => number_format($potensiTotal, 2),
            'tanam'   => number_format($tanamTotal, 2),
            'panen'   => number_format($panenTotal, 2),
            'serapan' => number_format($serapanTotal, 2),
            'potensi_details' => $potensiDetails,
            'tanam_details' => $tanamDetails,
            'panen_details' => $panenDetails,
            'serapan_details' => $serapanDetails,
            'jenis_lahan_list' => $jenisLahanList
        ];

        // Menggunakan view view.kelola-lahan.view_kelola yang sudah dimodifikasi tanpa tombol edit
        return view('view.kelola-lahan.view_kelola', compact(
            'polresList', 
            'polsekList', 
            'komoditiList', 
            'filters', 
            'stats',
            'data',
            'lahanStagesMap'
        ));
    }

    public function potensiIndex(\Illuminate\Http\Request $request)
    {
        $anggotaMap = DB::table('anggota')->pluck('nama_anggota', 'id_anggota');
        $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');
        $search     = $request->input('search', '');

        $lahanQuery = DB::table('lahan')
            ->where('deletestatus', '!=', '0')
            ->orderBy('id_wilayah');

        if ($search) {
            $lahanQuery->where(function($q) use ($search, $wilayahMap) {
                $q->where('id_lahan', $search)
                  ->orWhere('alamat_lahan', 'like', "%{$search}%")
                  ->orWhere('cp_polisi', 'like', "%{$search}%")
                  ->orWhere('cp_lahan', 'like', "%{$search}%")
                  ->orWhere('poktan', 'like', "%{$search}%")
                  ->orWhere('id_wilayah', 'like', "%{$search}%");
                foreach ($wilayahMap as $wId => $wNama) {
                    if (stripos($wNama, $search) !== false) {
                        $q->orWhere('id_wilayah', 'like', "{$wId}%");
                    }
                }
            });
        }

        $lahanList = $lahanQuery->paginate(25)->appends(request()->query());

        $tingkatMap  = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $komoditiMap = DB::table('komoditi')->get()->keyBy('id_komoditi');

        $lahanList->getCollection()->transform(function ($lahan) use ($wilayahMap, $anggotaMap, $tingkatMap, $komoditiMap) {
            $parts    = explode('.', $lahan->id_wilayah);
            $kabId    = count($parts) >= 2 ? $parts[0].'.'.$parts[1] : $lahan->id_wilayah;
            $kecId    = count($parts) >= 3 ? $parts[0].'.'.$parts[1].'.'.$parts[2] : $kabId.'.000';
            $desaNama = $wilayahMap[$lahan->id_wilayah] ?? $lahan->id_wilayah;
            $kecNama  = $wilayahMap[$kecId]  ?? $kecId;
            $kabNama  = $wilayahMap[$kabId]  ?? $kabId;

            $idTingkat  = $lahan->id_tingkat ?? '';
            $parts2     = explode('.', $idTingkat);
            $polresId   = count($parts2) >= 2 ? $parts2[0].'.'.$parts2[1] : $idTingkat;
            $polsekId   = count($parts2) >= 3 ? $idTingkat : null;
            $namaPolres = $tingkatMap[$polresId] ?? $polresId;
            $namaPolsek = $polsekId ? ($tingkatMap[$polsekId] ?? $polsekId) : '-';

            $km           = $komoditiMap[$lahan->id_komoditi] ?? null;
            $namaKomoditi = $km ? ($km->jenis_komoditi.' - '.$km->nama_komoditi) : '-';

            return [
                'id_lahan'         => $lahan->id_lahan,
                'id_tingkat'       => $lahan->id_tingkat,
                'nama_polres'      => $namaPolres,
                'nama_polsek'      => $namaPolsek,
                'cp_lahan'         => $lahan->cp_lahan,
                'no_cp_lahan'      => $lahan->no_cp_lahan,
                'cp_polisi'        => $lahan->cp_polisi,
                'no_cp_polisi'     => $lahan->no_cp_polisi,
                'ket_polisi'       => $lahan->ket_polisi,
                'alamat_lahan'     => $lahan->alamat_lahan,
                'longitude'        => $lahan->longitude,
                'latitude'         => $lahan->latitude,
                'luas_lahan'       => $lahan->luas_lahan,
                'poktan'           => $lahan->poktan,
                'jml_petani'       => $lahan->jml_petani,
                'id_jenis_lahan'   => $lahan->id_jenis_lahan,
                'nama_komoditi'    => $namaKomoditi,
                'keterangan_lahan' => $lahan->keterangan_lahan,
                'dokumentasi_lahan'=> $lahan->dokumentasi_lahan,
                'status_lahan'     => $lahan->status_lahan,
                'edit_oleh'        => $lahan->edit_oleh  ? ($anggotaMap[$lahan->edit_oleh]  ?? $lahan->edit_oleh)  : null,
                'tgl_edit'         => $lahan->tgl_edit,
                'valid_oleh'       => $lahan->valid_oleh ? ($anggotaMap[$lahan->valid_oleh] ?? $lahan->valid_oleh) : null,
                'tgl_valid'        => $lahan->tgl_valid,
                'kec_nama'         => $kecNama,
                'desa_nama'        => $desaNama,
                'kab_nama'         => $kabNama,
                'id_wilayah'       => $lahan->id_wilayah,
                'id_komoditi'      => $lahan->id_komoditi,
                'wilayah_label'    => 'Desa '.$desaNama.' Kecamatan '.$kecNama.' Kabupaten '.$kabNama,
            ];
        });

        $tingkatSemua  = DB::table('tingkat')->where('id_tingkat', 'like', '11.%')->get();
        $polresList    = $tingkatSemua->filter(fn($t) => substr_count($t->id_tingkat, '.') == 1)->values();
        $polsekList    = $tingkatSemua->filter(fn($t) => substr_count($t->id_tingkat, '.') == 2)->values();
        $komoditiList  = DB::table('komoditi')->where('deletestatus', '!=', '0')->get();
        $wilayahSemua  = DB::table('wilayah')->get();
        $kabupatenList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 1)->values();
        $kecamatanList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 2)->values();
        $desaList      = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 3)->values();
        $anggotaList   = DB::table('anggota')->where('deletestatus', '!=', '0')->select('id_anggota', 'nama_anggota', 'no_telp_anggota')->get();

        $summary         = ['total_ha' => '0'];
        $cats            = [];
        $kategoriMapping = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',   2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',              4 => 'PESANTREN',
            5 => 'MILIK POLRI',                        6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',           8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA',
        ];

        // Menggunakan view khusus viewer yang read-only
        return view('view.kelola-lahan.potensi.index', compact(
            'summary', 'cats', 'lahanList', 'polresList', 'polsekList',
            'kategoriMapping', 'komoditiList', 'kabupatenList', 'kecamatanList',
            'desaList', 'anggotaList'
        ));
    }
}
