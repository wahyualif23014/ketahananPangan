<?php

namespace App\Http\Controllers\view;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $quarterFilter = $request->input('quarter', 'all');
        $yearFilter    = $request->input('year', null);

        // ── Jurisdictional Scope (sama persis pola Operator) ─────────────────
        $user  = auth()->user();
        $scope = $user->id_tugas ?? '0';

        // Closure scope — berlaku untuk semua query
        $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where($column, 'LIKE', $scope . '%');
            }
            return $query;
        };

        // Nama wilayah user view (untuk display di header)
        $userWilayahLabel = DB::table('tingkat')
            ->where('id_tingkat', $scope)
            ->value('nama_tingkat') ?? 'Semua Wilayah';

        // ── Auto-detect tahun terbaru dalam scope ────────────────────────────
        if (!$yearFilter) {
            $yearDetect = DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('panen.deletestatus', '1');
            $yearDetect = $applyScope($yearDetect, 'lahan.id_tingkat');
            $detectedYear = $yearDetect->max(DB::raw('YEAR(panen.tgl_panen)'));

            if (!$detectedYear) {
                $yearDetect2 = DB::table('tanam')
                    ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                    ->where('tanam.deletestatus', '1');
                $yearDetect2 = $applyScope($yearDetect2, 'lahan.id_tingkat');
                $detectedYear = $yearDetect2->max(DB::raw('YEAR(tanam.tgl_tanam)'));
            }
            $yearFilter = $detectedYear ?? date('Y');
        }

        // ── 1. KPI Summary ──────────────────────────────────────────────────
        $potensiTotal = $applyScope(
            DB::table('lahan')->where('deletestatus', '!=', '0'), 'id_tingkat'
        )->sum('luas_lahan');

        $tanamQuery = $applyScope(
            DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->where('tanam.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereYear('tanam.tgl_tanam', $yearFilter),
            'lahan.id_tingkat'
        );

        $panenQuery = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereYear('panen.tgl_panen', $yearFilter),
            'lahan.id_tingkat'
        );

        if ($quarterFilter != 'all') {
            $tanamQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]);
            $panenQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }

        $tanamTotal = $tanamQuery->sum('tanam.luas_tanam');
        $panenTotal = $panenQuery->sum('panen.luas_panen');

        $totalTitikLahan = $applyScope(
            DB::table('lahan')->where('deletestatus', '!=', '0'), 'id_tingkat'
        )->count();

        $totalPolsek = $applyScope(
            DB::table('lahan')->where('deletestatus', '!=', '0'), 'id_tingkat'
        )->distinct('id_tingkat')->count('id_tingkat');
        $polsekAktif = $totalPolsek;

        // Denominator donut charts (scoped)
        $totalPolsekInScope = max(
            $applyScope(DB::table('tingkat'), 'id_tingkat')->count(),
            $polsekAktif
        );
        $totalLahanAll = max(
            $applyScope(DB::table('lahan'), 'id_tingkat')->count(),
            $totalTitikLahan
        );

        // ── 2. Master Jenis Lahan ──────────────────────
        $jenisLahanList = DB::table('jenislahan')->pluck('nama_jenis_lahan', 'id_jenis_lahan');

        // ── 3. Details per jenis lahan ──────────────────────────────────────
        $potensiDetails = $applyScope(
            DB::table('lahan')
                ->select('id_jenis_lahan', DB::raw('SUM(luas_lahan) as total_luas'), DB::raw('COUNT(id_lahan) as total_lokasi'))
                ->where('deletestatus', '!=', '0'),
            'id_tingkat'
        )->groupBy('id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        $tanamDetailsQuery = $applyScope(
            DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->select('lahan.id_jenis_lahan', DB::raw('SUM(tanam.luas_tanam) as total_luas'), DB::raw('COUNT(tanam.id_tanam) as total_lokasi'))
                ->where('tanam.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereYear('tanam.tgl_tanam', $yearFilter),
            'lahan.id_tingkat'
        );
        if ($quarterFilter != 'all') {
            $tanamDetailsQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]);
        }
        $tanamDetails = $tanamDetailsQuery->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        $panenDetailsQuery = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->select('lahan.id_jenis_lahan', DB::raw('SUM(panen.luas_panen) as total_luas'), DB::raw('COUNT(panen.id_panen) as total_lokasi'))
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereYear('panen.tgl_panen', $yearFilter),
            'lahan.id_tingkat'
        );
        if ($quarterFilter != 'all') {
            $panenDetailsQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }
        $panenDetails = $panenDetailsQuery->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        // ── 4. Serapan Hasil ─────────────────────────────────────────────────
        $serapanRawQuery = $applyScope(
            DB::table('distribusi')
                ->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
                ->select('distribusi.distribusi_ke', DB::raw('SUM(distribusi.total_distribusi) as val'))
                ->where('distribusi.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereYear('distribusi.tgl_distribusi', $yearFilter),
            'lahan.id_tingkat'
        );

        if ($quarterFilter != 'all') {
            $serapanRawQuery->whereRaw('QUARTER(distribusi.tgl_distribusi) = ?', [$quarterFilter]);
        }

        $serapanRaw = $serapanRawQuery->groupBy('distribusi.distribusi_ke')->pluck('val', 'distribusi_ke');

        $serapanBulog     = $serapanRaw['1'] ?? 0;
        $serapanPabrik    = $serapanRaw['2'] ?? 0;
        $serapanTengkulak = $serapanRaw['3'] ?? 0;
        $serapanKonsumsi  = $serapanRaw['4'] ?? 0;
        $totalSerapan     = $serapanBulog + $serapanTengkulak + $serapanPabrik + $serapanKonsumsi;

        // ── 5. Harvest Status Cards ──────────────────────────────────────────
        $harvestCardsDataQuery = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->select('panen.status_panen', DB::raw('SUM(panen.luas_panen) as val'))
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereYear('panen.tgl_panen', $yearFilter),
            'lahan.id_tingkat'
        );
        if ($quarterFilter != 'all') {
            $harvestCardsDataQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }
        $harvestCardsData = $harvestCardsDataQuery->groupBy('panen.status_panen')->pluck('val', 'status_panen');

        $harvestStats = [
            'normal'  => $harvestCardsData['1'] ?? 0,
            'failed'  => $harvestCardsData['2'] ?? 0,
            'early'   => $harvestCardsData['3'] ?? 0,
            'tebasan' => $harvestCardsData['4'] ?? 0,
        ];

        // ── 6. Planting & Harvesting Analytics ──────────────────────────────
        $plantingAnalytics = [];
        $totalT = $tanamTotal > 0 ? $tanamTotal : 1;
        foreach ($tanamDetails as $id => $det) {
            $name = $jenisLahanList[$id] ?? 'Lain-lain';
            $plantingAnalytics[$name] = [
                'val' => number_format($det->total_luas, 2),
                'pct' => round(($det->total_luas / $totalT) * 100)
            ];
        }
        arsort($plantingAnalytics);

        $harvestingAnalytics = [];
        $totalP = $panenTotal > 0 ? $panenTotal : 1;
        foreach ($panenDetails as $id => $det) {
            $name = $jenisLahanList[$id] ?? 'Lain-lain';
            $harvestingAnalytics[$name] = [
                'val' => number_format($det->total_luas, 2),
                'pct' => round(($det->total_luas / $totalP) * 100)
            ];
        }
        arsort($harvestingAnalytics);

        // ── 7. Kwartal Data ──────────────────────────────────────────────────
        $kwartalRaw = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->select(
                    DB::raw('QUARTER(panen.tgl_panen) as q'),
                    'lahan.id_jenis_lahan',
                    DB::raw('SUM(panen.luas_panen) as total_ha'),
                    DB::raw('SUM(panen.total_panen) as total_ton')
                )
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('panen.tgl_panen')
                ->whereYear('panen.tgl_panen', $yearFilter)
                ->groupBy('q', 'lahan.id_jenis_lahan'),
            'lahan.id_tingkat'
        )->get();

        $allJenisLahan = [
            1 => ['label' => 'Produktif (Poktan Binaan Polri)', 'accent' => 'emerald'],
            2 => ['label' => 'Hutan (Perhutanan Sosial)',       'accent' => 'teal'],
            3 => ['label' => 'Luas Baku Sawah (LBS)',           'accent' => 'blue'],
            4 => ['label' => 'Pesantren',                       'accent' => 'violet'],
            5 => ['label' => 'Milik Polri',                     'accent' => 'indigo'],
            6 => ['label' => 'Produktif (Masy. Binaan Polri)',  'accent' => 'sky'],
            7 => ['label' => 'Produktif (Tumpang Sari)',        'accent' => 'amber'],
            8 => ['label' => 'Hutan (Perhutani/Inhutani)',      'accent' => 'rose'],
            9 => ['label' => 'Lahan Lainnya',                   'accent' => 'slate'],
        ];

        // Build per-jenis Q arrays
        $jenisQData = [];
        $totalQ = array_fill(0, 4, ['luas' => 0, 'hasil' => 0]);

        foreach ($allJenisLahan as $jId => $jInfo) {
            $jenisQData[$jId] = array_fill(0, 4, ['luas' => 0, 'hasil' => 0]);
        }

        foreach ($kwartalRaw as $item) {
            $qIndex = $item->q - 1;
            if ($qIndex >= 0 && $qIndex <= 3) {
                $jId = $item->id_jenis_lahan;
                if (isset($jenisQData[$jId])) {
                    $jenisQData[$jId][$qIndex]['luas']  += $item->total_ha;
                    $jenisQData[$jId][$qIndex]['hasil'] += $item->total_ton;
                }
                $totalQ[$qIndex]['luas']  += $item->total_ha;
                $totalQ[$qIndex]['hasil'] += $item->total_ton;
            }
        }

        $kwartalData = [];
        foreach ($allJenisLahan as $jId => $jInfo) {
            $kwartalData[] = [
                'category' => $jId . '. ' . $jInfo['label'],
                'accent'   => $jInfo['accent'],
                'q'        => $jenisQData[$jId],
            ];
        }
        $kwartalData[] = ['category' => 'Total Keseluruhan', 'accent' => 'amber', 'q' => $totalQ];

        // ── 8. Map Data (scoped) ─────────────────────────────────────────────
        $mapData = $applyScope(
            DB::table('lahan')
                ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
                ->select('tingkat.nama_tingkat as title', 'lahan.latitude as lat', 'lahan.longitude as lng', 'lahan.status_lahan as status')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('lahan.latitude')
                ->whereNotNull('lahan.longitude')
                ->where('lahan.latitude', '!=', '')
                ->where('lahan.longitude', '!=', ''),
            'lahan.id_tingkat'
        )->inRandomOrder()->limit(200)->get()->map(function ($item) {
            $statusMap    = ['1' => 'Produktif', '2' => 'Tanam', '3' => 'Panen'];
            $item->status = $statusMap[$item->status] ?? 'Produktif';
            return $item;
        });

        // ── 9. Pending Validasi (scoped) ─────────────────────────────────────
        $pendingSearch = $request->input('pending_search', '');
        $pendingYear   = $request->input('pending_year', '');
        $pendingMonth  = $request->input('pending_month', '');
        $pendingJenis  = $request->input('pending_jenis', '');

        $qPotensi = $applyScope(
            DB::table('lahan')
                ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
                ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', 'lahan.luas_lahan', 'lahan.datetransaction', 'lahan.id_jenis_lahan')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNull('lahan.valid_oleh'),
            'lahan.id_tingkat'
        );
        if ($pendingSearch) {
            $qPotensi->where(function ($q) use ($pendingSearch) {
                $q->where('lahan.alamat_lahan', 'like', "%{$pendingSearch}%")
                  ->orWhere('tingkat.nama_tingkat', 'like', "%{$pendingSearch}%")
                  ->orWhere('lahan.id_lahan', 'like', "%{$pendingSearch}%");
            });
        }
        if ($pendingYear)  $qPotensi->whereYear('lahan.datetransaction', $pendingYear);
        if ($pendingMonth) $qPotensi->whereMonth('lahan.datetransaction', $pendingMonth);
        if ($pendingJenis) $qPotensi->where('lahan.id_jenis_lahan', $pendingJenis);
        $pendingPotensi = $qPotensi->orderBy('lahan.datetransaction', 'desc')->limit(100)->get();

        $qTanam = $applyScope(
            DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
                ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', DB::raw("'Tanam' as jenis"), 'tanam.tgl_tanam as tanggal', 'tanam.luas_tanam as luas', 'lahan.id_jenis_lahan')
                ->where('tanam.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNull('tanam.valid_oleh'),
            'lahan.id_tingkat'
        );

        $qPanen = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
                ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', DB::raw("'Panen' as jenis"), 'panen.tgl_panen as tanggal', 'panen.luas_panen as luas', 'lahan.id_jenis_lahan')
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNull('panen.valid_oleh'),
            'lahan.id_tingkat'
        );

        foreach ([$qTanam, $qPanen] as &$q) {
            if ($pendingSearch) {
                $q->where(function ($sq) use ($pendingSearch) {
                    $sq->where('lahan.alamat_lahan', 'like', "%{$pendingSearch}%")
                       ->orWhere('tingkat.nama_tingkat', 'like', "%{$pendingSearch}%")
                       ->orWhere('lahan.id_lahan', 'like', "%{$pendingSearch}%");
                });
            }
            if ($pendingJenis) $q->where('lahan.id_jenis_lahan', $pendingJenis);
        }
        if ($pendingYear)  { $qTanam->whereYear('tanam.tgl_tanam', $pendingYear);   $qPanen->whereYear('panen.tgl_panen', $pendingYear); }
        if ($pendingMonth) { $qTanam->whereMonth('tanam.tgl_tanam', $pendingMonth); $qPanen->whereMonth('panen.tgl_panen', $pendingMonth); }

        $pendingKelola = $qTanam->union($qPanen)->orderBy('tanggal', 'desc')->limit(100)->get();

        // ── 10. Chart Data (scoped) ──────────────────────────────────────────
        $yearlyPanenData = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->select(DB::raw('YEAR(panen.tgl_panen) as year'), DB::raw('SUM(panen.luas_panen) as total'))
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('panen.tgl_panen')
                ->groupBy('year')
                ->orderBy('year', 'asc'),
            'lahan.id_tingkat'
        )->get();

        $chartTahunan      = ['labels' => $yearlyPanenData->pluck('year')->toArray(), 'data' => $yearlyPanenData->pluck('total')->toArray()];
        $chartYearlyLabels = $chartTahunan['labels'];
        $chartYearlyData   = $chartTahunan['data'];

        $chartYear  = $request->input('chart_year', $yearFilter);
        $chartMonth = $request->input('chart_month', 'all');

        $monthlyPanenQuery = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->select(DB::raw('MONTH(panen.tgl_panen) as month'), DB::raw('SUM(panen.luas_panen) as total'))
                ->where('panen.deletestatus', '1')
                ->where('lahan.deletestatus', '!=', '0')
                ->whereNotNull('panen.tgl_panen')
                ->whereYear('panen.tgl_panen', $chartYear),
            'lahan.id_tingkat'
        );
        if ($chartMonth !== 'all') {
            $monthlyPanenQuery->whereMonth('panen.tgl_panen', (int) $chartMonth);
        }
        $monthlyPanenData = $monthlyPanenQuery->groupBy('month')->orderBy('month')->pluck('total', 'month');

        $monthNames       = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartBulanan     = ['labels' => $monthNames, 'data' => []];
        $chartMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlyPanenData[$i] ?? 0;
            $chartBulanan['data'][] = $val;
            $chartMonthlyData[]     = $val;
        }

        // Tahun tersedia dari data dalam scope
        $chartYears = $applyScope(
            DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->select(DB::raw('YEAR(panen.tgl_panen) as yr'))
                ->where('panen.deletestatus', '1')
                ->whereNotNull('panen.tgl_panen')
                ->groupBy('yr'),
            'lahan.id_tingkat'
        )->pluck('yr')
        ->merge(
            $applyScope(
                DB::table('lahan')
                    ->select(DB::raw('YEAR(datetransaction) as yr'))
                    ->whereNotNull('datetransaction')
                    ->groupBy('yr'),
                'id_tingkat'
            )->pluck('yr')
        )
        ->filter()->unique()->sort()->values()->toArray();

        if (empty($chartYears)) {
            $chartYears = range(2024, (int) date('Y') + 1);
        }

        return view('view.dashboard', compact(
            'quarterFilter',
            'yearFilter',
            'potensiTotal',
            'jenisLahanList',
            'potensiDetails',
            'tanamTotal',
            'tanamDetails',
            'panenTotal',
            'panenDetails',
            'totalTitikLahan',
            'totalPolsek',
            'totalSerapan',
            'serapanBulog',
            'serapanPabrik',
            'serapanTengkulak',
            'serapanKonsumsi',
            'harvestStats',
            'plantingAnalytics',
            'harvestingAnalytics',
            'kwartalData',
            'mapData',
            'pendingPotensi',
            'pendingKelola',
            'chartMonthlyData',
            'chartYearlyLabels',
            'chartYearlyData',
            'chartTahunan',
            'chartBulanan',
            'polsekAktif',
            'chartYears',
            'totalPolsekInScope',
            'totalLahanAll',
            'userWilayahLabel',
            'scope'
        ));
    }
}
