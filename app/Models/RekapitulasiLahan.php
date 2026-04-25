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
}
