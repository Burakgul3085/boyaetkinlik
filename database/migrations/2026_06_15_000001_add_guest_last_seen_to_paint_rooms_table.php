<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paint_rooms', function (Blueprint $table) {
            $table->timestamp('guest_last_seen_at')->nullable()->after('guest_token');
        });
    }

    public function down(): void
    {
        Schema::table('paint_rooms', function (Blueprint $table) {
            $table->dropColumn('guest_last_seen_at');
        });
    }
};
