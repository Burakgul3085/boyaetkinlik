<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Throwable;

class ContactController extends Controller
{
    private const WHATSAPP_NUMBER = '905395189339';

    public function show()
    {
        return view('frontend.contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        try {
            $this->sendMail($data);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['contact' => 'Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.'])
                ->withInput();
        }

        return back()->with('success', 'Mesajınız başarıyla gönderildi.');
    }

    public function sendWhatsApp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'wa_full_name' => ['required', 'string', 'max:120'],
            'wa_email' => ['required', 'email', 'max:255'],
            'wa_message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        $message = $this->buildWhatsAppMessage($data);
        $url = 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.urlencode($message);

        return redirect()->away($url);
    }

    private function sendMail(array $data): void
    {
        $recipientEmail = Setting::getValue('contact_email', '');
        $smtpHost = Setting::getValue('smtp_host', '');
        $smtpPort = (int) (Setting::getValue('smtp_port', '587') ?: 587);
        $smtpUsername = Setting::getValue('smtp_username', '');
        $smtpPassword = Setting::getValue('smtp_password', '');
        $smtpEncryption = strtolower((string) (Setting::getValue('smtp_encryption', 'tls') ?: 'tls'));
        $fromEmail = Setting::getValue('smtp_from_email', $smtpUsername ?: $recipientEmail);
        $fromName = Setting::getValue('smtp_from_name', 'Boya Etkinlik İletişim');

        if (! $recipientEmail || ! $smtpHost || ! $smtpPort || ! $smtpUsername || ! $smtpPassword || ! $fromEmail) {
            throw new Exception('SMTP veya alıcı ayarları eksik.');
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

        $mailer->setFrom($fromEmail, $fromName ?: 'Boya Etkinlik İletişim');
        $mailer->addAddress($recipientEmail);
        $mailer->addReplyTo($data['email'], $data['full_name']);

        $mailer->isHTML(true);
        $mailer->Subject = 'Yeni iletişim mesajı - '.$data['full_name'];
        $mailer->Body = $this->buildHtmlMailBody($data);
        $mailer->AltBody = "Yeni iletişim mesajı\n"
            ."Ad Soyad: {$data['full_name']}\n"
            ."E-posta: {$data['email']}\n\n"
            ."Mesaj:\n{$data['message']}";

        $mailer->send();
    }

    private function buildHtmlMailBody(array $data): string
    {
        $appName = config('app.name', 'Boya Etkinlik');
        $sentAt = now()->format('d.m.Y H:i');
        $fullName = e($data['full_name']);
        $email = e($data['email']);
        $message = nl2br(e($data['message']));

        return <<<HTML
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni iletişim mesajı</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Inter,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" style="width:100%;border-collapse:collapse;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" style="width:100%;max-width:680px;border-collapse:collapse;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:16px 16px 0 0;padding:22px 24px;">
                            <p style="margin:0;font-size:12px;color:#e0e7ff;letter-spacing:0.12em;text-transform:uppercase;">{$appName}</p>
                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;color:#ffffff;">Yeni iletişim formu mesajı</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 16px 16px;padding:24px;">
                            <p style="margin:0 0 18px;font-size:14px;color:#475569;">Sitenizden yeni bir ziyaretçi mesajı geldi.</p>
                            <table role="presentation" style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:14px;color:#334155;"><strong>Ad Soyad:</strong> {$fullName}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #e2e8f0;font-size:14px;color:#334155;"><strong>E-posta:</strong> {$email}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;font-size:14px;color:#334155;"><strong>Gönderim zamanı:</strong> {$sentAt}</td>
                                </tr>
                            </table>
                            <div style="margin-top:18px;border:1px solid #dbeafe;background:#f8fafc;border-radius:12px;padding:16px;">
                                <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#1e293b;">Mesaj:</p>
                                <p style="margin:0;font-size:14px;line-height:1.7;color:#0f172a;">{$message}</p>
                            </div>
                            <p style="margin:18px 0 0;font-size:12px;color:#64748b;">Bu mesaja doğrudan cevap vererek ziyaretçiye ulaşabilirsiniz.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function buildWhatsAppMessage(array $data): string
    {
        $sentAt = now()->format('d.m.Y H:i');

        return "Merhaba, web sitesi üzerinden yeni bir iletişim mesajı gönderildi.\n\n"
            ."Ad Soyad: {$data['wa_full_name']}\n"
            ."E-posta: {$data['wa_email']}\n"
            ."Tarih: {$sentAt}\n\n"
            ."Mesaj:\n{$data['wa_message']}";
    }
}
