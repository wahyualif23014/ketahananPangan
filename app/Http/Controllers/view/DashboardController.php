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
        $serapanTengkulak = $serapanRaw['2'] ?? 0;
        $serapanPabrik = $serapanRaw['3'] ?? 0;
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

        $milikPolriQ = array_fill(0, 4, ['luas' => 0, 'hasil' => 0]);
        $poktanBinaanQ = array_fill(0, 4, ['luas' => 0, 'hasil' => 0]);
        $totalQ = array_fill(0, 4, ['luas' => 0, 'hasil' => 0]);

        foreach ($kwartalRaw as $item) {
            $qIndex = $item->q - 1;
            if ($qIndex >= 0 && $qIndex <= 3) {
                if ($item->id_jenis_lahan == 5) {
                    $milikPolriQ[$qIndex]['luas'] += $item->total_ha;
                    $milikPolriQ[$qIndex]['hasil'] += $item->total_ton;
                }
                if ($item->id_jenis_lahan == 1) { // assuming 1 is Poktan Binaan
                    $poktanBinaanQ[$qIndex]['luas'] += $item->total_ha;
                    $poktanBinaanQ[$qIndex]['hasil'] += $item->total_ton;
                }
                $totalQ[$qIndex]['luas'] += $item->total_ha;
                $totalQ[$qIndex]['hasil'] += $item->total_ton;
            }
        }

        $kwartalData = [
            ['category' => 'Milik Polri', 'accent' => 'blue', 'q' => $milikPolriQ],
            ['category' => 'Produktif (Poktan Binaan)', 'accent' => 'emerald', 'q' => $poktanBinaanQ],
            ['category' => 'Total Keseluruhan', 'accent' => 'amber', 'q' => $totalQ],
        ];

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

        $monthlyPanenData = DB::table('panen')
            ->select(DB::raw('MONTH(tgl_panen) as month'), DB::raw('SUM(luas_panen) as total'))
            ->where('deletestatus', '1')
            ->whereNotNull('tgl_panen')
            ->whereYear('tgl_panen', $yearFilter)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('total', 'month');

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartBulanan = ['labels' => $monthNames, 'data' => []];
        $chartMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $val = $monthlyPanenData[$i] ?? 0;
            $chartBulanan['data'][] = $val;
            $chartMonthlyData[] = $val;
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
            'pendingValidation',
            'totalPendingSatwil',
            'chartMonthlyData',
            'chartYearlyLabels',
            'chartYearlyData',
            'chartTahunan',
            'chartBulanan',
            'polsekAktif'
        ));
    }
}
