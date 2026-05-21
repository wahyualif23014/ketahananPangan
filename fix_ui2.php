<?php
$file = 'resources/views/admin/kelola-lahan/lahan/index.blade.php';
$c = file_get_contents($file);

// Replace the UI part for Kapasitas Potensi Lahan
$oldUI = '<span class="text-[10px] font-black text-emerald-800 bg-emerald-200 px-2 py-0.5 rounded-lg" x-text="(activeLahan?.luas_lahan ?? 0) + \' Ha\'"></span>';
$newUI = '<span class="text-[10px] font-black text-emerald-800 bg-emerald-200 px-2 py-0.5 rounded-lg" x-text="(activeLahan?.sisa_lahan ?? activeLahan?.luas_lahan ?? 0) + \' Ha Tersedia\'"></span>';
$c = str_replace($oldUI, $newUI, $c);

// Replace the max text
$oldMax = '<span class="text-[9px] text-emerald-600 font-bold" x-text="\'Max: \' + (activeLahan?.luas_lahan ?? 0) + \' Ha\'"></span>';
$newMax = '<span class="text-[9px] text-emerald-600 font-bold" x-text="\'Sisa: \' + (activeLahan?.sisa_lahan ?? activeLahan?.luas_lahan ?? 0) + \' Ha\'"></span>';
$c = str_replace($oldMax, $newMax, $c);

// Replace the progress bar math
$oldMath = ':style="\'width:\' + Math.min(100, ((parseFloat(formTanam.luas_tanam)||0) / Math.max(0.01, parseFloat(activeLahan?.luas_lahan||1))) * 100) + \'%\'"></div>';
$newMath = ':style="\'width:\' + Math.min(100, ((parseFloat(formTanam.luas_tanam)||0) / Math.max(0.01, parseFloat(activeLahan?.sisa_lahan ?? activeLahan?.luas_lahan||1))) * 100) + \'%\'"></div>';
$c = str_replace($oldMath, $newMath, $c);

// Replace the color threshold
$oldColor = ':class="(parseFloat(formTanam.luas_tanam)||0) > parseFloat(activeLahan?.luas_lahan||0) ? \'text-rose-600\' : \'text-emerald-700\'"';
$newColor = ':class="(parseFloat(formTanam.luas_tanam)||0) > parseFloat(activeLahan?.sisa_lahan ?? activeLahan?.luas_lahan||0) ? \'text-rose-600\' : \'text-emerald-700\'"';
$c = str_replace($oldColor, $newColor, $c);

// Operator Polsek typo fix (re-applying it since I restored from HEAD~1)
$c = str_replace(
    "['admin', 'operator_polsek']",
    "['admin', 'operator']",
    $c
);

// Fix the Tolak Siklus button visibility for operator
$c = str_replace(
    "@if(auth()->user()->role === 'admin' && !is_null(\$tanam->id_tanam))",
    "@if(in_array(auth()->user()->role, ['admin', 'operator']) && !is_null(\$tanam->id_tanam))",
    $c
);

if (empty($c)) {
    die("Error: Replace resulted in empty string.");
}

file_put_contents($file, $c);
echo "UI fixes applied. Now calculating sisa_lahan.\n";
