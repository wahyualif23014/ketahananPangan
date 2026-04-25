<?php
require "vendor/autoload.php";
require "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

Illuminate\Support\Facades\DB::statement("DROP VIEW IF EXISTS view_rekapitulasi_lahan");
Illuminate\Support\Facades\DB::statement("
    CREATE OR REPLACE VIEW view_rekapitulasi_lahan AS
    SELECT 
        SUBSTRING_INDEX(l.id_tingkat, '.', 2) AS id_polres, 
        t_polres.nama_tingkat AS nama_polres,
        l.id_tingkat AS id_polsek,
        t_polsek.nama_tingkat AS nama_polsek,
        l.id_wilayah,
        w.nama_wilayah AS nama_desa,

        l.id_jenis_lahan,
        jl.nama_jenis_lahan,
        l.id_komoditi,
        k.nama_komoditi,
        l.tahun_lahan,

        COUNT(l.id_lahan) AS total_titik_lahan,
        SUM(l.luas_lahan) AS kapasitas_lahan_ha,
        SUM(l.jml_petani) AS total_sdm_petani,
        
        SUM(COALESCE(t_data.total_tanam, 0)) AS aktual_tanam_ha,
        SUM(COALESCE(p_data.total_luas_panen, 0)) AS aktual_panen_ha,
        SUM(COALESCE(p_data.total_hasil_panen, 0)) AS total_produksi_panen,

        CASE 
            WHEN SUM(l.luas_lahan) > 0 
            THEN ROUND((SUM(COALESCE(t_data.total_tanam, 0)) / SUM(l.luas_lahan)) * 100, 2)
            ELSE 0 
        END AS persentase_serapan

    FROM lahan l
    LEFT JOIN wilayah w ON l.id_wilayah = w.id_wilayah
    LEFT JOIN (SELECT id_tingkat, MAX(nama_tingkat) as nama_tingkat FROM tingkat GROUP BY id_tingkat) t_polsek 
        ON l.id_tingkat = t_polsek.id_tingkat
    LEFT JOIN (SELECT id_tingkat, MAX(nama_tingkat) as nama_tingkat FROM tingkat GROUP BY id_tingkat) t_polres 
        ON SUBSTRING_INDEX(l.id_tingkat, '.', 2) = t_polres.id_tingkat
    LEFT JOIN komoditi k ON l.id_komoditi = k.id_komoditi
    LEFT JOIN (SELECT id_jenis_lahan, MAX(nama_jenis_lahan) as nama_jenis_lahan FROM jenislahan GROUP BY id_jenis_lahan) jl 
        ON l.id_jenis_lahan = jl.id_jenis_lahan
    
    LEFT JOIN (
        SELECT id_lahan, SUM(luas_tanam) as total_tanam 
        FROM tanam WHERE deletestatus = '1' GROUP BY id_lahan
    ) t_data ON l.id_lahan = t_data.id_lahan
    LEFT JOIN (
        SELECT id_lahan, SUM(luas_panen) as total_luas_panen, SUM(total_panen) as total_hasil_panen
        FROM panen WHERE deletestatus = '1' GROUP BY id_lahan
    ) p_data ON l.id_lahan = p_data.id_lahan
    
    WHERE l.deletestatus = '1'
    GROUP BY 
        id_polres, nama_polres, id_polsek, nama_polsek, l.id_wilayah, w.nama_wilayah,
        l.id_jenis_lahan, jl.nama_jenis_lahan, l.id_komoditi, k.nama_komoditi, l.tahun_lahan
");
echo "OK";
