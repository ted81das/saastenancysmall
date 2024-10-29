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
        Schema::table('roadmap_items', function (Blueprint $table) {
            $table->string('slug', 255)->unique('roadmap_items_slug_unique_idx')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roadmap_items', function (Blueprint $table) {
            $table->uuid('slug')->unique()->change();
        });
    }
};
