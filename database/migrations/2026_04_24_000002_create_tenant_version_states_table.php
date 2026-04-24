<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_version_states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tenant_id', 36)->index();
            $table->string('current_version')->nullable();
            $table->timestamp('last_update_at')->nullable();
            $table->enum('update_status', ['success','failed','pending'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_version_states');
    }
};
