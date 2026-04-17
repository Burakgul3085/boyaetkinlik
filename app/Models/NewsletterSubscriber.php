<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'last_contacted_at',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
