<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paint_rooms', function (Blueprint $table) {
            $table->foreignId('coloring_page_id')->nullable()->after('owner_user_id')->constrained('coloring_pages')->nullOnDelete();
            $table->mediumText('canvas_snapshot')->nullable()->after('closed_reason');
        });
    }

    public function down(): void
    {
        Schema::table('paint_rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coloring_page_id');
            $table->dropColumn('canvas_snapshot');
        });
    }
};
