<?php
$src = $argv[1] ?? 'database/backups/meatshop_pos_before_dump_restore_20260424_154726.sql';
$dst = $argv[2] ?? 'C:/Users/OWNER/meatshop_pos_before_dump_restore_20260424_154726.utf8.sql';
if (!file_exists($src)) { echo "Source not found: $src\n"; exit(1); }
$bin = file_get_contents($src);
if ($bin === false) { echo "Failed reading $src\n"; exit(1); }
$prefix = substr($bin,0,2);
$encoding = null;
if ($prefix === "\xFF\xFE") { $encoding = 'UTF-16LE'; }
elseif ($prefix === "\xFE\xFF") { $encoding = 'UTF-16BE'; }
else {
    // Heuristic: if many null bytes, assume UTF-16LE
    $nulls = substr_count($bin, "\x00");
    if ($nulls > strlen($bin)/10) { $encoding = 'UTF-16LE'; }
}
if ($encoding) {
    echo "Detected encoding: $encoding\n";
    $utf8 = @iconv($encoding, 'UTF-8//IGNORE', $bin);
    if ($utf8 === false) { echo "iconv failed\n"; exit(1); }
    file_put_contents($dst, $utf8);
    echo "Wrote $dst\n";
} else {
    // assume already UTF-8
    echo "No special encoding detected, copying as-is to $dst\n";
    file_put_contents($dst, $bin);
}
