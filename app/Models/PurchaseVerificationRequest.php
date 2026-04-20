<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseVerificationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'coloring_page_id',
        'order_no',
        'email',
        'phone',
        'customer_name',
        'status',
        'verification_token',
        'transaction_id',
        'reviewed_by',
        'admin_note',
        'reviewed_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function coloringPage(): BelongsTo
    {
        return $this->belongsTo(ColoringPage::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
