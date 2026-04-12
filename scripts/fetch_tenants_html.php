<?php
$opts = ['http' => ['method'=>'GET','header'=>"User-Agent: CLI\r\n"]];
$context = stream_context_create($opts);
$status = $argv[1] ?? 'disabled';
$url = "http://127.0.0.1:8000/tenants?status=" . urlencode($status);
$s = @file_get_contents($url, false, $context);
if ($s === false) {
    echo "REQUEST_FAILED\n";
    exit(1);
}
file_put_contents(__DIR__ . '/../storage/logs/tenants_page.html', $s);
echo "WROTE storage/logs/tenants_page.html\n";
