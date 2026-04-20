<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('pending'); // pending, downloading, installing, completed, failed
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_updates');
    }
};
