<?php

namespace App\Exports;

use App\Models\RekapitulasiLahan;
use Maatwebsite\Excel\Concerns\{FromCollection, Exportable, WithColumnWidths, WithStyles, WithEvents};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RekapitulasiExport implements FromCollection, WithColumnWidths, WithStyles, WithEvents
{
    use Exportable;

    protected $filters;
    protected $mode;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        $this->mode    = $filters['mode'] ?? '1';
        set_time_limit(300);
        ini_set('memory_limit', '512M');
    }

    // COLLECTION 
    public function collection()
    {
        switch ($this->mode) {
            case '2':
                return $this->buildMode2();
            case '3':
                return $this->buildMode3();
            case '4':
                return $this->buildMode4();
            default:
                return $this->buildMode1();
        }
    }

    // MODE 1 — Rekapitulasi Data Produksi Lahan (per Desa)

    private function buildMode1(): Collection
    {
        $lines = new Collection();

        // Subquery Tanam
        $tanamSub = DB::table('tanam')
            ->select('id_lahan', DB::raw('SUM(luas_tanam) as luas_tanam'))
            ->where('deletestatus', '1');
        if (!empty($this->filters['tahun'])) {
            $tanamSub->whereYear('tgl_tanam', $this->filters['tahun']);
        }
        $tanamSub = $tanamSub->groupBy('id_lahan');

        // Subquery Panen
        $panenSub = DB::table('panen')
            ->select('id_lahan', DB::raw('SUM(luas_panen) as luas_panen'), DB::raw('SUM(total_panen) as total_panen'))
            ->where('deletestatus', '1')
            ->groupBy('id_lahan');

        $query = DB::table('lahan as l')
            ->select([
                'tw_polres.nama_tingkat as nama_polres',
                'tw_polsek.id_tingkat as id_polsek',
                'tw_polsek.nama_tingkat as nama_polsek',
                'w.nama_wilayah as nama_desa',
                DB::raw('SUM(l.luas_lahan) as potensi_lahan'),
                DB::raw('SUM(COALESCE(tn.luas_tanam, 0)) as tanam_lahan'),
                DB::raw('SUM(COALESCE(p.luas_panen, 0)) as luas_panen'),
                DB::raw('SUM(COALESCE(p.total_panen, 0)) as total_panen'),
            ])
            ->join('tingkat as tw_polsek', 'l.id_tingkat', '=', 'tw_polsek.id_tingkat')
            ->leftJoin('tingkat as tw_polres', function ($j) {
                $j->whereRaw('TRIM(tw_polsek.id_tingkat) LIKE CONCAT(TRIM(tw_polres.id_tingkat), "%")')
                    ->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5');
            })
            ->leftJoin('wilayah as w', 'l.id_wilayah', '=', 'w.id_wilayah')
            ->leftJoinSub($tanamSub, 'tn', 'l.id_lahan', '=', 'tn.id_lahan')
            ->leftJoinSub($panenSub, 'p', 'l.id_lahan', '=', 'p.id_lahan')
            ->where('l.deletestatus', '1')
            ->when($this->filters['polres'] ?? null, fn($q, $v) => $q->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5 AND tw_polres.id_tingkat = ?', [$v]))
            ->when($this->filters['polsek'] ?? null, fn($q, $v) => $q->where('tw_polsek.id_tingkat', $v))
            ->groupBy('tw_polres.nama_tingkat', 'tw_polsek.id_tingkat', 'tw_polsek.nama_tingkat', 'w.nama_wilayah')
            ->orderBy('tw_polres.nama_tingkat')
            ->orderBy('tw_polsek.nama_tingkat')
            ->orderBy('w.nama_wilayah');

        $data = $query->get();

        // Baris 1: Judul
        $lines->push(['REKAPITULASI DATA PRODUKSI LAHAN']);
        // Baris 2: kosong
        $lines->push(['']);
        $lines->push([
            '',
            '',
            '',
            '',
            'Potensi Lahan (Ha)',
            '',
            'Tanam Lahan (Ha)',
            '',
            'Luas Panen (Ha)',
            '',
            'Total Panen (Ton)',
            '',
            'Total Serapan (Ton)',
            '',
        ]);
        // Baris 4: sub-header
        $lines->push([
            'No.',
            'Polsek',
            'Polres',
            'Wilayah',
            'Valid',
            'Proses',
            'Valid',
            'Proses',
            'Valid',
            'Proses',
            'Valid',
            'Proses',
            'Valid',
            'Proses',
        ]);

        $no = 1;
        foreach ($data as $r) {
            $potensi = (float) $r->potensi_lahan;
            $tanam   = (float) $r->tanam_lahan;
            $serapan = $potensi > 0 ? ($tanam / $potensi) * 100 : 0;

            $lines->push([
                $no++,
                $r->nama_polsek ?? '-',
                $r->nama_polres ?? '-',
                $r->nama_desa   ?? '-',
                $this->fmt($potensi),
                '0.00',
                $this->fmt($tanam),
                '0.00',
                $this->fmt($r->luas_panen),
                '0.00',
                $this->fmt($r->total_panen),
                '0.00',
                $this->fmt($serapan),
                '0.00',
            ]);
        }

        return $lines;
    }

    // MODE 2 — Rekapitulasi Total Data Produksi per Polsek
    private function buildMode2(): Collection
    {
        $lines = new Collection();

        // Subquery Tanam
        $tanamSub = DB::table('tanam')
            ->select('id_lahan', DB::raw('SUM(luas_tanam) as luas_tanam'))
            ->where('deletestatus', '1');
        if (!empty($this->filters['tahun'])) {
            $tanamSub->whereYear('tgl_tanam', $this->filters['tahun']);
        }
        $tanamSub = $tanamSub->groupBy('id_lahan');

        // Subquery Panen
        $panenSub = DB::table('panen')
            ->select('id_lahan', DB::raw('SUM(luas_panen) as luas_panen'), DB::raw('SUM(total_panen) as total_panen'))
            ->where('deletestatus', '1')
            ->groupBy('id_lahan');

        $query = DB::table('lahan as l')
            ->select([
                'tw_polres.nama_tingkat as nama_polres',
                'tw_polsek.id_tingkat as id_polsek',
                'tw_polsek.nama_tingkat as nama_polsek',
                DB::raw('SUM(l.luas_lahan) as potensi_lahan'),
                DB::raw('SUM(COALESCE(tn.luas_tanam, 0)) as tanam_lahan'),
                DB::raw('SUM(COALESCE(p.luas_panen, 0)) as luas_panen'),
                DB::raw('SUM(COALESCE(p.total_panen, 0)) as total_panen'),
            ])
            ->join('tingkat as tw_polsek', 'l.id_tingkat', '=', 'tw_polsek.id_tingkat')
            ->leftJoin('tingkat as tw_polres', function ($j) {
                $j->whereRaw('TRIM(tw_polsek.id_tingkat) LIKE CONCAT(TRIM(tw_polres.id_tingkat), "%")')
                    ->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5');
            })
            ->leftJoinSub($tanamSub, 'tn', 'l.id_lahan', '=', 'tn.id_lahan')
            ->leftJoinSub($panenSub, 'p', 'l.id_lahan', '=', 'p.id_lahan')
            ->where('l.deletestatus', '1')
            ->when($this->filters['polres'] ?? null, fn($q, $v) => $q->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5 AND tw_polres.id_tingkat = ?', [$v]))
            ->when($this->filters['polsek'] ?? null, fn($q, $v) => $q->where('tw_polsek.id_tingkat', $v))
            ->groupBy('tw_polres.nama_tingkat', 'tw_polsek.id_tingkat', 'tw_polsek.nama_tingkat')
            ->orderBy('tw_polres.nama_tingkat')
            ->orderBy('tw_polsek.nama_tingkat');

        $data = $query->get();

        // Baris 1: Judul
        $lines->push(['REKAPITULASI TOTAL DATA PRODUKSI  LAHAN PER POLSEK']);
        $lines->push([
            'No.',
            'Polres',
            'Polsek',
            'Potensi Lahan (Ha)',
            'Tanam Lahan (Ha)',
            'Luas Panen (Ha)',
            'Total Panen (Ton)',
            'Persentase Serapan (%)',
        ]);

        $no = 1;
        foreach ($data as $r) {
            $potensi = (float) $r->potensi_lahan;
            $tanam   = (float) $r->tanam_lahan;
            $serapan = $potensi > 0 ? ($tanam / $potensi) * 100 : 0;

            $lines->push([
                $no++,
                $r->nama_polres ?? '-',
                $r->nama_polsek ?? '-',
                $this->fmt($potensi),
                $this->fmt($tanam),
                $this->fmt($r->luas_panen),
                $this->fmt($r->total_panen),
                $this->fmt($serapan),
            ]);
        }

        return $lines;
    }

    // MODE 3 — Perincian Data Produksi Lahan 

    private function buildMode3(): Collection
    {
        $lines = new Collection();

        $data = DB::table('lahan as l')
            ->select([
                // Wilayah
                'tw_polres.nama_tingkat as nama_polres',
                'tw_polsek.nama_tingkat as nama_polsek',
                'w.nama_wilayah as nama_desa',
                'kab.nama_wilayah as nama_kecamatan',
                'w.Latitude as latitude',
                'w.longitude as longitude',
                // Lahan
                'l.id_lahan',
                'l.alamat_lahan',
                'l.poktan',
                'l.cp_lahan as nama_pemilik',
                'l.no_cp_lahan as no_hp_pemilik',
                'l.cp_polisi as nama_pj',
                'l.no_cp_polisi as no_hp_pj',
                'l.ket_polisi as ket_pj',
                'jl.nama_jenis_lahan',
                'k.nama_komoditi',
                'l.luas_lahan',
                'l.sumber_data_lahan',
                'l.no_sk',
                'l.lembaga_lahan as penerima_sk',
                'l.jml_petani',
                'l.status_lahan',
                // Tanam
                'tn.luas_tanam',
                'tn.tgl_tanam',
                'tn.kebutuhan_bibit',
                'tn.nama_bibit',
                // Panen
                'p.luas_panen',
                'p.total_panen',
                'p.tgl_panen',
                'p.status_panen',
            ])
            ->leftJoin('tingkat as tw_polsek', 'l.id_tingkat', '=', 'tw_polsek.id_tingkat')
            ->leftJoin('tingkat as tw_polres', function ($j) {
                $j->whereRaw('TRIM(tw_polsek.id_tingkat) LIKE CONCAT(TRIM(tw_polres.id_tingkat), "%")')
                    ->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5');
            })
            ->leftJoin('wilayah as w', 'l.id_wilayah', '=', 'w.id_wilayah')
            ->leftJoin('wilayah as kab', function ($j) {
                $j->whereRaw('w.id_wilayah LIKE CONCAT(kab.id_wilayah, ".%")')
                    ->whereRaw('LENGTH(kab.id_wilayah) = 8');
            })
            ->leftJoin('jenislahan as jl', 'l.id_jenis_lahan', '=', 'jl.id_jenis_lahan')
            ->leftJoin('komoditi as k', 'l.id_komoditi', '=', 'k.id_komoditi')
            ->leftJoin('tanam as tn', function ($j) {
                $j->on('l.id_lahan', '=', 'tn.id_lahan')->where('tn.deletestatus', '=', '1');
            })
            ->leftJoin('panen as p', function ($j) {
                $j->on('tn.id_lahan', '=', 'p.id_lahan')->where('p.deletestatus', '=', '1');
            })
            ->where('l.deletestatus', '1')
            ->when($this->filters['polres'] ?? null, fn($q, $v) => $q->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5 AND tw_polres.id_tingkat = ?', [$v]))
            ->when($this->filters['polsek'] ?? null, fn($q, $v) => $q->where('tw_polsek.id_tingkat', $v))
            ->orderBy('tw_polres.nama_tingkat')
            ->orderBy('tw_polsek.nama_tingkat')
            ->orderBy('w.nama_wilayah')
            ->get();

        // Baris 1: Judul
        $lines->push(['PERINCIAN DATA PRODUKSI LAHAN']);
        // Baris 2: Header Group
        $lines->push([
            'No.',
            'Data Wilayah',
            '',
            '',
            '',
            '',
            '',
            'Data Pemilik / Penggarap',
            '',
            'Data Penanggung Jawab',
            '',
            '',
            'Data Lahan & Komoditi',
            '',
            'Data Potensi Lahan',
            '',
            '',
            '',
            '',
            '',
            'Data Tanam Lahan',
            '',
            '',
            '',
            'Data Panen',
            '',
            '',
            '',
        ]);
        // Baris 3: Sub Header
        $lines->push([
            'No.',
            'Polres',
            'Polsek',
            'Alamat / Desa',
            'Kecamatan',
            'Latitude',
            'Longitude',
            'Nama Pemilik',
            'No. HP',
            'Nama PJ',
            'No. HP',
            'Keterangan',
            'Jenis Lahan',
            'Komoditi',
            'Luas Lahan (Ha)',
            'Sumber Data',
            'No. SK',
            'Penerima',
            'Jml. Petani',
            'Status Lahan',
            'Luas Tanam (Ha)',
            'Tgl. Tanam',
            'Kebutuhan Bibit',
            'Nama Bibit',
            'Luas Panen (Ha)',
            'Total Panen (Ton)',
            'Tgl. Panen',
            'Status Panen',
        ]);

        $no = 1;
        foreach ($data as $r) {
            $lines->push([
                $no++,
                $r->nama_polres   ?? '-',
                $r->nama_polsek   ?? '-',
                $r->nama_desa     ?? '-',
                $r->nama_kecamatan ?? '-',
                $r->latitude      ?? '-',
                $r->longitude     ?? '-',
                $r->nama_pemilik  ?? '-',
                $r->no_hp_pemilik ?? '-',
                $r->nama_pj       ?? '-',
                $r->no_hp_pj      ?? '-',
                $r->ket_pj        ?? '-',
                $r->nama_jenis_lahan ?? '-',
                $r->nama_komoditi ?? '-',
                $this->fmt($r->luas_lahan ?? 0),
                $r->sumber_data_lahan ?? '-',
                $r->no_sk         ?? '-',
                $r->penerima_sk   ?? '-',
                $r->jml_petani    ?? 0,
                $r->status_lahan  ?? '-',
                $this->fmt($r->luas_tanam ?? 0),
                $r->tgl_tanam     ?? '-',
                $this->fmt($r->kebutuhan_bibit ?? 0),
                $r->nama_bibit    ?? '-',
                $this->fmt($r->luas_panen ?? 0),
                $this->fmt($r->total_panen ?? 0),
                $r->tgl_panen     ?? '-',
                $r->status_panen  ?? '-',
            ]);
        }

        return $lines;
    }

    // MODE 4 — Rekapitulasi Data Potensi dan Tanam per Polres

    private function buildMode4(): Collection
    {
        $lines = new Collection();

        $polresList = DB::table('tingkat')
            ->whereRaw('LENGTH(TRIM(id_tingkat)) = 5')
            ->orderBy('nama_tingkat')
            ->get();

        $jenisList = DB::table('jenislahan')
            ->orderBy('id_jenis_lahan')
            ->get(['id_jenis_lahan', 'nama_jenis_lahan']);

        $aggQuery = DB::table('lahan as l')
            ->select([
                'tw_polres.id_tingkat as id_polres',
                'l.id_jenis_lahan',
                DB::raw('SUM(l.luas_lahan) as total_potensi'),
                DB::raw('SUM(COALESCE(tn.luas_tanam, 0)) as total_tanam'),
            ])
            ->join('tingkat as tw_polsek', 'l.id_tingkat', '=', 'tw_polsek.id_tingkat')
            ->join('tingkat as tw_polres', function ($j) {
                $j->whereRaw('TRIM(tw_polsek.id_tingkat) LIKE CONCAT(TRIM(tw_polres.id_tingkat), "%")')
                    ->whereRaw('LENGTH(TRIM(tw_polres.id_tingkat)) = 5');
            })
            ->leftJoin('tanam as tn', function ($j) {
                $j->on('l.id_lahan', '=', 'tn.id_lahan')->where('tn.deletestatus', '=', '1');
            })
            ->where('l.deletestatus', '1')
            ->groupBy('tw_polres.id_tingkat', 'l.id_jenis_lahan');

        // Terapkan filter dari request
        $tahun  = $this->filters['tahun']  ?? null;
        $polres = $this->filters['polres'] ?? null;
        if ($tahun)  $aggQuery->whereYear('tn.tgl_tanam', $tahun);
        if ($polres) $aggQuery->where('tw_polres.id_tingkat', $polres);
        $pivot = [];
        foreach ($aggQuery->get() as $r) {
            $pivot[trim($r->id_polres)][$r->id_jenis_lahan] = [
                'potensi' => (float) $r->total_potensi,
                'tanam'   => (float) $r->total_tanam,
            ];
        }

        $lines->push(['REKAPITULASI DATA POTENSI DAN TANAM PER POLRES']);
        $lines->push(['']);

        $h1   = ['No.', 'Nama Polres'];
        $h2   = ['',    ''];
        foreach ($jenisList as $j) {
            $h1[] = $j->nama_jenis_lahan;
            $h1[] = '';
            $h2[] = 'Potensi Lahan (Ha)';
            $h2[] = 'Tanam Lahan (Ha)';
        }
        // Kolom Jumlah di akhir
        $h1[] = 'JUMLAH';
        $h1[] = '';
        $h2[] = 'Total Potensi (Ha)';
        $h2[] = 'Total Tanam (Ha)';

        $lines->push($h1);
        $lines->push($h2);

        // perpolres
        $grandPotensi = [];
        $grandTanam   = [];
        $no = 1;

        foreach ($polresList as $pr) {
            $prId           = trim($pr->id_tingkat);
            $rowPotensiSum  = 0;
            $rowTanamSum    = 0;
            $row            = [$no++, $pr->nama_tingkat];

            foreach ($jenisList as $j) {
                $vals    = $pivot[$prId][$j->id_jenis_lahan] ?? ['potensi' => 0, 'tanam' => 0];
                $potensi = $vals['potensi'];
                $tanam   = $vals['tanam'];

                $row[] = $this->fmt($potensi);
                $row[] = $this->fmt($tanam);

                $rowPotensiSum += $potensi;
                $rowTanamSum   += $tanam;

                $grandPotensi[$j->id_jenis_lahan] = ($grandPotensi[$j->id_jenis_lahan] ?? 0) + $potensi;
                $grandTanam[$j->id_jenis_lahan]   = ($grandTanam[$j->id_jenis_lahan]   ?? 0) + $tanam;
            }

            // Kolom Jumlah
            $row[] = $this->fmt($rowPotensiSum);
            $row[] = $this->fmt($rowTanamSum);

            $lines->push($row);
        }

        // Baris TOTAL

        $footer         = ['', 'TOTAL'];
        $totalPotensiAll = 0;
        $totalTanamAll   = 0;
        foreach ($jenisList as $j) {
            $tp = $grandPotensi[$j->id_jenis_lahan] ?? 0;
            $tt = $grandTanam[$j->id_jenis_lahan]   ?? 0;
            $footer[]        = $this->fmt($tp);
            $footer[]        = $this->fmt($tt);
            $totalPotensiAll += $tp;
            $totalTanamAll   += $tt;
        }
        $footer[] = $this->fmt($totalPotensiAll);
        $footer[] = $this->fmt($totalTanamAll);
        $lines->push($footer);

        return $lines;
    }

    // ui
    private function fmt($val): string
    {
        return number_format((float)$val, 2, '.', '');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();

                $headerBg   = 'F4A7B9';
                $headerFont = '000000';
                $totalBg    = 'FFF2CC';

                // ---- MODE 1 ----
                if ($this->mode == '1') {
                    $sheet->mergeCells('A1:N1');
                    $sheet->getStyle('A1')->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $merges = [
                        'E3:F3',
                        'G3:H3',
                        'I3:J3',
                        'K3:L3',
                        'M3:N3',
                    ];
                    foreach ($merges as $m) {
                        $sheet->mergeCells($m);
                    }

                    // Style header baris 3 & 4
                    $sheet->getStyle('A3:N4')->applyFromArray([
                        'font'      => ['bold' => true],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    // Border seluruh data
                    if ($lastRow > 4) {
                        $sheet->getStyle("A5:N{$lastRow}")->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        ]);
                    }

                    // Lebar kolom data
                    foreach (range('E', 'N') as $col) {
                        $sheet->getColumnDimension($col)->setWidth(14);
                    }
                    $sheet->getColumnDimension('D')->setWidth(40);
                    $sheet->getColumnDimension('B')->setWidth(22);
                    $sheet->getColumnDimension('C')->setWidth(22);
                }

                // ---- MODE 2 ----
                if ($this->mode == '2') {
                    // Merge judul A1 sampai I1
                    $sheet->mergeCells('A1:I1');
                    $sheet->getStyle('A1')->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    // Style header baris 2
                    $sheet->getStyle('A2:I2')->applyFromArray([
                        'font'      => ['bold' => true],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    if ($lastRow > 2) {
                        $sheet->getStyle("A3:I{$lastRow}")->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        ]);
                    }

                    foreach (['E', 'F', 'G', 'H', 'I'] as $col) {
                        $sheet->getColumnDimension($col)->setWidth(18);
                    }
                    $sheet->getColumnDimension('D')->setWidth(45);
                    $sheet->getColumnDimension('B')->setWidth(22);
                    $sheet->getColumnDimension('C')->setWidth(22);
                }

                // ---- MODE 3 ----
                if ($this->mode == '3') {
                    // Merge judul
                    $sheet->mergeCells("A1:{$lastCol}1");
                    $sheet->getStyle('A1')->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    // Merge Header Row 2
                    $merges = [
                        'A2:A3', // No
                        'B2:G2', // Wilayah
                        'H2:I2', // Pemilik
                        'J2:L2', // PJ
                        'M2:N2', // Lahan & Komoditi
                        'O2:T2', // Potensi
                        'U2:X2', // Tanam
                        'Y2:AB2', // Panen
                    ];
                    foreach ($merges as $m) {
                        $sheet->mergeCells($m);
                    }

                    // Style header baris 2 & 3
                    $sheet->getStyle("A2:{$lastCol}3")->applyFromArray([
                        'font'      => ['bold' => true],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getRowDimension(2)->setRowHeight(25);
                    $sheet->getRowDimension(3)->setRowHeight(35);

                    if ($lastRow > 3) {
                        $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        ]);
                        $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Lebar kolom mode 3
                    $widths = [
                        'A' => 6,
                        'B' => 18,
                        'C' => 18,
                        'D' => 25,
                        'E' => 18,
                        'F' => 15,
                        'G' => 15,
                        'H' => 22,
                        'I' => 15,
                        'J' => 22,
                        'K' => 15,
                        'L' => 20,
                        'M' => 18,
                        'N' => 18,
                        'O' => 15,
                        'P' => 18,
                        'Q' => 20,
                        'R' => 20,
                        'S' => 12,
                        'T' => 15,
                        'U' => 15,
                        'V' => 15,
                        'W' => 15,
                        'X' => 18,
                        'Y' => 15,
                        'Z' => 18,
                        'AA' => 15,
                        'AB' => 15,
                    ];
                    foreach ($widths as $col => $w) {
                        $sheet->getColumnDimension($col)->setWidth($w);
                    }
                }

                // ---- MODE 4 ----
                if ($this->mode == '4') {
                    $jenisList = DB::table('jenislahan')
                        ->orderBy('id_jenis_lahan')
                        ->get(['id_jenis_lahan', 'nama_jenis_lahan']);

                    // Jumlah kolom data = (jenis x 2) + 2 (Jumlah) + 2 (No, Nama)
                    $totalDataCols = ($jenisList->count() * 2) + 2;

                    // Merge judul A1 sampai kolom terakhir
                    $sheet->mergeCells("A1:{$lastCol}1");
                    $sheet->getStyle('A1')->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);


                    $startColNum = 3;
                    foreach ($jenisList as $j) {
                        $sheet->mergeCellsByColumnAndRow($startColNum, 3, $startColNum + 1, 3);
                        $startColNum += 2;
                    }
                    // Merge kolom Jumlah di akhir
                    $sheet->mergeCellsByColumnAndRow($startColNum, 3, $startColNum + 1, 3);

                    // Style header baris 3 & 4
                    $sheet->getStyle("A3:{$lastCol}4")->applyFromArray([
                        'font'      => ['bold' => true],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                    $sheet->getRowDimension(3)->setRowHeight(36);
                    $sheet->getRowDimension(4)->setRowHeight(30);

                    $sheet->mergeCellsByColumnAndRow(1, 3, 1, 4);
                    $sheet->mergeCellsByColumnAndRow(2, 3, 2, 4);

                    $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $totalBg]],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    if ($lastRow > 4) {
                        $sheet->getStyle("A5:{$lastCol}{$lastRow}")->applyFromArray([
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                    }

                    // Lebar kolom
                    $sheet->getColumnDimension('A')->setWidth(5);
                    $sheet->getColumnDimension('B')->setWidth(28);
                    $sheet->getStyle("B5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $colIdx = 3; // mulai dari kolom C
                    foreach ($jenisList as $j) {
                        $sheet->getColumnDimensionByColumn($colIdx)->setWidth(16);
                        $sheet->getColumnDimensionByColumn($colIdx + 1)->setWidth(16);
                        $colIdx += 2;
                    }
                    $sheet->getColumnDimensionByColumn($colIdx)->setWidth(18);
                    $sheet->getColumnDimensionByColumn($colIdx + 1)->setWidth(18);
                }

                $freezeRow = ($this->mode == '1' || $this->mode == '4') ? 5 : ($this->mode == '3' ? 4 : 3);
                $sheet->freezePane("A{$freezeRow}");
            },
        ];
    }


    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 25, 'C' => 25];
    }
}
