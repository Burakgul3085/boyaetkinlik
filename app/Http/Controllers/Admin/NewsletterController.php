<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Throwable;

class NewsletterController extends Controller
{
    public function index()
    {
        return view('admin.newsletter.index', [
            'subscribers' => NewsletterSubscriber::query()->latest()->paginate(20),
            'totalSubscribers' => NewsletterSubscriber::query()->count(),
            'contactedSubscribers' => NewsletterSubscriber::query()->whereNotNull('last_contacted_at')->count(),
        ]);
    }

    public function sendToSubscriber(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subscriber_id' => ['required', 'integer', 'exists:newsletter_subscribers,id'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:8000'],
        ]);

        $subscriber = NewsletterSubscriber::query()->findOrFail($data['subscriber_id']);

        try {
            $this->sendNewsletterMail($subscriber, $data['subject'], $data['message']);
            $subscriber->update(['last_contacted_at' => now()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['newsletter' => 'E-posta gönderilemedi. SMTP ayarlarını kontrol edin.']);
        }

        return back()->with('success', $subscriber->full_name.' kişisine e-bülten gönderildi.');
    }

    public function sendBulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:8000'],
        ]);

        /** @var \Illuminate\Database\Eloquent\Collection<int, NewsletterSubscriber> $subscribers */
        $subscribers = NewsletterSubscriber::query()->get();
        if ($subscribers->isEmpty()) {
            return back()->withErrors(['newsletter' => 'Gönderim için kayıtlı e-bülten abonesi bulunmuyor.']);
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($subscribers as $subscriber) {
            /** @var NewsletterSubscriber $subscriber */
            try {
                $this->sendNewsletterMail($subscriber, $data['subject'], $data['message']);
                $subscriber->update(['last_contacted_at' => now()]);
                $successCount++;
            } catch (Throwable $exception) {
                report($exception);
                $failedCount++;
            }
        }

        if ($successCount === 0) {
            return back()->withErrors(['newsletter' => 'Toplu gönderim başarısız oldu. SMTP ayarlarını kontrol edin.']);
        }

        $message = "Toplu gönderim tamamlandı. Başarılı: {$successCount}";
        if ($failedCount > 0) {
            $message .= " | Başarısız: {$failedCount}";
        }

        return back()->with('success', $message);
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $fullName = $subscriber->full_name;
        $subscriber->delete();

        return back()->with('success', $fullName.' e-bülten listesinden silindi.');
    }

    private function sendNewsletterMail(NewsletterSubscriber $subscriber, string $subject, string $message): void
    {
        $mailer = $this->buildMailer();

        $mailer->addAddress($subscriber->email, $subscriber->full_name);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $this->buildHtmlMailBody($subscriber->full_name, $subject, $message);
        $mailer->AltBody = "Merhaba {$subscriber->full_name},\n\n{$message}";

        $mailer->send();
    }

    private function buildMailer(): PHPMailer
    {
        $recipientEmail = Setting::getValue('contact_email', '');
        $smtpHost = Setting::getValue('smtp_host', '');
        $smtpPort = (int) (Setting::getValue('smtp_port', '587') ?: 587);
        $smtpUsername = Setting::getValue('smtp_username', '');
        $smtpPassword = Setting::getValue('smtp_password', '');
        $smtpEncryption = strtolower((string) (Setting::getValue('smtp_encryption', 'tls') ?: 'tls'));
        $fromEmail = Setting::getValue('smtp_from_email', $smtpUsername ?: $recipientEmail);
        $fromName = Setting::getValue('smtp_from_name', 'Boya Etkinlik E-Bülten');

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
        $mailer->setFrom($fromEmail, $fromName ?: 'Boya Etkinlik E-Bülten');

        return $mailer;
    }

    private function buildHtmlMailBody(string $fullName, string $subject, string $message): string
    {
        $appName = config('app.name', 'Boya Etkinlik');
        $safeMessage = nl2br(e($message));
        $safeFullName = e($fullName);
        $safeSubject = e($subject);
        $sentAt = now()->format('d.m.Y H:i');
        $contactEmail = e((string) (Setting::getValue('contact_email', 'info@boyaetkinlik.com') ?: 'info@boyaetkinlik.com'));

        return <<<HTML
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeSubject}</title>
</head>
<body style="margin:0;padding:0;background:#eef2ff;font-family:Inter,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" style="width:100%;border-collapse:collapse;padding:28px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" style="width:100%;max-width:680px;border-collapse:collapse;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#4338ca,#7c3aed);border-radius:18px 18px 0 0;padding:24px 26px;">
                            <p style="margin:0;font-size:11px;color:#e0e7ff;letter-spacing:0.14em;text-transform:uppercase;font-weight:600;">{$appName} • E-Bülten</p>
                            <h1 style="margin:10px 0 0;font-size:24px;line-height:1.35;color:#ffffff;font-weight:700;">{$safeSubject}</h1>
                            <p style="margin:8px 0 0;font-size:12px;color:#ddd6fe;">Gönderim zamanı: {$sentAt}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border:1px solid #dbe3ff;border-top:none;border-radius:0 0 18px 18px;padding:26px;">
                            <p style="margin:0 0 14px;font-size:15px;color:#1e293b;">Merhaba <strong>{$safeFullName}</strong>,</p>

                            <div style="margin-top:8px;border:1px solid #dbeafe;background:#f8faff;border-radius:14px;padding:18px;">
                                <p style="margin:0;font-size:14px;line-height:1.8;color:#0f172a;">{$safeMessage}</p>
                            </div>

                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-top:16px;">
                                <tr>
                                    <td style="font-size:12px;color:#64748b;">
                                        Sorularınız için bizimle iletişime geçebilirsiniz:
                                        <a href="mailto:{$contactEmail}" style="color:#4f46e5;text-decoration:none;font-weight:600;">{$contactEmail}</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:18px;padding-top:14px;border-top:1px solid #e2e8f0;">
                                <p style="margin:0;font-size:12px;color:#94a3b8;">
                                    Bu e-posta {$appName} tarafından, e-bülten aboneliğiniz kapsamında gönderilmiştir.
                                </p>
                            </div>
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
}
