<?php

namespace App\Http\Controllers\view;

use App\Http\Controllers\Controller;
use App\Models\Komoditi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelolaLahanController extends Controller
{
    // ─── shared scope closure ────────────────────────────────────────────────
    private function makeScope(): array
    {
        $user  = auth()->user();
        $scope = $user->id_tugas ?? '0';

        $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function($q) use ($column, $scope) { $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%'); });
            }
            return $query;
        };

        $applyTingkatScope = function ($query) use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function($q) use ($scope) { $q->where('id_tingkat', $scope)->orWhere('id_tingkat', 'LIKE', $scope . '.%'); });
            }
            return $query;
        };

        return [$scope, $applyScope, $applyTingkatScope];
    }

    // ─── index ───────────────────────────────────────────────────────────────
    private function getIndexData(Request $request, $mode = 'active')
    {
        [$scope, $applyScope, $applyTingkatScope] = $this->makeScope();

        // 1. Dropdowns (scoped)
        $polresList = $applyTingkatScope(DB::table('tingkat'))
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')->get();

        $polsekList = $applyTingkatScope(DB::table('tingkat'))
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
            ->orderBy('id_tingkat')->get();

        $komoditiList = Komoditi::orderBy('jenis_komoditi')->orderBy('nama_komoditi')
            ->get(['id_komoditi', 'jenis_komoditi', 'nama_komoditi']);

        // 2. Filters
        $filters = [
            'resor'    => $request->resor,
            'sektor'   => $request->sektor,
            'jenis'    => $request->jenis,
            'komoditi' => $request->komoditi,
            'start'    => $request->start_date,
            'end'      => $request->end_date,
            'kategori' => $request->kategori ?? 'semua',
            'search'   => $request->search,
        ];

        // 3. Sub-queries for latest cycle data
        if ($mode === 'history') {
            $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam GROUP BY id_lahan)) as t');
        } else {
            $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam WHERE is_active = 1 GROUP BY id_lahan)) as t');
        }
        $latestPanen     = DB::raw('(SELECT * FROM panen WHERE id_panen IN (SELECT MAX(id_panen) FROM panen GROUP BY id_tanam)) as p');
        $latestDistribusi = DB::raw('(SELECT * FROM distribusi WHERE id_distribusi IN (SELECT MAX(id_distribusi) FROM distribusi GROUP BY id_tanam)) as d');

        // 4. Base query (SCOPED)
        $dataQuery = $applyScope(
            DB::table('lahan')->where('lahan.deletestatus', '!=', '0')->whereNotNull('lahan.valid_oleh'),
            'lahan.id_tingkat'
        )
            ->leftJoin('tingkat',   'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah',   'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->leftJoin('anggota',   'lahan.id_anggota', '=', 'anggota.id_anggota')
            ->leftJoin('komoditi',  'lahan.id_komoditi', '=', 'komoditi.id_komoditi')
            ->leftJoin($latestTanam,     'lahan.id_lahan', '=', 't.id_lahan')
            ->leftJoin($latestPanen,     't.id_tanam', '=', 'p.id_tanam')
            ->leftJoin($latestDistribusi, 't.id_tanam', '=', 'd.id_tanam');

        // Apply common filters
        if ($filters['sektor']) {
            $dataQuery->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $dataQuery->where('lahan.id_tingkat', 'LIKE', $filters['resor'] . '%');
        }
        if ($filters['jenis'])    $dataQuery->where('lahan.id_jenis_lahan', $filters['jenis']);
        if ($filters['komoditi']) $dataQuery->where('lahan.id_komoditi', $filters['komoditi']);

        // Date / kategori filters
        $dateField = match($filters['kategori']) {
            'panen'   => 'p.tgl_panen',
            'serapan' => 'd.tgl_distribusi',
            default   => 't.tgl_tanam',
        };
        if ($filters['kategori'] !== 'semua') {
            $targetStage = match($filters['kategori']) { 'tanam' => 0, 'panen' => 1, default => 2 };
            $dataQuery->whereRaw("CASE WHEN t.id_tanam IS NULL THEN 0 WHEN p.id_panen IS NULL THEN 1 WHEN d.id_distribusi IS NULL THEN 2 ELSE 0 END = ?", [$targetStage]);
        }
        if ($filters['start']) $dataQuery->where($dateField, '>=', $filters['start']);
        if ($filters['end'])   $dataQuery->where($dateField, '<=', $filters['end']);

        if ($filters['search']) {
            $s = $filters['search'];
            $wIds = DB::table('wilayah')->where('nama_wilayah', 'LIKE', "%{$s}%")->pluck('id_wilayah')->toArray();
            $dataQuery->where(function($q) use ($s, $wIds) {
                $q->where('wilayah.nama_wilayah',  'LIKE', "%{$s}%")
                  ->orWhere('tingkat.nama_tingkat', 'LIKE', "%{$s}%")
                  ->orWhere('lahan.alamat_lahan',  'LIKE', "%{$s}%")
                  ->orWhere('lahan.cp_polisi',     'LIKE', "%{$s}%")
                  ->orWhere('lahan.cp_lahan',      'LIKE', "%{$s}%")
                  ->orWhere('lahan.poktan',         'LIKE', "%{$s}%");
                foreach ($wIds as $wId) $q->orWhere('lahan.id_wilayah', 'LIKE', $wId . '%');
            });
        }

        // 5. Hierarchical pagination
        $resorBaseQuery = $applyTingkatScope(DB::table('tingkat'))
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'");

        $matchingResors = (clone $dataQuery)
            ->selectRaw("LEFT(lahan.id_tingkat, 5) as resor_id")
            ->distinct()->pluck('resor_id')->toArray();

        $lahanStagesMap = [];
        $data = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        if (!empty($matchingResors) || collect($filters)->filter()->isEmpty()) {
            if (!empty($matchingResors)) $resorBaseQuery->whereIn('id_tingkat', $matchingResors);

            $paginator = $resorBaseQuery->orderBy('id_tingkat')->paginate(5)->appends(request()->query());
            $resorIds  = collect($paginator->items())->pluck('id_tingkat')->toArray();

            $allSektors = DB::table('tingkat')
                ->where(function($q) use ($resorIds) {
                    foreach ($resorIds as $id) $q->orWhere('id_tingkat', 'LIKE', $id . '.%');
                })
                ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
                ->get();

            $allRecordsQuery = (clone $dataQuery)
                ->select(
                    'lahan.*',
                    'tingkat.nama_tingkat', 'wilayah.nama_wilayah',
                    'anggota.nama_anggota', 'komoditi.nama_komoditi', 'komoditi.jenis_komoditi',
                    't.id_tanam', 't.luas_tanam', 't.tgl_tanam', 't.est_awal_panen', 't.est_akhir_panen',
                    'p.id_panen', 'p.total_panen', 'p.tgl_panen', 'p.status_panen', 'p.luas_panen',
                    'd.id_distribusi', 'd.total_distribusi', 'd.tgl_distribusi', 'd.distribusi_ke',
                    'd.valid_oleh as serapan_valid_oleh'
                )
                ->where(function($q) use ($resorIds) {
                    foreach ($resorIds as $id) $q->orWhere('lahan.id_tingkat', 'LIKE', $id . '%');
                });

            $recordsCollection = $allRecordsQuery->get();

            // Resolve kecamatan
            $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');

            // ── Load FULL history per lahan ──────────────────────────────────
            $lahanIds = $recordsCollection->pluck('id_lahan')->unique()->toArray();

            $allTanams = collect();
            if (!empty($lahanIds)) {
                $allTanamsRaw = DB::table('tanam')
                    ->whereIn('id_lahan', $lahanIds)
                    ->where('deletestatus', '1')
                    ->orderBy('id_tanam')
                    ->get();

                $tanamIdsAll = $allTanamsRaw->pluck('id_tanam')->toArray();

                $allPanens = DB::table('panen')
                    ->whereIn('id_tanam', $tanamIdsAll)
                    ->where('deletestatus', '1')
                    ->orderBy('id_panen')
                    ->get()->groupBy('id_tanam');

                $allDistribusis = DB::table('distribusi')
                    ->whereIn('id_tanam', $tanamIdsAll)
                    ->where('deletestatus', '1')
                    ->orderBy('id_distribusi')
                    ->get()->groupBy('id_tanam');

                $allTanams = $allTanamsRaw->map(function($t) use ($allPanens, $allDistribusis) {
                    $t->panens     = $allPanens[$t->id_tanam]     ?? collect();
                    $t->distribusis = $allDistribusis[$t->id_tanam] ?? collect();
                    return $t;
                })->groupBy('id_lahan');
            }

            // Transform records
            $recordsCollection->transform(function($row) use ($wilayahMap, $allTanams) {
                $idW    = $row->id_wilayah ?? '';
                $wParts = explode('.', $idW);
                $kecId  = (count($wParts) >= 3) ? $wParts[0].'.'.$wParts[1].'.'.$wParts[2] : null;
                $row->nama_kecamatan = $kecId ? ($wilayahMap[$kecId] ?? $kecId) : '-';
                $row->history_tanam  = $allTanams[$row->id_lahan] ?? collect();
                return $row;
            });

            // Build stages map
            if (!empty($lahanIds)) {
                $latestTanams = DB::table('tanam')
                    ->select('id_lahan', DB::raw('MAX(id_tanam) as max_id_tanam'))
                    ->whereIn('id_lahan', $lahanIds)
                    ->where('is_active', 1)
                    ->groupBy('id_lahan')
                    ->get()->keyBy('id_lahan');

                $tanamIds2  = $latestTanams->pluck('max_id_tanam')->toArray();
                $panens2    = DB::table('panen')->whereIn('id_tanam', $tanamIds2)->pluck('id_panen', 'id_tanam');
                $distribs2  = DB::table('distribusi')->whereIn('id_tanam', $tanamIds2)->pluck('id_distribusi', 'id_tanam');

                foreach ($lahanIds as $idLahan) {
                    if (!isset($latestTanams[$idLahan])) {
                        $lahanStagesMap[$idLahan] = 0;
                    } else {
                        $idTanam = $latestTanams[$idLahan]->max_id_tanam;
                        $lahanStagesMap[$idLahan] = !isset($panens2[$idTanam]) ? 1
                            : (!isset($distribs2[$idTanam]) ? 2 : 0);
                    }
                }
            }

            // Build hierarchy
            $groupedItems = collect($paginator->items())->map(function($resor) use ($allSektors, $recordsCollection) {
                $resor->sektors = $allSektors->filter(fn($s) => str_starts_with($s->id_tingkat, $resor->id_tingkat . '.'))
                    ->map(function($sektor) use ($recordsCollection) {
                        $sektor->lahans = $recordsCollection->filter(fn($l) => $l->id_tingkat === $sektor->id_tingkat);
                        return $sektor;
                    })->filter(fn($s) => $s->lahans->isNotEmpty());
                return $resor;
            })->filter(fn($r) => $r->sektors->isNotEmpty());

            $paginator->setCollection($groupedItems);
            $data = $paginator;
        }

        // 6. Stats (SCOPED)
        $statsBase = $applyScope(
            DB::table('lahan')->where('deletestatus', '!=', '0')->whereNotNull('valid_oleh'), 'id_tingkat'
        );
        if ($filters['sektor'])      (clone $statsBase)->where('id_tingkat', $filters['sektor']);
        elseif ($filters['resor'])   (clone $statsBase)->where(function($q) use ($filters) { $q->where('id_tingkat', $filters['resor'])->orWhere('id_tingkat', 'LIKE', $filters['resor'] . '.%'); });

        $potensiTotal   = (clone $statsBase)->sum('luas_lahan');
        $potensiDetails = (clone $statsBase)
            ->selectRaw('id_jenis_lahan, SUM(luas_lahan) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Tanam / Panen / Serapan stats via scoped joins
        $tanamStats = $applyScope(
            DB::table('tanam')->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->where('tanam.deletestatus', '1')->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('tanam.valid_oleh')->where('tanam.valid_oleh', '!=', ''),
            'lahan.id_tingkat'
        );
        $tanamTotal   = (clone $tanamStats)->sum('tanam.luas_tanam') ?? 0;
        $tanamDetails = (clone $tanamStats)
            ->selectRaw('lahan.id_jenis_lahan, SUM(tanam.luas_tanam) as total_luas, COUNT(lahan.id_lahan) as total_lokasi')
            ->whereNotNull('lahan.id_jenis_lahan')->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        $panenStats = $applyScope(
            DB::table('panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('panen.deletestatus', '1')->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('panen.valid_oleh')->where('panen.valid_oleh', '!=', ''),
            'lahan.id_tingkat'
        );
        $panenTotal   = (clone $panenStats)->sum('panen.luas_panen') ?? 0;
        $panenTonTotal   = (clone $panenStats)->sum('panen.total_panen') ?? 0;
        $panenDetails = (clone $panenStats)
            ->selectRaw('lahan.id_jenis_lahan, SUM(panen.luas_panen) as total_luas, COUNT(lahan.id_lahan) as total_lokasi')
            ->whereNotNull('lahan.id_jenis_lahan')->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        $serapanStats = $applyScope(
            DB::table('distribusi')->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
                ->where('distribusi.deletestatus', '1')->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('distribusi.valid_oleh')->where('distribusi.valid_oleh', '!=', ''),
            'lahan.id_tingkat'
        );
        $serapanTotal   = (clone $serapanStats)->sum('distribusi.total_distribusi') ?? 0;
        $serapanDetails = (clone $serapanStats)
            ->selectRaw('lahan.id_jenis_lahan, SUM(distribusi.total_distribusi) as total_luas, COUNT(lahan.id_lahan) as total_lokasi')
            ->whereNotNull('lahan.id_jenis_lahan')->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        $jenisLahanList = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',   2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',              4 => 'PESANTREN',
            5 => 'MILIK POLRI',                        6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',           8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA',
        ];

        $stats = [
            'potensi'         => number_format($potensiTotal, 2),
            'tanam'           => number_format($tanamTotal, 2),
            'panen'           => number_format($panenTotal, 2),
            'panen_ton'       => number_format($panenTonTotal, 2),
            'serapan'         => number_format($serapanTotal, 2),
            'potensi_details' => $potensiDetails,
            'tanam_details'   => $tanamDetails,
            'panen_details'   => $panenDetails,
            'serapan_details' => $serapanDetails,
            'jenis_lahan_list'=> $jenisLahanList,
            'mode'            => $mode,
        ];

        return compact(
            'polresList', 'polsekList', 'komoditiList', 'filters', 'stats', 'data', 'lahanStagesMap'
        );
    }

    public function index(Request $request)
    {
        return view('view.kelola-lahan.view_potensi.view_kelola', $this->getIndexData($request, 'active'));
    }

    public function riwayatIndex(Request $request)
    {
        return view('view.kelola-lahan.view_riwayat.index', $this->getIndexData($request, 'history'));
    }

    // ─── potensi index ────────────────────────────────────────────────────────
    public function potensiIndex(Request $request)
    {
        [$scope, $applyScope, $applyTingkatScope] = $this->makeScope();

        $anggotaMap = DB::table('anggota')->pluck('nama_anggota', 'id_anggota');
        $wilayahMap = DB::table('wilayah')->pluck('nama_wilayah', 'id_wilayah');
        $search     = $request->input('search', '');

        $lahanQuery = $applyScope(
            DB::table('lahan')->where('deletestatus', '!=', '0')->orderBy('id_wilayah'),
            'id_tingkat'
        );

        if ($search) {
            $lahanQuery->where(function($q) use ($search, $wilayahMap) {
                $q->where('id_lahan',      $search)
                  ->orWhere('alamat_lahan', 'like', "%{$search}%")
                  ->orWhere('cp_polisi',    'like', "%{$search}%")
                  ->orWhere('cp_lahan',     'like', "%{$search}%")
                  ->orWhere('poktan',       'like', "%{$search}%")
                  ->orWhere('id_wilayah',   'like', "%{$search}%");
                foreach ($wilayahMap as $wId => $wNama) {
                    if (stripos($wNama, $search) !== false) {
                        $q->orWhere('id_wilayah', 'like', "{$wId}%");
                    }
                }
            });
        }

        $lahanList  = $lahanQuery->paginate(25)->appends(request()->query());
        $tingkatMap  = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');
        $komoditiMap = DB::table('komoditi')->get()->keyBy('id_komoditi');

        $lahanList->getCollection()->transform(function($lahan) use ($wilayahMap, $anggotaMap, $tingkatMap, $komoditiMap) {
            $parts    = explode('.', $lahan->id_wilayah ?? '');
            $kabId    = count($parts) >= 2 ? $parts[0].'.'.$parts[1] : ($lahan->id_wilayah ?? '');
            $kecId    = count($parts) >= 3 ? $parts[0].'.'.$parts[1].'.'.$parts[2] : $kabId.'.000';
            $desaNama = $wilayahMap[$lahan->id_wilayah] ?? $lahan->id_wilayah;
            $kecNama  = $wilayahMap[$kecId] ?? $kecId;
            $kabNama  = $wilayahMap[$kabId] ?? $kabId;

            $parts2     = explode('.', $lahan->id_tingkat ?? '');
            $polresId   = count($parts2) >= 2 ? $parts2[0].'.'.$parts2[1] : ($lahan->id_tingkat ?? '');
            $polsekId   = count($parts2) >= 3 ? $lahan->id_tingkat : null;
            $km         = $komoditiMap[$lahan->id_komoditi] ?? null;

            return [
                'id_lahan'          => $lahan->id_lahan,
                'id_tingkat'        => $lahan->id_tingkat,
                'nama_polres'       => $tingkatMap[$polresId] ?? $polresId,
                'nama_polsek'       => $polsekId ? ($tingkatMap[$polsekId] ?? $polsekId) : '-',
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
                'nama_komoditi'     => $km ? ($km->jenis_komoditi.' - '.$km->nama_komoditi) : '-',
                'keterangan_lahan'  => $lahan->keterangan_lahan,
                'dokumentasi_lahan' => $lahan->dokumentasi_lahan,
                'status_lahan'      => $lahan->status_lahan,
                'edit_oleh'         => $lahan->edit_oleh  ? ($anggotaMap[$lahan->edit_oleh]  ?? $lahan->edit_oleh)  : null,
                'tgl_edit'          => $lahan->tgl_edit,
                'valid_oleh'        => $lahan->valid_oleh ? ($anggotaMap[$lahan->valid_oleh] ?? $lahan->valid_oleh) : null,
                'tgl_valid'         => $lahan->tgl_valid,
                'kec_nama'          => $kecNama,
                'desa_nama'         => $desaNama,
                'kab_nama'          => $kabNama,
                'id_wilayah'        => $lahan->id_wilayah,
                'id_komoditi'       => $lahan->id_komoditi,
                'wilayah_label'     => "Desa {$desaNama} Kecamatan {$kecNama} Kabupaten {$kabNama}",
            ];
        });

        $tingkatSemua  = $applyTingkatScope(DB::table('tingkat'))->get();
        $polresList    = $tingkatSemua->filter(fn($t) => substr_count($t->id_tingkat, '.') == 1)->values();
        $polsekList    = $tingkatSemua->filter(fn($t) => substr_count($t->id_tingkat, '.') == 2)->values();
        $komoditiList  = DB::table('komoditi')->where('deletestatus', '!=', '0')->get();
        $wilayahSemua  = DB::table('wilayah')->get();
        $kabupatenList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 1)->values();
        $kecamatanList = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 2)->values();
        $desaList      = $wilayahSemua->filter(fn($w) => substr_count($w->id_wilayah, '.') == 3)->values();
        $anggotaList   = DB::table('anggota')->where('deletestatus', '!=', '0')
            ->select('id_anggota', 'nama_anggota', 'no_telp_anggota')->get();

        $kategoriMapping = [
            1 => 'PRODUKTIF (POKTAN BINAAN POLRI)',   2 => 'HUTAN (PERHUTANAN SOSIAL)',
            3 => 'LUAS BAKU SAWAH (LBS)',              4 => 'PESANTREN',
            5 => 'MILIK POLRI',                        6 => 'PRODUKTIF (MASYARAKAT BINAAN POLRI)',
            7 => 'PRODUKTIF (TUMPANG SARI)',           8 => 'HUTAN (PERHUTANI/INHUTANI)',
            9 => 'LAHAN LAINNYA',
        ];

        return view('view.kelola-lahan.potensi.index', compact(
            'lahanList', 'polresList', 'polsekList', 'kategoriMapping',
            'komoditiList', 'kabupatenList', 'kecamatanList', 'desaList', 'anggotaList',
        ));
    }
}

