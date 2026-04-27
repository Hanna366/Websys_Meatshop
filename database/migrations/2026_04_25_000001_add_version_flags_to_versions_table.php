<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            if (! Schema::hasColumn('versions', 'is_stable')) {
                $table->boolean('is_stable')->default(false)->after('status');
            }
            if (! Schema::hasColumn('versions', 'is_available_to_tenants')) {
                $table->boolean('is_available_to_tenants')->default(false)->after('is_stable');
            }
            if (! Schema::hasColumn('versions', 'is_deprecated')) {
                $table->boolean('is_deprecated')->default(false)->after('is_available_to_tenants');
            }
        });
    }

    public function down(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            if (Schema::hasColumn('versions', 'is_deprecated')) {
                $table->dropColumn('is_deprecated');
            }
            if (Schema::hasColumn('versions', 'is_available_to_tenants')) {
                $table->dropColumn('is_available_to_tenants');
            }
            if (Schema::hasColumn('versions', 'is_stable')) {
                $table->dropColumn('is_stable');
            }
        });
    }
};
