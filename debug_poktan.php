<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$ctrl = app()->make(\App\Http\Controllers\Admin\KelolaLahanController::class);
$res = $ctrl->poktanIndex($request);
print_r($res->getData()['data']->items());
