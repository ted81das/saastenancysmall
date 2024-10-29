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
            $table->string('uuid');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('plan_id')->constrained();
            $table->unsignedInteger('price');
            $table->foreignId('currency_id')->constrained();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_provider_subscription_id')->nullable();
            $table->string('payment_provider_status')->nullable();
            $table->foreignId('payment_provider_id')->nullable()->constrained();
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('status');
            $table->foreignId('interval_id')->constrained();
            $table->unsignedInteger('interval_count');
            $table->boolean('is_canceled_at_end_of_cycle')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->mediumText('cancellation_additional_info')->nullable();
            $table->timestamps();
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
