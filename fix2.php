<?php
$lines = file('C:\laragon\www\ketahananPangan\resources\views\admin\kelola-lahan\lahan\index.blade.php');

// We want to delete from line 889 to 957 (or keep the correct number of closing divs)
// Let's just output lines 720 to 970 to understand the structure first
$output = [];
for($i=720; $i<=970; $i++) {
    if(isset($lines[$i-1])) {
        $output[] = $i . ": " . rtrim($lines[$i-1]);
    }
}
file_put_contents('dump.txt', implode("\n", $output));
echo "Dumped to dump.txt";
