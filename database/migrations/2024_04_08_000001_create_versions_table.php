<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20); // e.g., "1.0.0"
            $table->string('release_name')->nullable(); // e.g., "Initial Release"
            $table->text('description')->nullable();
            $table->enum('type', ['major', 'minor', 'patch', 'hotfix']);
            $table->enum('status', ['development', 'testing', 'stable', 'deprecated']);
            $table->dateTime('release_date')->nullable();
            $table->json('features')->nullable(); // New features list
            $table->json('fixes')->nullable(); // Bug fixes list
            $table->json('requirements')->nullable(); // System requirements
            $table->string('download_url')->nullable();
            $table->string('checksum')->nullable(); // File integrity
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('auto_update')->default(false);
            $table->timestamps();
            
            $table->index(['version', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
