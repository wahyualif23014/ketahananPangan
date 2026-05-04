<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = new \Illuminate\Http\Request();
$req->merge(["search"=>"a"]);
$c = new \App\Http\Controllers\Admin\PotensiLahanController();
try {
    $res = $c->index($req);
    echo "PotensiLahan Success\n";
} catch (\Exception $e) {
    echo "PotensiLahan Error: " . $e->getMessage() . "\n";
}

$c2 = new \App\Http\Controllers\Admin\KelolaLahanController();
try {
    $res2 = $c2->index($req);
    echo "KelolaLahan Success\n";
} catch (\Exception $e) {
    echo "KelolaLahan Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

