<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
    public function index(Request $request) {
        $yearFilter = $request->query('year', 2026);
        $quarterFilter = $request->query('quarter', 'all');

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

        // 1. Potensi Lahan
        $potensiTotal = DB::table('lahan')->where('tahun_lahan', $yearFilter)->sum('luas_lahan');
        $potensiDetails = DB::table('lahan')
            ->selectRaw('id_jenis_lahan, SUM(luas_lahan) as total_luas, COUNT(id_lahan) as total_lokasi')
            ->whereNotNull('id_jenis_lahan')
            ->where('tahun_lahan', $yearFilter)
            ->groupBy('id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // 2. Tanam
        $tanamQuery = DB::table('tanam')->whereYear('tgl_tanam', $yearFilter);
        if ($quarterFilter !== 'all') { $tanamQuery->whereRaw('QUARTER(tgl_tanam) = ?', [$quarterFilter]); }
        $tanamTotal = (clone $tanamQuery)->sum('luas_tanam');
        
        $tanamDetailsQuery = DB::table('tanam')->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')->whereYear('tanam.tgl_tanam', $yearFilter);
        if ($quarterFilter !== 'all') { $tanamDetailsQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]); }
        $tanamDetails = $tanamDetailsQuery
            ->selectRaw('lahan.id_jenis_lahan, SUM(tanam.luas_tanam) as total_luas, COUNT(DISTINCT tanam.id_lahan) as total_lokasi')
            ->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // 3. Panen
        $panenQuery = DB::table('panen')->whereYear('tgl_panen', $yearFilter);
        if ($quarterFilter !== 'all') { $panenQuery->whereRaw('QUARTER(tgl_panen) = ?', [$quarterFilter]); }
        $panenTotal = (clone $panenQuery)->sum('luas_panen');
        
        $panenDetailsQuery = DB::table('panen')->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')->whereYear('panen.tgl_panen', $yearFilter);
        if ($quarterFilter !== 'all') { $panenDetailsQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]); }
        $panenDetails = $panenDetailsQuery
            ->selectRaw('lahan.id_jenis_lahan, SUM(panen.luas_panen) as total_luas, COUNT(DISTINCT panen.id_lahan) as total_lokasi')
            ->groupBy('lahan.id_jenis_lahan')
            ->get()->keyBy('id_jenis_lahan');

        // 4. Titik Lahan
        $totalTitikLahan = DB::table('lahan')->where('tahun_lahan', $yearFilter)->count();
        $totalPolsek = DB::table('lahan')
            ->where('tahun_lahan', $yearFilter)
            ->whereRaw("id_tingkat REGEXP '^[0-9]+\\.[0-9]+\\.[0-9]+$'")
            ->distinct()
            ->count('id_tingkat');

        // 5. Distribusi / Serapan
        $distribusiQuery = DB::table('distribusi')->whereYear('tgl_distribusi', $yearFilter);
        if ($quarterFilter !== 'all') { $distribusiQuery->whereRaw('QUARTER(tgl_distribusi) = ?', [$quarterFilter]); }
        
        $serapanBulog = (clone $distribusiQuery)->where('distribusi_ke', 1)->sum('total_distribusi');
        $serapanPabrik = (clone $distribusiQuery)->where('distribusi_ke', 2)->sum('total_distribusi');
        $serapanTengkulak = (clone $distribusiQuery)->where('distribusi_ke', 3)->sum('total_distribusi');
        $serapanKonsumsi = (clone $distribusiQuery)->where('distribusi_ke', 4)->sum('total_distribusi');
        
        $totalSerapan = (clone $distribusiQuery)->sum('total_distribusi');

        // 6. Monitoring Target & Hasil Kwartal (Panen)
        $kwartalQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->whereYear('panen.tgl_panen', $yearFilter);
        if ($quarterFilter !== 'all') { $kwartalQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]); }
        
        $kwartalQuery = $kwartalQuery->whereNotNull('lahan.id_jenis_lahan')
            ->selectRaw('lahan.id_jenis_lahan, QUARTER(panen.tgl_panen) as q, SUM(panen.luas_panen) as total_luas, SUM(panen.total_panen) as total_hasil')
            ->groupBy('lahan.id_jenis_lahan', 'q')
            ->get();

        $kwartalMap = [];
        $colors = ['blue', 'emerald', 'amber', 'rose', 'indigo', 'teal', 'sky', 'violet', 'slate'];
        foreach ($jenisLahanList as $id => $nama) {
            $kwartalMap[$id] = [
                'category' => $nama,
                'q' => [
                    ['luas' => 0, 'hasil' => 0],
                    ['luas' => 0, 'hasil' => 0],
                    ['luas' => 0, 'hasil' => 0],
                    ['luas' => 0, 'hasil' => 0],
                ],
                'accent' => $colors[($id - 1) % count($colors)]
            ];
        }

        foreach ($kwartalQuery as $row) {
            if (isset($kwartalMap[$row->id_jenis_lahan])) {
                $qIndex = $row->q - 1;
                $kwartalMap[$row->id_jenis_lahan]['q'][$qIndex]['luas'] = $row->total_luas;
                $kwartalMap[$row->id_jenis_lahan]['q'][$qIndex]['hasil'] = $row->total_hasil;
            }
        }
        $kwartalData = array_values($kwartalMap);

        // 7. Status Panen (Normal, Gagal, Dini, Tebasan)
        $harvestQuery = DB::table('panen')->whereYear('tgl_panen', $yearFilter);
        if ($quarterFilter !== 'all') { $harvestQuery->whereRaw('QUARTER(tgl_panen) = ?', [$quarterFilter]); }
        
        $harvestNormal = (clone $harvestQuery)->where('status_panen', 1)->sum('luas_panen');
        $harvestGagal = (clone $harvestQuery)->where('status_panen', 2)->sum('luas_panen');
        $harvestDini = (clone $harvestQuery)->where('status_panen', 3)->sum('luas_panen');
        $harvestTebasan = (clone $harvestQuery)->where('status_panen', 4)->sum('luas_panen');
        
        $harvestStats = [
            'normal' => $harvestNormal,
            'failed' => $harvestGagal,
            'early' => $harvestDini,
            'tebasan' => $harvestTebasan
        ];

        // 8. Chart Data (Tren Luasan Lahan)
        // Monthly
        $monthlyLahan = DB::table('lahan')
            ->selectRaw('MONTH(datetransaction) as month, SUM(luas_lahan) as total')
            ->whereYear('datetransaction', $yearFilter)
            ->groupBy('month')
            ->pluck('total', 'month')->toArray();
        $chartMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartMonthlyData[] = $monthlyLahan[$i] ?? 0;
        }

        // Yearly
        $yearlyLahan = DB::table('lahan')
            ->selectRaw('YEAR(datetransaction) as year, SUM(luas_lahan) as total')
            ->whereIn(DB::raw('YEAR(datetransaction)'), [$yearFilter-5, $yearFilter-4, $yearFilter-3, $yearFilter-2, $yearFilter-1, $yearFilter])
            ->groupBy('year')
            ->pluck('total', 'year')->toArray();
        $chartYearlyLabels = [];
        $chartYearlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $y = $yearFilter - $i;
            $chartYearlyLabels[] = (string)$y;
            $chartYearlyData[] = $yearlyLahan[$y] ?? 0;
        }

        // 9. Sistem Validasi Terintegrasi (Pending Validasi)
        $pendingValidation = DB::table('lahan')
            ->join('wilayah', 'lahan.id_wilayah', '=', 'wilayah.id_wilayah')
            ->where(function ($q) {
                $q->whereNull('lahan.valid_oleh')
                  ->orWhere('lahan.valid_oleh', '0');
            })
            ->selectRaw('wilayah.nama_wilayah as satwil, COUNT(lahan.id_lahan) as pending_count')
            ->groupBy('wilayah.nama_wilayah')
            ->orderByDesc('pending_count')
            ->limit(4)
            ->get();
            
        $totalPendingSatwil = DB::table('lahan')
            ->where(function ($q) {
                $q->whereNull('valid_oleh')
                  ->orWhere('valid_oleh', '0');
            })
            ->distinct('id_wilayah')
            ->count('id_wilayah');

        return view('admin.dashboard', compact(
            'yearFilter', 'quarterFilter',
            'jenisLahanList',
            'potensiTotal', 'potensiDetails',
            'tanamTotal', 'tanamDetails',
            'panenTotal', 'panenDetails',
            'totalTitikLahan', 'totalPolsek',
            'serapanBulog', 'serapanPabrik', 'serapanTengkulak', 'serapanKonsumsi', 'totalSerapan',
            'kwartalData', 'harvestStats',
            'chartMonthlyData', 'chartYearlyLabels', 'chartYearlyData',
            'pendingValidation', 'totalPendingSatwil'
        ));
    }
}
