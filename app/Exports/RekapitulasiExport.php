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

    public function __construct(array $filters)
    {
        $this->filters = $filters;
        set_time_limit(300);
        ini_set('memory_limit', '512M');
    }

    public function sheets(): array
    {
        return [
            new RekapitulasiSheet($this->filters, 'polres'),
            new RekapitulasiSheet($this->filters, 'desa')
        ];
    }
}

class RekapitulasiSheet implements FromCollection, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    protected $filters;
    protected $type;

    public function __construct(array $filters, string $type)
    {
        $this->filters = $filters;
        $this->type = $type;
    }

    public function title(): string
    {
        return $this->type === 'polres' ? 'Rekap Polres' : 'Rincian Polsek & Desa';
    }

    public function collection()
    {
        $lines = new Collection();

        if ($this->type === 'polres') {
            $data = RekapitulasiLahan::filter($this->filters)
                ->select(
                    'nama_polres',
                    DB::raw('SUM(total_titik_lahan) as total_titik_lahan'),
                    DB::raw('SUM(kapasitas_lahan_ha) as kapasitas_lahan_ha'),
                    DB::raw('SUM(aktual_tanam_ha) as aktual_tanam_ha'),
                    DB::raw('SUM(total_produksi_panen) as total_produksi_panen')
                )
                ->groupBy('nama_polres')
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
                'Total Serapan Bulog (Ton)'
            ]);

            $no = 1;
            foreach ($data as $r) {
                $potensi = (float) $r->kapasitas_lahan_ha;
                $tanam = (float) $r->aktual_tanam_ha;
                $persentase = $potensi > 0 ? ($tanam / $potensi) * 100 : 0;

                $titikPotensi = (int) $r->total_titik_lahan;
                $titikTanam = $tanam > 0 ? $titikPotensi : 0;

                $lines->push([
                    $no++,
                    $r->nama_polres ?? '-',
                    $titikPotensi,
                    $titikTanam,
                    $this->fmt($potensi),
                    $this->fmt($tanam),
                    $this->fmt($persentase) . '%',
                    $this->fmt($r->total_produksi_panen),
                    '0.00',
                ]);
            }
        } else {
            $data = RekapitulasiLahan::filter($this->filters)
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
                'Total Serapan Bulog (Ton)'
            ]);

            $no = 1;
            foreach ($data as $r) {
                $titikPotensi = (int) $r->total_titik_lahan;
                $titikTanam = $r->aktual_tanam_ha > 0 ? $titikPotensi : 0;

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
                    '0.00',
                ]);
            }
        }

        return $lines;
    }

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

                $headerBg   = '10B981';
                $headerFont = 'FFFFFF';
                $lastCol    = $this->type === 'polres' ? 'I' : 'K';

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
                    } else {
                        $sheet->getStyle("E4:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I4:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
                'I' => 24,
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
            'K' => 24,
        ];
    }
}
