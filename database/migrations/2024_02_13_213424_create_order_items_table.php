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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('one_time_product_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->foreignId('currency_id')->nullable()->constrained();
            $table->unsignedInteger('price_per_unit');
            $table->unsignedInteger('price_per_unit_after_discount')->default(0);
            $table->unsignedInteger('discount_per_unit')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
