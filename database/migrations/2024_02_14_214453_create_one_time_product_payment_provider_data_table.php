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
        Schema::create('one_time_product_payment_provider_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('one_time_product_id')->constrained(indexName: 'one_time_product_payment_264vc5');
            $table->foreignId('payment_provider_id')->constrained(indexName: 'one_time_product_payment_264vc6');
            $table->string('payment_provider_product_id')->nullable();
            $table->unique(['one_time_product_id', 'payment_provider_id'], 'pro_payment_provider_data_unq');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_time_product_payment_provider_data');
    }
};
