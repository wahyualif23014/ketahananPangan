<?php

$index = file('resources/views/admin/rekapitulasi/index.blade.php');
$dir = 'resources/views/components/rekapitulasi/';

file_put_contents($dir . 'table-block.blade.php', implode('', array_slice($index, 240, 104)));
file_put_contents($dir . 'pagination-block.blade.php', implode('', array_slice($index, 334, 9)));
file_put_contents($dir . 'mobile-block.blade.php', implode('', array_slice($index, 345, 32)));

echo "OK\n";
