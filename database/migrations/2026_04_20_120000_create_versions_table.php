<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('versions')) {
            return;
        }

        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('release_name')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['development','testing','stable','deprecated'])->default('development');
            $table->string('download_url')->nullable();
            $table->string('checksum')->nullable();
            $table->timestamp('release_date')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('versions');
    }
};
