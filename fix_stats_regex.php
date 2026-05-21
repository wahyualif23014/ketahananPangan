<?php
function updateStats($filePath) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return;
    }
    
    $content = file_get_contents($filePath);
    
    // 1. Tanam: Add where('tanam.is_active', 1)
    $content = preg_replace(
        "/(\\$tanamQuery\\s*=\\s*DB::table\\('tanam'\\).*?->whereIn\\('tanam\\.id_lahan', \\\$filteredLahanIds\\);)/s",
        "$1\n                ->where('tanam.is_active', 1);",
        $content,
        -1,
        $countTanam
    );
    // Wait, the regex captures the whole semicolon, so adding ->where after semicolon is bad!
    // Better to match the ->whereIn and insert before it.
    
    return $countTanam;
}
