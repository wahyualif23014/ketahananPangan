<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getIndexData($request);
        return view('admin.dashboard', $data);
    }

    public function indexOperator(Request $request)
    {
        $data = $this->getIndexData($request);
        return view('operator.dashboard', $data);
    }

    public function indexView(Request $request)
    {
        $data = $this->getIndexData($request);
        return view('view.dashboard', $data);
    }

    private function getIndexData(Request $request)
    {
        $quarterFilter = $request->input('quarter', 'all');
        $yearFilter = $request->input('year', date('Y'));

        // 1. KPI Summary
        $potensiTotal = DB::table('lahan')->where('deletestatus', '1')->sum('luas_lahan');
        
        $tanamQuery = DB::table('tanam')->where('deletestatus', '1')->whereYear('tgl_tanam', $yearFilter);
        $panenQuery = DB::table('panen')->where('deletestatus', '1')->whereYear('tgl_panen', $yearFilter);
        
        if ($quarterFilter != 'all') {
            $tanamQuery->whereRaw('QUARTER(tgl_tanam) = ?', [$quarterFilter]);
            $panenQuery->whereRaw('QUARTER(tgl_panen) = ?', [$quarterFilter]);
        }

        $tanamTotal = $tanamQuery->sum('luas_tanam');
        $panenTotal = $panenQuery->sum('luas_panen');
        
        $totalTitikLahan = DB::table('lahan')->where('deletestatus', '1')->count();
        $totalPolsek = DB::table('lahan')->where('deletestatus', '1')->distinct('id_tingkat')->count('id_tingkat');
        $polsekAktif = $totalPolsek; // For doughnut chart

        // Master Jenis Lahan mapping
        $jenisLahanList = DB::table('jenislahan')
            ->pluck('nama_jenis_lahan', 'id_jenis_lahan');

        // Details
        $potensiDetails = DB::table('lahan')
            ->select('id_jenis_lahan', DB::raw('SUM(luas_lahan) as total_luas'), DB::raw('COUNT(id_lahan) as total_lokasi'))
            ->where('deletestatus', '1')
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        $tanamDetailsQuery = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(tanam.luas_tanam) as total_luas'), DB::raw('COUNT(tanam.id_tanam) as total_lokasi'))
            ->where('tanam.deletestatus', '1')
            ->whereYear('tanam.tgl_tanam', $yearFilter);
            
        if ($quarterFilter != 'all') {
            $tanamDetailsQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]);
        }
        $tanamDetails = $tanamDetailsQuery->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        $panenDetailsQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(panen.luas_panen) as total_luas'), DB::raw('COUNT(panen.id_panen) as total_lokasi'))
            ->where('panen.deletestatus', '1')
            ->whereYear('panen.tgl_panen', $yearFilter);
            
        if ($quarterFilter != 'all') {
            $panenDetailsQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        }
        $panenDetails = $panenDetailsQuery->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        // Serapan Hasil
        $serapanRaw = DB::table('distribusi')
            ->select('distribusi_ke', DB::raw('SUM(total_distribusi) as val'))
            ->where('deletestatus', '1')
            ->groupBy('distribusi_ke')
            ->pluck('val', 'distribusi_ke');

        $serapanBulog = $serapanRaw['1'] ?? 0;
        $serapanTengkulak = $serapanRaw['3'] ?? 0;
        $serapanPabrik = $serapanRaw['2'] ?? 0;
        $serapanKonsumsi = $serapanRaw['4'] ?? 0;
        $totalSerapan = $serapanBulog + $serapanTengkulak + $serapanPabrik + $serapanKonsumsi;

        // Harvest Status Cards
        $harvestCardsData = DB::table('panen')
            ->select('status_panen', DB::raw('SUM(luas_panen) as val'))
            ->where('deletestatus', '1')
            ->whereYear('tgl_panen', $yearFilter);
            
        if ($quarterFilter != 'all') {
            $harvestCardsData->whereRaw('QUARTER(tgl_panen) = ?', [$quarterFilter]);
        }
        $harvestCardsData = $harvestCardsData->groupBy('status_panen')->pluck('val', 'status_panen');

        $harvestStats = [
            'normal' => $harvestCardsData['1'] ?? 0,
            'failed' => $harvestCardsData['2'] ?? 0,
            'early'  => $harvestCardsData['3'] ?? 0,
            'tebasan' => $harvestCardsData['4'] ?? 0,
        ];

        // Planting & Harvesting Analytics (with lokasi)
        $plantingAnalytics = [];
        $totalT = $tanamTotal > 0 ? $tanamTotal : 1;
        foreach ($tanamDetails as $id => $det) {
            $name = $jenisLahanList[$id] ?? 'Lain-lain';
            $plantingAnalytics[$name] = [
                'val'    => number_format($det->total_luas, 2),
                'lokasi' => $det->total_lokasi,
                'pct'    => round(($det->total_luas / $totalT) * 100)
            ];
        }
        arsort($plantingAnalytics);

        $harvestingAnalytics = [];
        $totalP = $panenTotal > 0 ? $panenTotal : 1;
        foreach ($panenDetails as $id => $det) {
            $name = $jenisLahanList[$id] ?? 'Lain-lain';
            $harvestingAnalytics[$name] = [
                'val'    => number_format($det->total_luas, 2),
                'lokasi' => $det->total_lokasi,
                'pct'    => round(($det->total_luas / $totalP) * 100)
            ];
        }
        arsort($harvestingAnalytics);

        // Kwartal Data - semua 9 jenis lahan
        $allJenisLahan = [
            1 => ['label' => 'Produktif (Poktan Binaan Polri)', 'accent' => 'emerald'],
            2 => ['label' => 'Hutan (Perhutanan Sosial)',       'accent' => 'teal'],
            3 => ['label' => 'Luas Baku Sawah (LBS)',           'accent' => 'blue'],
            4 => ['label' => 'Pesantren',                        'accent' => 'violet'],
            5 => ['label' => 'Milik Polri',                      'accent' => 'indigo'],
            6 => ['label' => 'Produktif (Masy. Binaan Polri)',   'accent' => 'sky'],
            7 => ['label' => 'Produktif (Tumpang Sari)',         'accent' => 'amber'],
            8 => ['label' => 'Hutan (Perhutani/Inhutani)',       'accent' => 'rose'],
            9 => ['label' => 'Lahan Lainnya',                    'accent' => 'slate'],
        ];

        $kwartalRaw = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select(
                DB::raw('QUARTER(panen.tgl_panen) as q'),
                'lahan.id_jenis_lahan',
                DB::raw('SUM(panen.luas_panen) as total_ha'),
                DB::raw('SUM(panen.total_panen) as total_ton')
            )
            ->where('panen.deletestatus', '1')
            ->whereNotNull('panen.tgl_panen')
            ->whereYear('panen.tgl_panen', $yearFilter)
            ->groupBy('q', 'lahan.id_jenis_lahan')
            ->get();

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

        // Available years for chart filter
        $chartYears = DB::table('lahan')
            ->select(DB::raw('YEAR(tgl_edit) as yr'))
            ->whereNotNull('tgl_edit')
            ->groupBy('yr')
            ->orderBy('yr')
            ->pluck('yr')
            ->filter()
            ->merge(
                DB::table('panen')->select(DB::raw('YEAR(tgl_panen) as yr'))
                    ->whereNotNull('tgl_panen')->groupBy('yr')->pluck('yr')
            )
            ->unique()->sort()->values()->toArray();
        if (empty($chartYears)) {
            $chartYears = range(2024, (int)date('Y') + 1);
        }

        // Map Data
        $mapData = DB::table('lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('tingkat.nama_tingkat as title', 'lahan.latitude as lat', 'lahan.longitude as lng', 'lahan.status_lahan as status')
            ->where('lahan.deletestatus', '1')
            ->whereNotNull('lahan.latitude')
            ->whereNotNull('lahan.longitude')
            ->where('lahan.latitude', '!=', '')
            ->where('lahan.longitude', '!=', '')
            ->inRandomOrder()
            ->limit(200)
            ->get()
            ->map(function ($item) {
                $statusMap = ['1' => 'Produktif', '2' => 'Tanam', '3' => 'Panen'];
                $item->status = $statusMap[$item->status] ?? 'Produktif';
                return $item;
            });

        // Pending Validation
        $pendingValidation = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('tingkat.nama_tingkat as satwil', DB::raw('COUNT(panen.id_panen) as pending_count'))
            ->where('panen.deletestatus', '1')
            ->whereNull('panen.valid_oleh')
            ->groupBy('tingkat.nama_tingkat')
            ->orderByDesc('pending_count')
            ->limit(4)
            ->get();
            
        $totalPendingSatwil = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->where('panen.deletestatus', '1')
            ->whereNull('panen.valid_oleh')
            ->distinct('tingkat.id_tingkat')
            ->count('tingkat.id_tingkat');

        // Line Chart Data
        $yearlyPanenData = DB::table('panen')
            ->select(DB::raw('YEAR(tgl_panen) as year'), DB::raw('SUM(luas_panen) as total'))
            ->where('deletestatus', '1')
            ->whereNotNull('tgl_panen')
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();

        $chartTahunan = [
            'labels' => $yearlyPanenData->pluck('year')->toArray(),
            'data'   => $yearlyPanenData->pluck('total')->toArray()
        ];
        $chartYearlyLabels = $chartTahunan['labels'];
        $chartYearlyData = $chartTahunan['data'];

        $chartYear  = $request->input('chart_year', $yearFilter);
        $chartMonth = $request->input('chart_month', 'all');

        $monthlyPanenQuery = DB::table('panen')
            ->select(DB::raw('MONTH(tgl_panen) as month'), DB::raw('SUM(luas_panen) as total'))
            ->where('deletestatus', '1')
            ->whereNotNull('tgl_panen')
            ->whereYear('tgl_panen', $chartYear);

        if ($chartMonth !== 'all') {
            $monthlyPanenQuery->whereMonth('tgl_panen', (int)$chartMonth);
        }

        $monthlyPanenData = $monthlyPanenQuery->groupBy('month')->orderBy('month')->pluck('total', 'month');

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartBulanan = ['labels' => $monthNames, 'data' => []];
        $chartMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlyPanenData[$i] ?? 0;
            $chartBulanan['data'][] = $val;
            $chartMonthlyData[] = $val;
        }

        return compact(
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
            'pendingValidation',
            'totalPendingSatwil',
            'chartMonthlyData',
            'chartYearlyLabels',
            'chartYearlyData',
            'chartTahunan',
            'chartBulanan',
            'polsekAktif',
            'chartYears'
        );
    }
}
