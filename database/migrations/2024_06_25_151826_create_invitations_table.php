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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('email')->index();
            $table->string('token')->unique();
            $table->timestamp('expires_at');
            $table->string('role')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('status')->default(\App\Constants\InvitationStatus::PENDING);
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
