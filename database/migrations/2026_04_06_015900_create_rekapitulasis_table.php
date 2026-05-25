<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // SAFETY NET: Membuat tabel master otomatis jika belum terbuat di antrean awal
        if (!Schema::hasTable('tingkat')) {
            Schema::create('tingkat', function (Blueprint $table) {
                $table->string('id_tingkat', 50)->primary();
                $table->string('nama_tingkat', 100)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tingkatwilayah')) {
            Schema::create('tingkatwilayah', function (Blueprint $table) {
                $table->string('id_tingkat', 50)->nullable();
                $table->string('id_wilayah', 50)->nullable();
            });
        }

        if (!Schema::hasTable('wilayah')) {
            Schema::create('wilayah', function (Blueprint $table) {
                $table->string('id_wilayah', 50)->primary();
                $table->string('nama_wilayah', 100)->nullable();
                $table->timestamps();
            });
        }

        // JINAKKAN RANJAU BARU: Force create tabel komoditi (akhiran -i)
        if (!Schema::hasTable('komoditi')) {
            Schema::create('komoditi', function (Blueprint $table) {
                $table->string('id_komoditi', 50)->primary();
                $table->string('nama_komoditi', 100)->nullable();
                $table->timestamps();
            });
        }

        // Eksekusi Pembuatan VIEW Utama
        DB::statement("DROP VIEW IF EXISTS view_rekapitulasi_lahan");

        DB::statement("
            CREATE VIEW view_rekapitulasi_lahan AS
            SELECT 
                t_polres.id_tingkat AS id_polres, 
                t_polres.nama_tingkat AS nama_polres,
                t_polsek.id_tingkat AS id_polsek,
                t_polsek.nama_tingkat AS nama_polsek,
                w_desa.id_wilayah AS id_wilayah,
                w_desa.nama_wilayah AS nama_desa,
                l_sum.ids_jenis,
                l_sum.ids_komoditi,
                l_sum.names_jenis AS nama_jenis_lahan,
                l_sum.names_komoditi AS nama_komoditi,
                l_sum.tahun_rekap AS tahun_lahan,
                COALESCE(l_sum.datetransaction, CAST(CONCAT(YEAR(CURDATE()), '-01-01') AS DATE)) AS datetransaction,
                COALESCE(l_sum.total_titik, 0) AS total_titik_lahan,
                COALESCE(l_sum.total_luas, 0) AS kapasitas_lahan_ha,
                COALESCE(l_sum.total_sdm, 0) AS total_sdm_petani,
                COALESCE(l_sum.aktual_tanam, 0) AS aktual_tanam_ha,
                COALESCE(l_sum.aktual_panen, 0) AS aktual_panen_ha,
                COALESCE(l_sum.total_produksi, 0) AS total_produksi_panen,
                CASE 
                    WHEN l_sum.total_luas > 0 
                    THEN ROUND((COALESCE(l_sum.aktual_tanam, 0) / l_sum.total_luas) * 100, 2)
                    ELSE 0 
                END AS persentase_serapan
            FROM (SELECT id_tingkat, nama_tingkat FROM tingkat WHERE LENGTH(TRIM(id_tingkat)) = 5) t_polres
            LEFT JOIN tingkat t_polsek ON t_polsek.id_tingkat LIKE CONCAT(t_polres.id_tingkat, '.%')
            LEFT JOIN (
                SELECT tw.id_tingkat, w.id_wilayah, w.nama_wilayah 
                FROM tingkatwilayah tw
                INNER JOIN wilayah w ON w.id_wilayah LIKE CONCAT(tw.id_wilayah, '%')
                WHERE LENGTH(w.id_wilayah) > 8
            ) w_desa ON t_polsek.id_tingkat = w_desa.id_tingkat
            LEFT JOIN (
                SELECT 
                    l.id_wilayah, 
                    MIN(tn.tgl_tanam) as datetransaction, 
                    COALESCE(YEAR(MIN(tn.tgl_tanam)), MAX(l.tahun_lahan)) as tahun_rekap, 
                    COUNT(DISTINCT l.id_lahan) as total_titik,
                    SUM(l.luas_lahan) as total_luas,
                    SUM(l.jml_petani) as total_sdm,
                    GROUP_CONCAT(DISTINCT l.id_jenis_lahan) as ids_jenis,
                    GROUP_CONCAT(DISTINCT l.id_komoditi) as ids_komoditi,
                    GROUP_CONCAT(DISTINCT jl.nama_jenis_lahan SEPARATOR ', ') as names_jenis,
                    GROUP_CONCAT(DISTINCT k.nama_komoditi SEPARATOR ', ') as names_komoditi,
                    SUM(COALESCE(tn.luas_tanam, 0)) as aktual_tanam,
                    SUM(COALESCE(p_sub.sum_panen_ha, 0)) as aktual_panen,
                    SUM(COALESCE(p_sub.sum_panen_ton, 0)) as total_produksi
                FROM lahan l
                LEFT JOIN tanam tn ON l.id_lahan = tn.id_lahan AND tn.deletestatus = '1'
                LEFT JOIN jenislahan jl ON l.id_jenis_lahan = jl.id_jenis_lahan
                LEFT JOIN komoditi k ON l.id_komoditi = k.id_komoditi
                LEFT JOIN (
                    SELECT id_lahan, 
                           YEAR(tgl_panen) as thn, 
                           QUARTER(tgl_panen) as qtr, 
                           SUM(luas_panen) as sum_panen_ha, 
                           SUM(total_panen) as sum_panen_ton 
                    FROM panen 
                    WHERE deletestatus = '1' 
                    GROUP BY id_lahan, thn, qtr
                ) p_sub ON l.id_lahan = p_sub.id_lahan 
                     AND p_sub.thn = YEAR(tn.tgl_tanam)
                     AND p_sub.qtr = QUARTER(tn.tgl_tanam)
                WHERE l.deletestatus = '1' 
                GROUP BY l.id_wilayah, YEAR(tn.tgl_tanam), QUARTER(tn.tgl_tanam)
            ) l_sum ON w_desa.id_wilayah = l_sum.id_wilayah
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_rekapitulasi_lahan");
        Schema::dropIfExists('tingkat');
        Schema::dropIfExists('tingkatwilayah');
        Schema::dropIfExists('wilayah');
        Schema::dropIfExists('komoditi');
    }
};