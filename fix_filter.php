<?php
$files = [
    'C:\laragon\www\ketahananPangan\resources\views\view\kelola-lahan\view_riwayat\index.blade.php',
    'C:\laragon\www\ketahananPangan\resources\views\operator\kelola-lahan\operator_riwayat\index.blade.php'
];

foreach($files as $f) {
    $c = file_get_contents($f);
    
    // Replace the filter part
    $s1 = '$totalTanamHa = collect($row->history_tanam)->filter(fn($t) => ($t->is_active ?? 1) == 1)->sum(\'luas_tanam\');';
    $r1 = '$totalTanamHa = collect($row->history_tanam)->sum(\'luas_tanam\');';
    $c = str_replace($s1, $r1, $c);
    
    file_put_contents($f, $c);
}
echo 'Fixed luas_tanam filter!';
