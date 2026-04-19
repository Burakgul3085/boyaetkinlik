<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PhpmailerSmtp;
use App\Models\VisitorFeedback;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Throwable;

class VisitorFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('durum')->toString();
        $query = VisitorFeedback::query()->latest();

        if ($status === 'bekleyen') {
            $query->where('is_approved', false);
        } elseif ($status === 'yayinda') {
            $query->where('is_approved', true);
        }

        return view('admin.visitor-feedback.index', [
            'items' => $query->paginate(25)->withQueryString(),
            'statusFilter' => $status,
            'replyEmailEnabled' => Setting::getValue('visitor_feedback_reply_email_enabled', '1') === '1',
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'visitor_feedback_reply_email_enabled' => ['nullable', 'in:1'],
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'visitor_feedback_reply_email_enabled'],
            ['value' => $request->has('visitor_feedback_reply_email_enabled') ? '1' : '0']
        );

        return back()->with('success', 'Yanıt e-posta ayarı güncellendi.');
    }

    public function approve(VisitorFeedback $visitorFeedback): RedirectResponse
    {
        $visitorFeedback->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Geri bildirim onaylandı ve sitede listelenir.');
    }

    public function toggleEmail(VisitorFeedback $visitorFeedback): RedirectResponse
    {
        $visitorFeedback->update([
            'show_email_public' => ! $visitorFeedback->show_email_public,
        ]);

        return back()->with('success', 'E-posta görünürlüğü güncellendi.');
    }

    public function updateReply(Request $request, VisitorFeedback $visitorFeedback): RedirectResponse
    {
        $data = $request->validate([
            'admin_reply' => ['required', 'string', 'min:2', 'max:8000'],
        ]);

        $visitorFeedback->update([
            'admin_reply' => $data['admin_reply'],
        ]);

        return back()->with('success', $visitorFeedback->admin_reply_published
            ? 'Yanıt güncellendi (yayımda).'
            : 'Yanıt kaydedildi. Yayınlamak için «Yanıtı yayınla» düğmesine basın.');
    }

    public function publishReply(VisitorFeedback $visitorFeedback): RedirectResponse
    {
        if ($visitorFeedback->admin_reply_published) {
            return back()->with('success', 'Yanıt zaten yayındaydı.');
        }

        if (! $visitorFeedback->is_approved) {
            return back()->withErrors(['admin_reply' => 'Önce geri bildirimi onaylamalısınız.']);
        }

        $reply = trim((string) $visitorFeedback->admin_reply);
        if ($reply === '') {
            return back()->withErrors(['admin_reply' => 'Yayınlamadan önce yanıt metni girilmelidir.']);
        }

        $sendMail = Setting::getValue('visitor_feedback_reply_email_enabled', '1') === '1'
            && $visitorFeedback->reply_email_sent_at === null;

        try {
            DB::transaction(function () use ($visitorFeedback, $sendMail): void {
                $visitorFeedback->update(['admin_reply_published' => true]);
                if ($sendMail) {
                    $this->sendReplyNotificationMail($visitorFeedback);
                    $visitorFeedback->update(['reply_email_sent_at' => now()]);
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['mail' => 'Yanıt yayınlanamadı veya e-posta gönderilemedi. SMTP ayarlarını kontrol edin.']);
        }

        return back()->with('success', 'Yanıt yayında. Ziyaretçi ana sayfada görebilir.');
    }

    public function destroy(VisitorFeedback $visitorFeedback): RedirectResponse
    {
        $visitorFeedback->delete();

        return back()->with('success', 'Kayıt silindi.');
    }

    private function sendReplyNotificationMail(VisitorFeedback $feedback): void
    {
        $recipientEmail = $feedback->email;
        $smtpHost = Setting::getValue('smtp_host', '');
        $smtpPort = (int) (Setting::getValue('smtp_port', '587') ?: 587);
        $smtpUsername = Setting::getValue('smtp_username', '');
        $smtpPassword = Setting::getValue('smtp_password', '');
        $smtpEncryption = strtolower((string) (Setting::getValue('smtp_encryption', 'tls') ?: 'tls'));
        $fromEmail = Setting::getValue('smtp_from_email', $smtpUsername ?: Setting::getValue('contact_email', ''));
        $fromName = Setting::getValue('smtp_from_name', 'Boya Etkinlik');

        if (! $smtpHost || ! $smtpPort || ! $smtpUsername || ! $smtpPassword || ! $fromEmail) {
            throw new Exception('SMTP ayarları eksik.');
        }

        $appName = config('app.name', 'Boya Etkinlik');
        $homeUrl = url('/');

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
        $mailer->setFrom($fromEmail, $fromName ?: $appName);
        $mailer->addAddress($recipientEmail, $feedback->first_name.' '.$feedback->last_name);
        $mailer->isHTML(true);
        $mailer->Subject = $appName.' — yorumunuza yanıt verildi';

        $safeReply = nl2br(e((string) $feedback->admin_reply));
        $safeName = e($feedback->first_name);
        $safeApp = e($appName);
        $mailer->Body = '<!doctype html><html lang="tr"><head><meta charset="UTF-8"></head>'
            .'<body style="margin:0;padding:24px;font-family:Inter,Arial,sans-serif;color:#0f172a;background:#f8fafc;">'
            .'<p>Merhaba <strong>'.$safeName.'</strong>,</p>'
            .'<p>'.$safeApp.' sayfası yöneticisi geri bildiriminize yanıt verdi. Ana sayfadaki yorumlar bölümünde yanıtınızı görebilirsiniz.</p>'
            .'<div style="margin-top:16px;padding:16px;border-radius:12px;background:#fff;border:1px solid #e2e8f0;">'
            .'<p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#64748b;">Yanıt</p>'
            .'<p style="margin:0;font-size:14px;line-height:1.6;">'.$safeReply.'</p></div>'
            .'<p style="margin-top:20px;font-size:13px;"><a href="'.e($homeUrl).'" style="color:#4f46e5;">Siteye git</a></p>'
            .'</body></html>';

        $mailer->AltBody = "Merhaba {$feedback->first_name},\n\n"
            ."{$appName} sayfası yöneticisi geri bildiriminize yanıt verdi.\n\n"
            ."Yanıt:\n{$feedback->admin_reply}\n\n"
            ."Site: {$homeUrl}\n";

        $mailer->send();
    }
}
