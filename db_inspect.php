<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['tanam', 'panen', 'distribusi'];
$result = [];
foreach ($tables as $table) {
    $result[$table] = Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM $table");
}
echo json_encode($result, JSON_PRETTY_PRINT);
