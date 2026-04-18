<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorFeedback extends Model
{
    protected $table = 'visitor_feedback';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'body',
        'rating',
        'is_approved',
        'show_email_public',
        'approved_at',
        'admin_reply',
        'admin_reply_published',
        'reply_email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'show_email_public' => 'boolean',
            'approved_at' => 'datetime',
            'admin_reply_published' => 'boolean',
            'reply_email_sent_at' => 'datetime',
        ];
    }

    public function scopeApprovedForPublic($query)
    {
        return $query->where('is_approved', true)->orderByDesc('approved_at');
    }
}
