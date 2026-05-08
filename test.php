<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = Illuminate\Support\Facades\DB::table('tanam')->where('id_tanam', 25)->update(['valid_oleh' => null]);
var_dump($t);
$row = Illuminate\Support\Facades\DB::table('tanam')->where('id_tanam', 25)->first();
var_dump($row);
