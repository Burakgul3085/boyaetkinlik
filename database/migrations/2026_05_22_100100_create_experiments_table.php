<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_category_id')->nullable()->constrained('experiment_categories')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 400);
            $table->longText('content');
            $table->string('image_path')->nullable();
            $table->string('youtube_url', 500)->nullable();
            $table->string('author_first_name', 100)->default('Boya');
            $table->string('author_last_name', 100)->default('Etkinlik');
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('experiment_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiments');
    }
};
