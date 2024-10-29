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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('currency_id')
                ->constrained()
                ->onDelete('cascade');
            $table->bigInteger('amount');
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained();
            $table->string('status');
            $table->foreignId('payment_provider_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
            $table->string('payment_provider_status')->nullable();
            $table->string('payment_provider_transaction_id')->nullable();
            $table->string('error_reason')->nullable();
            $table->bigInteger('total_discount')->default(0);
            $table->bigInteger('total_tax')->default(0);
            $table->bigInteger('total_fees')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
