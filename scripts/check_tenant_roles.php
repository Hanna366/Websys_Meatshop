<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$tenant = Tenant::first();
if (! $tenant) {
    echo "NO_TENANT" . PHP_EOL;
    exit(0);
}

$tenant->run(function () {
    echo "ROLES:" . \Spatie\Permission\Models\Role::count() . PHP_EOL;
    echo "PERMS:" . \Spatie\Permission\Models\Permission::count() . PHP_EOL;
});
