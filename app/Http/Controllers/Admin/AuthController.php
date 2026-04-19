<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors(['email' => 'Bilgiler hatalı.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! $request->user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => 'Bu alana erişim yetkiniz yok.']);
        }

        try {
            $this->issueAndSendVerificationCode($request);
        } catch (Throwable $exception) {
            report($exception);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'Doğrulama kodu gönderilemedi. SMTP ayarlarınızı kontrol edin.']);
        }

        return redirect()->route('admin.verify.form');
    }

    public function showVerify(Request $request)
    {
        if ($this->isAdminCodeVerified($request)) {
            return redirect()->route('admin.dashboard');
        }

        $wrong = (int) $request->session()->get('admin_verification_wrong_count', 0);
        $hasPending = $request->session()->has('admin_verification_code_hash')
            && (int) $request->session()->get('admin_verification_expires_at', 0) > 0;

        return view('admin.auth.verify', [
            'attemptsRemaining' => max(0, 3 - $wrong),
            'hasPendingCode' => $hasPending,
            'sentToEmail' => (string) $request->session()->get('admin_verification_sent_to', ''),
        ]);
    }

    public function verify(Request $request)
    {
        if ($this->isAdminCodeVerified($request)) {
            return redirect()->route('admin.dashboard');
        }

        $rawCode = (string) $request->input('verification_code', '');
        $normalizedCode = preg_replace('/\D+/', '', $rawCode) ?? '';

        if (strlen($normalizedCode) !== 6) {
            return back()->withErrors(['verification_code' => 'Doğrulama kodu 6 haneli olmalıdır.']);
        }

        $storedHash = (string) $request->session()->get('admin_verification_code_hash', '');
        $expiresAt = (int) $request->session()->get('admin_verification_expires_at', 0);
        $now = now()->timestamp;

        if ($storedHash === '' || $expiresAt === 0) {
            return back()->withErrors([
                'verification_code' => 'Doğrulama verisi bulunamadı veya kod zaten kullanıldı. Lütfen tekrar giriş yapıp yeni kod isteyin.',
            ]);
        }

        if ($now > $expiresAt) {
            $request->session()->forget($this->allVerificationStateKeys());

            return back()->withErrors(['verification_code' => 'Kodun süresi doldu. Lütfen tekrar giriş yapın.']);
        }

        if (! hash_equals($storedHash, hash('sha256', $normalizedCode))) {
            $failed = (int) $request->session()->get('admin_verification_wrong_count', 0) + 1;
            $request->session()->put('admin_verification_wrong_count', $failed);

            if ($failed >= 3) {
                $request->session()->forget($this->allVerificationStateKeys());
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'Üç kez hatalı doğrulama kodu girildi. Güvenlik nedeniyle oturum kapatıldı; lütfen tekrar giriş yapın.']);
            }

            $remaining = 3 - $failed;

            return back()->withErrors([
                'verification_code' => 'Doğrulama kodu hatalı. Kalan deneme hakkı: '.$remaining.'.',
            ]);
        }

        $request->session()->put('admin_code_verified', true);
        $request->session()->forget($this->allVerificationStateKeys());

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Çıkış yapıldı. Tekrar giriş yapabilirsiniz.');
    }

    private function issueAndSendVerificationCode(Request $request): void
    {
        $verificationCode = (string) random_int(100000, 999999);
        $recipientEmail = trim((string) (Setting::getValue('contact_email', '') ?? ''));

        $smtpHost = trim((string) (Setting::getValue('smtp_host', '') ?? ''));
        $smtpPort = (int) (Setting::getValue('smtp_port', '587') ?: 587);
        $smtpUsername = trim((string) (Setting::getValue('smtp_username', '') ?? ''));
        $smtpPassword = (string) (Setting::getValue('smtp_password', '') ?? '');
        $smtpEncryption = strtolower((string) (Setting::getValue('smtp_encryption', 'tls') ?: 'tls'));
        $fromEmail = Setting::smtpFromEmail($smtpUsername !== '' ? $smtpUsername : $recipientEmail);
        $fromName = (string) (Setting::getValue('smtp_from_name', 'Boya Etkinlik Güvenlik') ?? 'Boya Etkinlik Güvenlik');

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

        $mailer->setFrom($fromEmail, $fromName ?: 'Boya Etkinlik Güvenlik');
        $mailer->addAddress($recipientEmail);
        $mailer->isHTML(true);
        $mailer->Subject = 'Admin giriş doğrulama kodu';
        $mailer->Body = $this->buildVerificationMailBody($verificationCode);
        $mailer->AltBody = "Admin giriş doğrulama kodunuz: {$verificationCode}\nBu kod 15 dakika geçerlidir.";
        $mailer->send();

        $request->session()->put('admin_code_verified', false);
        $request->session()->put('admin_verification_wrong_count', 0);
        $request->session()->put('admin_verification_code_hash', hash('sha256', $verificationCode));
        $request->session()->put('admin_verification_expires_at', now()->addMinutes(15)->timestamp);
        $request->session()->put('admin_verification_sent_to', $recipientEmail);
        $request->session()->save();
    }

    private function isAdminCodeVerified(Request $request): bool
    {
        return (bool) $request->session()->get('admin_code_verified', false);
    }

    /**
     * Kod, süre ve yanlış deneme sayacı (başarılı doğrulama veya kilitleme sonrası temizlenir).
     *
     * @return list<string>
     */
    private function allVerificationStateKeys(): array
    {
        return [
            'admin_verification_code_hash',
            'admin_verification_expires_at',
            'admin_verification_sent_to',
            'admin_verification_wrong_count',
        ];
    }

    private function buildVerificationMailBody(string $verificationCode): string
    {
        $appName = config('app.name', 'Boya Etkinlik');
        $sentAt = now()->format('d.m.Y H:i');

        return <<<HTML
<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin giriş doğrulama kodu</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Inter,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" style="width:100%;border-collapse:collapse;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" style="width:100%;max-width:620px;border-collapse:collapse;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#4338ca,#7c3aed);border-radius:16px 16px 0 0;padding:22px 24px;">
                            <p style="margin:0;font-size:12px;color:#ddd6fe;letter-spacing:0.12em;text-transform:uppercase;">{$appName}</p>
                            <h1 style="margin:8px 0 0;font-size:23px;line-height:1.3;color:#ffffff;">Admin giriş doğrulama</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 16px 16px;padding:24px;">
                            <p style="margin:0 0 16px;font-size:14px;color:#475569;">Admin paneline giriş için doğrulama kodunuz:</p>
                            <div style="display:inline-block;padding:14px 20px;border-radius:12px;background:#dde4ff;border:1px solid #c7d2fe;">
                                <span style="font-size:32px;letter-spacing:0.22em;font-weight:700;color:#312e81;">{$verificationCode}</span>
                            </div>
                            <p style="margin:16px 0 0;font-size:13px;color:#475569;">Bu kod <strong>15 dakika</strong> geçerlidir.</p>
                            <p style="margin:8px 0 0;font-size:12px;color:#64748b;">Gönderim zamanı: {$sentAt}</p>
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
