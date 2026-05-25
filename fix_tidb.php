<?php
$file = 'sdmapps.sql';
$content = file_get_contents($file);

// 1. Find all ALTER TABLE ... MODIFY ... AUTO_INCREMENT
$pattern = "/ALTER TABLE `([^`]+)`\s+MODIFY `([^`]+)`([^;]+)AUTO_INCREMENT(.*?);/s";
preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $full_match = $match[0];
    $table = $match[1];
    $column = $match[2];
    $type_def = $match[3]; // e.g. " bigint UNSIGNED NOT NULL "
    
    // Remove the ALTER TABLE statement
    $content = str_replace($full_match, "", $content);

    // Regex to find the column definition and add AUTO_INCREMENT
    $col_pattern = "/(CREATE TABLE `$table` \([^;]+?`$column`)([^,\n]+)/s";
    
    $content = preg_replace_callback($col_pattern, function($m) {
        return $m[1] . $m[2] . " AUTO_INCREMENT";
    }, $content);
}

// Clean up left over empty comments around the removed ALTER TABLEs
$content = preg_replace("/--\n-- AUTO_INCREMENT for table [^\n]+\n--\n\n/s", "", $content);
$content = preg_replace("/\n\s*\n\s*\n/", "\n\n", $content); // Remove excessive newlines

// ADD DROP TABLE IF EXISTS before CREATE TABLE so re-importing doesn't fail
$content = preg_replace("/CREATE TABLE `([^`]+)`/s", "DROP TABLE IF EXISTS `$1`;\nCREATE TABLE `$1`", $content);

file_put_contents('sdmapps_tidb.sql', $content);
echo "Berhasil membuat file sdmapps_tidb.sql yang kompatibel dengan TiDB!\n";
