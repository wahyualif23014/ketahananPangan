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

    public function notifyPending(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->role !== 'admin') {
            return back()->with('error', 'Hanya admin yang dapat mengirim notifikasi.');
        }

        $dataType    = $request->input('data_type', 'all');    // all | potensi | kelola
        $targetType  = $request->input('target_type', 'all'); // all | polres | pending
        $targetPolres = $request->input('target_polres', []);  // diisi jika targetType = polres

        $pendingPotensiTingkat = [];
        $pendingKelolaaTingkat  = [];

        // ── Data Potensi ──────────────────────────────────────────────────────
        if (in_array($dataType, ['all', 'potensi'])) {
            $pendingPotensiTingkat = DB::table('lahan')
                ->where('deletestatus', '2')
                ->whereNull('valid_oleh')
                ->pluck('id_tingkat')->toArray();
        }

        // ── Kelola Lahan (Tanam + Panen) ──────────────────────────────────────
        if (in_array($dataType, ['all', 'kelola'])) {
            $pendingTanamTingkat = DB::table('tanam')
                ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
                ->where('tanam.deletestatus', '2')
                ->where('lahan.deletestatus', '!=', '0')
                ->where(function($q) { $q->whereNull('tanam.valid_oleh')->orWhere('tanam.valid_oleh', 0); })
                ->pluck('lahan.id_tingkat')->toArray();

            $pendingPanenTingkat = DB::table('panen')
                ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
                ->where('panen.deletestatus', '2')
                ->where('lahan.deletestatus', '!=', '0')
                ->where(function($q) { $q->whereNull('panen.valid_oleh')->orWhere('panen.valid_oleh', 0); })
                ->pluck('lahan.id_tingkat')->toArray();

            $pendingKelolaaTingkat = array_merge($pendingTanamTingkat, $pendingPanenTingkat);
        }

        $allPendingTingkat = array_unique(array_merge($pendingPotensiTingkat, $pendingKelolaaTingkat));

        if (empty($allPendingTingkat)) {
            return back()->with('success', 'Tidak ada data pending yang memerlukan notifikasi.');
        }

        // ── Ekstrak Polres dari pending tingkat (2 segmen, misal 11.30) ───────
        $polresPending = [];
        foreach ($allPendingTingkat as $tingkat) {
            $parts = explode('.', $tingkat);
            $polresPending[] = count($parts) >= 2 ? $parts[0] . '.' . $parts[1] : $tingkat;
        }
        $polresPending = array_values(array_unique($polresPending));

        // ── Tentukan polres target berdasarkan target_type ────────────────────
        if ($targetType === 'polres') {
            // Kirim hanya ke polres yang dipilih AND punya pending
            if (empty($targetPolres)) {
                return back()->with('error', 'Pilih minimal satu Polres terlebih dahulu.');
            }
            $polresTingkat = array_values(array_intersect($polresPending, $targetPolres));
        } elseif ($targetType === 'pending') {
            // Otomatis hanya ke polres yang ada data pendingnya
            $polresTingkat = $polresPending;
        } else {
            // 'all' → semua polres yang memiliki operator aktif (tanpa filter pending)
            $polresTingkat = $polresPending;
        }

        if (empty($polresTingkat)) {
            return back()->with('error', 'Polres yang dipilih tidak memiliki data pending.');
        }

        // ── Buat pesan sesuai tipe data ───────────────────────────────────────
        $typeLabel = match($dataType) {
            'potensi' => 'Data Potensi Lahan',
            'kelola'  => 'Data Kelola Lahan (Tanam/Panen)',
            default   => 'Data Potensi maupun Kelola Lahan',
        };

        // ── Cari operator Polres ──────────────────────────────────────────────
        $penerima = \App\Models\Anggota::where('role', 'operator')
            ->whereIn('id_tugas', $polresTingkat)
            ->where('deletestatus', '2')
            ->get();

        if ($penerima->isEmpty()) {
            return back()->with('error', 'Tidak ditemukan operator Polres untuk wilayah yang dipilih.');
        }

        $count = 0;
        foreach ($penerima as $p) {
            \App\Models\Pesan::create([
                'id_pesan'     => \Illuminate\Support\Str::uuid(),
                'sender_id'    => $user->id_anggota,
                'recipient_id' => $p->id_anggota,
                'judul'        => 'Peringatan: Data Menunggu Validasi',
                'isi_pesan'    => 'Halo ' . $p->nama_anggota . ', terdapat ' . $typeLabel . ' di wilayah Anda (atau polsek jajaran Anda) yang masih menunggu validasi. Mohon segera periksa menu Kelola Lahan dan lakukan validasi. Terima kasih.',
                'is_read'      => false
            ]);
            $count++;
        }

        return back()->with('success', "Notifikasi berhasil dikirim ke {$count} operator Polres.");
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
        $chartYear  = $request->input('chart_year', $yearFilter);
        $chartMonth = $request->input('chart_month', 'all');

        $pendingSearch = $request->input('pending_search', '');
        $pendingYear = $request->input('pending_year', '');
        $pendingMonth = $request->input('pending_month', '');
        $pendingJenis = $request->input('pending_jenis', '');

        // 1. KPI Summary — semua data tanpa filter
        $potensiTotal = DB::table('lahan')->where('deletestatus', '2')->sum('luas_lahan');

        $tanamQuery = DB::table('tanam')->where('deletestatus', '2')->whereYear('tgl_tanam', $yearFilter);
        $panenQuery = DB::table('panen')->where('deletestatus', '2')->whereYear('tgl_panen', $yearFilter);

        if ($quarterFilter != 'all') {
            $tanamQuery->whereRaw('QUARTER(tgl_tanam) = ?', [$quarterFilter]);
            $panenQuery->whereRaw('QUARTER(tgl_panen) = ?', [$quarterFilter]);
        }

        $tanamTotal = $tanamQuery->sum('luas_tanam');
        $panenTotal = $panenQuery->sum('luas_panen');

        $totalTitikLahan = DB::table('lahan')->where('deletestatus', '2')->count();
        $totalPolsek = DB::table('lahan')->where('deletestatus', '2')->distinct('id_tingkat')->count('id_tingkat');
        $polsekAktif = $totalPolsek;


            // Polres List untuk Notifikasi - Optimasi: Cache
            $polresList = \Illuminate\Support\Facades\Cache::remember('global_polres_list', 3600, function() {
                return DB::table('tingkat')
                    ->select('id_tingkat', 'nama_tingkat')
                    ->whereRaw('LENGTH(TRIM(id_tingkat)) = 5')
                    ->get();
            });

            // Master Jenis Lahan mapping - Optimasi: Cache master data
            $jenisLahanList = \Illuminate\Support\Facades\Cache::remember('global_jenislahan_list', 3600, function() {
                return DB::table('jenislahan')->pluck('nama_jenis_lahan', 'id_jenis_lahan');
            });

        // Details
        $potensiDetails = DB::table('lahan')
            ->select('id_jenis_lahan', DB::raw('SUM(luas_lahan) as total_luas'), DB::raw('COUNT(id_lahan) as total_lokasi'))
            ->where('deletestatus', '2')->groupBy('id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        $tanamDetailsQuery = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(tanam.luas_tanam) as total_luas'), DB::raw('COUNT(tanam.id_tanam) as total_lokasi'))
            ->where('tanam.deletestatus', '2')->whereYear('tanam.tgl_tanam', $yearFilter);
        if ($quarterFilter != 'all') $tanamDetailsQuery->whereRaw('QUARTER(tanam.tgl_tanam) = ?', [$quarterFilter]);
        $tanamDetails = $tanamDetailsQuery->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        $panenDetailsQuery = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->select('lahan.id_jenis_lahan', DB::raw('SUM(panen.luas_panen) as total_luas'), DB::raw('COUNT(panen.id_panen) as total_lokasi'))
            ->where('panen.deletestatus', '2')->whereYear('panen.tgl_panen', $yearFilter);
        if ($quarterFilter != 'all') $panenDetailsQuery->whereRaw('QUARTER(panen.tgl_panen) = ?', [$quarterFilter]);
        $panenDetails = $panenDetailsQuery->groupBy('lahan.id_jenis_lahan')->get()->keyBy('id_jenis_lahan');

        // Serapan Hasil
        $serapanRaw = DB::table('distribusi')
            ->select('distribusi_ke', DB::raw('SUM(total_distribusi) as val'))
            ->where('deletestatus', '2')->groupBy('distribusi_ke')->pluck('val', 'distribusi_ke');

            $serapanBulog = $serapanRaw['1'] ?? 0;
            $serapanTengkulak = $serapanRaw['3'] ?? 0;
            $serapanPabrik = $serapanRaw['2'] ?? 0;
            $serapanKonsumsi = $serapanRaw['4'] ?? 0;
            $totalSerapan = $serapanBulog + $serapanTengkulak + $serapanPabrik + $serapanKonsumsi;

        // Harvest Status Cards
        $harvestCardsQuery = DB::table('panen')
            ->select('status_panen', DB::raw('SUM(luas_panen) as val'))
            ->where('deletestatus', '2')->whereYear('tgl_panen', $yearFilter);
        if ($quarterFilter != 'all') $harvestCardsQuery->whereRaw('QUARTER(tgl_panen) = ?', [$quarterFilter]);
        $harvestCardsData = $harvestCardsQuery->groupBy('status_panen')->pluck('val', 'status_panen');

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

            // Kwartal Data
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
            ->select(DB::raw('QUARTER(panen.tgl_panen) as q'), 'lahan.id_jenis_lahan', DB::raw('SUM(panen.luas_panen) as total_ha'), DB::raw('SUM(panen.total_panen) as total_ton'))
            ->where('panen.deletestatus', '2')->whereNotNull('panen.tgl_panen')->whereYear('panen.tgl_panen', $yearFilter)
            ->groupBy('q', 'lahan.id_jenis_lahan')->get();

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
            $chartYears = \Illuminate\Support\Facades\Cache::remember('chart_available_years', 3600, function() {
                return DB::table('lahan')
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
            });
            if (empty($chartYears)) $chartYears = range(2024, (int)date('Y') + 1);

        // Map Data
        $mapData = DB::table('lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('tingkat.nama_tingkat as title', 'lahan.latitude as lat', 'lahan.longitude as lng', 'lahan.status_lahan as status')
            ->where('lahan.deletestatus', '2')->whereNotNull('lahan.latitude')->whereNotNull('lahan.longitude')
            ->where('lahan.latitude', '!=', '')->where('lahan.longitude', '!=', '')->limit(300)
            ->get()->map(function ($item) {
                $statusMap = ['1' => 'Produktif', '2' => 'Tanam', '3' => 'Panen'];
                $item->status = $statusMap[$item->status] ?? 'Produktif';
                return $item;
            });

        // Pending Validation Potensi
        $qPotensi = DB::table('lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', 'lahan.luas_lahan', 'lahan.datetransaction', 'lahan.id_jenis_lahan')
            ->where('lahan.deletestatus', '2')->whereNull('lahan.valid_oleh');

            if ($pendingSearch) {
                $qPotensi->where(function ($q) use ($pendingSearch) {
                    $q->where('lahan.alamat_lahan', 'like', "%{$pendingSearch}%")
                        ->orWhere('tingkat.nama_tingkat', 'like', "%{$pendingSearch}%")
                        ->orWhere('lahan.id_lahan', 'like', "%{$pendingSearch}%");
                });
            }
            if ($pendingYear) $qPotensi->whereYear('lahan.datetransaction', $pendingYear);
            if ($pendingMonth) $qPotensi->whereMonth('lahan.datetransaction', $pendingMonth);
            if ($pendingJenis) $qPotensi->where('lahan.id_jenis_lahan', $pendingJenis);

            $totalPendingPotensi = (clone $qPotensi)->count();
            $pendingPotensi = $qPotensi->orderBy('lahan.datetransaction', 'desc')->limit(100)->get();

        // Pending Validation Kelola
        $qTanam = DB::table('tanam')
            ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', DB::raw("'Tanam' as jenis"), 'tanam.tgl_tanam as tanggal', 'tanam.luas_tanam as luas', 'lahan.id_jenis_lahan')
            ->where('tanam.deletestatus', '2')->where(function($q) { $q->whereNull('tanam.valid_oleh')->orWhere('tanam.valid_oleh', 0); });
        if ($pendingSearch) $qTanam->where(function($q) use ($pendingSearch) { $q->where('lahan.alamat_lahan','like',"%{$pendingSearch}%")->orWhere('tingkat.nama_tingkat','like',"%{$pendingSearch}%"); });
        if ($pendingYear) $qTanam->whereYear('tanam.tgl_tanam', $pendingYear);
        if ($pendingMonth) $qTanam->whereMonth('tanam.tgl_tanam', $pendingMonth);
        if ($pendingJenis) $qTanam->where('lahan.id_jenis_lahan', $pendingJenis);
        $qPanen = DB::table('panen')
            ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
            ->join('tingkat', 'lahan.id_tingkat', '=', 'tingkat.id_tingkat')
            ->select('lahan.id_lahan', 'lahan.alamat_lahan', 'tingkat.nama_tingkat as satwil', DB::raw("'Panen' as jenis"), 'panen.tgl_panen as tanggal', 'panen.luas_panen as luas', 'lahan.id_jenis_lahan')
            ->where('panen.deletestatus', '2')->where(function($q) { $q->whereNull('panen.valid_oleh')->orWhere('panen.valid_oleh', 0); });
        if ($pendingSearch) $qPanen->where(function($q) use ($pendingSearch) { $q->where('lahan.alamat_lahan','like',"%{$pendingSearch}%")->orWhere('tingkat.nama_tingkat','like',"%{$pendingSearch}%"); });
        if ($pendingYear) $qPanen->whereYear('panen.tgl_panen', $pendingYear);
        if ($pendingMonth) $qPanen->whereMonth('panen.tgl_panen', $pendingMonth);
        if ($pendingJenis) $qPanen->where('lahan.id_jenis_lahan', $pendingJenis);

        $totalPendingKelola = (clone $qTanam)->count() + (clone $qPanen)->count();
        $pendingKelola = $qTanam->union($qPanen)->orderBy('tanggal', 'desc')->limit(100)->get();

        // Line Chart Data
        $yearlyPanenData = DB::table('panen')
            ->select(DB::raw('YEAR(tgl_panen) as year'), DB::raw('SUM(luas_panen) as total'))
            ->where('deletestatus', '2')->whereNotNull('tgl_panen')->groupBy('year')->orderBy('year', 'asc')->get();
        $chartTahunan = ['labels' => $yearlyPanenData->pluck('year')->toArray(), 'data' => $yearlyPanenData->pluck('total')->toArray()];

        $monthlyPanenQuery = DB::table('panen')
            ->select(DB::raw('MONTH(tgl_panen) as month'), DB::raw('SUM(luas_panen) as total'))
            ->where('deletestatus', '2')->whereNotNull('tgl_panen')->whereYear('tgl_panen', $chartYear);
        if ($chartMonth !== 'all') $monthlyPanenQuery->whereMonth('tgl_panen', (int)$chartMonth);
        $monthlyPanenData = $monthlyPanenQuery->groupBy('month')->orderBy('month')->pluck('total', 'month');
        $chartMonthlyData = [];
        for ($i = 1; $i <= 12; $i++) $chartMonthlyData[] = $monthlyPanenData[$i] ?? 0;

        return [
                'quarterFilter'       => $quarterFilter,
                'yearFilter'          => $yearFilter,
                'potensiTotal'        => $potensiTotal,
                'jenisLahanList'      => $jenisLahanList,
                'potensiDetails'      => $potensiDetails,
                'tanamTotal'          => $tanamTotal,
                'tanamDetails'        => $tanamDetails,
                'panenTotal'          => $panenTotal,
                'panenDetails'        => $panenDetails,
                'totalTitikLahan'     => $totalTitikLahan,
                'totalPolsek'         => $totalPolsek,
                'totalSerapan'        => $totalSerapan,
                'serapanBulog'        => $serapanBulog,
                'serapanPabrik'       => $serapanPabrik,
                'serapanTengkulak'    => $serapanTengkulak,
                'serapanKonsumsi'     => $serapanKonsumsi,
                'harvestStats'        => $harvestStats,
                'plantingAnalytics'   => $plantingAnalytics,
                'harvestingAnalytics' => $harvestingAnalytics,
                'kwartalData'         => $kwartalData,
                'mapData'             => $mapData,
                'pendingPotensi'      => $pendingPotensi,
                'pendingKelola'       => $pendingKelola,
                'totalPendingPotensi' => $totalPendingPotensi,
                'totalPendingKelola'  => $totalPendingKelola,
                'chartMonthlyData'    => $chartMonthlyData,
                'chartYearlyLabels'   => $chartTahunan['labels'],
                'chartYearlyData'     => $chartTahunan['data'],
                'chartTahunan'        => $chartTahunan,
                'chartBulanan'        => ['labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'], 'data' => $chartMonthlyData],
                'polsekAktif'         => $polsekAktif,
            'chartYears'          => $chartYears,
            'polresList'          => $polresList,
            'totalPolsekInScope'  => $totalPolsek,
            'totalLahanAll'       => $totalTitikLahan,
        ];
    }
}
