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
        Schema::create('plan_price_payment_provider_data', function (Blueprint $table) {
            $table->foreignId('plan_price_id')->constrained();
            $table->foreignId('payment_provider_id')->constrained();
            $table->string('payment_provider_price_id')->nullable();
            $table->unique(['plan_price_id', 'payment_provider_id'], 'plan_price_payment_provider_data_unq');
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_price_payment_provider_data');
    }
};
