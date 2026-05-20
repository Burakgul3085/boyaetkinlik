<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('blog_category_id')->nullable()->after('slug')->constrained('blog_categories')->nullOnDelete();
            $table->string('suggested_category_name', 120)->nullable()->after('blog_category_id');
            $table->index(['blog_category_id', 'status']);
        });

        $now = now();
        $defaultId = DB::table('blog_categories')->insertGetId([
            'name' => 'Genel',
            'slug' => 'genel',
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
            'source' => 'admin',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('blogs')->whereNull('blog_category_id')->update(['blog_category_id' => $defaultId]);
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropForeign(['blog_category_id']);
            $table->dropColumn(['blog_category_id', 'suggested_category_name']);
        });
    }
};
