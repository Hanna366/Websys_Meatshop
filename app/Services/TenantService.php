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
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class TenantService
{
    /**
     * Create a new tenant record and provision its database.
     */
    public static function createTenant(array $data): Tenant
    {
        $plan = SubscriptionService::normalizePlan((string) ($data['plan'] ?? 'basic'));
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

        $now = now();
        $planStartedAt = $data['plan_started_at'] ?? $now;
        $planEndsAt = $data['plan_ends_at'] ?? $now->copy()->addMonth();
        $billingCycle = $data['billing_cycle'] ?? 'monthly';
        $subscriptionPayload = array_merge([
            'plan' => $plan,
            'status' => 'active',
            'billing_cycle' => $billingCycle,
            'current_period_start' => $planStartedAt instanceof \Carbon\CarbonInterface ? $planStartedAt->toDateString() : (string) $planStartedAt,
            'current_period_end' => $planEndsAt instanceof \Carbon\CarbonInterface ? $planEndsAt->toDateString() : (string) $planEndsAt,
        ], $data['subscription'] ?? []);

        $planLimits = SubscriptionService::getPlanLimits($plan);
        $limitsPayload = array_merge($planLimits, is_array($data['limits'] ?? null) ? $data['limits'] : []);

        // Store the tenant record (password stored encrypted)
        $tenant = Tenant::create([
            'tenant_id' => $tenantId,
            'business_name' => $data['business_name'] ?? $data['name'] ?? 'Tenant',
            'business_email' => $data['business_email'] ?? $data['email'] ?? null,
            'business_phone' => $data['business_phone'] ?? null,
            'business_address' => $data['business_address'] ?? null,
            'subscription' => $subscriptionPayload,
            'settings' => $data['settings'] ?? [],
            'usage' => $data['usage'] ?? [],
            'limits' => $limitsPayload,
            'status' => $data['status'] ?? 'active',
            'payment_status' => $data['payment_status'] ?? 'paid',
            'suspended_message' => $data['suspended_message'] ?? 'Please contact your administrator.',
            'domain' => $domain,
            'db_name' => $dbName,
            'db_username' => $dbUsername,
            'db_password' => encrypt($dbPasswordPlain),
            'plan' => $plan,
            'plan_started_at' => $planStartedAt,
            'plan_ends_at' => $planEndsAt,
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
        self::runTenantSeeders($tenant);

        // Create initial admin user on tenant database
        $adminPassword = $data['password'] ?? Str::random(16);
        $adminEmail = $data['admin_email'] ?? $data['business_email'] ?? $data['email'] ?? null;
        $adminName = $data['admin_name'] ?? $data['business_name'] ?? 'Tenant Admin';
        $adminUsername = Str::slug($adminName, '_');

        $adminUser = \App\Models\User::on('tenant')->create([
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $adminUser->syncRoles(['Owner']);

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
     * Update central tenant business/profile fields.
     */
    public static function updateTenantProfile(string $tenantId, array $payload): Tenant
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

            $payload['domain'] = $domain;
        }

        $tenant->fill($payload);
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
     * Update tenant subscription/plan state and billing period tracking.
     */
    public static function updateTenantSubscription(string $tenantId, array $payload): Tenant
    {
        $tenant = Tenant::where('tenant_id', $tenantId)->firstOrFail();

        $now = now();
        $billingCycle = $payload['billing_cycle'] ?? 'monthly';
        $periodStart = isset($payload['current_period_start'])
            ? \Carbon\Carbon::parse((string) $payload['current_period_start'])
            : $now->copy();
        $periodEnd = isset($payload['current_period_end'])
            ? \Carbon\Carbon::parse((string) $payload['current_period_end'])
            : ($billingCycle === 'annual' ? $periodStart->copy()->addYear() : $periodStart->copy()->addMonth());

        $status = $payload['subscription_status'] ?? 'active';
        $plan = SubscriptionService::normalizePlan((string) ($payload['plan'] ?? ($tenant->plan ?? 'basic')));

        $existingSubscription = is_array($tenant->subscription) ? $tenant->subscription : [];

        $tenant->plan = $plan;
        $tenant->plan_started_at = $periodStart;
        $tenant->plan_ends_at = $periodEnd;
        $tenant->status = $payload['tenant_status'] ?? $tenant->status;
        $tenant->payment_status = $payload['payment_status'] ?? $tenant->payment_status;

        $tenant->subscription = array_merge($existingSubscription, [
            'plan' => $plan,
            'status' => $status,
            'billing_cycle' => $billingCycle,
            'current_period_start' => $periodStart->toDateString(),
            'current_period_end' => $periodEnd->toDateString(),
            'updated_at' => $now->toDateTimeString(),
        ]);

        $managedLimits = SubscriptionService::getPlanLimits($plan);
        $existingLimits = is_array($tenant->limits) ? $tenant->limits : [];
        $customLimitKeys = array_diff_key($existingLimits, $managedLimits);
        $tenant->limits = array_merge($managedLimits, $customLimitKeys);

        if ($status === 'expired') {
            $tenant->payment_status = 'overdue';
            if (empty($tenant->suspended_message)) {
                $tenant->suspended_message = 'Subscription expired. Please renew to restore access.';
            }
        }

        if ($status === 'unpaid') {
            $tenant->payment_status = 'unpaid';
        }

        if ($status === 'active' && in_array($tenant->payment_status, ['unpaid', 'overdue'], true)) {
            $tenant->payment_status = 'paid';
        }

        $tenant->save();

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

    /**
     * Seed initial catalog and pricing data for a newly provisioned tenant.
     */
    public static function runTenantSeeders(Tenant $tenant): void
    {
        $tenantConfig = $tenant->getTenantDatabaseConfig();
        config(['database.connections.tenant' => $tenantConfig]);
        config(['seeding.tenant_id' => (string) $tenant->tenant_id]);

        DB::purge('tenant');

        tenancy()->initialize($tenant);

        try {
            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => \Database\Seeders\TenantRoleSeeder::class,
                '--force' => true,
            ]);

            Artisan::call('db:seed', [
                '--database' => 'tenant',
                '--class' => \Database\Seeders\KitayamaRetail2025Seeder::class,
                '--force' => true,
            ]);
        } finally {
            tenancy()->end();
        }
    }
}

