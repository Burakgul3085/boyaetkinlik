<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteMailer;
use App\Support\VerificationMailFlow;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function showRegister()
    {
        return view('admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('register_email', ''))),
            'phone' => $this->normalizePhoneInput((string) $request->input('register_phone', '')),
        ]);

        $data = $request->validate([
            'register_first_name' => ['required', 'string', 'max:120'],
            'register_last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40', 'unique:users,phone'],
            'register_password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ], [
            'register_first_name.required' => 'Ad alanı zorunludur.',
            'register_last_name.required' => 'Soyad alanı zorunludur.',
            'email.unique' => 'Bu e-posta ile kayıtlı bir admin zaten var.',
            'phone.unique' => 'Bu telefon numarası başka bir hesapta kullanılıyor.',
        ]);

        try {
            $this->issueAndSendAdminSignupCode($request, [
                'first_name' => $data['register_first_name'],
                'last_name' => $data['register_last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['register_password'],
            ]);
        } catch (Throwable $exception) {
            $msg = VerificationMailFlow::isSessionSaveFailure($exception)
                ? VerificationMailFlow::sessionSaveFailedMessage()
                : 'Kayıt doğrulama kodu gönderilemedi. SMTP ayarlarını kontrol edin.';

            return back()->withErrors(['register_email' => $msg])->withInput();
        }

        return redirect()->route('admin.register.verify.form')
            ->with('success', 'Doğrulama kodu gönderildi. Kodu girerek admin üyeliğini tamamlayın.');
    }

    public function showRegisterVerify(Request $request)
    {
        $pending = (array) $request->session()->get('admin_signup_pending', []);
        if ($pending === []) {
            return redirect()->route('admin.login')
                ->withErrors(['register_email' => 'Bekleyen admin üyeliği bulunamadı.']);
        }

        return view('admin.auth.register-verify', [
            'attemptsRemaining' => max(0, 3 - (int) $request->session()->get('admin_signup_wrong_count', 0)),
            'sentToEmail' => (string) $request->session()->get('admin_signup_sent_to', ''),
            'pendingEmail' => (string) ($pending['email'] ?? ''),
        ]);
    }

    public function verifyRegister(Request $request)
    {
        $normalizedCode = preg_replace('/\D+/', '', (string) $request->input('verification_code', '')) ?? '';
        if (strlen($normalizedCode) !== 6) {
            return back()->withErrors(['verification_code' => 'Doğrulama kodu 6 haneli olmalıdır.']);
        }

        $pending = (array) $request->session()->get('admin_signup_pending', []);
        $hash = (string) $request->session()->get('admin_signup_code_hash', '');
        $expiresAt = (int) $request->session()->get('admin_signup_expires_at', 0);

        if ($pending === [] || $hash === '' || $expiresAt === 0) {
            return back()->withErrors(['verification_code' => 'Doğrulama verisi bulunamadı.']);
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget($this->adminSignupSessionKeys());
            return redirect()->route('admin.login')->withErrors(['register_email' => 'Kodun süresi doldu, tekrar kayıt olun.']);
        }

        if (! hash_equals($hash, hash('sha256', $normalizedCode))) {
            $failed = (int) $request->session()->get('admin_signup_wrong_count', 0) + 1;
            $request->session()->put('admin_signup_wrong_count', $failed);

            if ($failed >= 3) {
                $request->session()->forget($this->adminSignupSessionKeys());
                return redirect()->route('admin.login')->withErrors(['register_email' => '3 kez hatalı kod girildi, kayıt işlemi iptal edildi.']);
            }

            return back()->withErrors(['verification_code' => 'Kod hatalı. Kalan hakkınız: '.(3 - $failed).'.']);
        }

        try {
            User::query()->create([
                'name' => trim(((string) ($pending['first_name'] ?? '')).' '.((string) ($pending['last_name'] ?? ''))),
                'first_name' => (string) ($pending['first_name'] ?? ''),
                'last_name' => (string) ($pending['last_name'] ?? ''),
                'email' => (string) ($pending['email'] ?? ''),
                'phone' => $pending['phone'] ?? null,
                'password' => (string) ($pending['password_hash'] ?? ''),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateConstraint($exception, 'email')) {
                return redirect()->route('admin.login')->withErrors(['register_email' => 'Bu e-posta ile kayıtlı bir admin zaten var.']);
            }

            if ($this->isDuplicateConstraint($exception, 'phone')) {
                return redirect()->route('admin.login')->withErrors(['register_phone' => 'Bu telefon numarası başka bir hesapta kullanılıyor.']);
            }

            throw $exception;
        }

        $request->session()->forget($this->adminSignupSessionKeys());

        return redirect()->route('admin.login')->with('success', 'Admin üyeliği tamamlandı. Şimdi giriş yapabilirsiniz.');
    }

    public function showForgotPassword()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendForgotPassword(Request $request)
    {
        $loginValue = trim((string) $request->input('login', ''));

        $request->merge([
            'login' => $loginValue,
        ]);

        $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ]);

        $admin = $this->resolveAdminByEmailOrPhone($loginValue);

        if ($admin !== null) {
            $newPassword = $this->generateNumericPassword(8);
            $admin->password = $newPassword;
            $admin->save();

            $recipientEmail = trim((string) (Setting::getValue('contact_email', '') ?? ''));
            if ($recipientEmail === '') {
                return back()->withErrors(['login' => 'İletişim e-postası (contact_email) ayarı bulunamadı.']);
            }

            $adminName = $admin->display_name !== '' ? $admin->display_name : $admin->email;
            $safeName = e($adminName);
            $safeEmail = e($admin->email);
            $safePhone = e((string) ($admin->phone ?: '-'));

            $html = <<<HTML
<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.6;padding:16px;">
    <p>Admin şifre sıfırlama talebi oluşturuldu.</p>
    <ul>
        <li><strong>Admin:</strong> {$safeName}</li>
        <li><strong>E-posta:</strong> {$safeEmail}</li>
        <li><strong>Telefon:</strong> {$safePhone}</li>
    </ul>
    <p>Yeni geçici şifre:</p>
    <p style="font-size:22px;font-weight:700;letter-spacing:0.15em;">{$newPassword}</p>
    <p style="font-size:13px;color:#475569;">Giriş yaptıktan sonra Admin Yönetimi bölümünden şifreyi güncelleyin.</p>
</body>
</html>
HTML;

            $text = "Admin şifre sıfırlama talebi\n"
                ."Admin: {$adminName}\n"
                ."E-posta: {$admin->email}\n"
                ."Telefon: ".($admin->phone ?: '-')."\n\n"
                ."Yeni geçici şifre: {$newPassword}\n"
                ."Giriş yaptıktan sonra Admin Yönetimi bölümünden şifreyi güncelleyin.\n";

            try {
                SiteMailer::send(
                    $recipientEmail,
                    'Admin şifre sıfırlama talebi',
                    $html,
                    $text
                );
            } catch (Throwable $exception) {
                report($exception);

                return back()->withErrors([
                    'login' => 'Şifre sıfırlama e-postası gönderilemedi. SMTP ayarlarını kontrol edin.',
                ]);
            }
        }

        return redirect()->route('admin.forgot-password')
            ->with('forgot_status', 'Eşleşen admin hesabı varsa yeni şifre güvenli adrese gönderildi.');
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
            $prev = $exception->getPrevious();
            Log::error('[admin_verify_mail] '.get_class($exception).': '.$exception->getMessage()
                .($prev ? ' | prev: '.get_class($prev).': '.$prev->getMessage() : ''));
            report($exception);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $msg = VerificationMailFlow::isSessionSaveFailure($exception)
                ? VerificationMailFlow::sessionSaveFailedMessage()
                : 'Doğrulama kodu gönderilemedi. SMTP ayarlarınızı kontrol edin.';

            return back()->withErrors(['email' => $msg]);
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

        if (! $recipientEmail) {
            throw new Exception('İletişim e-postası (contact_email) ayarı eksik.');
        }

        // php artisan mail:test ile aynı yol (SiteMailer) — web/CLI farkı olmasın.
        SiteMailer::send(
            $recipientEmail,
            'Admin giriş doğrulama kodu',
            $this->buildVerificationMailBody($verificationCode),
            "Admin giriş doğrulama kodunuz: {$verificationCode}\nBu kod 15 dakika geçerlidir."
        );

        try {
            $request->session()->put('admin_code_verified', false);
            $request->session()->put('admin_verification_wrong_count', 0);
            $request->session()->put('admin_verification_code_hash', hash('sha256', $verificationCode));
            $request->session()->put('admin_verification_expires_at', now()->addMinutes(15)->timestamp);
            $request->session()->put('admin_verification_sent_to', $recipientEmail);
            $request->session()->save();
        } catch (Throwable $e) {
            report($e);
            throw new Exception(
                VerificationMailFlow::SESSION_SAVE_FAILED_MARKER.$e->getMessage(),
                0,
                $e
            );
        }
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

    private function resolveAdminByEmailOrPhone(string $value): ?User
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $email = Str::lower($value);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return User::query()
                ->where('is_admin', true)
                ->where('email', $email)
                ->first();
        }

        $normalizedInputPhone = $this->normalizePhone($value);
        if ($normalizedInputPhone === '') {
            return null;
        }

        return User::query()
            ->where('is_admin', true)
            ->whereNotNull('phone')
            ->get()
            ->first(function (User $user) use ($normalizedInputPhone) {
                return $this->normalizePhone((string) $user->phone) === $normalizedInputPhone;
            });
    }

    private function issueAndSendAdminSignupCode(Request $request, array $payload): void
    {
        $verificationCode = (string) random_int(100000, 999999);
        $recipientEmail = trim((string) (Setting::getValue('contact_email', '') ?? ''));

        if ($recipientEmail === '') {
            throw new Exception('İletişim e-postası (contact_email) ayarı eksik.');
        }

        $fullName = trim($payload['first_name'].' '.$payload['last_name']);
        $safeName = e($fullName);
        $safeEmail = e($payload['email']);
        $safePhone = e((string) ($payload['phone'] ?? '-'));

        $html = <<<HTML
<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.6;padding:16px;">
    <p>Yeni admin üyelik talebi alındı:</p>
    <ul>
        <li><strong>Ad Soyad:</strong> {$safeName}</li>
        <li><strong>E-posta:</strong> {$safeEmail}</li>
        <li><strong>Telefon:</strong> {$safePhone}</li>
    </ul>
    <p>Onay kodu:</p>
    <p style="font-size:24px;font-weight:700;letter-spacing:0.2em;">{$verificationCode}</p>
    <p>Bu kod 15 dakika geçerlidir.</p>
</body>
</html>
HTML;

        $text = "Yeni admin üyelik talebi\n"
            ."Ad Soyad: {$fullName}\n"
            ."E-posta: {$payload['email']}\n"
            ."Telefon: ".(($payload['phone'] ?? '') ?: '-')."\n\n"
            ."Onay kodu: {$verificationCode}\n"
            ."Bu kod 15 dakika geçerlidir.";

        SiteMailer::send($recipientEmail, 'Yeni admin üyelik doğrulama kodu', $html, $text);

        try {
            $request->session()->put('admin_signup_pending', [
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'password_hash' => Hash::make($payload['password']),
            ]);
            $request->session()->put('admin_signup_code_hash', hash('sha256', $verificationCode));
            $request->session()->put('admin_signup_expires_at', now()->addMinutes(15)->timestamp);
            $request->session()->put('admin_signup_wrong_count', 0);
            $request->session()->put('admin_signup_sent_to', $recipientEmail);
            $request->session()->save();
        } catch (Throwable $e) {
            report($e);
            throw new Exception(
                VerificationMailFlow::SESSION_SAVE_FAILED_MARKER.$e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * @return list<string>
     */
    private function adminSignupSessionKeys(): array
    {
        return [
            'admin_signup_pending',
            'admin_signup_code_hash',
            'admin_signup_expires_at',
            'admin_signup_wrong_count',
            'admin_signup_sent_to',
        ];
    }

    private function isDuplicateConstraint(QueryException $exception, string $field): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        if ($sqlState === '23000' && $driverCode === 1062) {
            return str_contains($message, $field);
        }

        if ($sqlState === '23505') {
            return str_contains($message, $field);
        }

        return str_contains($message, 'unique')
            && (str_contains($message, 'users_'.$field) || str_contains($message, 'users.'.$field));
    }

    private function normalizePhoneInput(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        return $digits === '' ? null : $digits;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function generateNumericPassword(int $length = 8): string
    {
        $length = max(6, min(32, $length));
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }
}
