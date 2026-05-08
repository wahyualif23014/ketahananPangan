<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = Illuminate\Support\Facades\DB::table('distribusi')
    ->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
    ->where('distribusi.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0')
    ->whereNotNull('distribusi.valid_oleh');

echo "Total Serapan (Valid): " . $q->sum('distribusi.total_distribusi') . "\n";

$q2 = Illuminate\Support\Facades\DB::table('distribusi')
    ->join('lahan', 'distribusi.id_lahan', '=', 'lahan.id_lahan')
    ->where('distribusi.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0');

echo "Total Serapan (Semua): " . $q2->sum('distribusi.total_distribusi') . "\n";
