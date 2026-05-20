<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Komoditi;
use App\Models\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KelolaLahanController extends Controller
{
    /**
     * Kirim notifikasi ke semua Operator Polres di wilayah lahan
     * ketika Operator Polsek memperbaiki data yang ditolak.
     */
    private function notifikasiPerbaikkanKePolres($lahan, string $tahap, $idLahan): void
    {
        if (!$lahan) return;

        $editor = auth()->user();
        $namaEditor = $editor->nama_anggota ?? $editor->username ?? 'Operator';
        $alamat = $lahan->alamat_lahan ?? '-';

        // Cari semua Operator Polres di wilayah induk lahan
        // id_tugas Polsek = X.XX.YY → Polresnya = X.XX
        $tingkat = $lahan->id_tingkat ?? '';
        $parts = explode('.', $tingkat);
        $polresTugas = count($parts) >= 3
            ? $parts[0] . '.' . $parts[1]
            : $tingkat;

        $recipients = DB::table('anggota')
            ->where('id_tugas', $polresTugas)
            ->where('role', 'operator')
            ->pluck('id_anggota')
            ->toArray();

        $judul   = '✅ Data ' . $tahap . ' Diperbaiki - Perlu Validasi';
        $isiPesan = "Operator Polsek **{$namaEditor}** telah memperbaiki data **{$tahap}** yang sebelumnya ditolak.\n\n" .
                    "📍 **Lokasi Lahan:** {$alamat}\n" .
                    "🆔 **ID Lahan:** #{$idLahan}\n\n" .
                    "Silakan cek dan lakukan validasi data tersebut.";

        foreach ($recipients as $recipientId) {
            Pesan::create([
                'id_pesan'     => Str::uuid(),
                'sender_id'    => $editor->id_anggota ?? 0,
                'recipient_id' => $recipientId,
                'judul'        => $judul,
                'isi_pesan'    => $isiPesan,
                'is_read'      => false,
            ]);
        }
    }

    private function getIndexData(Request $request, $mode = 'active')
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

        if ($mode === 'history') {
            $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam GROUP BY id_lahan)) as t');
        } else {
            $latestTanam = DB::raw('(SELECT * FROM tanam WHERE id_tanam IN (SELECT MAX(id_tanam) FROM tanam WHERE is_active = 1 GROUP BY id_lahan)) as t');
        }
        $latestPanen = DB::raw('(SELECT * FROM panen WHERE id_panen IN (SELECT MAX(id_panen) FROM panen GROUP BY id_tanam)) as p');
        $latestDistribusi = DB::raw('(SELECT * FROM distribusi WHERE id_distribusi IN (SELECT MAX(id_distribusi) FROM distribusi GROUP BY id_panen)) as d');

        // 3. Build Base Data Query (Applying Filters)
        $dataQuery = $applyScope(DB::table('lahan')->where('lahan.deletestatus', '!=', '0')->whereNotNull('lahan.valid_oleh'))
            ->leftJoin('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->leftJoin('anggota', 'lahan.id_anggota', '=', 'anggota.id_anggota')
            ->leftJoin('komoditi', 'lahan.id_komoditi', '=', 'komoditi.id_komoditi')
            ->leftJoin($latestTanam, 'lahan.id_lahan', '=', 't.id_lahan')
            ->leftJoin($latestPanen, 't.id_tanam', '=', 'p.id_tanam')
            ->leftJoin($latestDistribusi, 'p.id_panen', '=', 'd.id_panen');

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
                    't.keterangan_tanam',
                    't.valid_oleh as tanam_valid_oleh',
                    't.alasan_tolak as tanam_alasan_tolak',
                    'p.id_panen',
                    'p.total_panen',
                    'p.tgl_panen',
                    'p.status_panen',
                    'p.luas_panen',
                    'p.ket_panen',
                    'p.valid_oleh as panen_valid_oleh',
                    'p.alasan_tolak as panen_alasan_tolak',
                    'd.id_distribusi',
                    'd.total_distribusi',
                    'd.tgl_distribusi',
                    'd.distribusi_ke',
                    'd.keterangan_distribusi',
                    'd.valid_oleh as serapan_valid_oleh',
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

        // 5. Calculate Stats (Aggregated) — scoped to operator's jurisdiction first
        $statsData = $applyScope(DB::table('lahan')->where('deletestatus', '!=', '0')->whereNotNull('valid_oleh'), 'id_tingkat');
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
        $tanamQuery = $applyScope(
            DB::table('tanam')->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->where('tanam.deletestatus', '!=', '0')->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('tanam.valid_oleh')->where('tanam.valid_oleh', '!=', ''),
            'lahan.id_tingkat'
        );
        if ($filters['sektor']) {
            $tanamQuery->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $tanamQuery->where(function($q) use ($filters) { $q->where('lahan.id_tingkat', $filters['resor'])->orWhere('lahan.id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
        }
        $tanamTotal = (clone $tanamQuery)->sum('tanam.luas_tanam') ?? 0;
        $tanamDetails = (clone $tanamQuery)->selectRaw('lahan.id_jenis_lahan, SUM(tanam.luas_tanam) as total_luas, COUNT(lahan.id_lahan) as total_lokasi')
            ->whereNotNull('lahan.id_jenis_lahan')
            ->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Panen Stats — scoped
        $panenQuery = $applyScope(
            DB::table('panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('panen.deletestatus', '!=', '0')->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('panen.valid_oleh')->where('panen.valid_oleh', '!=', ''),
            'lahan.id_tingkat'
        );
        if ($filters['sektor']) {
            $panenQuery->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $panenQuery->where(function($q) use ($filters) { $q->where('lahan.id_tingkat', $filters['resor'])->orWhere('lahan.id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
        }
        $panenTotal = (clone $panenQuery)->sum('panen.luas_panen') ?? 0;
        $panenTonTotal = (clone $panenQuery)->sum('panen.total_panen') ?? 0;
        $panenDetails = (clone $panenQuery)->selectRaw('lahan.id_jenis_lahan, SUM(panen.luas_panen) as total_luas, COUNT(lahan.id_lahan) as total_lokasi')
            ->whereNotNull('lahan.id_jenis_lahan')
            ->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // Serapan Stats — scoped
        $serapanQuery = $applyScope(
            DB::table('distribusi')
                ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('distribusi.deletestatus', '!=', '0')->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('distribusi.valid_oleh')->where('distribusi.valid_oleh', '!=', ''),
            'lahan.id_tingkat'
        );
        if ($filters['sektor']) {
            $serapanQuery->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $serapanQuery->where(function($q) use ($filters) { $q->where('lahan.id_tingkat', $filters['resor'])->orWhere('lahan.id_tingkat', 'LIKE', $filters['resor'] . '.%'); });
        }
        $serapanTotal = (clone $serapanQuery)->sum('distribusi.total_distribusi') ?? 0;
        $serapanDetails = (clone $serapanQuery)->selectRaw('lahan.id_jenis_lahan, SUM(distribusi.total_distribusi) as total_luas, COUNT(lahan.id_lahan) as total_lokasi')
            ->whereNotNull('lahan.id_jenis_lahan')
            ->groupBy('lahan.id_jenis_lahan')
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
            'panen_ton' => number_format($panenTonTotal, 2),
            'serapan' => number_format($serapanTotal, 2),
            'potensi_details' => $potensiDetails,
            'tanam_details' => $tanamDetails,
            'panen_details' => $panenDetails,
            'serapan_details' => $serapanDetails,
            'jenis_lahan_list' => $jenisLahanList,
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

        $harvestQuery = $applyScope(
            DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
                ->leftJoin('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
                ->whereNotNull('tanam.est_awal_panen')
                ->where('tanam.deletestatus', '!=', '0')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('lahan.valid_oleh'),
            'lahan.id_tingkat'
        );

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

        $scope = $this->getScope();
        $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function ($q) use ($column, $scope) {
                    $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%');
                });
            }
            return $query;
        };

        // ── SERAPAN DISTRIBUSI BREAKDOWN ────────────────────────────────
        $serapanBreakdown = $applyScope(
            DB::table('distribusi')
                ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->whereNotNull('distribusi.valid_oleh')
                ->where('distribusi.valid_oleh', '!=', '')
                ->where('distribusi.deletestatus', '!=', '0')
                ->where('lahan.deletestatus', '!=', '0'),
            'lahan.id_tingkat'
        )
        ->selectRaw('distribusi_ke, SUM(total_distribusi) as total')
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

        return view('operator.kelola-lahan.operator_riwayat.index', array_merge($baseData, [
            'serapanChartData'  => $serapanChartData,
        ]));
    }

    public function poktanIndex(Request $request)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';

        $applyTingkatScope = function ($query) use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function ($q) use ($scope) {
                    $q->where(function($q) use ($scope) { $q->where('id_tingkat', $scope)->orWhere('id_tingkat', 'LIKE', $scope . '.%'); })
                        ->orWhereRaw("? = id_tingkat OR ? LIKE CONCAT(id_tingkat, '.%')", [$scope, $scope]);
                });
            }
            return $query;
        };

        $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function($q) use ($column, $scope) { $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%'); });
            }
            return $query;
        };

        $polresQuery = $applyTingkatScope(DB::table('tingkat'))->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+$'");
        $polsekQuery = $applyTingkatScope(DB::table('tingkat'))->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'");

        $polresList = $polresQuery->orderBy('id_tingkat')->get();
        $polsekList = $polsekQuery->orderBy('id_tingkat')->get();

        $filters = [
            'resor'  => $request->resor,
            'sektor' => $request->sektor,
            'search' => $request->search
        ];

        $applyPoktanScope = function ($query) use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function($q) use ($scope) {
                    $q->where('lahan.id_tingkat', $scope)
                      ->orWhere('lahan.id_tingkat', 'LIKE', $scope . '.%');
                });
            }
            return $query;
        };

        $query = $applyPoktanScope(DB::table('lahan'))
            ->where('lahan.deletestatus', '!=', '0')
            ->whereNotNull('lahan.id_poktan')
            ->whereNotNull('lahan.valid_oleh')
            ->join('poktan', 'lahan.id_poktan', '=', 'poktan.id_poktan')
            ->select(
                'poktan.nama_poktan',
                DB::raw('MAX(poktan.id_polda) as id_polda'),
                DB::raw('MAX(poktan.id_polres) as id_polres'),
                DB::raw('MAX(poktan.id_polsek) as id_polsek'),
                DB::raw('SUM(lahan.luas_lahan) as luas_lahan'),
                DB::raw('MAX(lahan.latitude) as latitude'),
                DB::raw('MAX(lahan.longitude) as longitude'),
                DB::raw('COUNT(lahan.id_lahan) as jumlah_lokasi')
            )
            ->groupBy('poktan.nama_poktan');

        if ($filters['sektor']) {
            $query->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $query->where('lahan.id_tingkat', 'LIKE', $filters['resor'] . '%');
        }

        if ($filters['search']) {
            $query->where('poktan.nama_poktan', 'LIKE', '%' . $filters['search'] . '%');
        }

        $data = $query->orderBy('poktan.nama_poktan')->paginate(20)->withQueryString();

        $displayedNames = $data->pluck('nama_poktan')->filter()->unique();
        
        $detailsQuery = DB::table('lahan')
            ->whereIn('poktan.nama_poktan', $displayedNames)
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
                'poktan.id_polsek'
            );
        if ($filters['sektor']) {
            $detailsQuery->where('lahan.id_tingkat', $filters['sektor']);
        } elseif ($filters['resor']) {
            $detailsQuery->where('lahan.id_tingkat', 'LIKE', $filters['resor'] . '%');
        }
        $details = $detailsQuery->get()->groupBy('nama_poktan');

        $tingkatMap = DB::table('tingkat')->pluck('nama_tingkat', 'id_tingkat');

        return view('admin.kelola-lahan.poktan.index', compact('data', 'polresList', 'polsekList', 'filters', 'tingkatMap'));
    }

    private function getScope()

    {
        $user   = auth()->user();
        return $user->id_tugas ?? '0';
    }

    private function getApplyScope()
    {
        $scope = $this->getScope();
        return function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where(function ($q) use ($column, $scope) {
                    $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%');
                });
            }
            return $query;
        };
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
            DB::transaction(function () use ($request, $lahan) {
                $newId = DB::table('tanam')->max('id_tanam') + 1;
                $idAnggota = auth()->id();

                // ── VALIDASI KAPASITAS LAHAN ──
                $luasLahan = (float)($lahan->luas_lahan ?? 0);
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
                $idTanam = $request->id_tanam ?? DB::table('tanam')->where('id_lahan', $request->id_lahan)->where('is_active', 1)->orderByDesc('id_tanam')->value('id_tanam') ?? 0;

                // ── VALIDASI: Tanam harus sudah divalidasi oleh Polres/Admin ──
                if ($idTanam) {
                    $tanam = DB::table('tanam')->where('id_tanam', $idTanam)->first();
                    if (!$tanam || !$tanam->valid_oleh) {
                        throw new \Exception('Data tanam pada lahan ini belum divalidasi oleh Polres/Admin. Harap tunggu validasi tanam terlebih dahulu sebelum menginput panen.');
                    }

                    // ── VALIDASI: Total luas panen tidak melebihi luas tanam ──
                    $luasTanam = (float)$tanam->luas_tanam;
                    $totalPanenSebelumnya = (float)DB::table('panen')->where('id_tanam', $idTanam)->sum('luas_panen');
                    $luasPanenBaru = (float)$request->luas_panen;
                    $sisaPanen = $luasTanam - $totalPanenSebelumnya;

                    if ($luasPanenBaru > $sisaPanen) {
                        throw new \Exception(
                            "Luas panen melebihi sisa luas tanam! " .
                            "Luas tanam: {$luasTanam} Ha | " .
                            "Sudah dipanen: " . number_format($totalPanenSebelumnya, 2) . " Ha | " .
                            "Sisa tersedia: " . number_format($sisaPanen, 2) . " Ha."
                        );
                    }
                } else {
                    throw new \Exception('Tidak ada data tanam aktif untuk lahan ini. Harap input tanam terlebih dahulu.');
                }
                // ── AKHIR VALIDASI ──

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
            'id_panen' => 'required|integer',
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
                $targetPanen = DB::table('panen')->where('id_panen', $request->id_panen)->first();

                if (!$targetPanen) {
                    throw new \Exception('Data panen tidak ditemukan.');
                }

                // ── VALIDASI: Panen harus sudah divalidasi oleh admin/polres ──
                if (!$targetPanen->valid_oleh) {
                    throw new \Exception('Data panen ini belum divalidasi oleh admin/polres. Harap tunggu validasi panen terlebih dahulu sebelum menginput serapan.');
                }
                
                $totalSerapanSebelumnya = (float)DB::table('distribusi')->where('id_panen', $targetPanen->id_panen)->sum('total_distribusi');
                $sisaTon = (float)$targetPanen->total_panen - $totalSerapanSebelumnya;
                if ((float)$request->total_distribusi > $sisaTon) {
                    throw new \Exception("Jumlah serapan melebihi hasil panen! Hasil panen: " . number_format($targetPanen->total_panen, 2) . " TON. Sisa yang belum diserap: " . number_format($sisaTon, 2) . " TON.");
                }
                // ── AKHIR VALIDASI ──

                DB::table('distribusi')->insert([
                    'id_distribusi' => $newId,
                    'id_lahan' => $request->id_lahan,
                    'id_panen' => $targetPanen->id_panen,
                    'id_tanam' => $targetPanen->id_tanam,
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
            // Cek apakah data sebelumnya ditolak
            $oldTanam = DB::table('tanam')->where('id_tanam', $id)->first();
            $wasTolak = $oldTanam && !empty($oldTanam->alasan_tolak);

            DB::table('tanam')->where('id_tanam', $id)->update([
                'tgl_tanam'        => $request->tgl_tanam,
                'luas_tanam'       => $request->luas_tanam,
                'nama_bibit'       => $request->jenis_bibit,
                'kebutuhan_bibit'  => $request->kebutuhan_bibit,
                'est_awal_panen'   => $request->est_awal_panen,
                'est_akhir_panen'  => $request->est_akhir_panen,
                'keterangan_tanam' => preg_replace('/^\[DITOLAK\].*?\n/s', '', $request->keterangan_tanam),
                'edit_oleh'        => auth()->user()->username ?? 'operator',
                'tgl_edit'         => now(),
                'valid_oleh'       => null,
                'tgl_valid'        => null,
                'alasan_tolak'     => null, // Hapus alasan tolak saat diperbaiki
            ]);

            // Kirim notifikasi ke Polres jika data sebelumnya ditolak
            if ($wasTolak) {
                $lahan = DB::table('lahan')->where('id_lahan', $oldTanam->id_lahan)->first();
                $this->notifikasiPerbaikkanKePOlres($lahan, 'Tanam', $oldTanam->id_lahan);
            }

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
            // Cek apakah data sebelumnya ditolak
            $oldPanen = DB::table('panen')->where('id_panen', $id)->first();
            $wasTolak = $oldPanen && !empty($oldPanen->alasan_tolak);

            DB::table('panen')->where('id_panen', $id)->update([
                'tgl_panen'    => $request->tgl_panen,
                'luas_panen'   => $request->luas_panen,
                'total_panen'  => $request->total_panen,
                'status_panen' => $request->status_panen,
                'ket_panen'    => preg_replace('/^\[DITOLAK\].*?\n/s', '', $request->keterangan_panen),
                'edit_oleh'    => auth()->user()->username ?? 'operator',
                'tgl_edit'     => now(),
                'valid_oleh'   => null,
                'tgl_valid'    => null,
                'alasan_tolak' => null, // Hapus alasan tolak saat diperbaiki
            ]);

            // Kirim notifikasi ke Polres jika data sebelumnya ditolak
            if ($wasTolak) {
                $lahan = DB::table('lahan')->where('id_lahan', $oldPanen->id_lahan)->first();
                $this->notifikasiPerbaikkanKePOlres($lahan, 'Panen', $oldPanen->id_lahan);
            }

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
        $serapan = DB::table('distribusi')->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->where('id_distribusi', $id)->first(['lahan.id_tingkat']);
        if (!$serapan || ($scope && $scope != '0' && ((string)$serapan->id_tingkat !== (string)$scope && !str_starts_with((string)$serapan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            // Cek apakah data sebelumnya ditolak
            $oldSerapan = DB::table('distribusi')->where('id_distribusi', $id)->first();
            $wasTolak = $oldSerapan && !empty($oldSerapan->alasan_tolak);

            if ($oldSerapan && $oldSerapan->id_panen) {
                $panenForSerapan = DB::table('panen')->where('id_panen', $oldSerapan->id_panen)->first();
                if ($panenForSerapan) {
                    $totalSerapanSebelumnya = (float)DB::table('distribusi')
                        ->where('id_panen', $oldSerapan->id_panen)
                        ->where('id_distribusi', '!=', $id)
                        ->sum('total_distribusi');
                    $sisaTon = (float)$panenForSerapan->total_panen - $totalSerapanSebelumnya;
                    if ((float)$request->total_distribusi > $sisaTon) {
                        throw new \Exception("Jumlah serapan melebihi hasil panen! Hasil panen: " . number_format($panenForSerapan->total_panen, 2) . " TON. Sisa yang belum diserap: " . number_format($sisaTon, 2) . " TON.");
                    }
                }
            }

            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'tgl_distribusi'       => $request->tgl_distribusi,
                'total_distribusi'     => $request->total_distribusi,
                'distribusi_ke'        => $request->distribusi_ke,
                'keterangan_distribusi'=> preg_replace('/^\[DITOLAK\].*?\n/s', '', $request->keterangan_serapan),
                'edit_oleh'            => auth()->user()->username ?? 'operator',
                'tgl_edit'             => now(),
                'valid_oleh'           => null,
                'tgl_valid'            => null,
                'alasan_tolak'         => null, // Hapus alasan tolak saat diperbaiki
            ]);

            // Kirim notifikasi ke Polres jika data sebelumnya ditolak
            if ($wasTolak) {
                $lahan = DB::table('lahan')->where('id_lahan', $oldSerapan->id_lahan)->first();
                $this->notifikasiPerbaikkanKePOlres($lahan, 'Serapan', $oldSerapan->id_lahan);
            }

            return response()->json(['success' => true, 'message' => 'Data Serapan berhasil diperbarui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()], 500);
        }
    }

    public function unvalidasiTanam(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }
        DB::table('tanam')->where('id_tanam', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Data Tanam berhasil di-unvalidasi']);
        return back()->with('success', 'Data Tanam berhasil di-unvalidasi');
    }

    public function unvalidasiPanen(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }
        DB::table('panen')->where('id_panen', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Data Panen berhasil di-unvalidasi']);
        return back()->with('success', 'Data Panen berhasil di-unvalidasi');
    }

    public function unvalidasiSerapan(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }
        DB::table('distribusi')->where('id_distribusi', $id)->update(['valid_oleh' => null, 'tgl_valid' => null]);
        if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Data Serapan berhasil di-unvalidasi']);
        return back()->with('success', 'Data Serapan berhasil di-unvalidasi');
    }

    public function validasiTanam(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        try {
            DB::table('tanam')->where('id_tanam', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'operator',
                'tgl_valid'  => now(),
            ]);
            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Data Tanam berhasil divalidasi']);
            return back()->with('success', 'Data Tanam berhasil divalidasi');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function validasiPanen(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        try {
            DB::table('panen')->where('id_panen', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'operator',
                'tgl_valid'  => now(),
            ]);
            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Data Panen berhasil divalidasi']);
            return back()->with('success', 'Data Panen berhasil divalidasi');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function validasiSerapan(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.'], 403);
            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');
        }

        $scope = $user->id_tugas ?? '0';
        $serapan = DB::table('distribusi')->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->where('id_distribusi', $id)->first(['lahan.id_tingkat']);
        if (!$serapan || ($scope && $scope != '0' && ((string)$serapan->id_tingkat !== (string)$scope && !str_starts_with((string)$serapan->id_tingkat, (string)$scope . '.')))) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
            return back()->with('error', 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!');
        }

        try {
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'valid_oleh' => auth()->user()->username ?? 'operator',
                'tgl_valid'  => now(),
            ]);
            if ($request->wantsJson()) return response()->json(['success' => true, 'message' => 'Data Serapan berhasil divalidasi']);
            return back()->with('success', 'Data Serapan berhasil divalidasi');
        } catch (\Exception $e) {
            if ($request->wantsJson()) return response()->json(['success' => false, 'message' => 'Gagal memvalidasi: ' . $e->getMessage()], 500);
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    public function destroyTanam($id)
    {
        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';
        $tanam = DB::table('tanam')->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')->where('tanam.id_tanam', $id)->first(['lahan.id_tingkat', 'tanam.id_tanam']);
        if (!$tanam || ($scope && $scope != '0' && ((string)$tanam->id_tingkat !== (string)$scope && !str_starts_with((string)$tanam->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($id) {
                $panenIds = DB::table('panen')->where('id_tanam', $id)->pluck('id_panen')->toArray();
                if (!empty($panenIds)) {
                    DB::table('distribusi')->whereIn('id_panen', $panenIds)->delete();
                }
                DB::table('panen')->where('id_tanam', $id)->delete();
                DB::table('tanam')->where('id_tanam', $id)->delete();
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
        $panen = DB::table('panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->where('panen.id_panen', $id)->first(['lahan.id_tingkat', 'panen.id_panen']);
        if (!$panen || ($scope && $scope != '0' && ((string)$panen->id_tingkat !== (string)$scope && !str_starts_with((string)$panen->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::transaction(function () use ($id) {
                DB::table('distribusi')->where('id_panen', $id)->delete();
                DB::table('panen')->where('id_panen', $id)->delete();
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
        $serapan = DB::table('distribusi')->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->where('distribusi.id_distribusi', $id)->first(['lahan.id_tingkat', 'distribusi.id_distribusi']);
        if (!$serapan || ($scope && $scope != '0' && ((string)$serapan->id_tingkat !== (string)$scope && !str_starts_with((string)$serapan->id_tingkat, (string)$scope . '.')))) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak: Data ini berada di luar wilayah tugas Anda!'], 403);
        }

        try {
            DB::table('distribusi')->where('id_distribusi', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Data Serapan berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiTanam(Request $request, $id)
    {
        $user = auth()->user();
        // Hanya Polres (1 titik) yang bisa menolak, bukan Polsek (2 titik)
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya Polres atau Admin yang bisa menolak data.'], 403);
        }
        $alasan = "Data ditolak oleh Polres. Silakan perbaiki data dan ajukan kembali.";

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->first();
            if (!$tanam) return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan.'], 404);

            $lahan = DB::table('lahan')->where('id_lahan', $tanam->id_lahan)->first();
            $alamat = $lahan ? ($lahan->alamat_lahan ?? '-') : '-';
            $penolak = $user->nama_anggota ?? ($user->username ?? 'Operator Polres');

            // Update: tandai sebagai ditolak, hapus validasi jika ada
            DB::table('tanam')->where('id_tanam', $id)->update([
                'alasan_tolak'     => $alasan,
                'keterangan_tanam' => '[DITOLAK] Alasan: ' . $alasan,
                'valid_oleh'       => null,
                'tgl_valid'        => null,
                'tgl_edit'         => now(),
            ]);

            // Kirim notifikasi ke operator pembuat data
            $recipient_id = $tanam->id_anggota ?? null;
            if (!$recipient_id) {
                $editOleh = $tanam->edit_oleh ?? ($lahan->edit_oleh ?? null);
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
                    'judul'        => '❌ Data Tanam Ditolak - Lahan #' . $tanam->id_lahan,
                    'isi_pesan'    => "Data tanam yang Anda laporkan telah **DITOLAK** oleh {$penolak}.\n\n" .
                                      "📍 **Lokasi Lahan:** {$alamat}\n" .
                                      "🆔 **ID Lahan:** #{$tanam->id_lahan}\n\n" .
                                      "📝 **Alasan Penolakan:**\n{$alasan}\n\n" .
                                      "Silakan perbaiki data dan ajukan kembali.",
                    'is_read'      => false,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Data tanam berhasil ditolak dan notifikasi telah dikirim ke pembuat data.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiPanen(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya Polres atau Admin yang bisa menolak data.'], 403);
        }
        $alasan = "Data ditolak oleh Polres. Silakan perbaiki data dan ajukan kembali.";

        try {
            $panen = DB::table('panen')->where('id_panen', $id)->first();
            if (!$panen) return response()->json(['success' => false, 'message' => 'Data Panen tidak ditemukan.'], 404);

            $lahan = DB::table('lahan')->where('id_lahan', $panen->id_lahan)->first();
            $alamat = $lahan ? ($lahan->alamat_lahan ?? '-') : '-';
            $penolak = $user->nama_anggota ?? ($user->username ?? 'Operator Polres');

            // Update: tandai sebagai ditolak, hapus validasi jika ada
            DB::table('panen')->where('id_panen', $id)->update([
                'alasan_tolak'     => $alasan,
                'ket_panen'        => '[DITOLAK] Alasan: ' . $alasan,
                'valid_oleh'       => null,
                'tgl_valid'        => null,
                'tgl_edit'         => now(),
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

            return response()->json(['success' => true, 'message' => 'Data panen berhasil ditolak dan notifikasi telah dikirim ke pembuat data.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function tolakValidasiSerapan(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya Polres atau Admin yang bisa menolak data.'], 403);
        }
        $alasan = "Data ditolak oleh Polres. Silakan perbaiki data dan ajukan kembali.";

        try {
            $serapan = DB::table('distribusi')->where('id_distribusi', $id)->first();
            if (!$serapan) return response()->json(['success' => false, 'message' => 'Data Serapan tidak ditemukan.'], 404);

            $lahan = DB::table('lahan')->where('id_lahan', $serapan->id_lahan)->first();
            $alamat = $lahan ? ($lahan->alamat_lahan ?? '-') : '-';
            $penolak = $user->nama_anggota ?? ($user->username ?? 'Operator Polres');

            // Update: tandai sebagai ditolak, hapus validasi jika ada
            DB::table('distribusi')->where('id_distribusi', $id)->update([
                'alasan_tolak'          => $alasan,
                'keterangan_distribusi' => '[DITOLAK] Alasan: ' . $alasan,
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

            return response()->json(['success' => true, 'message' => 'Data serapan berhasil ditolak dan notifikasi telah dikirim ke pembuat data.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
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
        $hasActive = DB::table('tanam')->where('id_lahan', $id)->where('is_active', 1)->exists();

        return response()->json([
            'lahan'      => $data,
            'tanam'      => $tanam,
            'panen'      => $panen,
            'serapan'    => $serapan,
            'has_active' => $hasActive
        ]);
    }

    public function selesaiSiklusTanam(Request $request, $id)
    {
        $user = auth()->user();
        if ($user && substr_count((string)$user->id_tugas, '.') >= 2) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menyelesaikan siklus.'], 403);
        }

        try {
            $tanam = DB::table('tanam')->where('id_tanam', $id)->where('is_active', 1)->first();

            if (!$tanam) {
                return response()->json(['success' => false, 'message' => 'Data Tanam tidak ditemukan atau sudah tidak aktif.'], 422);
            }

            if (is_null($tanam->valid_oleh)) {
                return response()->json(['success' => false, 'message' => 'Data Tanam belum divalidasi.'], 422);
            }
            
            $panen = DB::table('panen')->where('id_tanam', $id)->first();
            if (!$panen) {
                return response()->json(['success' => false, 'message' => 'Siklus belum selesai. Panen belum dicatat.'], 422);
            }
            if (is_null($panen->valid_oleh)) {
                return response()->json(['success' => false, 'message' => 'Data Panen belum divalidasi.'], 422);
            }

            $serapan = DB::table('distribusi')
                ->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')
                ->where('panen.id_tanam', $id)
                ->select('distribusi.*')
                ->first();
            if (!$serapan) {
                return response()->json(['success' => false, 'message' => 'Siklus belum selesai. Serapan belum dicatat.'], 422);
            }
            if (is_null($serapan->valid_oleh)) {
                return response()->json(['success' => false, 'message' => 'Data Serapan belum divalidasi.'], 422);
            }

            DB::table('tanam')->where('id_tanam', $id)->update(['is_active' => 0]);
            
            return response()->json(['success' => true, 'message' => 'Siklus Tanam ini selesai dan telah diarsipkan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan siklus: ' . $e->getMessage()], 500);
        }
    }
}
