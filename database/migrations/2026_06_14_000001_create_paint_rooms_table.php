<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paint_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_code', 16)->unique();
            $table->char('pin', 6);
            $table->string('invite_token', 64)->unique();
            $table->timestamp('invite_token_used_at')->nullable();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('guest_display_name', 80)->nullable();
            $table->string('guest_token', 64)->nullable();
            $table->string('status', 20)->default('waiting');
            $table->timestamp('expires_at');
            $table->timestamp('closed_at')->nullable();
            $table->string('closed_reason', 120)->nullable();
            $table->timestamps();

            $table->index(['pin', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paint_rooms');
    }
};
