<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tenants', 'disabled_message')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('disabled_message')->nullable()->after('payment_status');
            });

            // Copy existing suspended_message values to disabled_message
            try {
                DB::table('tenants')->whereNotNull('suspended_message')->update([
                    'disabled_message' => DB::raw('suspended_message'),
                ]);
            } catch (\Throwable $e) {
                // Ignore failures in environments without tenants table/data yet
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tenants', 'disabled_message')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('disabled_message');
            });
        }
    }
};
