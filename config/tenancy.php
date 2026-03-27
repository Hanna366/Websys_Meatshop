<?php

declare(strict_types=1);

use App\Models\Domain;
use App\Models\Tenant;
use App\Tenancy\Bootstrappers\LegacyDatabaseBootstrapper;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return [
    'tenant_model' => Tenant::class,
    'domain_model' => Domain::class,

    // Keep compatibility with existing tenant domain generation.
    'fallback_domain' => env('TENANT_ROOT_DOMAIN', 'localhost'),

    'central_domains' => array_filter(array_map('trim', explode(',', (string) env('TENANCY_CENTRAL_DOMAINS', '127.0.0.1,localhost')))),

    'bootstrappers' => [
        LegacyDatabaseBootstrapper::class,
        CacheTenancyBootstrapper::class,
        FilesystemTenancyBootstrapper::class,
        QueueTenancyBootstrapper::class,
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base' => 'tenant',
        'disks' => ['local', 'public'],
        'root_override' => [
            'local' => '%storage_path%/app/',
            'public' => '%storage_path%/app/public/',
        ],
        'suffix_storage_path' => true,
        'asset_helper_tenancy' => true,
    ],

    'tenant_lookup_cache' => [
        'enabled' => (bool) env('TENANCY_CACHED_LOOKUP', true),
        'ttl' => (int) env('TENANCY_CACHED_LOOKUP_TTL', 3600),
        'store' => env('TENANCY_CACHED_LOOKUP_STORE', null),
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => null,
        'prefix' => 'tenant_',
        'suffix' => '',
        'managers' => [
            'mysql' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
            'mariadb' => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
            'pgsql' => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
            'sqlite' => Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager::class,
        ],
    ],

    'routes' => true,

    // Keep same intent from prior custom config.
    'allow_public_fallback' => true,
];
