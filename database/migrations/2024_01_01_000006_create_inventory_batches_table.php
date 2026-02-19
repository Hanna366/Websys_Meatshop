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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('batch_number')->unique();
            $table->foreignId('product_id');
            $table->foreignId('supplier_id')->nullable();
            $table->json('quantity');
            $table->json('cost');
            $table->json('dates');
            $table->json('quality');
            $table->json('storage')->nullable();
            $table->json('tracking')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'batch_number']);
            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'supplier_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'dates_expiry_date']);
            $table->index(['tenant_id', 'quantity_current_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('inventory_batches');
    }
};
