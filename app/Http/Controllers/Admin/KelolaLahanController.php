<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\Komoditi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelolaLahanController extends Controller
{
    private function getIndexData(Request $request, $mode = 'active')
    {
        $polresQuery = DB::table('tingkat')->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'");
        $polsekQuery = DB::table('tingkat')->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'");

        // 1. Fetch Filters Data (Dropdowns)
        $polresList = $polresQuery->orderBy('id_tingkat')->get();
        $polsekList = $polsekQuery->orderBy('id_tingkat')->get();

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
            ->where('lahan.deletestatus', '!=', '0')
            ->whereNotNull('lahan.valid_oleh')
            ->leftJoin('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->leftJoin('anggota', 'lahan.id_anggota', '=', 'anggota.id_anggota')
            ->leftJoin('komoditi', 'lahan.id_komoditi', '=', 'komoditi.id_komoditi');



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

        if ($mode === 'history') {
            $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam GROUP BY id_lahan)) as t');
        } else {
            $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam WHERE is_active = 1 GROUP BY id_lahan)) as t');
        }
        $latestPanen = DB::raw('(SELECT * FROM panen WHERE id_panen IN (SELECT MAX(id_panen) FROM panen GROUP BY id_tanam)) as p');
        $latestDistribusi = DB::raw('(SELECT * FROM distribusi WHERE id_distribusi IN (SELECT MAX(id_distribusi) FROM distribusi GROUP BY id_panen)) as d');

        $dataQuery->leftJoin($latestTanam, 'lahan.id_lahan', '=', 't.id_lahan')
            ->leftJoin($latestPanen, 't.id_tanam', '=', 'p.id_tanam')
            ->leftJoin($latestDistribusi, 'p.id_panen', '=', 'd.id_panen');

        // Category-Specific Joins & Date Filters
        if ($filters['kategori'] === 'tanam') {
            $dataQuery->whereNotNull('t.id_tanam');
            $dateField = 't.tgl_tanam';
        } elseif ($filters['kategori'] === 'panen') {
            $dataQuery->whereNotNull('p.id_panen');
            $dateField = 'p.tgl_panen';
        } elseif ($filters['kategori'] === 'serapan') {
            $dataQuery->whereNotNull('d.id_distribusi');
            $dateField = 'd.tgl_distribusi';
        } else {
            $dateField = DB::raw("COALESCE(d.tgl_distribusi, p.tgl_panen, t.tgl_tanam)");
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

            $dataQuery->where(function ($q) use ($searchStr, $matchingWilayahIds) {
                $q->where('wilayah.nama_wilayah', 'LIKE', '%' . $searchStr . '%')
                    ->orWhere('tingkat.nama_tingkat', 'LIKE', '%' . $searchStr . '%')
                    ->orWhere('lahan.id_lahan', $searchStr)
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
                ->where(function ($q) use ($resorIds) {
                    foreach ($resorIds as $id) {
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
                    't.id_tanam',
                    't.luas_tanam',
                    't.tgl_tanam',
                    't.est_awal_panen',
                    't.est_akhir_panen',
                    't.valid_oleh as tanam_valid_oleh',
                    'p.id_panen',
                    'p.total_panen',
                    'p.tgl_panen',
                    'p.status_panen',
                    'p.luas_panen',
                    'p.valid_oleh as panen_valid_oleh',
                    'd.id_distribusi',
                    'd.total_distribusi',
                    'd.tgl_distribusi',
                    'd.distribusi_ke',
                    'd.valid_oleh as serapan_valid_oleh',
                    't.alasan_tolak as tanam_alasan_tolak',
                    'p.alasan_tolak as panen_alasan_tolak',
                    'd.alasan_tolak as serapan_alasan_tolak'
                )
                ->where(function ($q) use ($resorIds) {
                    foreach ($resorIds as $id) {
                        $q->orWhere('lahan.id_tingkat', 'LIKE', $id . '%');
                    }
                });

            $recordsCollection = $allRecordsQuery->get();

            // Resolve Kecamatan for each record
            $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');
            $recordsCollection->transform(function ($row) use ($wilayahMap) {
                $idW = $row->id_wilayah ?? '';
                $wParts = explode('.', $idW);
                $kecId = (count($wParts) >= 3) ? $wParts[0] . '.' . $wParts[1] . '.' . $wParts[2] : null;
                $row->nama_kecamatan = $kecId ? ($wilayahMap[$kecId] ?? $kecId) : '-';
                return $row;
            });

            $lahanIdsForHistory = $recordsCollection->pluck('id_lahan')->unique()->toArray();
            if (!empty($lahanIdsForHistory)) {
                // Untuk mode active: hanya tampilkan siklus tanam yang masih aktif (is_active = 1)
                // Untuk mode history: tampilkan semua siklus termasuk yang sudah diarsipkan
                $tanamQuery = DB::table('tanam')->whereIn('id_lahan', $lahanIdsForHistory)->orderBy('tgl_tanam', 'desc');
                if ($mode === 'active') {
                    $tanamQuery->where('is_active', 1);
                }
                $allTanams = $tanamQuery->get();
                $allTanamIds = $allTanams->pluck('id_tanam')->unique()->toArray();

                $allPanens = empty($allTanamIds) ? collect() : DB::table('panen')->whereIn('id_tanam', $allTanamIds)->get()->groupBy('id_tanam');
                
                $allPanenIds = collect();
                foreach($allPanens as $panensForTanam) {
                    $allPanenIds = $allPanenIds->merge($panensForTanam->pluck('id_panen'));
                }
                $allPanenIds = $allPanenIds->unique()->toArray();
                
                $allDistribusis = empty($allPanenIds) ? collect() : DB::table('distribusi')->whereIn('id_panen', $allPanenIds)->get()->groupBy('id_panen');

                foreach($allPanens as $id_tanam => $panensForTanam) {
                    $panensForTanam->transform(function($p) use ($allDistribusis) {
                        $p->distribusis = $allDistribusis->get($p->id_panen, collect());
                        return $p;
                    });
                }

                $allTanams->transform(function ($t) use ($allPanens) {
                    $t->panens = $allPanens->get($t->id_tanam, collect());
                    return $t;
                });

                $tanamByLahan = $allTanams->groupBy('id_lahan');

                $recordsCollection->transform(function ($row) use ($tanamByLahan) {
                    $row->history_tanam = $tanamByLahan->get($row->id_lahan, collect());
                    return $row;
                });
            } else {
                $recordsCollection->transform(function ($row) {
                    $row->history_tanam = collect();
                    return $row;
                });
            }

            // Build Hierarchy
            $groupedItems = collect($paginator->items())->map(function ($resor) use ($allSektors, $recordsCollection) {
                $resor->sektors = $allSektors->filter(function ($s) use ($resor) {
                    return str_starts_with($s->id_tingkat, $resor->id_tingkat . '.');
                })->map(function ($sektor) use ($recordsCollection) {
                    $sektor->lahans = $recordsCollection->filter(function ($l) use ($sektor) {
                        return $l->id_tingkat === $sektor->id_tingkat;
                    });
                    return $sektor;
                })->filter(function ($sektor) {
                    return $sektor->lahans->isNotEmpty();
                });
                return $resor;
            })->filter(function ($resor) {
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
                    ->select('id_lahan', 'id_tanam', 'valid_oleh')
                    ->whereIn('id_lahan', $lahanIds)
                    ->where('is_active', 1)
                    ->get()
                    ->keyBy('id_lahan');

                $tanamIds = $latestTanams->pluck('id_tanam')->toArray();

                $panens = DB::table('panen')
                    ->select('id_tanam', 'id_panen', 'valid_oleh')
                    ->whereIn('id_tanam', $tanamIds)
                    ->get()
                    ->keyBy('id_tanam');

                $distribusis = DB::table('distribusi')
                    ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                    ->select('panen.id_tanam', 'distribusi.id_distribusi', 'distribusi.valid_oleh')
                    ->whereIn('panen.id_tanam', $tanamIds)
                    ->get()
                    ->keyBy('id_tanam');

                foreach ($lahanIds as $idLahan) {
                    if (!isset($latestTanams[$idLahan])) {
                        $lahanStagesMap[$idLahan] = 0; // Tanam
                    } else {
                        $t = $latestTanams[$idLahan];
                        if (!$t->valid_oleh) {
                            $lahanStagesMap[$idLahan] = 0; // Lock at Tanam until validated
                        } else if (!isset($panens[$t->id_tanam])) {
                            $lahanStagesMap[$idLahan] = 1; // Panen
                        } else {
                            $p = $panens[$t->id_tanam];
                            if (!$p->valid_oleh) {
                                $lahanStagesMap[$idLahan] = 1; // Lock at Panen until validated
                            } else if (!isset($distribusis[$t->id_tanam])) {
                                $lahanStagesMap[$idLahan] = 2; // Serapan
                            } else {
                                $s = $distribusis[$t->id_tanam];
                                if (!$s->valid_oleh) {
                                    $lahanStagesMap[$idLahan] = 2; // Lock at Serapan until validated
                                } else {
                                    $lahanStagesMap[$idLahan] = 3; // Fully validated
                                }
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
            $panenTonTotal = 0;
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

            // Tanam Stats (All validated cycles)
            $tanamQuery = DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->whereNotNull('tanam.valid_oleh')->where('tanam.valid_oleh', '!=', '')
                ->whereIn('tanam.id_lahan', $filteredLahanIds);
            if ($mode === 'active') {
                $tanamQuery->where('tanam.is_active', 1);
            }

            $tanamTotal = (clone $tanamQuery)->sum('tanam.luas_tanam') ?? 0;
            $tanamDetails = (clone $tanamQuery)->selectRaw('lahan.id_jenis_lahan, SUM(tanam.luas_tanam) as total_luas, COUNT(tanam.id_lahan) as total_lokasi')
                ->whereNotNull('lahan.id_jenis_lahan')
                ->groupBy('lahan.id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');

            // Panen Stats (All validated cycles)
            $panenQuery = DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')
                ->whereNotNull('panen.valid_oleh')->where('panen.valid_oleh', '!=', '')
                ->whereIn('panen.id_lahan', $filteredLahanIds);
            if ($mode === 'active') {
                $panenQuery->where('tanam.is_active', 1);
            }

            $panenTotal = (clone $panenQuery)->sum('panen.luas_panen') ?? 0;
            $panenTonTotal = (clone $panenQuery)->sum('panen.total_panen') ?? 0;
            $panenDetails = (clone $panenQuery)->selectRaw('lahan.id_jenis_lahan, SUM(panen.luas_panen) as total_luas, COUNT(panen.id_lahan) as total_lokasi')
                ->whereNotNull('lahan.id_jenis_lahan')
                ->groupBy('lahan.id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');

            // Serapan Stats (All validated cycles)
            $serapanQuery = DB::table('distribusi')
                ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                ->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->whereNotNull('distribusi.valid_oleh')->where('distribusi.valid_oleh', '!=', '')
                ->whereIn('panen.id_lahan', $filteredLahanIds);
            if ($mode === 'active') {
                $serapanQuery->where('tanam.is_active', 1);
            }

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
            'panen_ton' => number_format($panenTonTotal, 2),
            'serapan' => number_format($serapanTotal, 2),
            'potensi_details' => $potensiDetails,
            'tanam_details' => $tanamDetails,
            'panen_details' => $panenDetails,
            'serapan_details' => $serapanDetails,
            'jenis_lahan_list' => $jenisLahanList,
            'distribusi_list' => $distribusiList,
            'mode' => $mode
        ];

        // ── FILTER PANEN MENDATANG ──────────────────────────────────────
        $harvestFilters = [
            'panen_bulan' => $request->get('panen_bulan'),
            'panen_tahun' => $request->get('panen_tahun', date('Y')),
            'resor'       => $request->get('panen_resor'),
            'panen_start' => $request->get('panen_start'),
            'panen_end'   => $request->get('panen_end'),
        ];

        $harvestQuery = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->whereNotNull('tanam.est_awal_panen')
            ->where('tanam.deletestatus', '!=', '0')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereNotNull('lahan.valid_oleh');

        if ($harvestFilters['resor']) {
            $harvestQuery->where('lahan.id_tingkat', 'LIKE', $harvestFilters['resor'] . '%');
        }
        if ($harvestFilters['panen_start'] && $harvestFilters['panen_end']) {
            $harvestQuery->whereBetween('tanam.est_awal_panen', [$harvestFilters['panen_start'], $harvestFilters['panen_end']]);
        } elseif ($harvestFilters['panen_tahun']) {
            $harvestQuery->whereYear('tanam.est_awal_panen', $harvestFilters['panen_tahun']);
            if ($harvestFilters['panen_bulan']) {
                $harvestQuery->whereMonth('tanam.est_awal_panen', $harvestFilters['panen_bulan']);
            }
        }

        $upcomingHarvests = $harvestQuery
            ->select(
                'tanam.*',
                'lahan.id_lahan',
                'lahan.luas_lahan',
                'lahan.alamat_lahan',
                'lahan.poktan',
                'lahan.id_tingkat',
                'tingkat.nama_tingkat',
                'wilayah.nama_wilayah'
            )
            ->orderBy('tanam.est_awal_panen')
            ->limit(100)
            ->get();
            
        $polresForHarvest = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        return compact(
            'polresList',
            'polsekList',
            'komoditiList',
            'filters',
            'stats',
            'data',
            'lahanStagesMap',
            'upcomingHarvests',
            'harvestFilters',
            'polresForHarvest'
        );
    }

    public function index(Request $request)
    {
        return view('admin.kelola-lahan.lahan.index', $this->getIndexData($request, 'active'));
    }

    public function riwayatIndex(Request $request)
    {
        $baseData = $this->getIndexData($request, 'history');



        // ── SERAPAN DISTRIBUSI BREAKDOWN ────────────────────────────────
        $serapanBreakdown = DB::table('distribusi')
            ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->whereNotNull('distribusi.valid_oleh')
            ->where('distribusi.valid_oleh', '!=', '')
            ->where('distribusi.deletestatus', '!=', '0')
            ->where('lahan.deletestatus', '!=', '0');

        if (!empty($filters['resor'])) {
            $serapanBreakdown->where('lahan.id_tingkat', 'LIKE', $filters['resor'] . '%');
        }
        if (!empty($filters['sektor'])) {
            $serapanBreakdown->where('lahan.id_tingkat', 'LIKE', $filters['sektor'] . '%');
        }
        if (!empty($filters['jenis'])) {
            $serapanBreakdown->where('lahan.id_jenis_lahan', $filters['jenis']);
        }
        if (!empty($filters['komoditi'])) {
            $serapanBreakdown->where('lahan.id_komoditi', $filters['komoditi']);
        }
        if (!empty($filters['start'])) {
            $serapanBreakdown->where('distribusi.tgl_distribusi', '>=', $filters['start']);
        }
        if (!empty($filters['end'])) {
            $serapanBreakdown->where('distribusi.tgl_distribusi', '<=', $filters['end']);
        }

        $serapanBreakdown = $serapanBreakdown->selectRaw('distribusi_ke, SUM(total_distribusi) as total')
            ->groupBy('distribusi_ke')
            ->pluck('total', 'distribusi_ke');

        $serapanLabels = [1 => 'Bulog', 2 => 'Pabrik Pakan', 3 => 'Tengkulak', 4 => 'Konsumsi Sendiri'];
        $serapanChartData = [];
        foreach ($serapanLabels as $id => $label) {
            $serapanChartData[] = [
                'id'    => $id,
                'label' => $label,
                'total' => (float)($serapanBreakdown[$id] ?? 0),
            ];
        }

        return view('admin.kelola-lahan.riwayat.index', array_merge($baseData, [
            'serapanChartData'  => $serapanChartData,
        ]));
    }

    public function poktanIndex(Request $request)
    {
        $polresQuery = DB::table('tingkat')->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'");
        $polsekQuery = DB::table('tingkat')->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'");

        $polresList = $polresQuery->orderBy('id_tingkat')->get();
        $polsekList = $polsekQuery->orderBy('id_tingkat')->get();

        $filters = [
            'resor'  => $request->resor,
            'sektor' => $request->sektor,
            'search' => $request->search
        ];

        $query = DB::table('lahan')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereNotNull('lahan.id_poktan')
            ->whereNotNull('lahan.valid_oleh')
            ->join('poktan', 'lahan.id_poktan', '=', 'poktan.id_poktan')
            ->select(
                'lahan.id_lahan',
                'lahan.luas_lahan',
                'lahan.latitude',
                'lahan.longitude',
                'poktan.nama_poktan',
                'poktan.id_polda',
                'poktan.id_polres',
                'poktan.id_polsek',
                'lahan.id_wilayah as nama_desa'
            );

        if ($filters['sektor']) {
            $query->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $query->where('lahan.id_tingkat', 'LIKE', $filters['resor'] . '%');
        }

        if ($filters['search']) {
            $query->where('poktan.nama_poktan', 'LIKE', '%' . $filters['search'] . '%')
                  ->orWhere('lahan.id_wilayah', 'LIKE', '%' . $filters['search'] . '%');
        }

        $data = $query->orderBy('poktan.id_polsek')->orderBy('poktan.nama_poktan')->paginate(20)->withQueryString();

        $tingkatMap = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');

        return view('admin.kelola-lahan.poktan.index', compact('data', 'polresList', 'polsekList', 'filters', 'tingkatMap', 'wilayahMap'));
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
            'id_lahan' => 'required|integer',
            'tgl_tanam' => 'required|date',
            'luas_tanam' => 'required|numeric|min:0.01',
            'jenis_bibit' => 'nullable|string|max:255',
            'kebutuhan_bibit' => 'nullable|string|max:255',
            'est_awal_panen' => 'nullable|date',
            'est_akhir_panen' => 'nullable|date',
            'keterangan_tanam' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($request) {

                $newId = DB::table('tanam')->max('id_tanam') + 1;
                $idAnggota = auth()->id();
                $lahanInfo = DB::table('lahan')->where('id_lahan', $request->id_lahan)->first();

                // ── VALIDASI KAPASITAS LAHAN ──
                $luasLahan = (float)($lahanInfo->luas_lahan ?? 0);

                // Hitung total luas tanam aktif (yang belum di-validasi keseluruhan / is_active = 1)
                $luasTerpakai = (float)DB::table('tanam')
                    ->where('id_lahan', $request->id_lahan)
                    ->where('is_active', 1)
                    ->sum('luas_tanam');

                $luasBaru = (float)$request->luas_tanam;
                $sisaLahan = $luasLahan - $luasTerpakai;

                if ($luasBaru > $sisaLahan) {
                    throw new \Exception(
                        "Luas tanam melebihi kapasitas lahan! " .
                        "Kapasitas lahan: {$luasLahan} Ha | " .
                        "Sudah digunakan (Siklus Aktif): {$luasTerpakai} Ha | " .
                        "Sisa tersedia: " . number_format($sisaLahan, 2) . " Ha. " .
                        "Anda memasukkan {$luasBaru} Ha."
                    );
                }
                // ── AKHIR VALIDASI KAPASITAS ──

                DB::table('tanam')->insert([
                    'id_tanam' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'tgl_tanam' => $request->tgl_tanam,
                    'luas_tanam' => $request->luas_tanam,
                    'nama_bibit' => $request->jenis_bibit,
                    'kebutuhan_bibit' => $request->kebutuhan_bibit,
                    'est_awal_panen' => $request->est_awal_panen,
                    'est_akhir_panen' => $request->est_akhir_panen,
                    'keterangan_tanam' => $request->keterangan_tanam,
                    'id_anggota' => $idAnggota,
                    'datetransaction' => now(),
                ]);

                AktivitasLog::catat('create', 'tanam', [
                    'record_id'   => $newId,
                    'label_modul' => 'Lahan #' . $request->id_lahan . ($lahanInfo ? ' - ' . ($lahanInfo->alamat_lahan ?? '') : ''),
                    'data_baru'   => $request->only(['id_lahan', 'tgl_tanam', 'luas_tanam', 'jenis_bibit', 'kebutuhan_bibit', 'est_awal_panen', 'est_akhir_panen', 'keterangan_tanam']),
                    'keterangan'  => 'Tambah data tanam lahan #' . $request->id_lahan . ', luas ' . $request->luas_tanam . ' Ha, tanggal ' . $request->tgl_tanam,
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data Tanam berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('storeTanam error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function storePanen(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required|integer',
            'tgl_panen' => 'required|date',
            'luas_panen' => 'required|numeric|min:0',
            'total_panen' => 'nullable|numeric|min:0',
            'status_panen' => 'required|integer|in:0,1,2,3,4',
            'keterangan_panen' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('panen')->max('id_panen') + 1;
                $idAnggota = auth()->id();
                $idTanam = $request->id_tanam ?? DB::table('tanam')->where('id_lahan', $request->id_lahan)->orderByDesc('id_tanam')->value('id_tanam') ?? 0;

                // ── VALIDASI: Tanam harus sudah divalidasi admin ──
                if ($idTanam) {
                    $tanam = DB::table('tanam')->where('id_tanam', $idTanam)->first();
                    if (!$tanam || !$tanam->valid_oleh) {
                        throw new \Exception('Data tanam pada lahan ini belum divalidasi oleh admin. Harap tunggu validasi tanam terlebih dahulu sebelum menginput panen.');
                    }

                    // ── VALIDASI: Total luas panen tidak melebihi luas tanam ──
                    $luasTanam = (float)$tanam->luas_tanam;
                    $totalPanenSebelumnya = DB::table('panen')
                        ->where('id_tanam', $idTanam)
                        ->sum('luas_panen');
                    $luasPanenBaru = (float)$request->luas_panen;
                    $sisaPanen = $luasTanam - (float)$totalPanenSebelumnya;

                    if ($luasPanenBaru > $sisaPanen) {
                        throw new \Exception(
                            "Luas panen melebihi sisa luas tanam! " .
                            "Luas tanam: {$luasTanam} Ha | " .
                            "Sudah dipanen: " . number_format($totalPanenSebelumnya, 2) . " Ha | " .
                            "Sisa tersedia: " . number_format($sisaPanen, 2) . " Ha."
                        );
                    }
                }
                // ── AKHIR VALIDASI ──

                // Jika status 2 (Gagal Panen), luas dan hasil diset 0
                $luasPanen = $request->luas_panen;
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
                    'ket_panen' => $request->keterangan_panen,
                    'datetransaction' => now(),
                ]);

                AktivitasLog::catat('create', 'panen', [
                    'record_id'   => $newId,
                    'label_modul' => 'Lahan #' . $request->id_lahan,
                    'data_baru'   => ['id_lahan' => $request->id_lahan, 'tgl_panen' => $request->tgl_panen, 'luas_panen' => $luasPanen, 'total_panen' => $totalPanen, 'status_panen' => $request->status_panen],
                    'keterangan'  => 'Tambah data panen lahan #' . $request->id_lahan . ', luas ' . $luasPanen . ' Ha, hasil ' . $totalPanen . ' Ton',
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data Panen berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('storePanen error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function storeSerapan(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required|integer',
            'tgl_distribusi' => 'required|date',
            'total_distribusi' => 'required|numeric|min:0',
            'distribusi_ke' => 'required|integer',
            'keterangan_serapan' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('distribusi')->max('id_distribusi') + 1;
                $idAnggota = auth()->id();
                $idTanam = $request->id_tanam ?? DB::table('tanam')->where('id_lahan', $request->id_lahan)->orderByDesc('id_tanam')->value('id_tanam') ?? 0;
                $latestPanen = DB::table('panen')->where('id_lahan', $request->id_lahan)->orderByDesc('id_panen')->first();

                // ── VALIDASI: Panen harus sudah divalidasi admin ──
                if (!$latestPanen || !$latestPanen->valid_oleh) {
                    throw new \Exception('Data panen pada lahan ini belum divalidasi oleh admin. Harap tunggu validasi panen terlebih dahulu sebelum menginput serapan.');
                }
                
                $idPanen = $request->id_panen ?? ($latestPanen ? $latestPanen->id_panen : 0);
                $panenForSerapan = DB::table('panen')->where('id_panen', $idPanen)->first();
                if ($panenForSerapan) {
                    $totalSerapanSebelumnya = (float)DB::table('distribusi')->where('id_panen', $idPanen)->sum('total_distribusi');
                    $sisaTon = (float)$panenForSerapan->total_panen - $totalSerapanSebelumnya;
                    if ((float)$request->total_distribusi > $sisaTon) {
                        throw new \Exception("Jumlah serapan melebihi hasil panen! Hasil panen: " . number_format($panenForSerapan->total_panen, 2) . " TON. Sisa yang belum diserap: " . number_format($sisaTon, 2) . " TON.");
                    }
                }
                // ── AKHIR VALIDASI ──

                DB::table('distribusi')->insert([
                    'id_distribusi' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'id_panen' => $idPanen,
                    'id_tanam' => $idTanam,
                    'id_anggota' => $idAnggota,
                    'tgl_distribusi' => $request->tgl_distribusi,
                    'total_distribusi' => $request->total_distribusi,
                    'distribusi_ke' => $request->distribusi_ke,
                    'keterangan_distribusi' => $request->keterangan_serapan,
                    'datetransaction' => now(),
                ]);

                AktivitasLog::catat('create', 'serapan', [
                    'record_id'   => $newId,
                    'label_modul' => 'Lahan #' . $request->id_lahan,
                    'data_baru'   => ['id_lahan' => $request->id_lahan, 'tgl_distribusi' => $request->tgl_distribusi, 'total_distribusi' => $request->total_distribusi, 'distribusi_ke' => $request->distribusi_ke],
                    'keterangan'  => 'Tambah data serapan lahan #' . $request->id_lahan . ', total ' . $request->total_distribusi . ' Ton, ke distribusi ke-' . $request->distribusi_ke,
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data Serapan berhasil disimpan']);
        } catch (\Exception $e) {
            Log::error('storeSerapan error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function updateTanam(Request $request, $id)
    {
        $request->validate([
            'tgl_tanam' => 'required|date',
            'luas_tanam' => 'required|numeric|min:0',
            'jenis_bibit' => 'nullable|string|max:255',
            'kebutuhan_bibit' => 'nullable|string|max:255',
            'est_awal_panen' => 'nullable|date',
            'est_akhir_panen' => 'nullable|date',
            'keterangan_tanam' => 'nullable|string|max:1000',
        ]);

        try {
            $old = DB::table('tanam')->where('id_tanam', $id)->first();
            if (!$old) throw new \Exception("Data tanam tidak ditemukan");
            
            $idLahan = $old->id_lahan;
            $lahanInfo = DB::table('lahan')->where('id_lahan', $idLahan)->first();
            
            // ── VALIDASI KAPASITAS LAHAN ──
            $luasLahan = (float)($lahanInfo->luas_lahan ?? 0);
            $luasTerpakai = (float)DB::table('tanam')
                ->where('id_lahan', $idLahan)
                ->where('is_active', 1)
                ->where('id_tanam', '!=', $id)
                ->sum('luas_tanam');

            $luasBaru = (float)$request->luas_tanam;
            $sisaLahan = $luasLahan - $luasTerpakai;

            if ($luasBaru > $sisaLahan) {
                throw new \Exception(
                    "Luas tanam melebihi kapasitas lahan! " .
                    "Kapasitas lahan: {$luasLahan} Ha | " .
                    "Sudah digunakan: {$luasTerpakai} Ha | " .
                    "Sisa tersedia: " . number_format($sisaLahan, 2) . " Ha. " .
                    "Anda memasukkan {$luasBaru} Ha."
                );
            }
            // ── AKHIR VALIDASI KAPASITAS ──

            DB::table('tanam')->where('id_tanam', $id)->update([
                'tgl_tanam' => $request->tgl_tanam,
                'luas_tanam' => $request->luas_tanam,
                'nama_bibit' => $request->jenis_bibit,
                'kebutuhan_bibit' => $request->kebutuhan_bibit,
                'est_awal_panen' => $request->est_awal_panen,
                'est_akhir_panen' => $request->est_akhir_panen,
                'keterangan_tanam' => preg_replace('/^\[DITOLAK\].*?(?:\n|$)/s', '', $request->keterangan_tanam),
                'alasan_tolak' => null,
                'edit_oleh' => auth()->user()->username ?? 'admin',
                'tgl_edit' => now(),
                'valid_oleh' => null,
                'tgl_valid' => null,
            ]);
            AktivitasLog::catat('update', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'ID Tanam #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                'data_lama'   => $old ? (array)$old : null,
                'data_baru'   => $request->only(['tgl_tanam', 'luas_tanam', 'jenis_bibit', 'kebutuhan_bibit', 'est_awal_panen', 'est_akhir_panen', 'keterangan_tanam']),
                'keterangan'  => 'Edit data tanam #' . $id . ', luas jadi ' . $request->luas_tanam . ' Ha',
            ]);
            return response()->json(['success' => true, 'message' => 'Data Tanam berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function updatePanen(Request $request, $id)
    {
        $request->validate([
            'tgl_panen' => 'required|date',
            'luas_panen' => 'required|numeric|min:0',
            'total_panen' => 'nullable|numeric|min:0',
            'status_panen' => 'required|integer|in:0,1,2,3,4',
            'keterangan_panen' => 'nullable|string|max:1000',
        ]);

        try {
            $old = DB::table('panen')->where('id_panen', $id)->first();
            if (!$old) throw new \Exception("Data panen tidak ditemukan");

            $idTanam = $old->id_tanam;
            $tanam = DB::table('tanam')->where('id_tanam', $idTanam)->first();
            
            // ── VALIDASI KAPASITAS PANEN ──
            if ($tanam) {
                $luasTanam = (float)$tanam->luas_tanam;
                $totalPanenSebelumnya = DB::table('panen')
                    ->where('id_tanam', $idTanam)
                    ->where('id_panen', '!=', $id)
                    ->sum('luas_panen');
                $luasPanenBaru = (float)$request->luas_panen;
                $sisaPanen = $luasTanam - (float)$totalPanenSebelumnya;

                if ($luasPanenBaru > $sisaPanen) {
                    throw new \Exception(
                        "Luas panen melebihi sisa luas tanam! " .
                        "Luas tanam: {$luasTanam} Ha | " .
                        "Sudah dipanen: " . number_format($totalPanenSebelumnya, 2) . " Ha | " .
                        "Sisa tersedia: " . number_format($sisaPanen, 2) . " Ha."
                    );
                }
            }
            // ── AKHIR VALIDASI KAPASITAS ──

            // Jika status 2 (Gagal Panen), luas dan hasil diset 0
            $luasPanenFix = $request->luas_panen;
            $totalPanenFix = $request->status_panen == 2 ? 0 : ($request->total_panen ?? 0);

            DB::table('panen')->where('id_panen', $id)->update([
                'tgl_panen' => $request->tgl_panen,
                'luas_panen' => $luasPanenFix,
                'total_panen' => $totalPanenFix,
                'status_panen' => $request->status_panen,
                'ket_panen' => preg_replace('/^\[DITOLAK\].*?(?:\n|$)/s', '', $request->keterangan_panen),
                'alasan_tolak' => null,
                'edit_oleh' => auth()->user()->username ?? 'admin',
                'tgl_edit' => now(),
                'valid_oleh' => null,
                'tgl_valid' => null,
            ]);
            AktivitasLog::catat('update', 'panen', [
                'record_id'   => $id,
                'label_modul' => 'ID Panen #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                'data_lama'   => $old ? (array)$old : null,
                'data_baru'   => $request->only(['tgl_panen', 'luas_panen', 'total_panen', 'status_panen', 'keterangan_panen']),
                'keterangan'  => 'Edit data panen #' . $id . ', luas jadi ' . $request->luas_panen . ' Ha',
            ]);
            return response()->json(['success' => true, 'message' => 'Data Panen berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function updateSerapan(Request $request, $id)
    {
        $request->validate([
            'tgl_distribusi' => 'required|date',
            'total_distribusi' => 'required|numeric|min:0',
            'distribusi_ke' => 'required|integer',
            'keterangan_serapan' => 'nullable|string|max:1000',
        ]);

        try {
            $old = DB::table('distribusi')->where('id_distribusi', $id)->first();
            if ($old && $old->id_panen) {
                $panenForSerapan = DB::table('panen')->where('id_panen', $old->id_panen)->first();
                if ($panenForSerapan) {
                    $totalSerapanSebelumnya = (float)DB::table('distribusi')
                        ->where('id_panen', $old->id_panen)
                        ->where('id_distribusi', '!=', $id)
                        ->sum('total_distribusi');
                    $sisaTon = (float)$panenForSerapan->total_panen - $totalSerapanSebelumnya;
                    if ((float)$request->total_distribusi > $sisaTon) {
                        throw new \Exception("Jumlah serapan melebihi hasil panen! Hasil panen: " . number_format($panenForSerapan->total_panen, 2) . " TON. Sisa yang belum diserap: " . number_format($sisaTon, 2) . " TON.");
                    }
                }
            }

            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'tgl_distribusi' => $request->tgl_distribusi,
                'total_distribusi' => $request->total_distribusi,
                'distribusi_ke' => $request->distribusi_ke,
                'keterangan_distribusi' => preg_replace('/^\[DITOLAK\].*?(?:\n|$)/s', '', $request->keterangan_serapan),
                'alasan_tolak' => null,
                'edit_oleh' => auth()->user()->username ?? 'admin',
                'tgl_edit' => now(),
                'valid_oleh' => null,
                'tgl_valid' => null,
            ]);
            AktivitasLog::catat('update', 'serapan', [
                'record_id'   => $id,
                'label_modul' => 'ID Distribusi #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                'data_lama'   => $old ? (array)$old : null,
                'data_baru'   => $request->only(['tgl_distribusi', 'total_distribusi', 'distribusi_ke', 'keterangan_serapan']),
                'keterangan'  => 'Edit data serapan #' . $id . ', total jadi ' . $request->total_distribusi . ' Ton',
            ]);
            return response()->json(['success' => true, 'message' => 'Data serapan berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function destroyTanam($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $old = DB::table('tanam')->where('id_tanam', $id)->first();
                if (!$old) {
                    throw new \Exception('Data tanam tidak ditemukan.');
                }

                // Cascade: hapus distribusi terkait panen-panen dari tanam ini
                $panenIds = DB::table('panen')->where('id_tanam', $id)->pluck('id_panen')->toArray();
                if (!empty($panenIds)) {
                    DB::table('distribusi')->whereIn('id_panen', $panenIds)->delete();
                }

                // Cascade: hapus panen terkait tanam ini
                DB::table('panen')->where('id_tanam', $id)->delete();

                // Hapus tanam
                DB::table('tanam')->where('id_tanam', $id)->delete();

                AktivitasLog::catat('delete', 'tanam', [
                    'record_id'   => $id,
                    'label_modul' => 'ID Tanam #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                    'data_lama'   => $old ? (array)$old : null,
                    'keterangan'  => 'Hapus data tanam #' . $id . ($old ? ', beserta panen & serapan terkait' : ''),
                ]);
            });

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Data tanam beserta panen & serapan terkait berhasil dihapus']);
            }
            return back()->with('success', 'Data tanam beserta panen & serapan terkait berhasil dihapus');
        } catch (\Exception $e) {
            \Log::error('destroyTanam error: ' . $e->getMessage());
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function destroyPanen($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $old = DB::table('panen')->where('id_panen', $id)->first();
                if (!$old) {
                    throw new \Exception('Data panen tidak ditemukan.');
                }

                // Cascade: hapus distribusi terkait panen ini
                DB::table('distribusi')->where('id_panen', $id)->delete();

                // Hapus panen
                DB::table('panen')->where('id_panen', $id)->delete();

                AktivitasLog::catat('delete', 'panen', [
                    'record_id'   => $id,
                    'label_modul' => 'ID Panen #' . $id . ($old ? ' - Lahan #' . $old->id_lahan : ''),
                    'data_lama'   => $old ? (array)$old : null,
                    'keterangan'  => 'Hapus data panen #' . $id . ($old ? ', beserta serapan terkait' : ''),
                ]);
            });

            return response()->json(['success' => true, 'message' => 'Data panen beserta serapan terkait berhasil dihapus']);
        } catch (\Exception $e) {
            \Log::error('destroyPanen error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
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

    public function unvalidasiTanam($id)
    {
        DB::table('tanam')->where('id_tanam', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        AktivitasLog::catat('unvalidasi', 'tanam', [
            'record_id'   => $id,
            'label_modul' => 'Tanam #' . $id,
            'keterangan'  => 'Membatalkan validasi data tanam #' . $id,
        ]);
        return back()->with('success', 'Data Tanam berhasil di-unvalidasi');
    }

    public function unvalidasiPanen($id)
    {
        DB::table('panen')->where('id_panen', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        AktivitasLog::catat('unvalidasi', 'panen', [
            'record_id'   => $id,
            'label_modul' => 'Panen #' . $id,
            'keterangan'  => 'Membatalkan validasi data panen #' . $id,
        ]);
        return back()->with('success', 'Data Panen berhasil di-unvalidasi');
    }

    public function unvalidasiSerapan($id)
    {
        DB::table('distribusi')->where('id_distribusi', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        AktivitasLog::catat('unvalidasi', 'serapan', [
            'record_id'   => $id,
            'label_modul' => 'Serapan #' . $id,
            'keterangan'  => 'Membatalkan validasi data serapan #' . $id,
        ]);
        return back()->with('success', 'Data Serapan berhasil di-unvalidasi');
    }

    public function validasiTanam(Request $request, $id)
    {
        try {
            DB::table('tanam')->where('id_tanam', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'admin',
                'tgl_valid' => now(),
            ]);
            AktivitasLog::catat('validasi', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'Tanam #' . $id,
                'keterangan'  => 'Validasi data tanam #' . $id,
            ]);
            if ($request->wantsJson() && !$request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Tanam berhasil divalidasi']);
            }
            return back()->with('success', 'Data Tanam berhasil divalidasi');
        } catch (\Exception $e) {
            if ($request->wantsJson() && !$request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function validasiPanen(Request $request, $id)
    {
        try {
            DB::table('panen')->where('id_panen', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'admin',
                'tgl_valid' => now(),
            ]);
            AktivitasLog::catat('validasi', 'panen', [
                'record_id'   => $id,
                'label_modul' => 'Panen #' . $id,
                'keterangan'  => 'Validasi data panen #' . $id,
            ]);
            if ($request->wantsJson() && !$request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Panen berhasil divalidasi']);
            }
            return back()->with('success', 'Data Panen berhasil divalidasi');
        } catch (\Exception $e) {
            if ($request->wantsJson() && !$request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function validasiSerapan(Request $request, $id)
    {
        try {
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'admin',
                'tgl_valid' => now(),
            ]);
            AktivitasLog::catat('validasi', 'serapan', [
                'record_id'   => $id,
                'label_modul' => 'Serapan #' . $id,
                'keterangan'  => 'Validasi data serapan #' . $id,
            ]);
            if ($request->wantsJson() && !$request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data Serapan berhasil divalidasi']);
            }
            return back()->with('success', 'Data Serapan berhasil divalidasi');
        } catch (\Exception $e) {
            if ($request->wantsJson() && !$request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function tolakValidasiTanam(Request $request, $id)
    {
        $alasan = "Data ditolak oleh Polres. Silakan perbaiki data dan ajukan kembali.";
        $user = auth()->user();

        $targetReject = $request->target_reject ?? 'tanam';

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->first();
            if (!$tanam) return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan.'], 404);

            $hasSerapan = DB::table('distribusi')
                ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                ->where('panen.id_tanam', $id)
                ->exists();
            if (!$hasSerapan && (isset($tanam->is_active) && $tanam->is_active != 0)) {
                return response()->json(['success' => false, 'message' => 'Penolakan hanya bisa dilakukan jika siklus sudah mencapai tahap serapan atau telah selesai.'], 403);
            }

            $lahan = DB::table('lahan')->where('id_lahan', $tanam->id_lahan)->first();
            $alamat = $lahan ? ($lahan->alamat_lahan ?? '-') : '-';
            $penolak = $user->nama_anggota ?? ($user->username ?? 'Admin');
            
            $panens = DB::table('panen')->where('id_tanam', $id)->pluck('id_panen');

            if ($targetReject === 'serapan') {
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                $judul = '❌ Data Serapan Ditolak - Lahan #' . $tanam->id_lahan;
                $msgBody = "Data Serapan yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n";
            } elseif ($targetReject === 'panen') {
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => null,
                    'valid_oleh' => null,
                    'tgl_valid' => null
                ]);
                DB::table('panen')->where('id_tanam', $id)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                $judul = '❌ Data Panen Ditolak - Lahan #' . $tanam->id_lahan;
                $msgBody = "Data Panen yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n";
            } elseif ($targetReject === 'semua') {
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                DB::table('panen')->where('id_tanam', $id)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                DB::table('tanam')->where('id_tanam', $id)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh'   => null,
                    'tgl_valid'    => null,
                    'tgl_edit'     => now(),
                ]);
                $judul = '❌ Seluruh Siklus Ditolak - Lahan #' . $tanam->id_lahan;
                $msgBody = "Seluruh siklus (Tanam, Panen, dan Serapan) yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n";
            } else {
                // default: tanam (reset all validations, rejection reason only on tanam)
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => null,
                    'valid_oleh' => null,
                    'tgl_valid' => null
                ]);
                DB::table('panen')->where('id_tanam', $id)->update([
                    'alasan_tolak' => null,
                    'valid_oleh' => null,
                    'tgl_valid' => null
                ]);
                DB::table('tanam')->where('id_tanam', $id)->update([
                    'alasan_tolak'     => $alasan,
                    'valid_oleh'       => null,
                    'tgl_valid'        => null,
                    'tgl_edit'         => now(),
                ]);
                $judul = '❌ Data Tanam Ditolak - Lahan #' . $tanam->id_lahan;
                $msgBody = "Data Tanam yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n";
            }

            // Kirim notifikasi ke pembuat asli dan semua operator polres/polsek di wilayah tersebut
            $targetTugas = [$lahan->id_tingkat];
            $parts = explode('.', $lahan->id_tingkat);
            if (count($parts) >= 3) {
                $targetTugas[] = $parts[0] . '.' . $parts[1]; // Tambah polres
            }
            
            $recipients = DB::table('anggota')
                ->whereIn('id_tugas', $targetTugas)
                ->where('role', 'operator')
                ->pluck('id_anggota')
                ->toArray();
                
            $pembuatAsli = $tanam->id_anggota ?? ($lahan->id_anggota ?? null);
            if ($pembuatAsli && !in_array($pembuatAsli, $recipients)) {
                $recipients[] = $pembuatAsli;
            }
            
            $editOleh = $tanam->edit_oleh ?? ($lahan->edit_oleh ?? null);
            if ($editOleh) {
                $recipientAnggota = DB::table('anggota')->where('username', $editOleh)->first()
                    ?? DB::table('anggota')->where('id_anggota', $editOleh)->first();
                if ($recipientAnggota && !in_array($recipientAnggota->id_anggota, $recipients)) {
                    $recipients[] = $recipientAnggota->id_anggota;
                }
            }
            
            foreach ($recipients as $recipient_id) {
                if ($recipient_id) {
                    \App\Models\Pesan::create([
                        'id_pesan'     => \Illuminate\Support\Str::uuid(),
                        'sender_id'    => $user->id_anggota ?? 0,
                        'recipient_id' => $recipient_id,
                        'judul'        => $judul,
                        'isi_pesan'    => $msgBody .
                                          "📍 **Lokasi Lahan:** {$alamat}\n" .
                                          "🆔 **ID Lahan:** #{$tanam->id_lahan}\n\n" .
                                          "📝 **Alasan Penolakan:**\n{$alasan}\n\n" .
                                          "Silakan perbaiki data dan ajukan kembali.",
                        'is_read'      => false,
                    ]);
                }
            }

            AktivitasLog::catat('tolak_validasi', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'Tanam #' . $id,
                'keterangan'  => 'Tolak data tanam #' . $id . '. Alasan: ' . $alasan,
            ]);

            return response()->json(['success' => true, 'message' => 'Data tanam berhasil ditolak dan notifikasi telah dikirim.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiPanen(Request $request, $id)
    {
        $alasan = "Data ditolak oleh Polres. Silakan perbaiki data dan ajukan kembali.";
        $user = auth()->user();

        try {
            $panen = DB::table('panen')->where('id_panen', $id)->first();
            if (!$panen) return response()->json(['success' => false, 'message' => 'Data Panen tidak ditemukan.'], 404);

            $tanam = DB::table('tanam')->where('id_tanam', $panen->id_tanam)->first();
            $hasSerapan = DB::table('distribusi')
                ->where('id_panen', $panen->id_panen)
                ->exists();
            
            if (!$hasSerapan && (isset($tanam->is_active) && $tanam->is_active != 0)) {
                return response()->json(['success' => false, 'message' => 'Penolakan hanya bisa dilakukan jika siklus sudah mencapai tahap serapan atau telah selesai.'], 403);
            }

            $lahan = DB::table('lahan')->where('id_lahan', $panen->id_lahan)->first();
            $alamat = $lahan ? ($lahan->alamat_lahan ?? '-') : '-';
            $penolak = $user->nama_anggota ?? ($user->username ?? 'Admin');

            // Update: tandai sebagai ditolak, hapus validasi jika ada
            DB::table('panen')->where('id_panen', $id)->update([
                'alasan_tolak' => $alasan,
                'valid_oleh'   => null,
                'tgl_valid'  => null,
                'tgl_edit'   => now(),
            ]);

            // Kirim notifikasi ke operator pembuat data
            $recipient_id = $panen->id_anggota ?? null;
            if (!$recipient_id) {
                $editOleh = $panen->edit_oleh ?? ($lahan->edit_oleh ?? null);
                if ($editOleh) {
                    $recipientAnggota = DB::table('anggota')->where('username', $editOleh)->first()
                        ?? DB::table('anggota')->where('id_anggota', $editOleh)->first();
                    $recipient_id = $recipientAnggota ? $recipientAnggota->id_anggota : null;
                } else {
                    $recipient_id = $lahan->id_anggota ?? null;
                }
            }

            if ($recipient_id) {
                \App\Models\Pesan::create([
                    'id_pesan'     => \Illuminate\Support\Str::uuid(),
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $recipient_id,
                    'judul'        => '❌ Data Panen Ditolak - Lahan #' . $panen->id_lahan,
                    'isi_pesan'    => "Data panen yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n" .
                                      "📍 **Lokasi Lahan:** {$alamat}\n" .
                                      "🆔 **ID Lahan:** #{$panen->id_lahan}\n\n" .
                                      "📝 **Alasan Penolakan:**\n{$alasan}\n\n" .
                                      "Silakan perbaiki data dan ajukan kembali.",
                    'is_read'      => false,
                ]);
            }

            AktivitasLog::catat('tolak_validasi', 'panen', [
                'record_id'   => $id,
                'label_modul' => 'Panen #' . $id,
                'keterangan'  => 'Tolak data panen #' . $id . '. Alasan: ' . $alasan,
            ]);

            return response()->json(['success' => true, 'message' => 'Data panen berhasil ditolak dan notifikasi telah dikirim.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiSerapan(Request $request, $id)
    {
        $alasan = "Data ditolak oleh Polres. Silakan perbaiki data dan ajukan kembali.";
        $user = auth()->user();

        try {
            $serapan = DB::table('distribusi')->where('id_distribusi', $id)->first();
            if (!$serapan) return response()->json(['success' => false, 'message' => 'Data Serapan tidak ditemukan.'], 404);

            $lahan = DB::table('lahan')->where('id_lahan', $serapan->id_lahan)->first();
            $alamat = $lahan ? ($lahan->alamat_lahan ?? '-') : '-';
            $penolak = $user->nama_anggota ?? ($user->username ?? 'Admin');

            // Update: tandai sebagai ditolak, hapus validasi jika ada
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'alasan_tolak'          => $alasan,
                'valid_oleh'            => null,
                'tgl_valid'             => null,
                'tgl_edit'              => now(),
            ]);

            // Kirim notifikasi ke operator pembuat data
            $recipient_id = $serapan->id_anggota ?? null;
            if (!$recipient_id) {
                $editOleh = $serapan->edit_oleh ?? ($lahan->edit_oleh ?? null);
                if ($editOleh) {
                    $recipientAnggota = DB::table('anggota')->where('username', $editOleh)->first()
                        ?? DB::table('anggota')->where('id_anggota', $editOleh)->first();
                    $recipient_id = $recipientAnggota ? $recipientAnggota->id_anggota : null;
                } else {
                    $recipient_id = $lahan->id_anggota ?? null;
                }
            }

            if ($recipient_id) {
                \App\Models\Pesan::create([
                    'id_pesan'     => \Illuminate\Support\Str::uuid(),
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $recipient_id,
                    'judul'        => '❌ Data Serapan Ditolak - Lahan #' . $serapan->id_lahan,
                    'isi_pesan'    => "Data serapan yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n" .
                                      "📍 **Lokasi Lahan:** {$alamat}\n" .
                                      "🆔 **ID Lahan:** #{$serapan->id_lahan}\n\n" .
                                      "📝 **Alasan Penolakan:**\n{$alasan}\n\n" .
                                      "Silakan perbaiki data dan ajukan kembali.",
                    'is_read'      => false,
                ]);
            }

            AktivitasLog::catat('tolak_validasi', 'serapan', [
                'record_id'   => $id,
                'label_modul' => 'Serapan #' . $id,
                'keterangan'  => 'Tolak data serapan #' . $id . '. Alasan: ' . $alasan,
            ]);

            return response()->json(['success' => true, 'message' => 'Data serapan berhasil ditolak dan notifikasi telah dikirim.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getValidasiData($id)
    {
        $tanam = DB::table('tanam')->where('id_lahan', $id)->where('is_active', 1)->whereNull('valid_oleh')->get();
        $panen = DB::table('panen')->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')->where('panen.id_lahan', $id)->where('tanam.is_active', 1)->whereNull('panen.valid_oleh')->select('panen.*')->get();
        $serapan = DB::table('distribusi')
            ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
            ->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')
            ->where('panen.id_lahan', $id)
            ->where('tanam.is_active', 1)
            ->whereNull('distribusi.valid_oleh')
            ->select('distribusi.*')
            ->get();
        $activeTanam = DB::table('tanam')->where('id_lahan', $id)->where('is_active', 1)->first();
        $hasActive = !is_null($activeTanam);
        $activeTanamId = $hasActive ? $activeTanam->id_tanam : null;

        return response()->json([
            'tanam' => $tanam,
            'panen' => $panen,
            'serapan' => $serapan,
            'has_active' => $hasActive,
            'active_tanam_id' => $activeTanamId,
            'active_tanam' => $activeTanam
        ]);
    }

    public function terimaAkhiriSiklus(Request $request, $id)
    {
        // Hanya admin yang boleh menyelesaikan siklus
        if (auth()->user() && auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menyetujui siklus.'], 403);
            return back()->with('error', 'Anda tidak memiliki izin untuk menyetujui siklus.');
        }

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->first();

            if (!$tanam) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan.'], 422);
                return back()->with('error', 'Data Tanam tidak ditemukan.');
            }

            DB::table('tanam')->where('id_tanam', $id)->update([
                'status_akhiri_siklus' => 2,
                'is_active' => 0
            ]);
            
            AktivitasLog::catat('selesai_siklus', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'Siklus Tanam #' . $id,
                'keterangan'  => 'Menyetujui penyelesaian siklus tanam #' . $id . ' dan mengarsipkannya.',
            ]);

            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Persetujuan akhir siklus berhasil. Siklus telah diarsipkan.']);
            return back()->with('success', 'Persetujuan akhir siklus berhasil. Siklus telah diarsipkan.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Gagal menyetujui siklus: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal menyetujui siklus: ' . $e->getMessage());
        }
    }

    public function tolakAkhiriSiklus(Request $request, $id)
    {
        if (auth()->user() && auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menolak siklus.'], 403);
            return back()->with('error', 'Anda tidak memiliki izin untuk menolak siklus.');
        }

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->first();
            if (!$tanam) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan.'], 422);
                return back()->with('error', 'Data Tanam tidak ditemukan.');
            }

            $target_reject = $request->input('target_reject', 'semua');
            $alasan = $request->input('alasan', $request->input('alasan_tolak_akhiri_siklus', 'Ditolak oleh Admin.'));

            DB::table('tanam')->where('id_tanam', $id)->update([
                'status_akhiri_siklus' => 0,
                'alasan_tolak_akhiri_siklus' => $alasan
            ]);

            $panens = DB::table('panen')->where('id_tanam', $id)->pluck('id_panen');
            $msgDetail = "";

            if ($target_reject === 'semua' || $target_reject === 'tanam') {
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                DB::table('panen')->where('id_tanam', $id)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                DB::table('tanam')->where('id_tanam', $id)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                $msgDetail = "Data Tanam, Panen, dan Serapan telah ditolak.";
            } elseif ($target_reject === 'panen') {
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                DB::table('panen')->where('id_tanam', $id)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                $msgDetail = "Data Panen dan Serapan ditolak. Data Tanam tetap valid.";
            } elseif ($target_reject === 'serapan') {
                DB::table('distribusi')->whereIn('id_panen', $panens)->update([
                    'alasan_tolak' => $alasan,
                    'valid_oleh' => null,
                    'tgl_valid' => null,
                    'tgl_edit' => now()
                ]);
                $msgDetail = "Data Serapan ditolak. Data Tanam dan Panen tetap valid.";
            }

            // Kirim notifikasi ke Polsek & Polres
            $lahan = DB::table('lahan')->where('id_lahan', $tanam->id_lahan)->first();
            $targetTugas = [$lahan->id_tingkat];
            $parts = explode('.', $lahan->id_tingkat);
            if (count($parts) >= 3) {
                $targetTugas[] = $parts[0] . '.' . $parts[1]; // Tambah polres
            }
            
            $recipients = DB::table('anggota')
                ->whereIn('id_tugas', $targetTugas)
                ->where('role', 'operator')
                ->pluck('id_anggota')
                ->toArray();

            foreach ($recipients as $recipient_id) {
                \App\Models\Pesan::create([
                    'id_pesan'     => \Illuminate\Support\Str::uuid(),
                    'sender_id'    => auth()->user()->id_anggota ?? 0,
                    'recipient_id' => $recipient_id,
                    'judul'        => '❌ Pengajuan Akhiri Siklus Ditolak',
                    'isi_pesan'    => "Pengajuan Akhiri Siklus untuk Lahan #" . $tanam->id_lahan . " telah **DITOLAK** oleh Admin.\n\n" .
                                      "📝 **Alasan:**\n{$alasan}\n\n" .
                                      "ℹ️ **Detail:**\n{$msgDetail}\n\n" .
                                      "Silakan perbaiki data yang salah.",
                    'is_read'      => false,
                ]);
            }

            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Penolakan akhir siklus berhasil dan notifikasi telah dikirim.']);
            return back()->with('success', 'Penolakan akhir siklus berhasil dan notifikasi telah dikirim.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Gagal menolak siklus: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal menolak siklus: ' . $e->getMessage());
        }
    }

    public function selesaiSiklusTanam(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->where('is_active', 1)->first();

            if (!$tanam) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan atau sudah tidak aktif.'], 422);
                return back()->with('error', 'Data Tanam tidak ditemukan atau sudah tidak aktif.');
            }

            if (is_null($tanam->valid_oleh)) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Data Tanam belum divalidasi.'], 422);
                return back()->with('error', 'Data Tanam belum divalidasi.');
            }
            
            $panen = DB::table('panen')->where('id_tanam', $id)->first();
            if (!$panen) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Siklus belum selesai. Panen belum dicatat.'], 422);
                return back()->with('error', 'Siklus belum selesai. Panen belum dicatat.');
            }
            if (is_null($panen->valid_oleh)) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Data Panen belum divalidasi.'], 422);
                return back()->with('error', 'Data Panen belum divalidasi.');
            }

            $serapan = DB::table('distribusi')
                ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                ->where('panen.id_tanam', $id)
                ->select('distribusi.*')
                ->first();
            if (!$serapan) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Siklus belum selesai. Serapan belum dicatat.'], 422);
                return back()->with('error', 'Siklus belum selesai. Serapan belum dicatat.');
            }
            if (is_null($serapan->valid_oleh)) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Data Serapan belum divalidasi.'], 422);
                return back()->with('error', 'Data Serapan belum divalidasi.');
            }

            DB::table('tanam')->where('id_tanam', $id)->update(['status_akhiri_siklus' => 1]);
            
            // Send notification to Admin
            $admins = DB::table('anggota')->where('role', 'admin')->pluck('id_anggota')->toArray();
            $namaPolres = $user->nama_anggota ?? 'Polres';
            $alamat = DB::table('lahan')->where('id_lahan', $tanam->id_lahan)->value('alamat_lahan') ?? '-';
            
            foreach ($admins as $adminId) {
                \App\Models\Pesan::create([
                    'id_pesan'     => \Illuminate\Support\Str::uuid(),
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $adminId,
                    'judul'        => '🔔 Pengajuan Akhiri Siklus Lahan #' . $tanam->id_lahan,
                    'isi_pesan'    => "{$namaPolres} mengajukan Akhiri Siklus untuk Lahan:\n\n" .
                                      "📍 **Lokasi Lahan:** {$alamat}\n" .
                                      "🆔 **ID Lahan:** #{$tanam->id_lahan}\n\n" .
                                      "Silakan cek dan lakukan Persetujuan di menu Kelola Lahan.",
                    'is_read'      => false,
                ]);
            }

            if ($request->wantsJson() && !$request->ajax()) return response()->json(['success' => true, 'message' => 'Pengajuan Akhiri Siklus berhasil dikirim ke Admin.']);
            return back()->with('success', 'Pengajuan Akhiri Siklus berhasil dikirim ke Admin.');
        } catch (\Exception $e) {
            if ($request->wantsJson() && !$request->ajax()) return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan siklus: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal menyelesaikan siklus: ' . $e->getMessage());
        }
    }

    public function unvalidasiSiklusTanam(Request $request, $id)
    {
        // Hanya admin yang boleh unvalidasi
        if (auth()->user() && auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk membatalkan arsip siklus.'], 403);
            return back()->with('error', 'Anda tidak memiliki izin untuk membatalkan arsip siklus.');
        }

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->first();
            if (!$tanam) {
                if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Siklus tidak ditemukan.'], 422);
                return back()->with('error', 'Siklus tidak ditemukan.');
            }

            DB::table('tanam')->where('id_tanam', $id)->update([
                'is_active' => 1,
                'status_akhiri_siklus' => 0
            ]);
            
            AktivitasLog::catat('unselesai_siklus', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'Siklus Tanam #' . $id,
                'keterangan'  => 'Membatalkan penyelesaian siklus tanam #' . $id . ' (diaktifkan kembali).',
            ]);

            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Berhasil membatalkan arsip. Data siklus kembali aktif di Kelola Lahan.']);
            return back()->with('success', 'Berhasil mengembalikan siklus. Data kembali aktif.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Gagal membatalkan arsip: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal membatalkan arsip: ' . $e->getMessage());
        }
    }
}
