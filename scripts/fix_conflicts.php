<?php
$file = 'c:\\laragon\\www\\ketahananPangan\\resources\\views\\admin\\kelola-lahan\\lahan\\index.blade.php';
$content = file_get_contents($file);

$pattern = '/<<<<<<< Updated upstream\r?\n(.*?)\r?\n=======\r?\n(.*?)\r?\n>>>>>>> Stashed changes\r?\n/s';

$resolved = preg_replace_callback($pattern, function($matches) {
    return $matches[2] . "\n";
}, $content);

file_put_contents($file, $resolved);
echo "Conflicts resolved in " . basename($file) . "\n";
