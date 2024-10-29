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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('status');
            $table->foreignId('currency_id')->nullable()->constrained();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->unsignedBigInteger('total_amount_after_discount')->default(0);
            $table->unsignedBigInteger('total_discount_amount')->default(0);
            $table->string('payment_provider_order_id')->nullable();
            $table->foreignId('payment_provider_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
