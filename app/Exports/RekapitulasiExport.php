<?php

namespace App\Exports;

use App\Models\RekapitulasiLahan;
use Maatwebsite\Excel\Concerns\{FromCollection, Exportable, WithColumnWidths, WithStyles, WithEvents, WithTitle, WithMultipleSheets};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RekapitulasiExport implements WithMultipleSheets
{
    use Exportable;

    protected $filters;
    protected $scope;

    /**
     * @param array         $filters  User-selected filter params
     * @param \Closure|null $scope    Optional jurisdictional scope closure (operator only)
     */
    public function __construct(array $filters, ?\Closure $scope = null)
    {
        $this->filters = $filters;
        $this->scope   = $scope;
        set_time_limit(300);
        ini_set('memory_limit', '512M');
    }

    public function sheets(): array
    {
        return [
            new RekapitulasiSheet($this->filters, 'polres', $this->scope),
            new RekapitulasiSheet($this->filters, 'desa',   $this->scope)
        ];
    }
}

class RekapitulasiSheet implements FromCollection, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    protected $filters;
    protected $type;
    protected $scope;

    public function __construct(array $filters, string $type, ?\Closure $scope = null)
    {
        $this->filters = $filters;
        $this->type    = $type;
        $this->scope   = $scope;
    }

    public function title(): string
    {
        return $this->type === 'polres' ? 'Rekap Polres' : 'Rincian Polsek & Desa';
    }

    public function collection()
    {
        $lines = new Collection();
        $baseQuery = RekapitulasiLahan::query();
        if ($this->scope) {
            ($this->scope)($baseQuery);
        }

        if ($this->type === 'polres') {
            $data = (clone $baseQuery)->filter($this->filters)
                ->select(
                    'nama_polres',
                    'id_polres',
                    DB::raw('SUM(total_titik_lahan) as total_titik_lahan'),
                    DB::raw('SUM(kapasitas_lahan_ha) as kapasitas_lahan_ha'),
                    DB::raw('SUM(aktual_tanam_ha) as aktual_tanam_ha'),
                    DB::raw('SUM(total_produksi_panen) as total_produksi_panen'),
                    DB::raw($this->getSerapanSubquery(1, 'polres') . ' as serapan_bulog'),
                    DB::raw($this->getSerapanSubquery(2, 'polres') . ' as serapan_pabrik'),
                    DB::raw($this->getSerapanSubquery(3, 'polres') . ' as serapan_tengkulak'),
                    DB::raw($this->getSerapanSubquery(4, 'polres') . ' as serapan_konsumsi')
                )
                ->groupBy('nama_polres', 'id_polres')
                ->orderBy('nama_polres')
                ->get();

            $lines->push(['REKAPITULASI PROGRES PER POLRES']);
            $lines->push(['']);
            $lines->push([
                'No',
                'Nama Satuan (Polres)',
                'Total Titik Potensi',
                'Total Titik Tanam',
                'Total Luas Potensi (Ha)',
                'Total Luas Tanam (Ha)',
                'Persentase (%)',
                'Total Produksi (Ton)',
                'Serapan Bulog (Ton)',
                'Serapan Pabrik (Ton)',
                'Serapan Tengkulak (Ton)',
                'Konsumsi (Ton)',
                'Total Serapan (Ton)'
            ]);

            $no = 1;
            foreach ($data as $r) {
                $potensi = (float) $r->kapasitas_lahan_ha;
                $tanam = (float) $r->aktual_tanam_ha;
                $persentase = $potensi > 0 ? ($tanam / $potensi) * 100 : 0;

                $titikPotensi = (int) $r->total_titik_lahan;
                $titikTanam = $tanam > 0 ? $titikPotensi : 0;

                $bulog = (float)$r->serapan_bulog;
                $pabrik = (float)$r->serapan_pabrik;
                $tengkulak = (float)$r->serapan_tengkulak;
                $konsumsi = (float)$r->serapan_konsumsi;
                $totalSerapan = $bulog + $pabrik + $tengkulak + $konsumsi;

                $lines->push([
                    $no++,
                    $r->nama_polres ?? '-',
                    $titikPotensi,
                    $titikTanam,
                    $this->fmt($potensi),
                    $this->fmt($tanam),
                    $this->fmt($persentase) . '%',
                    $this->fmt($r->total_produksi_panen),
                    $this->fmt($bulog),
                    $this->fmt($pabrik),
                    $this->fmt($tengkulak),
                    $this->fmt($konsumsi),
                    $this->fmt($totalSerapan),
                ]);
            }
        } else {
            $data = (clone $baseQuery)->filter($this->filters)
                ->select('*')
                ->addSelect([
                    'serapan_bulog' => DB::raw($this->getSerapanSubquery(1, 'desa')),
                    'serapan_pabrik' => DB::raw($this->getSerapanSubquery(2, 'desa')),
                    'serapan_tengkulak' => DB::raw($this->getSerapanSubquery(3, 'desa')),
                    'serapan_konsumsi' => DB::raw($this->getSerapanSubquery(4, 'desa')),
                ])
                ->orderBy('nama_polres')
                ->orderBy('nama_polsek')
                ->orderBy('nama_desa')
                ->get();

            $lines->push(['RINCIAN PROGRES POLSEK DAN DESA']);
            $lines->push(['']);
            $lines->push([
                'No',
                'Polres',
                'Polsek',
                'Desa',
                'Total Titik Potensi',
                'Total Titik Tanam',
                'Total Luas Potensi (Ha)',
                'Total Luas Tanam (Ha)',
                'Persentase (%)',
                'Total Produksi (Ton)',
                'Serapan Bulog (Ton)',
                'Serapan Pabrik (Ton)',
                'Serapan Tengkulak (Ton)',
                'Konsumsi (Ton)',
                'Total Serapan (Ton)'
            ]);

            $no = 1;
            foreach ($data as $r) {
                $titikPotensi = (int) $r->total_titik_lahan;
                $titikTanam = $r->aktual_tanam_ha > 0 ? $titikPotensi : 0;

                $bulog = (float)$r->serapan_bulog;
                $pabrik = (float)$r->serapan_pabrik;
                $tengkulak = (float)$r->serapan_tengkulak;
                $konsumsi = (float)$r->serapan_konsumsi;
                $totalSerapan = $bulog + $pabrik + $tengkulak + $konsumsi;

                $lines->push([
                    $no++,
                    $r->nama_polres ?? '-',
                    $r->nama_polsek ?? '-',
                    $r->nama_desa ?? '-',
                    $titikPotensi,
                    $titikTanam,
                    $this->fmt($r->kapasitas_lahan_ha),
                    $this->fmt($r->aktual_tanam_ha),
                    $this->fmt($r->persentase_serapan) . '%',
                    $this->fmt($r->total_produksi_panen),
                    $this->fmt($bulog),
                    $this->fmt($pabrik),
                    $this->fmt($tengkulak),
                    $this->fmt($konsumsi),
                    $this->fmt($totalSerapan),
                ]);
            }
        }

        return $lines;
    }

    private function fmt($val): string
    {
        return number_format((float)$val, 2, '.', '');
    }

    private function getSerapanSubquery($distribusiKe, $level)
    {
        $filterSql = "d.deletestatus = 0 AND d.distribusi_ke = " . (int)$distribusiKe;

        if (!empty($this->filters['tahun'])) {
            $filterSql .= " AND YEAR(d.tgl_distribusi) = " . (int)$this->filters['tahun'];
        }

        if (!empty($this->filters['bulan']) && $this->filters['bulan'] !== 'SEMUA BULAN') {
            $bulanIndo = ['Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12];
            if (isset($bulanIndo[$this->filters['bulan']])) {
                $filterSql .= " AND MONTH(d.tgl_distribusi) = " . $bulanIndo[$this->filters['bulan']];
            }
        }

        if (!empty($this->filters['kwartal'])) {
            $kwartalMap = ['KWARTAL I' => 1, 'KWARTAL II' => 2, 'KWARTAL III' => 3, 'KWARTAL IV' => 4];
            $q = $kwartalMap[strtoupper($this->filters['kwartal'])] ?? null;
            if ($q) {
                $filterSql .= " AND QUARTER(d.tgl_distribusi) = " . (int)$q;
            }
        }

        if (!empty($this->filters['jenis_lahan'])) {
            $filterSql .= " AND l.id_jenis_lahan = " . (int)$this->filters['jenis_lahan'];
        }
        if (!empty($this->filters['komoditi'])) {
            $filterSql .= " AND l.id_komoditi = " . (int)$this->filters['komoditi'];
        }

        if ($level === 'polres') {
            return "(SELECT SUM(d.total_distribusi) FROM distribusi d JOIN lahan l ON d.id_lahan = l.id_lahan WHERE l.id_tingkat LIKE CONCAT(view_rekapitulasi_lahan.id_polres, '%') AND $filterSql)";
        } else {
            return "(SELECT SUM(d.total_distribusi) FROM distribusi d JOIN lahan l ON d.id_lahan = l.id_lahan WHERE l.id_tingkat = view_rekapitulasi_lahan.id_wilayah AND $filterSql)";
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $headerBg   = '10B981';
                $headerFont = 'FFFFFF';
                $lastCol    = $this->type === 'polres' ? 'M' : 'O';

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => $headerFont]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBg]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                if ($lastRow > 3) {
                    $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);

                    $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    if ($this->type === 'polres') {
                        $sheet->getStyle("C4:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("G4:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I4:M{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    } else {
                        $sheet->getStyle("E4:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I4:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("K4:O{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                        // Vertical Merge Logic for Polres and Polsek
                        $currentPolres = null;
                        $startPolresRow = 4;
                        $currentPolsek = null;
                        $startPolsekRow = 4;

                        for ($i = 4; $i <= $lastRow; $i++) {
                            $polresValue = $sheet->getCell("B{$i}")->getValue();
                            $polsekValue = $sheet->getCell("C{$i}")->getValue();

                            $polresChanged = ($polresValue !== $currentPolres);

                            // Merge Polsek (must break if Polres changed)
                            if ($polsekValue !== $currentPolsek || $polresChanged) {
                                if ($currentPolsek !== null && ($i - 1) > $startPolsekRow) {
                                    $sheet->mergeCells("C{$startPolsekRow}:C" . ($i - 1));
                                }
                                $currentPolsek = $polsekValue;
                                $startPolsekRow = $i;
                            }

                            // Merge Polres
                            if ($polresChanged) {
                                if ($currentPolres !== null && ($i - 1) > $startPolresRow) {
                                    $sheet->mergeCells("B{$startPolresRow}:B" . ($i - 1));
                                }
                                $currentPolres = $polresValue;
                                $startPolresRow = $i;
                            }
                        }

                        // Close remaining merges
                        if ($startPolresRow < $lastRow) {
                            $sheet->mergeCells("B{$startPolresRow}:B{$lastRow}");
                        }
                        if ($startPolsekRow < $lastRow) {
                            $sheet->mergeCells("C{$startPolsekRow}:C{$lastRow}");
                        }

                        // Align vertically to center for merged columns
                        $sheet->getStyle("B4:C{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }

                $sheet->freezePane('A4');
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
        if ($this->type === 'polres') {
            return [
                'A' => 6,
                'B' => 30,
                'C' => 18,
                'D' => 18,
                'E' => 22,
                'F' => 22,
                'G' => 16,
                'H' => 22,
                'I' => 18,
                'J' => 18,
                'K' => 18,
                'L' => 18,
                'M' => 22,
            ];
        }

        return [
            'A' => 6,
            'B' => 25,
            'C' => 25,
            'D' => 25,
            'E' => 18,
            'F' => 18,
            'G' => 22,
            'H' => 22,
            'I' => 16,
            'J' => 22,
            'K' => 18,
            'L' => 18,
            'M' => 18,
            'N' => 18,
            'O' => 22,
        ];
    }
}
