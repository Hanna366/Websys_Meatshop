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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('supplier_code')->unique();
            $table->json('business_info');
            $table->json('address');
            $table->json('business_details');
            $table->json('product_categories');
            $table->json('payment_terms');
            $table->json('delivery_info');
            $table->json('quality_standards');
            $table->json('performance_metrics');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'supplier_code']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'performance_metrics_quality_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
};
