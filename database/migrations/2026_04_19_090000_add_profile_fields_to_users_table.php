<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 120)->nullable()->after('id');
            $table->string('last_name', 120)->nullable()->after('first_name');
        });

        DB::table('users')
            ->whereNull('first_name')
            ->update([
                'first_name' => DB::raw("TRIM(SUBSTRING_INDEX(name, ' ', 1))"),
                'last_name' => DB::raw("TRIM(SUBSTRING(name, LENGTH(SUBSTRING_INDEX(name, ' ', 1)) + 1))"),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
