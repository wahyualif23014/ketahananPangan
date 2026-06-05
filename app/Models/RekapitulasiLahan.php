<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapitulasiLahan extends Model
{
    use HasFactory;
    protected $table = 'view_rekapitulasi_lahan';
    public $timestamps = true;
    protected $primaryKey = null;
    public $incrementing = false;

    public function scopeFilter($query, array $filters)
    {

        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_desa', 'like', '%' . trim($search) . '%')
                    ->orWhere('nama_polsek', 'like', '%' . trim($search) . '%')
                    ->orWhere('nama_polres', 'like', '%' . trim($search) . '%');
            });
        });
        $query->when($filters['polres'] ?? null, function ($query, $polres) {
            $query->where('id_polres', trim($polres));
        })->when($filters['polsek'] ?? null, function ($query, $polsek) {
            $query->where('id_polsek', trim($polsek));
        });

        $query->when($filters['jenis_lahan'] ?? null, function ($query, $jenis) {
            $query->whereRaw("FIND_IN_SET(?, ids_jenis)", [trim($jenis)]);
        })->when($filters['komoditi'] ?? null, function ($query, $komoditi) {
            $query->whereRaw("FIND_IN_SET(?, ids_komoditi)", [trim($komoditi)]);
        });

        $periode = $filters['periode'] ?? 'tahun';

        if ($periode === 'tahun') {
            $query->when($filters['tahun'] ?? null, function ($query, $tahun) {
                $query->where(function ($q) use ($tahun) {
                    $q->where('tahun_lahan', $tahun)
                        ->orWhereNull('tahun_lahan');
                });
            });

            if (!empty($filters['bulan']) && $filters['bulan'] !== 'SEMUA BULAN') {
                $bulanIndo = [
                    'Januari' => 1,
                    'Februari' => 2,
                    'Maret' => 3,
                    'April' => 4,
                    'Mei' => 5,
                    'Juni' => 6,
                    'Juli' => 7,
                    'Agustus' => 8,
                    'September' => 9,
                    'Oktober' => 10,
                    'November' => 11,
                    'Desember' => 12
                ];
                if (isset($bulanIndo[$filters['bulan']])) {
                    $query->where(function ($q) use ($bulanIndo, $filters) {
                        $q->whereRaw('MONTH(datetransaction) = ?', [$bulanIndo[$filters['bulan']]])
                            ->orWhereNull('tahun_lahan'); // Tetap izinkan Polsek kosong muncul
                    });
                }
            }
        } elseif ($periode === 'kwartal') {
            $query->when($filters['tahun'] ?? null, function ($query, $tahun) {
                $query->where(function ($q) use ($tahun) {
                    $q->where('tahun_lahan', $tahun)
                        ->orWhereNull('tahun_lahan');
                });
            });

            if (!empty($filters['kwartal'])) {
                $kwartalMap = ['KWARTAL I' => 1, 'KWARTAL II' => 2, 'KWARTAL III' => 3, 'KWARTAL IV' => 4];
                $q = $kwartalMap[strtoupper($filters['kwartal'])] ?? null;
                if ($q) {
                    $query->where(function ($query) use ($q) {
                        $query->whereRaw('QUARTER(datetransaction) = ?', [$q])
                            ->orWhereNull('tahun_lahan'); // Tetap izinkan Polsek kosong muncul
                    });
                }
            }
        }

        return $query;
    }

    public function scopeWithSerapanDetails($query, array $filters)
    {
        $getSerapanSql = function ($distribusiKe) use ($filters) {
            $filterSql = "d.deletestatus != '0' AND d.distribusi_ke = " . (int)$distribusiKe;

            if (!empty($filters['tahun'])) {
                $filterSql .= " AND YEAR(d.tgl_distribusi) = " . (int)$filters['tahun'];
            }

            if (!empty($filters['bulan']) && $filters['bulan'] !== 'SEMUA BULAN') {
                $bulanIndo = ['Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12];
                if (isset($bulanIndo[$filters['bulan']])) {
                    $filterSql .= " AND MONTH(d.tgl_distribusi) = " . $bulanIndo[$filters['bulan']];
                }
            }

            if (!empty($filters['kwartal'])) {
                $kwartalMap = ['KWARTAL I' => 1, 'KWARTAL II' => 2, 'KWARTAL III' => 3, 'KWARTAL IV' => 4];
                $q = $kwartalMap[strtoupper($filters['kwartal'])] ?? null;
                if ($q) {
                    $filterSql .= " AND QUARTER(d.tgl_distribusi) = " . (int)$q;
                }
            }

            if (!empty($filters['jenis_lahan'])) {
                $filterSql .= " AND l.id_jenis_lahan = " . (int)$filters['jenis_lahan'];
            }
            if (!empty($filters['komoditi'])) {
                $filterSql .= " AND l.id_komoditi = " . (int)$filters['komoditi'];
            }

            return "(SELECT SUM(d.total_distribusi) FROM distribusi d JOIN lahan l ON d.id_lahan = l.id_lahan WHERE l.id_wilayah = view_rekapitulasi_lahan.id_wilayah AND $filterSql)";
        };

        return $query->addSelect([
            \Illuminate\Support\Facades\DB::raw($getSerapanSql(1) . ' as serapan_bulog'),
            \Illuminate\Support\Facades\DB::raw($getSerapanSql(2) . ' as serapan_pabrik'),
            \Illuminate\Support\Facades\DB::raw($getSerapanSql(3) . ' as serapan_tengkulak'),
            \Illuminate\Support\Facades\DB::raw($getSerapanSql(4) . ' as serapan_konsumsi'),
        ]);
    }
}
