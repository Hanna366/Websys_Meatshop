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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('sale_number')->unique();
            $table->foreignId('customer_id')->nullable();
            $table->json('items');
            $table->json('payment');
            $table->json('staff');
            $table->json('transaction');
            $table->json('loyalty')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sale_number']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'transaction_date']);
            $table->index(['tenant_id', 'payment_payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
};
