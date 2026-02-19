<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->json('profile');
            $table->json('permissions');
            $table->json('preferences')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('lock_until')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'role']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
