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
        Schema::create('user_stripe_data', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // unique index on user_id, stripe_customer_id, stripe_payment_method_id
            $table->unique(['user_id', 'stripe_customer_id', 'stripe_payment_method_id'], 'user_stripe_data_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stripe_data');
    }
};
