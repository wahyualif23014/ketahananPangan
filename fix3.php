<?php
$lines = file('C:\laragon\www\ketahananPangan\resources\views\admin\kelola-lahan\lahan\index.blade.php');
for($i=889; $i<=953; $i++){
    unset($lines[$i-1]);
}
file_put_contents('C:\laragon\www\ketahananPangan\resources\views\admin\kelola-lahan\lahan\index.blade.php', implode('', $lines));
echo "Fixed endif error!";
