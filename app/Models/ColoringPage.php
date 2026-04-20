<?php

namespace App\Models;

use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ColoringPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'cover_image_path',
        'pdf_path',
        'price',
        'shopier_product_url',
        'is_free',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function purchaseVerificationRequests(): HasMany
    {
        return $this->hasMany(PurchaseVerificationRequest::class);
    }

    /**
     * Yönetim panelindeki «Dosya» alanı (pdf_path). İndirme / yazdırma / e-posta bu yolu kullanır.
     * Kapak görseli cover_image_path ile karıştırılmamalıdır.
     */
    public function mainDownloadRelativePath(): string
    {
        return (string) $this->pdf_path;
    }

    /**
     * Ana dosyanın bulunduğu disk (ücretsiz kayıtlar genelde public; eski/özel durumlarda local).
     */
    public function diskForMainFile(): FilesystemContract
    {
        if (Storage::disk('public')->exists($this->pdf_path)) {
            return Storage::disk('public');
        }

        if (Storage::disk('local')->exists($this->pdf_path)) {
            return Storage::disk('local');
        }

        return Storage::disk('public');
    }

    /**
     * pdf_path yanlışlıkla kapak klasörüne yazılmış mı? (Normalde yalnızca free-pages/ veya paid-pages/)
     */
    public function mainFilePathLooksLikeCoverFolder(): bool
    {
        $path = (string) $this->pdf_path;

        return $path !== '' && str_starts_with($path, 'covers/');
    }
}
