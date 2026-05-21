<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->boolean('online_lab_enabled')->default(false)->after('youtube_url');
            $table->string('online_lab_type', 40)->nullable()->after('online_lab_enabled');
            $table->string('online_lab_age_label', 40)->nullable()->after('online_lab_type');
            $table->string('online_lab_duration_label', 40)->nullable()->after('online_lab_age_label');
            $table->unsignedInteger('online_lab_sort_order')->default(0)->after('online_lab_duration_label');
        });
    }

    public function down(): void
    {
        Schema::table('experiments', function (Blueprint $table) {
            $table->dropColumn([
                'online_lab_enabled',
                'online_lab_type',
                'online_lab_age_label',
                'online_lab_duration_label',
                'online_lab_sort_order',
            ]);
        });
    }
};
