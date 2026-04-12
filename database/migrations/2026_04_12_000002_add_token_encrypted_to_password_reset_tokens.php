<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                if (! Schema::hasColumn('password_reset_tokens', 'token_encrypted')) {
                    $table->text('token_encrypted')->nullable()->after('token');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('password_reset_tokens', 'token_encrypted')) {
                    $table->dropColumn('token_encrypted');
                }
            });
        }
    }
};
