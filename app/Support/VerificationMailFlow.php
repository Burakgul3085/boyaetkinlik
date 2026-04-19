<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;

/**
 * Doğrulama e-postası gönderildikten sonra oturum yazılamazsa SMTP sanılmasın diye işaret.
 */
final class VerificationMailFlow
{
    public const SESSION_SAVE_FAILED_MARKER = '[verification_session_save_failed] ';

    public static function isSessionSaveFailure(Throwable $e): bool
    {
        return str_starts_with($e->getMessage(), self::SESSION_SAVE_FAILED_MARKER);
    }

    public static function sessionSaveFailedMessage(): string
    {
        return 'Doğrulama e-postası gönderilmiş olabilir; ancak sunucu oturumu kaydedilemedi. '
            .'Veritabanında `sessions` tablosu var mı kontrol edin: `php artisan session:table` ve `php artisan migrate`. '
            .'Ayrıca `.env` içinde `SESSION_DRIVER=database` ise MySQL bağlantısının yazılabilir olduğundan emin olun.';
    }
}
