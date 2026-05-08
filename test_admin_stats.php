<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/admin/kelola-lahan', 'GET');
$controller = $app->make(App\Http\Controllers\Admin\KelolaLahanController::class);
$response = $controller->index($request);

$data = $response->getData();
echo "Potensi: " . $data['stats']['potensi'] . "\n";
echo "Tanam: " . $data['stats']['tanam'] . "\n";
echo "Panen: " . $data['stats']['panen'] . "\n";
echo "Serapan: " . $data['stats']['serapan'] . "\n";
