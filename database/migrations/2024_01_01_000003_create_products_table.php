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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('product_code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['beef', 'pork', 'chicken', 'lamb', 'seafood', 'processed', 'other']);
            $table->string('subcategory')->nullable();
            $table->json('pricing');
            $table->json('inventory');
            $table->json('batch_tracking');
            $table->json('physical_attributes')->nullable();
            $table->json('supplier_info')->nullable();
            $table->json('images')->nullable();
            $table->json('tags')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'product_code']);
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
