<?php
$lines = file('storage/logs/laravel.log');
$lastLines = array_slice($lines, -100);
foreach(array_reverse($lastLines) as $line) {
    if(strpos($line, 'local.ERROR') !== false) {
        echo $line;
        break;
    }
}
