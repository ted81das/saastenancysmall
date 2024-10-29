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
        Schema::create('roadmap_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default(\App\Constants\RoadmapItemStatus::PENDING_APPROVAL->value);
            $table->string('type')->default(\App\Constants\RoadmapItemType::FEATURE->value);
            $table->integer('upvotes')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_items');
    }
};
