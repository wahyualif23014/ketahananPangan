<?php
$pdo = new PDO('mysql:host=localhost;dbname=sdmapps', 'root', '');
// Check jenislahan
$stmt = $pdo->query("SELECT * FROM jenislahan");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

// Quarter panen
$stmt2 = $pdo->query("
    SELECT QUARTER(p.tgl_panen) as q, SUM(p.total_panen) as total_ton, SUM(p.luas_panen) as total_ha, l.id_jenis_lahan
    FROM panen p
    JOIN lahan l ON p.id_lahan = l.id_lahan
    WHERE p.deletestatus = '1' AND YEAR(p.tgl_panen) = 2026
    GROUP BY q, l.id_jenis_lahan
");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
