<?php
$file = 'C:\laragon\www\ketahananPangan\app\Http\Controllers\view\KelolaLahanController.php';
$content = file_get_contents($file);

$search = <<<EOT
                \$allPanens = DB::table('panen')
                    ->whereIn('id_tanam', \$tanamIdsAll)
                    ->where('deletestatus', '!=', '0')
                    ->orderBy('id_panen')
                    ->get()->groupBy('id_tanam');

                \$allDistribusis = DB::table('distribusi')
                    ->whereIn('id_tanam', \$tanamIdsAll)
                    ->where('deletestatus', '!=', '0')
                    ->orderBy('id_distribusi')
                    ->get()->groupBy('id_tanam');

                \$allTanams = \$allTanamsRaw->map(function(\$t) use (\$allPanens, \$allDistribusis) {
                    \$t->panens     = \$allPanens[\$t->id_tanam]     ?? collect();
                    \$t->distribusis = \$allDistribusis[\$t->id_tanam] ?? collect();
                    return \$t;
                })->groupBy('id_lahan');
EOT;

$replace = <<<EOT
                \$allPanens = DB::table('panen')
                    ->whereIn('id_tanam', \$tanamIdsAll)
                    ->where('deletestatus', '!=', '0')
                    ->orderBy('id_panen')
                    ->get();
                \$panenIdsAll = \$allPanens->pluck('id_panen')->toArray();
                \$allPanensGrouped = \$allPanens->groupBy('id_tanam');

                \$allDistribusis = empty(\$panenIdsAll) ? collect() : DB::table('distribusi')
                    ->whereIn('id_panen', \$panenIdsAll)
                    ->where('deletestatus', '!=', '0')
                    ->orderBy('id_distribusi')
                    ->get()->groupBy('id_panen');

                \$allTanams = \$allTanamsRaw->map(function(\$t) use (\$allPanensGrouped, \$allDistribusis) {
                    \$t->panens = \$allPanensGrouped[\$t->id_tanam] ?? collect();
                    foreach (\$t->panens as \$p) {
                        \$p->distribusis = \$allDistribusis[\$p->id_panen] ?? collect();
                    }
                    \$t->distribusis = \$t->panens->flatMap->distribusis;
                    return \$t;
                })->groupBy('id_lahan');
EOT;

// fix CRLF just in case
$search = str_replace("\r\n", "\n", $search);
$content = str_replace("\r\n", "\n", $content);

$newContent = str_replace($search, $replace, $content);
if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo 'Replaced view controller logic!';
} else {
    echo 'Search string not found!';
}
