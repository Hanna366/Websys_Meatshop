<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Safely drop foreign key if present, then change column type to varchar(36)
        try {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
        } catch (\Throwable $e) {
            // ignore if not present
        }

        DB::statement("ALTER TABLE `support_tickets` MODIFY `tenant_id` VARCHAR(36) NOT NULL");
    }

    public function down(): void
    {
        // Revert to unsigned big integer; may fail if existing UUIDs present
        try {
            DB::statement("ALTER TABLE `support_tickets` MODIFY `tenant_id` BIGINT UNSIGNED NOT NULL");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
