<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'domain')) {
                $table->string('domain')->nullable()->unique()->after('tenant_id');
            }
            if (!Schema::hasColumn('tenants', 'db_name')) {
                $table->string('db_name')->nullable()->unique()->after('domain');
            }
            if (!Schema::hasColumn('tenants', 'db_username')) {
                $table->string('db_username')->nullable()->after('db_name');
            }
            if (!Schema::hasColumn('tenants', 'db_password')) {
                $table->text('db_password')->nullable()->after('db_username');
            }
            if (!Schema::hasColumn('tenants', 'plan')) {
                $table->string('plan')->default('basic')->after('subscription');
            }
            if (!Schema::hasColumn('tenants', 'plan_started_at')) {
                $table->timestamp('plan_started_at')->nullable()->after('plan');
            }
            if (!Schema::hasColumn('tenants', 'plan_ends_at')) {
                $table->timestamp('plan_ends_at')->nullable()->after('plan_started_at');
            }
            if (!Schema::hasColumn('tenants', 'admin_name')) {
                $table->string('admin_name')->nullable()->after('business_email');
            }
            if (!Schema::hasColumn('tenants', 'admin_email')) {
                $table->string('admin_email')->nullable()->after('admin_name');
            }
            if (!Schema::hasColumn('tenants', 'payment_status')) {
                $table->string('payment_status')->default('paid')->after('status');
            }
            if (!Schema::hasColumn('tenants', 'suspended_message')) {
                $table->string('suspended_message')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columns = [
                'payment_status',
                'suspended_message',
            ];

            $existing = array_filter($columns, fn ($column) => Schema::hasColumn('tenants', $column));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
