<?php

namespace App\Http\Controllers;

use App\Models\ColoringPage;
use App\Models\Setting;
use App\Support\PhpmailerSmtp;
use App\Models\Transaction;
use App\Support\FileFormatDownloadService;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ColoringPageController extends Controller
{
    public function show(ColoringPage $coloringPage, FileFormatDownloadService $downloadService)
    {
        $sourceExtension = $downloadService->sourceExtension($coloringPage->mainDownloadRelativePath());

        return view('frontend.product', [
            'coloringPage' => $coloringPage,
            'downloadFormats' => $downloadService->downloadOptions($sourceExtension),
        ]);
    }

    public function downloadFree(Request $request, ColoringPage $coloringPage, FileFormatDownloadService $downloadService)
    {
        abort_unless($coloringPage->is_free, 403);

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: null);

        if ($requestedFormat !== null && ! in_array($requestedFormat, $downloadService->allowedFormats(), true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = $coloringPage->diskForMainFile();

        return $downloadService->download(
            $disk,
            $coloringPage->mainDownloadRelativePath(),
            $coloringPage->title,
            $requestedFormat
        );
    }

    public function printFree(Request $request, ColoringPage $coloringPage, FileFormatDownloadService $downloadService)
    {
        abort_unless($coloringPage->is_free, 403);

        $sourceExtension = $downloadService->sourceExtension($coloringPage->mainDownloadRelativePath());
        $availableFormats = $downloadService->downloadOptions($sourceExtension);

        $requestedFormat = $request->string('format')->toString();
        $requestedFormat = $downloadService->normalizeFormat($requestedFormat ?: $sourceExtension);

        if (! in_array((string) $requestedFormat, $availableFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = $coloringPage->diskForMainFile();

        return $downloadService->inline(
            $disk,
            $coloringPage->mainDownloadRelativePath(),
            $coloringPage->title,
            $requestedFormat
        );
    }

    public function sendFreeToEmail(Request $request, ColoringPage $coloringPage, FileFormatDownloadService $downloadService): RedirectResponse
    {
        abort_if(auth()->check(), 403);
        abort_unless($coloringPage->is_free, 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'format' => ['nullable', 'string', 'max:12'],
        ]);

        if ($coloringPage->mainFilePathLooksLikeCoverFolder()) {
            return back()
                ->withInput()
                ->withErrors([
                    'email_send' => 'Bu kayıtta ana dosya yolu hatalı görünüyor (kapak klasörü). Yönetim panelinde «Dosya (PDF/PNG/…)» alanına asıl indirilecek dosyayı yeniden yükleyin; kapak ayrı alanda kalmalıdır.',
                ]);
        }

        $mainRelative = $coloringPage->mainDownloadRelativePath();

        $sourceExtension = $downloadService->sourceExtension($mainRelative);
        $availableFormats = $downloadService->downloadOptions($sourceExtension);

        $requestedFormat = $downloadService->normalizeFormat($data['format'] ?? null) ?: $sourceExtension;
        if (! in_array($requestedFormat, $availableFormats, true)) {
            return back()
                ->withInput()
                ->withErrors(['format' => 'Geçersiz dosya formatı.']);
        }

        /** @var FilesystemAdapter $disk */
        $disk = $coloringPage->diskForMainFile();

        try {
            $attachment = $downloadService->prepareAttachment(
                $disk,
                $mainRelative,
                $coloringPage->title,
                $requestedFormat
            );

            try {
                $this->sendFreeFileMail($data['email'], $coloringPage->title, $attachment['path'], $attachment['filename']);
            } finally {
                if ($attachment['delete_after_send'] && is_file($attachment['path'])) {
                    @unlink($attachment['path']);
                }
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['email_send' => 'E-posta gönderilemedi. Dosya hazırlanamadı veya sunucu ayarlarını kontrol edin.']);
        }

        return back()->with('free_email_sent', true);
    }

    private function sendFreeFileMail(string $toEmail, string $pageTitle, string $absolutePath, string $attachmentFilename): void
    {
        $smtpHost = Setting::getValue('smtp_host', '');
        $smtpPort = (int) (Setting::getValue('smtp_port', '587') ?: 587);
        $smtpUsername = Setting::getValue('smtp_username', '');
        $smtpPassword = Setting::getValue('smtp_password', '');
        $smtpEncryption = strtolower((string) (Setting::getValue('smtp_encryption', 'tls') ?: 'tls'));
        $fromEmail = Setting::getValue('smtp_from_email', $smtpUsername);
        $fromName = Setting::getValue('smtp_from_name', 'Boya Etkinlik');

        if (! $smtpHost || ! $smtpPort || ! $smtpUsername || ! $smtpPassword || ! $fromEmail) {
            throw new Exception('SMTP ayarları eksik.');
        }

        $absolutePath = realpath($absolutePath) ?: $absolutePath;

        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new Exception('Gönderilecek dosya bulunamadı.');
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
        PhpmailerSmtp::applyTransportDefaults($mailer);

        $mailer->setFrom($fromEmail, $fromName ?: 'Boya Etkinlik');
        $mailer->addAddress($toEmail);

        $mailer->isHTML(true);
        $appName = config('app.name', 'Boya Etkinlik');
        $safeTitle = e($pageTitle);
        $mailer->Subject = $appName.' — '.$pageTitle.' (ücretsiz içerik)';
        $mailer->Body = <<<HTML
<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:24px;font-family:Inter,Arial,sans-serif;color:#0f172a;background:#f8fafc;">
    <p style="margin:0 0 12px;">Merhaba,</p>
    <p style="margin:0 0 12px;">İstediğiniz ücretsiz boyama sayfası ektedir: <strong>{$safeTitle}</strong></p>
    <p style="margin:0 0 12px;color:#64748b;font-size:14px;">İyi eğlenceler!</p>
    <p style="margin:16px 0 0;color:#94a3b8;font-size:12px;">{$appName}</p>
</body>
</html>
HTML;
        $mailer->AltBody = "Ücretsiz boyama sayfası ektedir: {$pageTitle}\n\n{$appName}";

        $mailer->addAttachment($absolutePath, $attachmentFilename);

        $mailer->send();
    }

    public function previewImage(ColoringPage $coloringPage): StreamedResponse
    {
        // Güvenlik: Asıl indirilebilir dosyayı (pdf/png/jpg/jpeg) preview endpointinden asla servis etmeyelim.
        // Böylece kullanıcı yalnızca "İndir" akışı üzerinden dosyaya ulaşır.
        if ($coloringPage->cover_image_path) {
            $coverExt = strtolower((string) pathinfo($coloringPage->cover_image_path, PATHINFO_EXTENSION));
            $isImageCover = in_array($coverExt, ['png', 'jpg', 'jpeg'], true);

            /** @var FilesystemAdapter $publicDisk */
            $publicDisk = Storage::disk('public');

            if ($isImageCover && $publicDisk->exists($coloringPage->cover_image_path)) {
                return $publicDisk->response($coloringPage->cover_image_path);
            }
        }

        abort(404);
    }

    public function buy(Request $request, ColoringPage $coloringPage)
    {
        abort_if($coloringPage->is_free, 403);

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $transaction = Transaction::query()->create([
            'user_id' => (auth()->check() && ! auth()->user()->is_admin && $request->session()->get('member_code_verified', false))
                ? auth()->id()
                : null,
            'coloring_page_id' => $coloringPage->id,
            'email' => $data['email'],
            'paid_amount' => $coloringPage->price,
            'status' => 'pending',
        ]);

        // Gerçek Shopier entegrasyonu için gerekli parametreler .env üzerinden doldurulmalıdır.
        return redirect()->route('shopier.redirect', $transaction);
    }
}
