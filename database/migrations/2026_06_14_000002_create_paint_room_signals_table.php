<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paint_room_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paint_room_id')->constrained('paint_rooms')->cascadeOnDelete();
            $table->string('from_role', 10);
            $table->string('signal_type', 20);
            $table->longText('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['paint_room_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paint_room_signals');
    }
};
