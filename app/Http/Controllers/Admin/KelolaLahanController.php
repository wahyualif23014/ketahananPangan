<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\Komoditi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelolaLahanController extends Controller
{
    private function getIndexData(Request $request)
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

        if ($filters['start'] || $filters['end']) {
            // Apply date filter dynamically based on the current active stage's date
            $dateCondition = "
                CASE 
                    WHEN t.id_tanam IS NULL THEN NULL
                    WHEN p.id_panen IS NULL THEN t.tgl_tanam
                    WHEN d.id_distribusi IS NULL THEN p.tgl_panen
                    ELSE d.tgl_distribusi
                END
            ";
            
            if ($filters['start']) {
                $dataQuery->whereRaw("$dateCondition >= ?", [$filters['start']]);
            }
            if ($filters['end']) {
                $dataQuery->whereRaw("$dateCondition <= ?", [$filters['end']]);
            }
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
            
            $paginator = $resorBaseQuery->orderBy('id_tingkat')->paginate(5)->withQueryString();
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

            $lahanIdsForHistory = $recordsCollection->pluck('id_lahan')->unique()->toArray();
            if (!empty($lahanIdsForHistory)) {
                $allTanams = DB::table('tanam')->whereIn('id_lahan', $lahanIdsForHistory)->orderBy('tgl_tanam', 'desc')->get();
                $allTanamIds = $allTanams->pluck('id_tanam')->unique()->toArray();
                
                $allPanens = empty($allTanamIds) ? collect() : DB::table('panen')->whereIn('id_tanam', $allTanamIds)->get()->groupBy('id_tanam');
                $allDistribusis = empty($allTanamIds) ? collect() : DB::table('distribusi')->whereIn('id_tanam', $allTanamIds)->get()->groupBy('id_tanam');

                $allTanams->transform(function($t) use ($allPanens, $allDistribusis) {
                    $t->panens = $allPanens->get($t->id_tanam, collect());
                    $t->distribusis = $allDistribusis->get($t->id_tanam, collect());
                    return $t;
                });

                $tanamByLahan = $allTanams->groupBy('id_lahan');

                $recordsCollection->transform(function($row) use ($tanamByLahan) {
                    $row->history_tanam = $tanamByLahan->get($row->id_lahan, collect());
                    return $row;
                });
            } else {
                $recordsCollection->transform(function($row) {
                    $row->history_tanam = collect();
                    return $row;
                });
            }

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

        // 5. Calculate Stats (Aggregated) based on completely filtered data
        $filteredLahanIds = (clone $dataQuery)->pluck('lahan.id_lahan')->unique()->toArray();

        if (empty($filteredLahanIds)) {
            $potensiTotal = 0;
            $potensiDetails = collect();
            $tanamTotal = 0;
            $tanamDetails = collect();
            $panenTotal = 0;
            $panenDetails = collect();
            $serapanTotal = 0;
            $serapanDetails = collect();
        } else {
            // Potensi Stats
            $statsData = DB::table('lahan')
                ->whereNotNull('valid_oleh')
                ->whereIn('id_lahan', $filteredLahanIds);
                
            $potensiTotal = (clone $statsData)->sum('luas_lahan');
            $potensiDetails = (clone $statsData)->selectRaw('id_jenis_lahan, SUM(luas_lahan) as total_luas, COUNT(id_lahan) as total_lokasi')
                ->whereNotNull('id_jenis_lahan')
                ->groupBy('id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');
            
            // Tanam Stats (Only latest cycle)
            $tanamQuery = DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->join(DB::raw('(SELECT MAX(id_tanam) as max_id FROM tanam GROUP BY id_lahan) as latest'), 'tanam.id_tanam', '=', 'latest.max_id')
                ->whereNotNull('tanam.valid_oleh')
                ->whereIn('tanam.id_lahan', $filteredLahanIds);
                
            $tanamTotal = (clone $tanamQuery)->sum('tanam.luas_tanam') ?? 0;
            $tanamDetails = (clone $tanamQuery)->selectRaw('lahan.id_jenis_lahan, SUM(tanam.luas_tanam) as total_luas, COUNT(tanam.id_lahan) as total_lokasi')
                ->whereNotNull('lahan.id_jenis_lahan')
                ->groupBy('lahan.id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');

            // Panen Stats (Only latest cycle)
            $panenQuery = DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->join(DB::raw('(SELECT MAX(id_panen) as max_id FROM panen GROUP BY id_lahan) as latest'), 'panen.id_panen', '=', 'latest.max_id')
                ->whereNotNull('panen.valid_oleh')
                ->whereIn('panen.id_lahan', $filteredLahanIds);
                
            $panenTotal = (clone $panenQuery)->sum('panen.luas_panen') ?? 0;
            $panenDetails = (clone $panenQuery)->selectRaw('lahan.id_jenis_lahan, SUM(panen.luas_panen) as total_luas, COUNT(panen.id_lahan) as total_lokasi')
                ->whereNotNull('lahan.id_jenis_lahan')
                ->groupBy('lahan.id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');

            // Serapan Stats (Only latest cycle)
            $serapanQuery = DB::table('distribusi')
                ->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
                ->join(DB::raw('(SELECT MAX(id_distribusi) as max_id FROM distribusi GROUP BY id_lahan) as latest'), 'distribusi.id_distribusi', '=', 'latest.max_id')
                ->whereNotNull('distribusi.valid_oleh')
                ->whereIn('distribusi.id_lahan', $filteredLahanIds);
                
            $serapanTotal = (clone $serapanQuery)->sum('distribusi.total_distribusi') ?? 0;
            $serapanDetails = (clone $serapanQuery)->selectRaw('distribusi.distribusi_ke, SUM(distribusi.total_distribusi) as total_luas, COUNT(distribusi.id_distribusi) as total_lokasi')
                ->whereNotNull('distribusi.distribusi_ke')
                ->groupBy('distribusi.distribusi_ke')
                ->get()->keyBy('distribusi_ke');
        }

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

        $distribusiList = [
            1 => 'BULOG',
            2 => 'PABRIK PAKAN',
            3 => 'TENGKULAK',
            4 => 'KONSUMSI SENDIRI'
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
            'jenis_lahan_list' => $jenisLahanList,
            'distribusi_list' => $distribusiList
        ];

        return compact(
            'polresList', 
            'polsekList', 
            'komoditiList', 
            'filters', 
            'stats',
            'data',
            'lahanStagesMap'
        );
    }

    public function index(Request $request)
    {
        return view('admin.kelola-lahan.lahan.index', $this->getIndexData($request));
    }

    public function indexOperator(Request $request)
    {
        return view('operator.kelola-lahan.operator_kelola.operator_kelola_index', $this->getIndexData($request));
    }

    public function indexView(Request $request)
    {
        return view('view.kelola-lahan.view_kelola', $this->getIndexData($request));
    }

    public function storeTanam(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required',
            'tgl_tanam' => 'required|date',
            'luas_tanam' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Check if total luas_tanam exceeds lahan luas_lahan
                $lahan = DB::table('lahan')->where('id_lahan', $request->id_lahan)->first();
                if ($lahan) {
                    $totalTanam = DB::table('tanam')->where('id_lahan', $request->id_lahan)->sum('luas_tanam');
                    if (($totalTanam + $request->luas_tanam) > $lahan->luas_lahan) {
                        throw new \Exception('Total luas tanam (' . ($totalTanam + $request->luas_tanam) . ' Ha) melebihi potensi lahan (' . $lahan->luas_lahan . ' Ha).');
                    }
                }

                $newId = DB::table('tanam')->max('id_tanam') + 1;
                $idAnggota = auth()->id();
                $lahanInfo = DB::table('lahan')->where('id_lahan', $request->id_lahan)->first();

                DB::table('tanam')->insert([
                    'id_tanam' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'tgl_tanam' => $request->tgl_tanam,
                    'luas_tanam' => $request->luas_tanam,
                    'nama_bibit' => $request->jenis_bibit, // mapped from frontend form
                    'kebutuhan_bibit' => $request->kebutuhan_bibit,
                    'est_awal_panen' => $request->est_awal_panen,
                    'est_akhir_panen' => $request->est_akhir_panen,
                    'keterangan_tanam' => $request->keterangan_tanam,
                    'datetransaction' => now(),
                ]);

                AktivitasLog::catat('create', 'tanam', [
                    'record_id'   => $newId,
                    'label_modul' => 'Lahan #' . $request->id_lahan . ($lahanInfo ? ' - ' . ($lahanInfo->alamat_lahan ?? '') : ''),
                    'data_baru'   => $request->only(['id_lahan','tgl_tanam','luas_tanam','jenis_bibit','kebutuhan_bibit','est_awal_panen','est_akhir_panen','keterangan_tanam']),
                    'keterangan'  => 'Tambah data tanam lahan #' . $request->id_lahan . ', luas ' . $request->luas_tanam . ' Ha, tanggal ' . $request->tgl_tanam,
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data Tanam berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('storeTanam error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function storePanen(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required',
            'tgl_panen' => 'required|date',
            'luas_panen' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('panen')->max('id_panen') + 1;
                $idAnggota = auth()->id();
                $idTanam = $request->id_tanam ?? DB::table('tanam')->where('id_lahan', $request->id_lahan)->orderByDesc('id_tanam')->value('id_tanam') ?? 0;

                // Jika status 2 (Gagal Panen), luas dan hasil diset 0
                $luasPanen = $request->status_panen == 2 ? 0 : $request->luas_panen;
                $totalPanen = $request->status_panen == 2 ? 0 : ($request->total_panen ?? 0);

                DB::table('panen')->insert([
                    'id_panen' => $newId,
                    'id_tanam' => $idTanam,
                    'id_lahan' => $request->id_lahan,
                    'id_anggota' => $idAnggota,
                    'tgl_panen' => $request->tgl_panen,
                    'luas_panen' => $luasPanen,
                    'total_panen' => $totalPanen,
                    'status_panen' => $request->status_panen,
                    'ket_panen' => $request->keterangan_panen, // mapped from frontend form
                    'datetransaction' => now(),
                ]);

                AktivitasLog::catat('create', 'panen', [
                    'record_id'   => $newId,
                    'label_modul' => 'Lahan #' . $request->id_lahan,
                    'data_baru'   => ['id_lahan'=>$request->id_lahan,'tgl_panen'=>$request->tgl_panen,'luas_panen'=>$luasPanen,'total_panen'=>$totalPanen,'status_panen'=>$request->status_panen],
                    'keterangan'  => 'Tambah data panen lahan #' . $request->id_lahan . ', luas ' . $luasPanen . ' Ha, hasil ' . $totalPanen . ' Ton',
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data Panen berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('storePanen error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function storeSerapan(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required',
            'tgl_distribusi' => 'required|date',
            'total_distribusi' => 'required|numeric',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('distribusi')->max('id_distribusi') + 1;
                $idAnggota = auth()->id();
                $idTanam = $request->id_tanam ?? DB::table('tanam')->where('id_lahan', $request->id_lahan)->orderByDesc('id_tanam')->value('id_tanam') ?? 0;
                $latestPanen = DB::table('panen')->where('id_lahan', $request->id_lahan)->orderByDesc('id_panen')->first();

                DB::table('distribusi')->insert([
                    'id_distribusi' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'id_panen' => $latestPanen ? $latestPanen->id_panen : 0,
                    'id_tanam' => $idTanam,
                    'id_anggota' => $idAnggota,
                    'tgl_distribusi' => $request->tgl_distribusi,
                    'total_distribusi' => $request->total_distribusi,
                    'distribusi_ke' => $request->distribusi_ke,
                    'keterangan_distribusi' => $request->keterangan_serapan, // mapped from frontend form
                    'datetransaction' => now(),
                ]);

                AktivitasLog::catat('create', 'serapan', [
                    'record_id'   => $newId,
                    'label_modul' => 'Lahan #' . $request->id_lahan,
                    'data_baru'   => ['id_lahan'=>$request->id_lahan,'tgl_distribusi'=>$request->tgl_distribusi,'total_distribusi'=>$request->total_distribusi,'distribusi_ke'=>$request->distribusi_ke],
                    'keterangan'  => 'Tambah data serapan lahan #' . $request->id_lahan . ', total ' . $request->total_distribusi . ' Ton, ke distribusi ke-' . $request->distribusi_ke,
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data Serapan berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('storeSerapan error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function updateTanam(Request $request, $id)
    {
        try {
            $old = DB::table('tanam')->where('id_tanam', $id)->first();
            DB::table('tanam')->where('id_tanam', $id)->update([
                'tgl_tanam' => $request->tgl_tanam,
                'luas_tanam' => $request->luas_tanam,
                'nama_bibit' => $request->jenis_bibit,
                'kebutuhan_bibit' => $request->kebutuhan_bibit,
                'est_awal_panen' => $request->est_awal_panen,
                'est_akhir_panen' => $request->est_akhir_panen,
                'keterangan_tanam' => $request->keterangan_tanam,
                'edit_oleh' => auth()->user()->username ?? 'admin',
                'tgl_edit' => now(),
            ]);
            AktivitasLog::catat('update', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'ID Tanam #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                'data_lama'   => $old ? (array)$old : null,
                'data_baru'   => $request->only(['tgl_tanam','luas_tanam','jenis_bibit','kebutuhan_bibit','est_awal_panen','est_akhir_panen','keterangan_tanam']),
                'keterangan'  => 'Edit data tanam #' . $id . ', luas jadi ' . $request->luas_tanam . ' Ha',
            ]);
            return response()->json(['success' => true, 'message' => 'Data Tanam berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function updatePanen(Request $request, $id)
    {
        try {
            $old = DB::table('panen')->where('id_panen', $id)->first();
            DB::table('panen')->where('id_panen', $id)->update([
                'tgl_panen' => $request->tgl_panen,
                'luas_panen' => $request->luas_panen,
                'total_panen' => $request->total_panen,
                'status_panen' => $request->status_panen,
                'ket_panen' => $request->keterangan_panen,
                'edit_oleh' => auth()->user()->username ?? 'admin',
                'tgl_edit' => now(),
            ]);
            AktivitasLog::catat('update', 'panen', [
                'record_id'   => $id,
                'label_modul' => 'ID Panen #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                'data_lama'   => $old ? (array)$old : null,
                'data_baru'   => $request->only(['tgl_panen','luas_panen','total_panen','status_panen','keterangan_panen']),
                'keterangan'  => 'Edit data panen #' . $id . ', luas jadi ' . $request->luas_panen . ' Ha',
            ]);
            return response()->json(['success' => true, 'message' => 'Data Panen berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function updateSerapan(Request $request, $id)
    {
        try {
            $old = DB::table('distribusi')->where('id_distribusi', $id)->first();
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'tgl_distribusi' => $request->tgl_distribusi,
                'total_distribusi' => $request->total_distribusi,
                'distribusi_ke' => $request->distribusi_ke,
                'keterangan_distribusi' => $request->keterangan_serapan,
                'edit_oleh' => auth()->user()->username ?? 'admin',
                'tgl_edit' => now(),
            ]);
            AktivitasLog::catat('update', 'serapan', [
                'record_id'   => $id,
                'label_modul' => 'ID Distribusi #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                'data_lama'   => $old ? (array)$old : null,
                'data_baru'   => $request->only(['tgl_distribusi','total_distribusi','distribusi_ke','keterangan_serapan']),
                'keterangan'  => 'Edit data serapan #' . $id . ', total jadi ' . $request->total_distribusi . ' Ton',
            ]);
            return response()->json(['success' => true, 'message' => 'Data serapan berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function destroyTanam($id)
    {
        $old = DB::table('tanam')->where('id_tanam', $id)->first();
        DB::table('tanam')->where('id_tanam', $id)->delete();
        AktivitasLog::catat('delete', 'tanam', [
            'record_id'   => $id,
            'label_modul' => 'ID Tanam #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
            'data_lama'   => $old ? (array)$old : null,
            'keterangan'  => 'Hapus data tanam #' . $id . ($old ? ', tgl tanam ' . $old->tgl_tanam : ''),
        ]);
        return response()->json(['success' => true, 'message' => 'Data tanam berhasil dihapus']);
    }

    public function destroyPanen($id)
    {
        $old = DB::table('panen')->where('id_panen', $id)->first();
        DB::table('panen')->where('id_panen', $id)->delete();
        AktivitasLog::catat('delete', 'panen', [
            'record_id'   => $id,
            'label_modul' => 'ID Panen #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
            'data_lama'   => $old ? (array)$old : null,
            'keterangan'  => 'Hapus data panen #' . $id . ($old ? ', tgl panen ' . $old->tgl_panen : ''),
        ]);
        return response()->json(['success' => true, 'message' => 'Data panen berhasil dihapus']);
    }

    public function destroySerapan($id)
    {
        $old = DB::table('distribusi')->where('id_distribusi', $id)->first();
        DB::table('distribusi')->where('id_distribusi', $id)->delete();
        AktivitasLog::catat('delete', 'serapan', [
            'record_id'   => $id,
            'label_modul' => 'ID Distribusi #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
            'data_lama'   => $old ? (array)$old : null,
            'keterangan'  => 'Hapus data serapan/distribusi #' . $id,
        ]);
        return response()->json(['success' => true, 'message' => 'Data serapan berhasil dihapus']);
    }

    public function validasiSerapan(Request $request, $id)
    {
        try {
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'admin',
                'tgl_valid' => now(),
            ]);
            return back()->with('success', 'Data Serapan berhasil divalidasi');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function getValidasiData($id)
    {
        $tanam = DB::table('tanam')->where('id_lahan', $id)->whereNull('valid_oleh')->get();
        $panen = DB::table('panen')->where('id_lahan', $id)->whereNull('valid_oleh')->get();
        $serapan = DB::table('distribusi')->where('id_lahan', $id)->whereNull('valid_oleh')->get();
        
        return response()->json([
            'tanam' => $tanam,
            'panen' => $panen,
            'serapan' => $serapan
        ]);
    }

    public function validasiLahan(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $username = auth()->user()->username ?? 'admin';
                $now = now();

                DB::table('tanam')->where('id_lahan', $id)->whereNull('valid_oleh')->update([
                    'valid_oleh' => $username,
                    'tgl_valid' => $now,
                ]);
                DB::table('panen')->where('id_lahan', $id)->whereNull('valid_oleh')->update([
                    'valid_oleh' => $username,
                    'tgl_valid' => $now,
                ]);
                DB::table('distribusi')->where('id_lahan', $id)->whereNull('valid_oleh')->update([
                    'valid_oleh' => $username,
                    'tgl_valid' => $now,
                ]);
            });
            return response()->json(['success' => true, 'message' => 'Semua data lahan berhasil divalidasi']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
        }
    }
}