<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Komoditi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelolaLahanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';

        $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function($q) use ($column, $scope) { $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%'); });
            }
            return $query;
        };

        $applyTingkatScope = function ($query) use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function ($q) use ($scope) {
                    $q->where(function($q) use ($scope) { $q->where('id_tingkat', $scope)->orWhere('id_tingkat', 'LIKE', $scope . '.%'); })
                        ->orWhereRaw("? = id_tingkat OR ? LIKE CONCAT(id_tingkat, '.%')", [$scope, $scope]);
                });
            }
            return $query;
        };

        // 1. Fetch Filters Data (Dropdowns)
        $polresList = $applyTingkatScope(DB::table('tingkat'))
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')
            ->get();

        $polsekList = $applyTingkatScope(DB::table('tingkat'))
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

        $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam GROUP BY id_lahan)) as t');
        $latestPanen = DB::raw('(SELECT * FROM panen WHERE id_panen IN (SELECT MAX(id_panen) FROM panen GROUP BY id_tanam)) as p');
        $latestDistribusi = DB::raw('(SELECT * FROM distribusi WHERE id_distribusi IN (SELECT MAX(id_distribusi) FROM distribusi GROUP BY id_tanam)) as d');

        // 3. Build Base Data Query (Applying Filters)
        $dataQuery = $applyScope(DB::table('lahan')->where('lahan.deletestatus', '!=', '0'))
            ->leftJoin('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->leftJoin('anggota', 'lahan.id_anggota', '=', 'anggota.id_anggota')
            ->leftJoin('komoditi', 'lahan.id_komoditi', '=', 'komoditi.id_komoditi')
            ->leftJoin($latestTanam, 'lahan.id_lahan', '=', 't.id_lahan')
            ->leftJoin($latestPanen, 't.id_tanam', '=', 'p.id_tanam')
            ->leftJoin($latestDistribusi, 't.id_tanam', '=', 'd.id_tanam');

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

            $dataQuery->where(function ($q) use ($searchStr, $matchingWilayahIds) {
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
        $resorBaseQuery = $applyTingkatScope(DB::table('tingkat'))
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
                    'p.id_panen',
                    'p.total_panen',
                    'p.tgl_panen',
                    'p.status_panen',
                    'd.id_distribusi',
                    'd.total_distribusi',
                    'd.tgl_distribusi',
                    'd.distribusi_ke',
                    'd.valid_oleh as serapan_valid_oleh'
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
                $allTanams = DB::table('tanam')->whereIn('id_lahan', $lahanIdsForHistory)->orderBy('tgl_tanam', 'desc')->get();
                $allTanamIds = $allTanams->pluck('id_tanam')->unique()->toArray();

                $allPanens = empty($allTanamIds) ? collect() : DB::table('panen')->whereIn('id_tanam', $allTanamIds)->get()->groupBy('id_tanam');
                $allDistribusis = empty($allTanamIds) ? collect() : DB::table('distribusi')->whereIn('id_tanam', $allTanamIds)->get()->groupBy('id_tanam');

                $allTanams->transform(function ($t) use ($allPanens, $allDistribusis) {
                    $t->panens = $allPanens->get($t->id_tanam, collect());
                    $t->distribusis = $allDistribusis->get($t->id_tanam, collect());
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
                    ->select('id_lahan', DB::raw('MAX(id_tanam) as max_id_tanam'))
                    ->whereIn('id_lahan', $lahanIds)
                    ->groupBy('id_lahan')
                    ->get()
                    ->keyBy('id_lahan');

                $tanamIds = $latestTanams->pluck('max_id_tanam')->toArray();

                $panens = DB::table('panen')->whereIn('id_tanam', $tanamIds)->pluck('id_panen', 'id_tanam');
                $distribusis = DB::table('distribusi')->whereIn('id_tanam', $tanamIds)->pluck('id_distribusi', 'id_tanam');

                foreach ($lahanIds as $idLahan) {
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

        // 5. Calculate Stats (Aggregated) — scoped to operator's jurisdiction first
        $statsData = $applyScope(DB::table('lahan')->where('deletestatus', '!=', '0'), 'id_tingkat');
        if ($filters['sektor']) {
            $statsData->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $statsData->where(function($q) use ($filters) { $q->where('id_tingkat', $filters['resor'])->orWhere('id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
        }

        $potensiTotal = (clone $statsData)->sum('luas_lahan');
        $potensiDetails = (clone $statsData)->selectRaw('id_jenis_lahan, SUM(luas_lahan) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Tanam Stats — scoped
        $tanamQuery = $applyScope(DB::table('view_tanam'), 'id_tingkat');
        if ($filters['sektor']) {
            $tanamQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $tanamQuery->where(function($q) use ($filters) { $q->where('id_tingkat', $filters['resor'])->orWhere('id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
        }
        $tanamTotal = (clone $tanamQuery)->sum('luas_tanam') ?? 0;
        $tanamDetails = (clone $tanamQuery)->selectRaw('id_jenis_lahan, SUM(luas_tanam) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Panen Stats — scoped
        $panenQuery = $applyScope(DB::table('view_panen'), 'id_tingkat');
        if ($filters['sektor']) {
            $panenQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $panenQuery->where(function($q) use ($filters) { $q->where('id_tingkat', $filters['resor'])->orWhere('id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
        }
        $panenTotal = (clone $panenQuery)->sum('luas_panen_ha') ?? 0;
        $panenDetails = (clone $panenQuery)->selectRaw('id_jenis_lahan, SUM(luas_panen_ha) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Serapan Stats — scoped
        $serapanQuery = $applyScope(DB::table('view_serapan'), 'id_tingkat');
        if ($filters['sektor']) {
            $serapanQuery->where('id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $serapanQuery->where(function($q) use ($filters) { $q->where('id_tingkat', $filters['resor'])->orWhere('id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
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

        return view('operator.kelola-lahan.operator_kelola.operator_kelola_index', compact(
            'polresList',
            'polsekList',
            'komoditiList',
            'filters',
            'stats',
            'data',
            'lahanStagesMap'
        ));
    }

    public function storeTanam(Request $request)
    {
        $request->validate([
            'id_lahan' => 'required|integer',
            'tgl_tanam' => 'required|date',
            'luas_tanam' => 'required|numeric|min:0',
            'jenis_bibit' => 'nullable|string|max:255',
            'kebutuhan_bibit' => 'nullable|string|max:255',
            'est_awal_panen' => 'nullable|date',
            'est_akhir_panen' => 'nullable|date',
            'keterangan_tanam' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $request->id_lahan)->first();
        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Lahan tidak valid atau di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('tanam')->max('id_tanam') + 1;
                $idAnggota = auth()->id();

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
            'id_lahan' => 'required|integer',
            'tgl_panen' => 'required|date',
            'luas_panen' => 'required|numeric|min:0',
            'total_panen' => 'nullable|numeric|min:0',
            'status_panen' => 'required|integer|in:0,1,2',
            'keterangan_panen' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $request->id_lahan)->first();
        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Lahan tidak valid atau di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('panen')->max('id_panen') + 1;
                $idAnggota = auth()->id();
                $idTanam = DB::table('tanam')->where('id_lahan', $request->id_lahan)->orderByDesc('id_tanam')->value('id_tanam') ?? 0;

                DB::table('panen')->insert([
                    'id_panen' => $newId,
                    'id_tanam' => $idTanam,
                    'id_lahan' => $request->id_lahan,
                    'id_anggota' => $idAnggota,
                    'tgl_panen' => $request->tgl_panen,
                    'luas_panen' => $request->luas_panen,
                    'total_panen' => $request->total_panen ?? 0,
                    'status_panen' => $request->status_panen,
                    'ket_panen' => $request->keterangan_panen, // mapped from frontend form
                    'datetransaction' => now(),
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
            'id_lahan' => 'required|integer',
            'tgl_distribusi' => 'required|date',
            'total_distribusi' => 'required|numeric|min:0',
            'distribusi_ke' => 'required|integer',
            'keterangan_serapan' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $lahan = DB::table('lahan')->where('id_lahan', $request->id_lahan)->first();
        if (!$lahan || ($scope && $scope != '0' && ((string)$lahan->id_tingkat !== (string)$scope && !str_starts_with((string)$lahan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Lahan tidak valid atau di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($request) {
                $newId = DB::table('distribusi')->max('id_distribusi') + 1;
                $idAnggota = auth()->id();
                $latestPanen = DB::table('panen')->where('id_lahan', $request->id_lahan)->orderByDesc('id_panen')->first();

                DB::table('distribusi')->insert([
                    'id_distribusi' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'id_panen' => $latestPanen ? $latestPanen->id_panen : 0,
                    'id_tanam' => $latestPanen ? $latestPanen->id_tanam : 0,
                    'id_anggota' => $idAnggota,
                    'tgl_distribusi' => $request->tgl_distribusi,
                    'total_distribusi' => $request->total_distribusi,
                    'distribusi_ke' => $request->distribusi_ke,
                    'keterangan_distribusi' => $request->keterangan_serapan, // mapped from frontend form
                    'datetransaction' => now(),
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
        $request->validate([
            'tgl_tanam' => 'required|date',
            'luas_tanam' => 'required|numeric|min:0',
            'jenis_bibit' => 'nullable|string|max:255',
            'kebutuhan_bibit' => 'nullable|string|max:255',
            'est_awal_panen' => 'nullable|date',
            'est_akhir_panen' => 'nullable|date',
            'keterangan_tanam' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $tanam = DB::table('tanam')->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')->where('id_tanam', $id)->first(['lahan.id_tingkat']);
        if (!$tanam || ($scope && $scope != '0' && ((string)$tanam->id_tingkat !== (string)$scope && !str_starts_with((string)$tanam->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::table('tanam')->where('id_tanam', $id)->update([
                'tgl_tanam' => $request->tgl_tanam,
                'luas_tanam' => $request->luas_tanam,
                'nama_bibit' => $request->jenis_bibit,
                'kebutuhan_bibit' => $request->kebutuhan_bibit,
                'est_awal_panen' => $request->est_awal_panen,
                'est_akhir_panen' => $request->est_akhir_panen,
                'keterangan_tanam' => $request->keterangan_tanam,
                'edit_oleh' => auth()->user()->username ?? 'operator',
                'tgl_edit' => now(),
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
            'status_panen' => 'required|integer|in:0,1,2',
            'keterangan_panen' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $panen = DB::table('panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->where('id_panen', $id)->first(['lahan.id_tingkat']);
        if (!$panen || ($scope && $scope != '0' && ((string)$panen->id_tingkat !== (string)$scope && !str_starts_with((string)$panen->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::table('panen')->where('id_panen', $id)->update([
                'tgl_panen' => $request->tgl_panen,
                'luas_panen' => $request->luas_panen,
                'total_panen' => $request->total_panen,
                'status_panen' => $request->status_panen,
                'ket_panen' => $request->keterangan_panen,
                'edit_oleh' => auth()->user()->username ?? 'operator',
                'tgl_edit' => now(),
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

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $serapan = DB::table('distribusi')->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')->where('id_distribusi', $id)->first(['lahan.id_tingkat']);
        if (!$serapan || ($scope && $scope != '0' && ((string)$serapan->id_tingkat !== (string)$scope && !str_starts_with((string)$serapan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'tgl_distribusi' => $request->tgl_distribusi,
                'total_distribusi' => $request->total_distribusi,
                'distribusi_ke' => $request->distribusi_ke,
                'keterangan_distribusi' => $request->keterangan_serapan,
                'edit_oleh' => auth()->user()->username ?? 'operator',
                'tgl_edit' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Data Serapan berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function validasiSerapan(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        $scope = $user->id_tugas ?? '0';
        $serapan = DB::table('distribusi')->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')->where('id_distribusi', $id)->first(['lahan.id_tingkat']);
        if (!$serapan || ($scope && $scope != '0' && ((string)$serapan->id_tingkat !== (string)$scope && !str_starts_with((string)$serapan->id_tingkat, (string)$scope . '.')))) {
            return back()->with('error', 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!');
        }

        try {
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'operator',
                'tgl_valid' => now(),
            ]);
            return back()->with('success', 'Data Serapan berhasil divalidasi');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function destroyTanam($id)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $tanam = DB::table('tanam')->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')->where('id_tanam', $id)->first(['lahan.id_tingkat']);
        if (!$tanam || ($scope && $scope != '0' && ((string)$tanam->id_tingkat !== (string)$scope && !str_starts_with((string)$tanam->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($id) {
                DB::table('tanam')->where('id_tanam', $id)->update(['deletestatus' => '0']);
                DB::table('panen')->where('id_tanam', $id)->update(['deletestatus' => '0']);
                DB::table('distribusi')->where('id_tanam', $id)->update(['deletestatus' => '0']);
            });
            return response()->json(['success' => true, 'message' => 'Data Tanam berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function destroyPanen($id)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $panen = DB::table('panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->where('id_panen', $id)->first(['lahan.id_tingkat']);
        if (!$panen || ($scope && $scope != '0' && ((string)$panen->id_tingkat !== (string)$scope && !str_starts_with((string)$panen->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($id) {
                DB::table('panen')->where('id_panen', $id)->update(['deletestatus' => '0']);
                DB::table('distribusi')->where('id_panen', $id)->update(['deletestatus' => '0']);
            });
            return response()->json(['success' => true, 'message' => 'Data Panen berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function destroySerapan($id)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $serapan = DB::table('distribusi')->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')->where('id_distribusi', $id)->first(['lahan.id_tingkat']);
        if (!$serapan || ($scope && $scope != '0' && ((string)$serapan->id_tingkat !== (string)$scope && !str_starts_with((string)$serapan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::table('distribusi')->where('id_distribusi', $id)->update(['deletestatus' => '0']);
            return response()->json(['success' => true, 'message' => 'Data Serapan berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function getValidasiData($id)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';

        $data = DB::table('lahan')
            ->leftJoin('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->where('lahan.id_lahan', $id)
            ->first();

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        if ($scope && $scope != '0' && ((string)$data->id_tingkat !== (string)$scope && !str_starts_with((string)$data->id_tingkat, (string)$scope . '.'))) {
            return response()->json(['error' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        $tanam = DB::table('tanam')->where('id_lahan', $id)->where('deletestatus', '1')->get();
        $panen = DB::table('panen')->where('id_lahan', $id)->where('deletestatus', '1')->get();
        $serapan = DB::table('distribusi')->where('id_lahan', $id)->where('deletestatus', '1')->get();

        return response()->json([
            'lahan' => $data,
            'tanam' => $tanam,
            'panen' => $panen,
            'serapan' => $serapan
        ]);
    }
}
