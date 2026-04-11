<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;

$business = $argv[1] ?? 'Caberto';
$t = Tenant::where('business_name', $business)->first();
if (!$t) {
    echo "tenant not found\n";
    exit(1);
}

$sub = is_array($t->subscription) ? $t->subscription : [];
$now = new \Carbon\Carbon();
$periodStart = $sub['current_period_start'] ?? $now->toDateString();
$periodEnd = $sub['current_period_end'] ?? $now->copy()->addMonth()->toDateString();
$t->subscription = array_merge($sub, [
    'plan' => $sub['plan'] ?? $t->plan ?? 'basic',
    'status' => 'active',
    'current_period_start' => $periodStart,
    'current_period_end' => $periodEnd,
]);
$t->save();
echo "subscription activated\n";
