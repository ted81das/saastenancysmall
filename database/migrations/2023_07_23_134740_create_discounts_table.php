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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyText('description')->nullable();
            $table->string('type');
            $table->float('amount');
            $table->dateTime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_combinable')->default(true);
            $table->string('action_type')->nullable();
            $table->integer('max_redemptions')->nullable();
            $table->integer('max_redemptions_per_user')->nullable();
            $table->unsignedInteger('redemptions')->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->dropColumn('is_combinable');
            $table->integer('duration_in_months')->nullable();
            $table->integer('maximum_recurring_intervals')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
