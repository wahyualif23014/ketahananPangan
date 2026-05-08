<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/operator/kelola-lahan', 'GET');
$controller = $app->make(App\Http\Controllers\Operator\KelolaLahanController::class);
$response = $controller->index($request);

// The response is a View. We can get the data passed to it.
$data = $response->getData();
echo "Potensi: " . $data['stats']['potensi'] . "\n";
echo "Tanam: " . $data['stats']['tanam'] . "\n";
echo "Panen: " . $data['stats']['panen'] . "\n";
echo "Serapan: " . $data['stats']['serapan'] . "\n";
