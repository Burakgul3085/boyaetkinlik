<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaintRoom extends Model
{
    public const STATUS_WAITING = 'waiting';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'room_code',
        'pin',
        'invite_token',
        'invite_token_used_at',
        'owner_user_id',
        'coloring_page_id',
        'guest_display_name',
        'guest_token',
        'status',
        'expires_at',
        'closed_at',
        'closed_reason',
        'canvas_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'invite_token_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'room_code';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function coloringPage(): BelongsTo
    {
        return $this->belongsTo(ColoringPage::class, 'coloring_page_id');
    }

    public function isOpen(): bool
    {
        return $this->status !== self::STATUS_CLOSED
            && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasGuest(): bool
    {
        return $this->guest_token !== null;
    }

    public function participantCount(): int
    {
        return 1 + ($this->hasGuest() ? 1 : 0);
    }

    public function inviteLinkUsed(): bool
    {
        return $this->invite_token_used_at !== null;
    }
}
