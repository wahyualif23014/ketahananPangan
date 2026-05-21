<?php
$file = 'resources/views/admin/kelola-lahan/lahan/index.blade.php';
$c = file_get_contents($file);

$c = str_replace(
    '@json($row)',
    '@json(collect($row)->merge([\'sisa_lahan\' => max(0, $row->luas_lahan - $row->history_tanam->filter(fn($t) => ($t->is_active ?? 1) == 1)->sum(\'luas_tanam\'))]))',
    $c
);

file_put_contents($file, $c);
echo "Replaced @json(\$row)\n";
