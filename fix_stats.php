<?php
function fixStats($file) {
    if (!file_exists($file)) return;
    $c = file_get_contents($file);
    
    // Add where tanam.is_active = 1 to tanamQuery
    $oldTanam = "->whereNotNull('tanam.valid_oleh')->where('tanam.valid_oleh', '!=', '')\n                  ->whereIn('tanam.id_lahan', \$filteredLahanIds);";
    $newTanam = "->whereNotNull('tanam.valid_oleh')->where('tanam.valid_oleh', '!=', '')\n                  ->where('tanam.is_active', 1)\n                  ->whereIn('tanam.id_lahan', \$filteredLahanIds);";
    $c = str_replace($oldTanam, $newTanam, $c);

    // Add join to tanam and where tanam.is_active = 1 to panenQuery
    $oldPanen = "->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')\n                  ->whereNotNull('panen.valid_oleh')";
    $newPanen = "->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')\n                  ->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')\n                  ->where('tanam.is_active', 1)\n                  ->whereNotNull('panen.valid_oleh')";
    $c = str_replace($oldPanen, $newPanen, $c);

    // Add join to tanam and where tanam.is_active = 1 to serapanQuery
    $oldSerapan = "->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')\n                  ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')\n                  ->whereNotNull('distribusi.valid_oleh')";
    $newSerapan = "->join('panen', 'distribusi.id_panen', '=', 'panen.id_panen')\n                  ->join('tanam', 'panen.id_tanam', '=', 'tanam.id_tanam')\n                  ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')\n                  ->where('tanam.is_active', 1)\n                  ->whereNotNull('distribusi.valid_oleh')";
    $c = str_replace($oldSerapan, $newSerapan, $c);
    
    file_put_contents($file, $c);
    echo "Fixed stats in $file\n";
}

fixStats('app/Http/Controllers/Admin/KelolaLahanController.php');
fixStats('app/Http/Controllers/Operator/KelolaLahanController.php');

