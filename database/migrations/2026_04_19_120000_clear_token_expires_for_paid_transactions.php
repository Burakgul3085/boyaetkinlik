<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transactions')
            ->where('status', 'paid')
            ->update(['token_expires_at' => null]);
    }

    public function down(): void
    {
        // Geri alınmaz: eski süre bilgisi tutulmadı.
    }
};
