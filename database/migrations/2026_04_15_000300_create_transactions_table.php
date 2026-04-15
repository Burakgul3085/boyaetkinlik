<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coloring_page_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('email');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('download_token')->nullable()->unique();
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
