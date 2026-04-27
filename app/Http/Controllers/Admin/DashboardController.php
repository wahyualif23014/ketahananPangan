<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. KPI Summary
        $totalPotensiLahan = DB::table('lahan')->where('deletestatus', '1')->sum('luas_lahan');
        $totalLahanTanam = DB::table('tanam')->where('deletestatus', '1')->sum('luas_tanam');
        $totalLahanPanen = DB::table('panen')->where('deletestatus', '1')->sum('luas_panen');
        $totalTitikLahan = DB::table('lahan')->where('deletestatus', '1')->count();
        $polsekAktif = DB::table('lahan')->where('deletestatus', '1')->distinct('id_tingkat')->count('id_tingkat');

        // Master Jenis Lahan mapping
        $jenisList = DB::table('jenislahan')
            ->select('id_jenis_lahan', 'nama_jenis_lahan')
            ->groupBy('id_jenis_lahan', 'nama_jenis_lahan')
            ->pluck('nama_jenis_lahan', 'id_jenis_lahan');

        // 2. Distribusi Potensi Lahan
        $potensiItemsRaw = DB::table('lahan')
            ->select('id_jenis_lahan', DB::raw('SUM(luas_lahan) as val'))
            ->where('deletestatus', '1')
            ->groupBy('id_jenis_lahan')
            ->orderByDesc('val')
            ->get();

        $totalPotensi = $potensiItemsRaw->sum('val') ?: 1;
        $potensiItems = [];
        foreach ($potensiItemsRaw as $item) {
            $name = $jenisList[$item->id_jenis_lahan] ?? 'Lain-lain';
            if (isset($potensiItems[$name])) {
                $potensiItems[$name]['val_num'] += $item->val;
            } else {
                $potensiItems[$name] = ['val_num' => $item->val];
            }
        }
        foreach ($potensiItems as $name => &$data) {
            $data['pct'] = round(($data['val_num'] / $totalPotensi) * 100);
            $data['val'] = number_format($data['val_num'], 2);
        }
        arsort($potensiItems);

        // 3. Harvest Status Cards
        $harvestCardsData = DB::table('panen')
            ->select('status_panen', DB::raw('SUM(luas_panen) as val'))
            ->where('deletestatus', '1')
            ->groupBy('status_panen')
            ->pluck('val', 'status_panen');

        $harvestStats = [
            'normal' => number_format($harvestCardsData['1'] ?? 0, 2),
            'failed' => number_format($harvestCardsData['2'] ?? 0, 2),
            'early'  => number_format($harvestCardsData['3'] ?? 0, 2),
            'yearly' => number_format($harvestCardsData['4'] ?? 0, 2),
        ];

        // 4. Planting & Harvesting Analytics
        $plantingAnalyticsRaw = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(tanam.luas_tanam) as val'))
            ->where('tanam.deletestatus', '1')
            ->groupBy('lahan.id_jenis_lahan')
            ->orderByDesc('val')
            ->get();

        $totalPlanting = $plantingAnalyticsRaw->sum('val') ?: 1;
        $plantingAnalytics = [];
        foreach ($plantingAnalyticsRaw as $item) {
            $name = $jenisList[$item->id_jenis_lahan] ?? 'Lain-lain';
            if (isset($plantingAnalytics[$name])) {
                $plantingAnalytics[$name]['val_num'] += $item->val;
            } else {
                $plantingAnalytics[$name] = ['val_num' => $item->val];
            }
        }
        foreach ($plantingAnalytics as $name => &$data) {
            $data['pct'] = round(($data['val_num'] / $totalPlanting) * 100);
            $data['val'] = number_format($data['val_num'], 2);
        }
        arsort($plantingAnalytics);
        //panen
        $harvestingAnalyticsRaw = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(panen.luas_panen) as val'))
            ->where('panen.deletestatus', '1')
            ->groupBy('lahan.id_jenis_lahan')
            ->orderByDesc('val')
            ->get();

        $totalHarvesting = $harvestingAnalyticsRaw->sum('val') ?: 1;
        $harvestingAnalytics = [];
        foreach ($harvestingAnalyticsRaw as $item) {
            $name = $jenisList[$item->id_jenis_lahan] ?? 'Lain-lain';
            if (isset($harvestingAnalytics[$name])) {
                $harvestingAnalytics[$name]['val_num'] += $item->val;
            } else {
                $harvestingAnalytics[$name] = ['val_num' => $item->val];
            }
        }
        foreach ($harvestingAnalytics as $name => &$data) {
            $data['pct'] = round(($data['val_num'] / $totalHarvesting) * 100);
            $data['val'] = number_format($data['val_num'], 2);
        }
        arsort($harvestingAnalytics);

        // 5. Serapan Hasil
        $serapanRaw = DB::table('distribusi')
            ->select('distribusi_ke', DB::raw('SUM(total_distribusi) as val'))
            ->where('deletestatus', '1')
            ->groupBy('distribusi_ke')
            ->pluck('val', 'distribusi_ke');

        $serapanData = [
            ['label' => 'Serapan Bulog', 'val' => number_format($serapanRaw['1'] ?? 0, 2), 'unit' => 'Ton', 'accent' => 'blue', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['label' => 'Serapan Pabrik Pakan', 'val' => number_format($serapanRaw['3'] ?? 0, 2), 'unit' => 'Ton', 'accent' => 'indigo', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
            ['label' => 'Serapan Tengkulak', 'val' => number_format($serapanRaw['2'] ?? 0, 2), 'unit' => 'Ton', 'accent' => 'amber', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
            ['label' => 'Konsumsi Sendiri', 'val' => number_format($serapanRaw['4'] ?? 0, 2), 'unit' => 'Ton', 'accent' => 'emerald', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ];

        // 6. Map Data
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
                // Status mapping roughly
                $statusMap = ['1' => 'Produktif', '2' => 'Tanam', '3' => 'Panen'];
                $item->status = $statusMap[$item->status] ?? 'Produktif';
                return $item;
            });

        // 7. Kwartal Data
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
            ->whereYear('panen.tgl_panen', date('Y'))
            ->groupBy('q', 'lahan.id_jenis_lahan')
            ->get();

        $milikPolri = ['0', '0', '0', '0'];
        $poktanBinaan = ['0', '0', '0', '0'];
        $hasilTon = [0, 0, 0, 0];

        foreach ($kwartalRaw as $item) {
            $qIndex = $item->q - 1;
            if ($qIndex >= 0 && $qIndex <= 3) {
                if ($item->id_jenis_lahan == 5) {
                    $milikPolri[$qIndex] = number_format($item->total_ha, 2);
                }
                if ($item->id_jenis_lahan == 1) {
                    $poktanBinaan[$qIndex] = number_format($item->total_ha, 2);
                }
                $hasilTon[$qIndex] += $item->total_ton;
            }
        }
        $hasilTonStr = array_map(function ($val) {
            return $val > 0 ? number_format($val, 2) : '0';
        }, $hasilTon);

        $kwartalData = [
            ['category' => 'Milik Polri', 'unit' => 'Ha', 'q' => $milikPolri, 'accent' => 'blue'],
            ['category' => 'Produktif (Poktan Binaan)', 'unit' => 'Ha', 'q' => $poktanBinaan, 'accent' => 'emerald'],
            ['category' => 'Hasil Panen Kwartal', 'unit' => 'Ton', 'q' => $hasilTonStr, 'accent' => 'amber'],
        ];

        // 8. Line Chart Data (Tahunan & Bulanan)
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

        $monthlyPanenData = DB::table('panen')
            ->select(DB::raw('MONTH(tgl_panen) as month'), DB::raw('SUM(luas_panen) as total'))
            ->where('deletestatus', '1')
            ->whereNotNull('tgl_panen')
            ->whereYear('tgl_panen', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartBulanan = ['labels' => [], 'data' => []];
        foreach ($monthlyPanenData as $item) {
            $chartBulanan['labels'][] = $monthNames[$item->month - 1];
            $chartBulanan['data'][] = $item->total;
        }

        // Calculate Yield and Growth Rate
        $currentYearYield = DB::table('panen')
            ->where('deletestatus', '1')
            ->whereYear('tgl_panen', date('Y'))
            ->sum('luas_panen');

        $lastYearYield = DB::table('panen')
            ->where('deletestatus', '1')
            ->whereYear('tgl_panen', date('Y') - 1)
            ->sum('luas_panen');

        $growthRate = 0;
        if ($lastYearYield > 0) {
            $growthRate = (($currentYearYield - $lastYearYield) / $lastYearYield) * 100;
        } elseif ($currentYearYield > 0) {
            $growthRate = 100; // 100% growth if previous year was 0 but this year has yield
        }

        // 9. Laporan Pending Validasi
        $pendingSatwil = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->where('panen.deletestatus', '1')
            ->whereNull('panen.valid_oleh')
            ->select('tingkat.nama_tingkat')
            ->distinct()
            ->limit(4)
            ->pluck('nama_tingkat')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalPotensiLahan',
            'totalLahanTanam',
            'totalLahanPanen',
            'totalTitikLahan',
            'polsekAktif',
            'potensiItems',
            'harvestStats',
            'plantingAnalytics',
            'harvestingAnalytics',
            'serapanData',
            'mapData',
            'kwartalData',
            'chartTahunan',
            'chartBulanan',
            'pendingSatwil',
            'currentYearYield',
            'growthRate'
        ));
    }
}
