<?php
$src = 'C:/Users/OWNER/meatshop_pos_before_dump_restore_20260424_154726.utf8.sql';
$tenants = ['tenant_fa839d5e51b5','tenant_b38386eab740'];
if (!file_exists($src)) { echo "converted dump not found: $src\n"; exit(1); }
$lines = file($src);
foreach ($tenants as $db) {
    $out = 'C:/Users/OWNER/'.$db.'.sql';
    $fp = fopen($out,'w');
    $capture = false;
    foreach ($lines as $ln) {
        if (preg_match('/USE `'.preg_quote($db,'/').'`/i', $ln) || preg_match('/CREATE DATABASE .*`'.preg_quote($db,'/').'`/i', $ln)) {
            $capture = true;
        }
        if ($capture) {
            fwrite($fp,$ln);
            // stop when next CREATE DATABASE for different DB encountered
            if (preg_match('/^CREATE DATABASE .*`([^`]+)`/i', $ln, $m)) {
                if ($m[1] !== $db) { break; }
            }
            // or when USE for different db encountered
            if (preg_match('/^USE `([^`]+)`/i', $ln, $m2)) {
                if ($m2[1] !== $db) { break; }
            }
        }
    }
    fclose($fp);
    echo "Wrote $out\n";
}
