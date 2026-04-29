<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$views = ['view_tanam', 'view_panen', 'view_serapan'];
foreach ($views as $v) {
    echo "VIEW $v:\n";
    $res = DB::select("SHOW CREATE VIEW $v");
    print_r($res[0]->{'Create View'});
    echo "\n\n";
}
