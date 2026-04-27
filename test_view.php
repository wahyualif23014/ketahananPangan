<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    view('admin.dashboard', [
        'quarterFilter'=>'all', 'yearFilter'=>'2026', 'potensiTotal'=>0, 'jenisLahanList'=>[], 
        'potensiDetails'=>[], 'tanamTotal'=>0, 'tanamDetails'=>[], 'panenTotal'=>0, 
        'panenDetails'=>[], 'totalTitikLahan'=>0, 'totalPolsek'=>0, 'totalSerapan'=>0, 
        'serapanBulog'=>0, 'serapanPabrik'=>0, 'serapanTengkulak'=>0, 'serapanKonsumsi'=>0, 
        'harvestStats'=>[], 'plantingAnalytics'=>[], 'harvestingAnalytics'=>[], 
        'kwartalData'=>[], 'mapData'=>[], 'pendingValidation'=>collect([]), 
        'totalPendingSatwil'=>0, 'chartMonthlyData'=>[], 'chartYearlyLabels'=>[], 
        'chartYearlyData'=>[], 'chartTahunan'=>[], 'chartBulanan'=>[], 'polsekAktif'=>0
    ])->render();
    echo "OK";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}
