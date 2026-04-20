<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coloring_pages', function (Blueprint $table) {
            $table->string('shopier_product_url', 1000)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('coloring_pages', function (Blueprint $table) {
            $table->dropColumn('shopier_product_url');
        });
    }
};
