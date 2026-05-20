<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'blog_category_id',
        'suggested_category_name',
        'excerpt',
        'content',
        'image_path',
        'author_first_name',
        'author_last_name',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $blog): void {
            if (trim((string) $blog->slug) === '') {
                $blog->slug = static::generateUniqueSlug($blog->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pendingCategoryLabel(): string
    {
        if ($this->category) {
            return $this->category->name;
        }

        if (trim((string) $this->suggested_category_name) !== '') {
            return 'Önerilen: '.$this->suggested_category_name;
        }

        return 'Kategori seçilmedi';
    }

    /**
     * Onay sırasında kategori: seçili id veya düzenlenmiş yeni kategori adı.
     */
    public static function resolveCategoryIdForApproval(self $blog, ?int $categoryId, ?string $categoryName): int
    {
        if ($categoryId) {
            return (int) BlogCategory::query()->findOrFail($categoryId)->id;
        }

        $name = trim((string) $categoryName);
        if ($name === '') {
            $name = trim((string) $blog->suggested_category_name);
        }

        if ($name === '' && $blog->blog_category_id) {
            return (int) $blog->blog_category_id;
        }

        if ($name === '') {
            abort(422, 'Onay için kategori seçin veya kategori adı girin.');
        }

        return BlogCategory::createFromName($name, 'visitor')->id;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function authorFullName(): string
    {
        return trim($this->author_first_name.' '.$this->author_last_name);
    }

    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title, '-', 'tr');
        if ($baseSlug === '') {
            $baseSlug = 'blog';
        }

        $slug = $baseSlug;
        $counter = 2;
        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
