<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('update_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tenant_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('current_version')->nullable();
            $table->string('requested_version')->nullable();
            $table->string('status')->default('pending')->index(); // pending, approved, rejected, completed
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('update_requests');
    }
};
