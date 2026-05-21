<?php
$file = 'app/Http/Controllers/Operator/KelolaLahanController.php';
$c = file_get_contents($file);

$functions = [
    'validasiTanam', 'validasiPanen', 'validasiSerapan',
    'unvalidasiTanam', 'unvalidasiPanen', 'unvalidasiSerapan',
    'selesaiSiklusTanam', 'unvalidasiSiklusTanam'
];

foreach ($functions as $fn) {
    $c = preg_replace(
        '/public function ' . $fn . '\(Request \$request, \$id\)\s*\{\s*\$user = auth\(\)->user\(\);/s',
        "public function $fn(Request \$request, \$id)\n    {\n        \$user = auth()->user();\n        if (\$user && substr_count((string)\$user->id_tugas, '.') >= 2) {\n            if (\$request->wantsJson()) return response()->json(['success' => false, 'message' => 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.'], 403);\n            return back()->with('error', 'Akses ditolak. Validasi hanya dapat dilakukan oleh tingkat Polres.');\n        }",
        $c
    );
}

$tolakFunctions = ['tolakValidasiTanam', 'tolakValidasiPanen', 'tolakValidasiSerapan'];
foreach ($tolakFunctions as $fn) {
    $c = preg_replace(
        '/public function ' . $fn . '\(Request \$request, \$id\)\s*\{\s*\$user = auth\(\)->user\(\);/s',
        "public function $fn(Request \$request, \$id)\n    {\n        \$user = auth()->user();\n        if (\$user && substr_count((string)\$user->id_tugas, '.') >= 2) {\n            return response()->json(['success' => false, 'message' => 'Akses ditolak. Hanya Polres atau Admin yang bisa menolak data.'], 403);\n        }",
        $c
    );
}

file_put_contents($file, $c);
echo "Done";
