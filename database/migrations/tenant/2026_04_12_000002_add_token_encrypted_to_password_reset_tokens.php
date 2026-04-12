<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            // tenant migration path: create table if missing
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->text('token_encrypted')->nullable();
                $table->timestamp('created_at')->nullable();
            });
            return;
        }

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            if (! Schema::hasColumn('password_reset_tokens', 'token_encrypted')) {
                $table->text('token_encrypted')->nullable()->after('token');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('password_reset_tokens') && Schema::hasColumn('password_reset_tokens', 'token_encrypted')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropColumn('token_encrypted');
            });
        }
    }
};
