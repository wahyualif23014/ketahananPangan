<?php
$file = 'resources/views/admin/kelola-lahan/lahan/index.blade.php';
$c = file_get_contents($file);

// Ensure Tolak Siklus is restricted to Admin or Operator Polres (not Polsek)
$c = str_replace(
    "@if(in_array(auth()->user()->role, ['admin', 'operator']) && !is_null(\$tanam->id_tanam))",
    "@if((auth()->user()->role === 'admin' || (auth()->user()->role === 'operator' && substr_count(auth()->user()->id_tugas, '.') === 1)) && !is_null(\$tanam->id_tanam))",
    $c
);

file_put_contents($file, $c);
echo "Restricted Tolak Siklus to Admin and Operator Polres.\n";
