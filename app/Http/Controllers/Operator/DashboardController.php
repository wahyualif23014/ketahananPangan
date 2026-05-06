<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $quarterFilter = $request->input('quarter', 'all');
        $yearFilter = $request->input('year', null);

        $user = auth()->user();
        $scope = $user->id_tugas ?? '0';

        // Auto-detect most recent year with panen data in scope (if not manually selected)
        if (!$yearFilter) {
            $yearDetect = DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('panen.deletestatus', '1');
            if ($scope && $scope != '0') {
                $yearDetect->where('lahan.id_tingkat', 'LIKE', $scope . '%');
            }
            $detectedYear = $yearDetect->max(DB::raw('YEAR(panen.tgl_panen)'));
            // Fallback: try from tanam
            if (!$detectedYear) {
                $yearDetect2 = DB::table('tanam')
                    ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                    ->where('tanam.deletestatus', '1');
                if ($scope && $scope != '0') {
                    $yearDetect2->where('lahan.id_tingkat', 'LIKE', $scope . '%');
                }
                $detectedYear = $yearDetect2->max(DB::raw('YEAR(tanam.tgl_tanam)'));
            }
            $yearFilter = $detectedYear ?? date('Y');
        }

        $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
            if ($scope && $scope != '0') {
                return $query->where($column, 'LIKE', $scope . '%');
            }
            return $query;
        };

        // 1. KPI Summary
        $potensiTotalQuery = DB::table('lahan')->where('deletestatus', '!=', '0');
        $potensiTotal = $applyScope($potensiTotalQuery, 'id_tingkat')->sum('luas_lahan');
        
        $tanamQuery = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->where('tanam.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereYear('tanam.tgl_tanam', $yearFilter);
        $tanamQuery = $applyScope($tanamQuery, 'lahan.id_tingkat');

        $panenQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->where('panen.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereYear('panen.tgl_panen', $yearFilter);
        $panenQuery = $applyScope($panenQuery, 'lahan.id_tingkat');
        
        if ($quarterFilter != 'all') {
            $tanamQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]);
            $panenQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }

        $tanamTotal = $tanamQuery->sum('tanam.luas_tanam');
        $panenTotal = $panenQuery->sum('panen.luas_panen');
        
        $totalTitikLahanQuery = DB::table('lahan')->where('deletestatus', '!=', '0');
        $totalTitikLahan = $applyScope($totalTitikLahanQuery, 'id_tingkat')->count();

        $totalPolsekQuery = DB::table('lahan')->where('deletestatus', '!=', '0');
        $totalPolsek = $applyScope($totalPolsekQuery, 'id_tingkat')->distinct('id_tingkat')->count('id_tingkat');
        $polsekAktif = $totalPolsek; // For doughnut chart

        // Total semua Polsek dalam scope (termasuk yang belum punya lahan) — untuk denominator donut
        $totalPolsekInScopeQuery = DB::table('tingkat')
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'");
        if ($scope && $scope != '0') {
            $totalPolsekInScopeQuery->where('id_tingkat', 'LIKE', $scope . '%');
        }
        $totalPolsekInScope = max($totalPolsekInScopeQuery->count(), $polsekAktif);

        // Total semua lahan (aktif + non-aktif) dalam scope — untuk denominator donut titik lahan
        $totalLahanAllQuery = DB::table('lahan');
        if ($scope && $scope != '0') {
            $totalLahanAllQuery->where('id_tingkat', 'LIKE', $scope . '%');
        }
        $totalLahanAll = max($totalLahanAllQuery->count(), $totalTitikLahan);

        // Master Jenis Lahan mapping
        $jenisLahanList = DB::table('jenislahan')
            ->pluck('nama_jenis_lahan', 'id_jenis_lahan');

        // Details
        $potensiDetailsQuery = DB::table('lahan')
            ->select('id_jenis_lahan', DB::raw('SUM(luas_lahan) as total_luas'), DB::raw('COUNT(id_lahan) as total_lokasi'))
            ->where('deletestatus', '!=', '0')
            ->groupBy('id_jenis_lahan');
        $potensiDetails = $applyScope($potensiDetailsQuery, 'id_tingkat')->get()->keyBy('id_jenis_lahan');

        $tanamDetailsQuery = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(tanam.luas_tanam) as total_luas'), DB::raw('COUNT(tanam.id_tanam) as total_lokasi'))
            ->where('tanam.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereYear('tanam.tgl_tanam', $yearFilter);
            
        if ($quarterFilter != 'all') {
            $tanamDetailsQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]);
        }
        $tanamDetails = $applyScope($tanamDetailsQuery, 'lahan.id_tingkat')->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        $panenDetailsQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(panen.luas_panen) as total_luas'), DB::raw('COUNT(panen.id_panen) as total_lokasi'))
            ->where('panen.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereYear('panen.tgl_panen', $yearFilter);
            
        if ($quarterFilter != 'all') {
            $panenDetailsQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }
        $panenDetails = $applyScope($panenDetailsQuery, 'lahan.id_tingkat')->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        // Serapan Hasil
        $serapanRawQuery = DB::table('distribusi')
            ->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
            ->select('distribusi.distribusi_ke', DB::raw('SUM(distribusi.total_distribusi) as val'))
            ->where('distribusi.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereYear('distribusi.tgl_distribusi', $yearFilter);

        if ($quarterFilter != 'all') {
            $serapanRawQuery->whereRaw('QUARTER(distribusi.tgl_distribusi) = ?', [$quarterFilter]);
        }

        $serapanRawQuery->groupBy('distribusi.distribusi_ke');
        $serapanRaw = $applyScope($serapanRawQuery, 'lahan.id_tingkat')->pluck('val', 'distribusi_ke');

        $serapanBulog = $serapanRaw['1'] ?? 0;
        $serapanPabrik = $serapanRaw['2'] ?? 0;
        $serapanTengkulak = $serapanRaw['3'] ?? 0;
        $serapanKonsumsi = $serapanRaw['4'] ?? 0;
        $totalSerapan = $serapanBulog + $serapanTengkulak + $serapanPabrik + $serapanKonsumsi;

        // Harvest Status Cards
        $harvestCardsDataQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select('panen.status_panen', DB::raw('SUM(panen.luas_panen) as val'))
            ->where('panen.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereYear('panen.tgl_panen', $yearFilter);
            
        if ($quarterFilter != 'all') {
            $harvestCardsDataQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }
        $harvestCardsData = $applyScope($harvestCardsDataQuery, 'lahan.id_tingkat')->groupBy('panen.status_panen')->pluck('val', 'status_panen');

        $harvestStats = [
            'normal' => $harvestCardsData['1'] ?? 0,
            'failed' => $harvestCardsData['2'] ?? 0,
            'early'  => $harvestCardsData['3'] ?? 0,
            'tebasan' => $harvestCardsData['4'] ?? 0,
        ];

        // Planting & Harvesting Analytics
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

        // Kwartal Data
        $kwartalRawQuery = DB::table('panen')
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
            ->groupBy('q', 'lahan.id_jenis_lahan');
            
        $kwartalRaw = $applyScope($kwartalRawQuery, 'lahan.id_tingkat')->get();

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

        // Map Data
        $mapDataQuery = DB::table('lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('tingkat.nama_tingkat as title', 'lahan.latitude as lat', 'lahan.longitude as lng', 'lahan.status_lahan as status')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereNotNull('lahan.latitude')
            ->whereNotNull('lahan.longitude')
            ->where('lahan.latitude', '!=', '')
            ->where('lahan.longitude', '!=', '')
            ->inRandomOrder()
            ->limit(200);
            
        $mapData = $applyScope($mapDataQuery, 'lahan.id_tingkat')
            ->get()
            ->map(function ($item) {
                $statusMap = ['1' => 'Produktif', '2' => 'Tanam', '3' => 'Panen'];
                $item->status = $statusMap[$item->status] ?? 'Produktif';
                return $item;
            });

        $pendingSearch = $request->input('pending_search', '');
        $pendingYear = $request->input('pending_year', '');
        $pendingMonth = $request->input('pending_month', '');
        $pendingJenis = $request->input('pending_jenis', '');

        // Pending Validation Potensi
        $qPotensi = DB::table('lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', 'lahan.luas_lahan', 'lahan.datetransaction', 'lahan.id_jenis_lahan')
            ->where('lahan.deletestatus', '!=', '0')
            ->whereNull('lahan.valid_oleh');

        if ($pendingSearch) {
            $qPotensi->where(function($q) use ($pendingSearch) {
                $q->where('lahan.alamat_lahan', 'like', "%{$pendingSearch}%")
                  ->orWhere('tingkat.nama_tingkat', 'like', "%{$pendingSearch}%")
                  ->orWhere('lahan.id_lahan', 'like', "%{$pendingSearch}%");
            });
        }
        if ($pendingYear) $qPotensi->whereYear('lahan.datetransaction', $pendingYear);
        if ($pendingMonth) $qPotensi->whereMonth('lahan.datetransaction', $pendingMonth);
        if ($pendingJenis) $qPotensi->where('lahan.id_jenis_lahan', $pendingJenis);

        $qPotensi = $applyScope($qPotensi, 'lahan.id_tingkat');
        $totalPendingPotensi = (clone $qPotensi)->count();
        $pendingPotensi = $qPotensi->orderBy('lahan.datetransaction', 'desc')->limit(100)->get();

        // Pending Validation Kelola
        $qTanam = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', DB::raw("'Tanam' as jenis"), 'tanam.tgl_tanam as tanggal', 'tanam.luas_tanam as luas', 'lahan.id_jenis_lahan')
            ->where('tanam.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->where(function($q) { $q->whereNull('tanam.valid_oleh')->orWhere('tanam.valid_oleh', '0'); });

        if ($pendingSearch) {
            $qTanam->where(function($q) use ($pendingSearch) {
                $q->where('lahan.alamat_lahan', 'like', "%{$pendingSearch}%")
                  ->orWhere('tingkat.nama_tingkat', 'like', "%{$pendingSearch}%")
                  ->orWhere('lahan.id_lahan', 'like', "%{$pendingSearch}%");
            });
        }
        if ($pendingYear) $qTanam->whereYear('tanam.tgl_tanam', $pendingYear);
        if ($pendingMonth) $qTanam->whereMonth('tanam.tgl_tanam', $pendingMonth);
        if ($pendingJenis) $qTanam->where('lahan.id_jenis_lahan', $pendingJenis);
        
        $qTanam = $applyScope($qTanam, 'lahan.id_tingkat');

        $qPanen = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', DB::raw("'Panen' as jenis"), 'panen.tgl_panen as tanggal', 'panen.luas_panen as luas', 'lahan.id_jenis_lahan')
            ->where('panen.deletestatus', '1')
            ->where('lahan.deletestatus', '!=', '0')
            ->where(function($q) { $q->whereNull('panen.valid_oleh')->orWhere('panen.valid_oleh', '0'); });

        if ($pendingSearch) {
            $qPanen->where(function($q) use ($pendingSearch) {
                $q->where('lahan.alamat_lahan', 'like', "%{$pendingSearch}%")
                  ->orWhere('tingkat.nama_tingkat', 'like', "%{$pendingSearch}%")
                  ->orWhere('lahan.id_lahan', 'like', "%{$pendingSearch}%");
            });
        }
        if ($pendingYear) $qPanen->whereYear('panen.tgl_panen', $pendingYear);
        if ($pendingMonth) $qPanen->whereMonth('panen.tgl_panen', $pendingMonth);
        if ($pendingJenis) $qPanen->where('lahan.id_jenis_lahan', $pendingJenis);
        
        $qPanen = $applyScope($qPanen, 'lahan.id_tingkat');

        $totalPendingKelola = (clone $qTanam)->count() + (clone $qPanen)->count();
        $pendingKelola = $qTanam->union($qPanen)
            ->orderBy('tanggal', 'desc')
            ->limit(100)
            ->get();

        // Line Chart Data
        $yearlyPanenDataQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select(DB::raw('YEAR(panen.tgl_panen) as year'), DB::raw('SUM(panen.luas_panen) as total'))
            ->where('panen.deletestatus', '1')
            ->whereNotNull('panen.tgl_panen')
            ->groupBy('year')
            ->orderBy('year', 'asc');
            
        $yearlyPanenData = $applyScope($yearlyPanenDataQuery, 'lahan.id_tingkat')->get();

        $chartTahunan = [
            'labels' => $yearlyPanenData->pluck('year')->toArray(),
            'data'   => $yearlyPanenData->pluck('total')->toArray()
        ];
        $chartYearlyLabels = $chartTahunan['labels'];
        $chartYearlyData = $chartTahunan['data'];

        $chartYear  = $request->input('chart_year', $yearFilter);
        $chartMonth = $request->input('chart_month', 'all');

        $monthlyPanenDataQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select(DB::raw('MONTH(panen.tgl_panen) as month'), DB::raw('SUM(panen.luas_panen) as total'))
            ->where('panen.deletestatus', '1')
            ->whereNotNull('panen.tgl_panen')
            ->whereYear('panen.tgl_panen', $chartYear);

        if ($chartMonth !== 'all') {
            $monthlyPanenDataQuery->whereMonth('panen.tgl_panen', (int)$chartMonth);
        }

        $monthlyPanenDataQuery->groupBy('month')->orderBy('month', 'asc');
            
        $monthlyPanenData = $applyScope($monthlyPanenDataQuery, 'lahan.id_tingkat')->pluck('total', 'month');

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartBulanan = ['labels' => $monthNames, 'data' => []];
        $chartMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlyPanenData[$i] ?? 0;
            $chartBulanan['data'][] = $val;
            $chartMonthlyData[] = $val;
        }

        // Available years for chart filter
        $chartYearsQueryLahan = DB::table('lahan')
            ->select(DB::raw('YEAR(datetransaction) as yr'))
            ->whereNotNull('datetransaction')
            ->groupBy('yr')
            ->orderBy('yr');
        $chartYearsQueryLahan = $applyScope($chartYearsQueryLahan, 'id_tingkat');

        $chartYearsQueryPanen = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select(DB::raw('YEAR(panen.tgl_panen) as yr'))
            ->whereNotNull('panen.tgl_panen')
            ->groupBy('yr');
        $chartYearsQueryPanen = $applyScope($chartYearsQueryPanen, 'lahan.id_tingkat');

        $chartYears = $chartYearsQueryLahan
            ->pluck('yr')
            ->filter()
            ->merge($chartYearsQueryPanen->pluck('yr'))
            ->unique()->sort()->values()->toArray();

        if (empty($chartYears)) {
            $chartYears = range(2024, (int)date('Y') + 1);
        }

        return view('operator.dashboard', compact(
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
            'totalPendingPotensi',
            'totalPendingKelola'
        ));
    }
}

