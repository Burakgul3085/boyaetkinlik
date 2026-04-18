<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('email', 255);
            $table->text('body');
            $table->unsignedTinyInteger('rating');
            $table->boolean('is_approved')->default(false);
            $table->boolean('show_email_public')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_reply')->nullable();
            $table->boolean('admin_reply_published')->default(false);
            $table->timestamp('reply_email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_feedback');
    }
};
