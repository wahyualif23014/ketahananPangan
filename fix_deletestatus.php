<?php
$file = 'app/Http/Controllers/view/KelolaLahanController.php';
$content = file_get_contents($file);

// Replace exactly the exact instances
$content = str_replace("->where('deletestatus', '1')", "->where('deletestatus', '!=', '0')", $content);
$content = str_replace("->where('tanam.deletestatus', '1')", "->where('tanam.deletestatus', '!=', '0')", $content);
$content = str_replace("->where('panen.deletestatus', '1')", "->where('panen.deletestatus', '!=', '0')", $content);
$content = str_replace("->where('distribusi.deletestatus', '1')", "->where('distribusi.deletestatus', '!=', '0')", $content);

file_put_contents($file, $content);
echo "Deletestatus fix applied to view controller.\n";
