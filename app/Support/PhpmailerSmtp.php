<?php

declare(strict_types=1);

namespace App\Support;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * VPS ve TLS ortamlarında PHPMailer SMTP için ortak taşıma ayarları.
 */
final class PhpmailerSmtp
{
    public static function applyTransportDefaults(PHPMailer $mailer): void
    {
        $mailer->Timeout = 60;
        $mailer->SMTPKeepAlive = false;
        $mailer->SMTPAutoTLS = true;

        $mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];
    }
}
