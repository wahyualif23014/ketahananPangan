<?php

$path = 'resources/views/admin/rekapitulasi/index.blade.php';
$lines = file($path);
$keep = array_merge(
    array_slice($lines, 0, 245),
    array_slice($lines, 385)
);
file_put_contents($path, implode('', $keep));
echo 'Lines: ' . count($keep) . "\n";
