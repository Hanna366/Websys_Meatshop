<?php
$url = 'http://chop.localhost:8000/dashboard/payments/checkout?plan=premium&billing=monthly';
$headers = @get_headers($url, 1);
if ($headers === false) {
    echo "NO RESPONSE\n";
    exit(1);
}
var_export($headers);
