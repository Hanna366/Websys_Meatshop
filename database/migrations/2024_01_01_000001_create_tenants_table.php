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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->string('business_name');
            $table->string('business_email')->unique();
            $table->string('business_phone');
            $table->json('business_address');
            $table->json('subscription');
            $table->json('settings');
            $table->json('usage');
            $table->json('limits');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('subscription_status');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
};
