<?php
$file = 'app/Http/Controllers/Operator/KelolaLahanController.php';
$c = file_get_contents($file);
$c = preg_replace('/^\s*if \(\$user && substr_count\(\(string\)\$user->id_tugas, \'\.\'\) >= 2\) \{.*?\}/ms', '', $c);
file_put_contents($file, $c);
