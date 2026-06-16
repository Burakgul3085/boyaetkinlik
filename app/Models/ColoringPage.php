<?php

namespace App\Models;

use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ColoringPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'age_group',
        'learning_outcomes',
        'usage_instructions',
        'teacher_note',
        'file_info',
        'copyright_note',
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
     * Ana dosyanın hangi diskte olduğunu bulur (public ve local sırayla denenir).
     *
     * @return array{disk: FilesystemContract, path: string}|null
     */
    public function resolveMainFileStorage(): ?array
    {
        $path = trim((string) $this->pdf_path);
        if ($path === '') {
            return null;
        }

        foreach (['public', 'local'] as $diskName) {
            $disk = Storage::disk($diskName);
            if ($disk->exists($path)) {
                return ['disk' => $disk, 'path' => $path];
            }
        }

        return null;
    }

    /**
     * Online boya: yalnızca indirilebilir ana dosya (pdf_path). Kapak önizlemesi kullanılmaz.
     *
     * @return array{disk: FilesystemContract, path: string}
     */
    public function lineArtFileSource(): array
    {
        $path = trim((string) $this->pdf_path);
        if ($path === '') {
            throw new RuntimeException('Ana dosya yolu tanımlı değil.');
        }

        foreach (['public', 'local'] as $diskName) {
            $disk = Storage::disk($diskName);
            if ($disk->exists($path) || is_file($disk->path($path))) {
                return ['disk' => $disk, 'path' => $path];
            }
        }

        throw new RuntimeException('Ana dosya sunucuda bulunamadı.');
    }

    /**
     * Ana dosyanın bulunduğu disk (ücretsiz kayıtlar genelde public; eski/özel durumlarda local).
     */
    public function diskForMainFile(): FilesystemContract
    {
        $resolved = $this->resolveMainFileStorage();

        return $resolved !== null ? $resolved['disk'] : Storage::disk('public');
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
