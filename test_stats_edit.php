<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = Illuminate\Support\Facades\DB::table('distribusi')->whereNotNull('valid_oleh')->first();
echo "Editing record ID: " . $d->id_distribusi . " with total_distribusi = " . $d->total_distribusi . "\n";
Illuminate\Support\Facades\DB::table('distribusi')->where('id_distribusi', $d->id_distribusi)->update(['valid_oleh' => null]);

$request = Illuminate\Http\Request::create('/operator/kelola-lahan', 'GET');
$controller = $app->make(App\Http\Controllers\Operator\KelolaLahanController::class);
$response = $controller->index($request);

$data = $response->getData();
echo "Serapan after edit: " . $data['stats']['serapan'] . "\n";
