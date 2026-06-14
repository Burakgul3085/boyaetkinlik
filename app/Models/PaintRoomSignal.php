<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaintRoomSignal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'paint_room_id',
        'from_role',
        'signal_type',
        'payload',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(PaintRoom::class, 'paint_room_id');
    }
}
