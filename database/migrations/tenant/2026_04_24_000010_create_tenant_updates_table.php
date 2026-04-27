<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_updates', function (Blueprint $table) {
            $table->id();
            $table->string('current_version')->nullable();
            $table->string('available_version')->nullable();
            $table->text('release_notes')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('force_update')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_updates');
    }
};
