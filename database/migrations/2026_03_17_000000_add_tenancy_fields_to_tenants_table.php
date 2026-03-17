<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Tenant host used to identify tenant by request host (e.g. ramcar_meatshop.localhost)
            if (!Schema::hasColumn('tenants', 'domain')) {
                $table->string('domain')->nullable()->unique()->after('tenant_id');
            }

            // Tenant database config
            if (!Schema::hasColumn('tenants', 'db_name')) {
                $table->string('db_name')->nullable()->unique()->after('domain');
            }
            if (!Schema::hasColumn('tenants', 'db_username')) {
                $table->string('db_username')->nullable()->after('db_name');
            }
            if (!Schema::hasColumn('tenants', 'db_password')) {
                $table->text('db_password')->nullable()->after('db_username');
            }

            // Plan metadata for dashboard
            if (!Schema::hasColumn('tenants', 'plan')) {
                $table->string('plan')->default('basic')->after('subscription');
            }
            if (!Schema::hasColumn('tenants', 'plan_started_at')) {
                $table->timestamp('plan_started_at')->nullable()->after('plan');
            }
            if (!Schema::hasColumn('tenants', 'plan_ends_at')) {
                $table->timestamp('plan_ends_at')->nullable()->after('plan_started_at');
            }

            // Admin contact info
            if (!Schema::hasColumn('tenants', 'admin_name')) {
                $table->string('admin_name')->nullable()->after('business_email');
            }
            if (!Schema::hasColumn('tenants', 'admin_email')) {
                $table->string('admin_email')->nullable()->after('admin_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['domain', 'db_name', 'db_username', 'db_password', 'plan', 'plan_started_at', 'plan_ends_at', 'admin_name', 'admin_email']);
        });
    }
};
