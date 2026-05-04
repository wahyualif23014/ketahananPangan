<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo json_encode(Illuminate\Support\Facades\Schema::getColumnListing('lahan')) . "\n";
echo json_encode(Illuminate\Support\Facades\Schema::getColumnListing('panen')) . "\n";
