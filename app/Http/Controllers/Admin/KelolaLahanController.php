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
        $latestDistribusi = DB::raw('(SELECT * FROM distribusi WHERE id_distribusi IN (SELECT MAX(id_distribusi) FROM distribusi GROUP BY id_tanam)) as d');

        $dataQuery->leftJoin($latestTanam, 'lahan.id_lahan', '=', 't.id_lahan')
            ->leftJoin($latestPanen, 't.id_tanam', '=', 'p.id_tanam')
            ->leftJoin($latestDistribusi, 't.id_tanam', '=', 'd.id_tanam');

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
                    ->where('is_active', 1)
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

            $tanamTotal = (clone $tanamQuery)->sum('tanam.luas_tanam') ?? 0;
            $tanamDetails = (clone $tanamQuery)->selectRaw('lahan.id_jenis_lahan, SUM(tanam.luas_tanam) as total_luas, COUNT(tanam.id_lahan) as total_lokasi')
                ->whereNotNull('lahan.id_jenis_lahan')
                ->groupBy('lahan.id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');

            // Panen Stats (All validated cycles)
            $panenQuery = DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->whereNotNull('panen.valid_oleh')->where('panen.valid_oleh', '!=', '')
                ->whereIn('panen.id_lahan', $filteredLahanIds);

            $panenTotal = (clone $panenQuery)->sum('panen.luas_panen') ?? 0;
            $panenTonTotal = (clone $panenQuery)->sum('panen.total_panen') ?? 0;
            $panenDetails = (clone $panenQuery)->selectRaw('lahan.id_jenis_lahan, SUM(panen.luas_panen) as total_luas, COUNT(panen.id_lahan) as total_lokasi')
                ->whereNotNull('lahan.id_jenis_lahan')
                ->groupBy('lahan.id_jenis_lahan')
                ->get()->keyBy('id_jenis_lahan');

            // Serapan Stats (All validated cycles)
            $serapanQuery = DB::table('distribusi')
                ->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
                ->whereNotNull('distribusi.valid_oleh')->where('distribusi.valid_oleh', '!=', '')
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
        return view('admin.kelola-lahan.lahan.index', $this->getIndexData($request, 'active'));
    }

    public function riwayatIndex(Request $request)
    {
        return view('admin.kelola-lahan.riwayat.index', $this->getIndexData($request, 'history'));
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
            'status_panen' => 'required|integer|in:0,1,2',
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
                    $luasPanenBaru = $request->status_panen == 2 ? 0 : (float)$request->luas_panen;
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
                // ── AKHIR VALIDASI ──

                DB::table('distribusi')->insert([
                    'id_distribusi' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'id_panen' => $latestPanen ? $latestPanen->id_panen : 0,
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
                'keterangan_tanam' => $request->keterangan_tanam,
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
            'status_panen' => 'required|integer|in:0,1,2',
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
                $luasPanenBaru = $request->status_panen == 2 ? 0 : (float)$request->luas_panen;
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
            $luasPanenFix = $request->status_panen == 2 ? 0 : $request->luas_panen;
            $totalPanenFix = $request->status_panen == 2 ? 0 : ($request->total_panen ?? 0);

            DB::table('panen')->where('id_panen', $id)->update([
                'tgl_panen' => $request->tgl_panen,
                'luas_panen' => $luasPanenFix,
                'total_panen' => $totalPanenFix,
                'status_panen' => $request->status_panen,
                'ket_panen' => $request->keterangan_panen,
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
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'tgl_distribusi' => $request->tgl_distribusi,
                'total_distribusi' => $request->total_distribusi,
                'distribusi_ke' => $request->distribusi_ke,
                'keterangan_distribusi' => $request->keterangan_serapan,
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

                // Cascade: hapus distribusi terkait tanam ini
                DB::table('distribusi')->where('id_tanam', $id)->delete();

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

            return response()->json(['success' => true, 'message' => 'Data tanam beserta panen & serapan terkait berhasil dihapus']);
        } catch (\Exception $e) {
            \Log::error('destroyTanam error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
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
        return back()->with('success', 'Data Tanam berhasil di-unvalidasi');
    }

    public function unvalidasiPanen($id)
    {
        DB::table('panen')->where('id_panen', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        return back()->with('success', 'Data Panen berhasil di-unvalidasi');
    }

    public function unvalidasiSerapan($id)
    {
        DB::table('distribusi')->where('id_distribusi', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        return back()->with('success', 'Data Serapan berhasil di-unvalidasi');
    }

    public function validasiTanam(Request $request, $id)
    {
        try {
            DB::table('tanam')->where('id_tanam', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'admin',
                'tgl_valid' => now(),
            ]);
            return back()->with('success', 'Data Tanam berhasil divalidasi');
        } catch (\Exception $e) {
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
            return back()->with('success', 'Data Panen berhasil divalidasi');
        } catch (\Exception $e) {
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
            return back()->with('success', 'Data Serapan berhasil divalidasi');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function tolakValidasiTanam(Request $request, $id)
    {
        $request->validate(['alasan' => 'required|string|max:1000']);
        $alasan = $request->alasan;

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->first();
            if (!$tanam) return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan.'], 404);

            $ket_baru = "[DITOLAK] Alasan: " . $alasan . "\n" . $tanam->keterangan_tanam;

            DB::table('tanam')->where('id_tanam', $id)->update([
                'valid_oleh' => null,
                'tgl_valid' => null,
                'keterangan_tanam' => $ket_baru,
            ]);

            $recipient_id = $tanam->id_anggota ?? DB::table('lahan')->where('id_lahan', $tanam->id_lahan)->value('edit_oleh');

            // Kirim pesan ke pembuat (id_anggota) jika ada
            if ($recipient_id) {
                $user = auth()->user();
                $penolak = $user->nama_anggota ?? 'Admin';
                $lahan = DB::table('lahan')->where('id_lahan', $tanam->id_lahan)->first();
                $alamat = $lahan ? ($lahan->alamat_lahan ?? 'Tidak diketahui') : 'Tidak diketahui';

                DB::table('pesans')->insert([
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $recipient_id,
                    'judul'        => '❌ Penolakan Validasi Data Tanam #' . $id,
                    'isi_pesan'    => "Data tanam yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n📍 **Lokasi Lahan:** {$alamat}\n🆔 **ID Lahan:** #{$tanam->id_lahan}\n\n📝 **Alasan Penolakan:**\n{$alasan}\n\nSilakan perbaiki data dan ajukan kembali.",
                    'is_read'      => false,
                ]);
            }

            AktivitasLog::catat('tolak_validasi', 'tanam', [
                'record_id'   => $id,
                'label_modul' => 'Tanam #' . $id,
                'keterangan'  => 'Tolak validasi tanam #' . $id . '. Alasan: ' . $alasan,
            ]);

            return response()->json(['success' => true, 'message' => 'Validasi tanam berhasil ditolak dan notifikasi telah dikirim.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiPanen(Request $request, $id)
    {
        $request->validate(['alasan' => 'required|string|max:1000']);
        $alasan = $request->alasan;

        try {
            $panen = DB::table('panen')->where('id_panen', $id)->first();
            if (!$panen) return response()->json(['success' => false, 'message' => 'Data Panen tidak ditemukan.'], 404);

            $ket_baru = "[DITOLAK] Alasan: " . $alasan . "\n" . $panen->ket_panen;

            DB::table('panen')->where('id_panen', $id)->update([
                'valid_oleh' => null,
                'tgl_valid' => null,
                'ket_panen' => $ket_baru,
            ]);

            $recipient_id = $panen->id_anggota ?? DB::table('lahan')->where('id_lahan', $panen->id_lahan)->value('edit_oleh');

            // Kirim pesan ke pembuat (id_anggota)
            if ($recipient_id) {
                $user = auth()->user();
                $penolak = $user->nama_anggota ?? 'Admin';
                $lahan = DB::table('lahan')->where('id_lahan', $panen->id_lahan)->first();
                $alamat = $lahan ? ($lahan->alamat_lahan ?? 'Tidak diketahui') : 'Tidak diketahui';

                DB::table('pesans')->insert([
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $recipient_id,
                    'judul'        => '❌ Penolakan Validasi Data Panen #' . $id,
                    'isi_pesan'    => "Data panen yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n📍 **Lokasi Lahan:** {$alamat}\n🆔 **ID Lahan:** #{$panen->id_lahan}\n\n📝 **Alasan Penolakan:**\n{$alasan}\n\nSilakan perbaiki data dan ajukan kembali.",
                    'is_read'      => false,
                ]);
            }

            AktivitasLog::catat('tolak_validasi', 'panen', [
                'record_id'   => $id,
                'label_modul' => 'Panen #' . $id,
                'keterangan'  => 'Tolak validasi panen #' . $id . '. Alasan: ' . $alasan,
            ]);

            return response()->json(['success' => true, 'message' => 'Validasi panen berhasil ditolak dan notifikasi telah dikirim.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiSerapan(Request $request, $id)
    {
        $request->validate(['alasan' => 'required|string|max:1000']);
        $alasan = $request->alasan;

        try {
            $serapan = DB::table('distribusi')->where('id_distribusi', $id)->first();
            if (!$serapan) return response()->json(['success' => false, 'message' => 'Data Serapan tidak ditemukan.'], 404);

            $ket_baru = "[DITOLAK] Alasan: " . $alasan . "\n" . $serapan->keterangan_distribusi;

            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'valid_oleh' => null,
                'tgl_valid' => null,
                'keterangan_distribusi' => $ket_baru,
            ]);

            $recipient_id = $serapan->id_anggota ?? DB::table('lahan')->where('id_lahan', $serapan->id_lahan)->value('edit_oleh');

            // Kirim pesan ke pembuat (id_anggota)
            if ($recipient_id) {
                $user = auth()->user();
                $penolak = $user->nama_anggota ?? 'Admin';
                $lahan = DB::table('lahan')->where('id_lahan', $serapan->id_lahan)->first();
                $alamat = $lahan ? ($lahan->alamat_lahan ?? 'Tidak diketahui') : 'Tidak diketahui';

                DB::table('pesans')->insert([
                    'sender_id'    => $user->id_anggota ?? 0,
                    'recipient_id' => $recipient_id,
                    'judul'        => '❌ Penolakan Validasi Data Serapan #' . $id,
                    'isi_pesan'    => "Data serapan hasil yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n📍 **Lokasi Lahan:** {$alamat}\n🆔 **ID Lahan:** #{$serapan->id_lahan}\n\n📝 **Alasan Penolakan:**\n{$alasan}\n\nSilakan perbaiki data dan ajukan kembali.",
                    'is_read'      => false,
                ]);
            }

            AktivitasLog::catat('tolak_validasi', 'serapan', [
                'record_id'   => $id,
                'label_modul' => 'Serapan #' . $id,
                'keterangan'  => 'Tolak validasi serapan #' . $id . '. Alasan: ' . $alasan,
            ]);

            return response()->json(['success' => true, 'message' => 'Validasi serapan berhasil ditolak dan notifikasi telah dikirim.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getValidasiData($id)
    {
        $tanam = DB::table('tanam')->where('id_lahan', $id)->where('is_active', 1)->whereNull('valid_oleh')->get();
        $panen = DB::table('panen')->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')->where('panen.id_lahan', $id)->where('tanam.is_active', 1)->whereNull('panen.valid_oleh')->select('panen.*')->get();
        $serapan = DB::table('distribusi')->join('tanam', 'distribusi.id_tanam', '=', 'tanam.id_tanam')->where('distribusi.id_lahan', $id)->where('tanam.is_active', 1)->whereNull('distribusi.valid_oleh')->select('distribusi.*')->get();
        $hasActive = DB::table('tanam')->where('id_lahan', $id)->where('is_active', 1)->exists();

        return response()->json([
            'tanam' => $tanam,
            'panen' => $panen,
            'serapan' => $serapan,
            'has_active' => $hasActive
        ]);
    }

    public function validasiLahan(Request $request, $id)
    {
        // Hanya admin dan operator polres yang boleh memvalidasi
        if (auth()->user() && auth()->user()->role === 'view') {
            return back()->with('error', 'Anda tidak memiliki izin untuk melakukan validasi.');
        }

        try {
            // Check if there are active tanam
            $activeTanams = DB::table('tanam')->where('id_lahan', $id)->where('is_active', 1)->get();

            if ($activeTanams->isEmpty()) {
                return back()->with('error', 'Tidak ada data siklus berjalan yang bisa divalidasi keseluruhan.');
            }

            foreach ($activeTanams as $t) {
                if (is_null($t->valid_oleh)) {
                    return back()->with('error', 'Masih ada data Tanam yang belum divalidasi.');
                }
                
                $panen = DB::table('panen')->where('id_tanam', $t->id_tanam)->first();
                if (!$panen) {
                    return back()->with('error', 'Siklus belum selesai. Ada Tanam yang belum dicatat Panen-nya.');
                }
                if (is_null($panen->valid_oleh)) {
                    return back()->with('error', 'Masih ada data Panen yang belum divalidasi.');
                }

                $serapan = DB::table('distribusi')->where('id_tanam', $t->id_tanam)->first();
                if (!$serapan) {
                    return back()->with('error', 'Siklus belum selesai. Ada Tanam yang belum dicatat Serapan-nya.');
                }
                if (is_null($serapan->valid_oleh)) {
                    return back()->with('error', 'Masih ada data Serapan yang belum divalidasi.');
                }
            }

            DB::table('tanam')->where('id_lahan', $id)->update(['is_active' => 0]);
            
            return back()->with('success', 'Siklus kelola lahan selesai dan telah diarsipkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }
}
