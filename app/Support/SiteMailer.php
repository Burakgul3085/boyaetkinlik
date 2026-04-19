<?php

namespace App\Support;

use App\Models\Setting;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class SiteMailer
{
    /**
     * Ayarlardaki SMTP ile tek alıcıya HTML e-posta gönderir.
     *
     * @param  array{email: string, name?: string}|null  $replyTo
     *
     * @throws Exception
     */
    public static function send(
        string $toEmail,
        string $subject,
        string $htmlBody,
        string $textBody,
        ?array $replyTo = null
    ): void {
        $smtpHost = trim((string) (Setting::getValue('smtp_host', '') ?? ''));
        $smtpPort = (int) (Setting::getValue('smtp_port', '587') ?: 587);
        $smtpUsername = trim((string) (Setting::getValue('smtp_username', '') ?? ''));
        $smtpPassword = (string) (Setting::getValue('smtp_password', '') ?? '');
        $smtpEncryption = strtolower((string) (Setting::getValue('smtp_encryption', 'tls') ?: 'tls'));
        $fromEmail = Setting::smtpFromEmail($smtpUsername);
        $fromName = (string) (Setting::getValue('smtp_from_name', 'Boya Etkinlik') ?? 'Boya Etkinlik');

        if (! $smtpHost || ! $smtpPort || ! $smtpUsername || ! $smtpPassword || ! $fromEmail) {
            throw new Exception('SMTP ayarları eksik.');
        }

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $smtpHost;
        $mailer->Port = $smtpPort;
        $mailer->SMTPAuth = true;
        $mailer->Username = $smtpUsername;
        $mailer->Password = $smtpPassword;
        $mailer->SMTPSecure = $smtpEncryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->SMTPDebug = SMTP::DEBUG_OFF;
        $mailer->CharSet = 'UTF-8';

        $mailer->setFrom($fromEmail, $fromName ?: 'Boya Etkinlik');
        $mailer->addAddress($toEmail);

        if ($replyTo !== null && ($replyTo['email'] ?? '') !== '') {
            $mailer->addReplyTo($replyTo['email'], $replyTo['name'] ?? '');
        }

        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $textBody;

        $mailer->send();
    }
}
