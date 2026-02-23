<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // reference to user ID (session-based)
            $table->string('plan'); // basic, standard, premium, enterprise
            $table->decimal('price', 8, 2)->nullable(); // null for enterprise (custom pricing)
            $table->string('status')->default('active'); // active, expired, cancelled, suspended
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->string('payment_method'); // credit_card, gcash, paypal, bank_transfer
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->json('features_used')->nullable(); // track usage of features
            $table->string('subscription_id')->nullable(); // external subscription ID
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
