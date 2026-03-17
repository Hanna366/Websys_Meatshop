<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class TenantService
{
    /**
     * Create a new tenant record and provision its database.
     */
    public static function createTenant(array $data): Tenant
    {
        $tenantId = Str::uuid()->toString();

        $domain = $data['domain'] ?? null;
        if (!$domain) {
            $baseSlug = Str::slug($data['business_name'] ?? ($data['email'] ?? 'tenant'));
            $root = config('tenancy.fallback_domain') ?? 'localhost';

            $domain = $baseSlug . '.' . $root;
            $counter = 1;
            while (Tenant::where('domain', $domain)->exists()) {
                $counter++;
                $domain = $baseSlug . '-' . $counter . '.' . $root;
            }
        }

        $dbName = self::generateDatabaseName($tenantId);
        $dbUsername = $data['db_username'] ?? config('database.connections.' . config('database.default') . '.username');
        $dbPasswordPlain = $data['db_password'] ?? Str::random(20);

        // Create the tenant database (file for SQLite or schema for MySQL)
        self::createDatabase($dbName);

        // Store the tenant record (password stored encrypted)
        $tenant = Tenant::create([
            'tenant_id' => $tenantId,
            'business_name' => $data['business_name'] ?? $data['name'] ?? 'Tenant',
            'business_email' => $data['business_email'] ?? $data['email'] ?? null,
            'business_phone' => $data['business_phone'] ?? null,
            'business_address' => $data['business_address'] ?? null,
            'subscription' => $data['subscription'] ?? [],
            'settings' => $data['settings'] ?? [],
            'usage' => $data['usage'] ?? [],
            'limits' => $data['limits'] ?? [],
            'status' => $data['status'] ?? 'active',
            'domain' => $domain,
            'db_name' => $dbName,
            'db_username' => $dbUsername,
            'db_password' => encrypt($dbPasswordPlain),
            'plan' => $data['plan'] ?? 'basic',
            'plan_started_at' => $data['plan_started_at'] ?? now(),
            'plan_ends_at' => $data['plan_ends_at'] ?? now()->addMonth(),
            'admin_name' => $data['admin_name'] ?? null,
            'admin_email' => $data['admin_email'] ?? null,
        ]);

        // Run tenant migrations
        self::runTenantMigrations($tenant);

        // Create initial admin user on tenant database
        $adminPassword = $data['password'] ?? Str::random(16);
        $adminEmail = $data['admin_email'] ?? $data['business_email'] ?? $data['email'] ?? null;
        $adminName = $data['admin_name'] ?? $data['business_name'] ?? 'Tenant Admin';
        $adminUsername = Str::slug($adminName, '_');

        \App\Models\User::on('tenant')->create([
            'tenant_id' => $tenantId,
            'username' => $adminUsername,
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'role' => 'owner',
            'profile' => [
                'first_name' => $adminName,
                'last_name' => '',
                'full_name' => $adminName,
            ],
        ]);

        return $tenant;
    }

    /**
     * Generate a tenant database name based on the tenant ID.
     */
    public static function generateDatabaseName(string $tenantId): string
    {
        // Use a name that is reasonably short and stable
        $hash = substr(sha1($tenantId), 0, 12);
        return 'tenant_' . $hash;
    }

    /**
     * Create the tenant database using the central DB connection.
     */
    public static function createDatabase(string $databaseName): void
    {
        $databaseName = preg_replace('/[^A-Za-z0-9_]/', '_', $databaseName);

        $defaultConnection = config('database.default');
        $connectionConfig = config("database.connections.{$defaultConnection}", []);
        $driver = $connectionConfig['driver'] ?? 'mysql';

        if ($driver === 'sqlite') {
            $tenantPath = database_path('tenants');
            if (!is_dir($tenantPath)) {
                mkdir($tenantPath, 0755, true);
            }

// The tenant database name is stored as <db_name>.sqlite
        $file = $tenantPath . DIRECTORY_SEPARATOR . ($databaseName ?: 'tenant') . '.sqlite';
            if (!file_exists($file)) {
                touch($file);
            }

            return;
        }

        $sql = "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        try {
            // Use central connection explicitly so tenant middleware cannot override it.
            DB::connection($defaultConnection)->statement($sql);
        } catch (Throwable $e) {
            $message = "Unable to create tenant database '{$databaseName}'. " .
                       "Ensure your database server is running and DB connection settings in .env are correct. " .
                       "Error: " . $e->getMessage();

            throw new \RuntimeException($message, $e->getCode(), $e);
        }
    }

    /**
     * Run tenant migrations on the tenant database.
     */
    public static function runTenantMigrations(Tenant $tenant): void
    {
        // Use the tenant's connection configuration
        $tenantConfig = $tenant->getTenantDatabaseConfig();
        config(['database.connections.tenant' => $tenantConfig]);

        // Purge any existing tenant connection to prevent caching issues
        DB::purge('tenant');

        try {
            // Run migrations for the tenant database
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        } catch (Throwable $e) {
            // If migration failed, and we are using sqlite, remove the tenant database file to prevent empty leftovers.
            if (($tenantConfig['driver'] ?? null) === 'sqlite' && !empty($tenantConfig['database'])) {
                @unlink($tenantConfig['database']);
            }

            throw $e;
        }
    }
}

