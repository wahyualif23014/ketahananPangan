<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasColumn('tanam', 'status_akhiri_siklus')) {
    DB::statement("ALTER TABLE tanam ADD COLUMN status_akhiri_siklus TINYINT DEFAULT 0 COMMENT '0=Belum, 1=Diajukan, 2=Diterima, 3=Ditolak'");
    echo "Column status_akhiri_siklus added.\n";
}
if (!Schema::hasColumn('tanam', 'alasan_tolak_akhiri_siklus')) {
    DB::statement("ALTER TABLE tanam ADD COLUMN alasan_tolak_akhiri_siklus TEXT NULL");
    echo "Column alasan_tolak_akhiri_siklus added.\n";
}
