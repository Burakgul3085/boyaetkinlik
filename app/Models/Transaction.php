<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coloring_page_id',
        'order_id',
        'email',
        'paid_amount',
        'download_token',
        'token_expires_at',
        'downloaded_at',
        'status',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'paid_amount' => 'decimal:2',
            'token_expires_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (! $transaction->order_id) {
                $transaction->order_id = 'ORD-'.strtoupper(Str::random(10));
            }
        });
    }

    public function coloringPage(): BelongsTo
    {
        return $this->belongsTo(ColoringPage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseSupportTickets(): HasMany
    {
        return $this->hasMany(PurchaseSupportTicket::class);
    }

    public function purchaseVerificationRequests(): HasMany
    {
        return $this->hasMany(PurchaseVerificationRequest::class);
    }
}
