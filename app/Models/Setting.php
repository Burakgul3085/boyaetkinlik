<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $value = static::query()->where('key', $key)->value('value');

        // Boş string kayıtlı olsa bile (migration/seed sonrası) varsayılanı kullan;
        // aksi halde smtp_from_email vb. '' kalıp "SMTP ayarları eksik" hatası oluşur.
        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    /**
     * Gönderen e-posta: smtp_from_email doluysa onu, değilse $fallback (ör. smtp kullanıcı adı).
     * Veritabanında boş string veya görünmez karakter kayıtlarında da güvenli.
     */
    public static function smtpFromEmail(string $fallback): string
    {
        $raw = static::query()->where('key', 'smtp_from_email')->value('value');
        $explicit = trim((string) ($raw ?? ''));

        return $explicit !== '' ? $explicit : trim($fallback);
    }
}
