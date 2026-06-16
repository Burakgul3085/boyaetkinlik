<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coloring_pages', function (Blueprint $table) {
            $table->string('age_group', 120)->nullable()->after('description');
            $table->text('learning_outcomes')->nullable()->after('age_group');
            $table->text('usage_instructions')->nullable()->after('learning_outcomes');
            $table->text('teacher_note')->nullable()->after('usage_instructions');
            $table->text('file_info')->nullable()->after('teacher_note');
            $table->text('copyright_note')->nullable()->after('file_info');
        });
    }

    public function down(): void
    {
        Schema::table('coloring_pages', function (Blueprint $table) {
            $table->dropColumn([
                'age_group',
                'learning_outcomes',
                'usage_instructions',
                'teacher_note',
                'file_info',
                'copyright_note',
            ]);
        });
    }
};
