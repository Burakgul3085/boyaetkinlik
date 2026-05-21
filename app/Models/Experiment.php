<?php

namespace App\Models;

use App\Support\YoutubeEmbed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Experiment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'experiment_category_id',
        'excerpt',
        'content',
        'image_path',
        'youtube_url',
        'online_lab_enabled',
        'online_lab_type',
        'online_lab_age_label',
        'online_lab_duration_label',
        'online_lab_sort_order',
        'author_first_name',
        'author_last_name',
        'status',
        'published_at',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'online_lab_enabled' => 'boolean',
            'online_lab_sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $experiment): void {
            if (trim((string) $experiment->slug) === '') {
                $experiment->slug = static::generateUniqueSlug($experiment->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExperimentCategory::class, 'experiment_category_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOnlineLabEnabled($query)
    {
        return $query->where('online_lab_enabled', true)->whereNotNull('online_lab_type');
    }

    public function hasPlayableOnlineLab(): bool
    {
        return $this->online_lab_enabled
            && \App\Support\OnlineExperimentLab::isPlayable($this->online_lab_type);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function authorFullName(): string
    {
        return trim($this->author_first_name.' '.$this->author_last_name);
    }

    public function youtubeEmbedUrl(): ?string
    {
        return YoutubeEmbed::embedUrl($this->youtube_url);
    }

    public function youtubeThumbnailUrl(): ?string
    {
        return YoutubeEmbed::thumbnailUrl($this->youtube_url);
    }

    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title, '-', 'tr');
        if ($baseSlug === '') {
            $baseSlug = 'deney';
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
