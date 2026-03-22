<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TenantService
{
    /**
     * Create a new tenant record and provision its database.
     */
    public static function createTenant(array $data): Tenant
    {
        $tenantId = Str::uuid()->toString();
        $defaultConnection = config('database.default');
        $connectionConfig = config("database.connections.{$defaultConnection}", []);

        $domain = $data['domain'] ?? null;
        if (!$domain) {
            $baseSlug = Str::slug($data['business_name'] ?? ($data['email'] ?? 'tenant'));
            $root = config('tenancy.fallback_domain') ?? 'localhost';

            $domain = $baseSlug . '.' . $root;
            $counter = 1;
            while (Tenant::where('domain', $domain)->exists() || (Schema::hasTable('domains') && Domain::where('domain', $domain)->exists())) {
                $counter++;
                $domain = $baseSlug . '-' . $counter . '.' . $root;
            }
        } elseif (Schema::hasTable('domains')) {
            self::releaseDomainFromDeletedTenants($domain);
        }

        $dbName = self::generateDatabaseName($tenantId);
        $dbUsername = $data['db_username'] ?? ('tenant_' . substr(sha1($tenantId), 0, 10));
        $dbPasswordPlain = $data['db_password'] ?? Str::random(24);

        // Create database and attempt dedicated database user creation.
        $hasDedicatedDbUser = self::createDatabase($dbName, $dbUsername, $dbPasswordPlain);

        if (!$hasDedicatedDbUser && (($connectionConfig['driver'] ?? 'mysql') !== 'sqlite')) {
            // Fallback to central credentials if DB user management is unavailable.
            $dbUsername = $connectionConfig['username'] ?? null;
            $dbPasswordPlain = $connectionConfig['password'] ?? '';
        }

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
            'payment_status' => $data['payment_status'] ?? 'paid',
            'suspended_message' => $data['suspended_message'] ?? 'Please contact your administrator.',
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

        if (Schema::hasTable('domains')) {
            Domain::firstOrCreate([
                'domain' => $domain,
            ], [
                'tenant_id' => $tenant->id,
            ]);
        }

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
    public static function createDatabase(string $databaseName, ?string $dbUsername = null, ?string $dbPassword = null): bool
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

            return true;
        }

        $sql = "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        try {
            // Use central connection explicitly so tenant middleware cannot override it.
            DB::connection($defaultConnection)->statement($sql);

            // Try to give each tenant its own DB user (best effort on local/dev MySQL).
            if (($driver === 'mysql' || $driver === 'mariadb') && $dbUsername) {
                $safeUser = preg_replace('/[^A-Za-z0-9_]/', '_', $dbUsername);
                $safeDb = preg_replace('/[^A-Za-z0-9_]/', '_', $databaseName);
                $quotedPassword = str_replace("'", "\\'", (string) ($dbPassword ?? ''));

                try {
                    DB::connection($defaultConnection)->statement("CREATE USER IF NOT EXISTS '{$safeUser}'@'%' IDENTIFIED BY '{$quotedPassword}'");
                    DB::connection($defaultConnection)->statement("GRANT ALL PRIVILEGES ON `{$safeDb}`.* TO '{$safeUser}'@'%'");
                    DB::connection($defaultConnection)->statement("FLUSH PRIVILEGES");

                    return true;
                } catch (Throwable $e) {
                    // Dedicated user creation is optional in local environments.
                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            $message = "Unable to create tenant database '{$databaseName}'. " .
                       "Ensure your database server is running and DB connection settings in .env are correct. " .
                       "Error: " . $e->getMessage();

            throw new \RuntimeException($message, $e->getCode(), $e);
        }
    }

    public static function updateTenantLifecycle(string $tenantId, array $payload): Tenant
    {
        $tenant = Tenant::where('tenant_id', $tenantId)->firstOrFail();
        $domain = array_key_exists('domain', $payload) ? $payload['domain'] : null;
        unset($payload['domain']);

        if ($domain !== null && $domain !== '') {
            if (Schema::hasTable('domains')) {
                self::releaseDomainFromDeletedTenants($domain);
            }

            $domainInUse = Tenant::query()
                ->where('domain', $domain)
                ->where('id', '!=', $tenant->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($domainInUse) {
                throw ValidationException::withMessages([
                    'domain' => 'The domain has already been taken by another tenant.',
                ]);
            }
        }

        $tenant->fill($payload);

        if ($domain !== null && $domain !== '') {
            $tenant->domain = $domain;
        }

        $tenant->save();

        if (Schema::hasTable('domains') && !empty($tenant->domain)) {
            Domain::updateOrCreate(
                ['tenant_id' => $tenant->id],
                ['domain' => $tenant->domain]
            );
        }

        return $tenant;
    }

    /**
     * Free a domain if it only belongs to soft-deleted tenants.
     */
    private static function releaseDomainFromDeletedTenants(string $domain): void
    {
        $deletedTenantIds = Tenant::onlyTrashed()->pluck('id');

        if ($deletedTenantIds->isEmpty()) {
            return;
        }

        Domain::where('domain', $domain)
            ->whereIn('tenant_id', $deletedTenantIds)
            ->delete();
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

