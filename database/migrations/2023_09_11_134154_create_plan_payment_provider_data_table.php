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
        Schema::create('plan_payment_provider_data', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained();
            $table->foreignId('payment_provider_id')->constrained();
            $table->string('payment_provider_product_id')->nullable();
            $table->unique(['plan_id', 'payment_provider_id'], 'plan_payment_provider_data_unq');
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_payment_provider_data');
    }
};
