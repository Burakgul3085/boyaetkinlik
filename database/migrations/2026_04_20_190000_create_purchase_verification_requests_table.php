<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coloring_page_id')->constrained()->cascadeOnDelete();
            $table->string('order_no', 120);
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('customer_name', 160)->nullable();
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->string('verification_token', 80)->unique();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_verification_requests');
    }
};
