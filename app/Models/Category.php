<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'nav_order',
        'show_in_nav',
    ];

    protected function casts(): array
    {
        return [
            'show_in_nav' => 'boolean',
        ];
    }

    public function coloringPages(): HasMany
    {
        return $this->hasMany(ColoringPage::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('nav_order')->orderBy('name');
    }
}
