<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseSupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'member_message',
        'admin_reply',
        'admin_replied_at',
    ];

    protected function casts(): array
    {
        return [
            'admin_replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isAnswered(): bool
    {
        return $this->admin_reply !== null && $this->admin_reply !== '';
    }
}
