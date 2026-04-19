<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SiteMailer;
use App\Support\VerificationMailFlow;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MemberAuthController extends Controller
{
    public function showRegister()
    {
        return view('frontend.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ], [
            'email.unique' => 'Bu e-posta ile kayıtlı bir hesap zaten var.',
        ]);

        try {
            $user = User::query()->create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_admin' => false,
                'email_verified_at' => null,
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateUserEmailConstraint($exception)) {
                return back()
                    ->withErrors(['email' => 'Bu e-posta ile kayıtlı bir hesap zaten var.'])
                    ->withInput();
            }

            throw $exception;
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        try {
            $this->issueAndSendMemberCode($request, 'register');
        } catch (Throwable $exception) {
            $prev = $exception->getPrevious();
            Log::error('[member_verify_mail register] '.get_class($exception).': '.$exception->getMessage()
                .($prev ? ' | prev: '.get_class($prev).': '.$prev->getMessage() : ''));
            report($exception);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $msg = VerificationMailFlow::isSessionSaveFailure($exception)
                ? VerificationMailFlow::sessionSaveFailedMessage()
                : 'Doğrulama kodu gönderilemedi. Lütfen SMTP ayarlarını kontrol edin.';

            return back()->withErrors(['email' => $msg])->withInput();
        }

        return redirect()->route('member.register.verify.form')
            ->with('success', 'Kayıt oluşturuldu. E-postanıza gelen kodu girerek üyeliğinizi tamamlayın.');
    }

    public function showRegisterVerify(Request $request)
    {
        if (! $request->user() || $request->user()->is_admin) {
            return redirect()->route('member.login');
        }

        return view('frontend.auth.register-verify');
    }

    public function verifyRegister(Request $request): RedirectResponse
    {
        if (! $request->user() || $request->user()->is_admin) {
            return redirect()->route('member.login');
        }

        $check = $this->verifyCodeAttempt($request, 'register');
        if ($check !== true) {
            return $check;
        }

        $request->user()->forceFill(['email_verified_at' => now()])->save();
        $request->session()->put('member_code_verified', true);
        $request->session()->forget($this->memberVerificationKeys());

        return redirect()->route('home')->with('success', 'Üyeliğiniz doğrulandı. Hoş geldiniz.');
    }

    public function showLogin()
    {
        return view('frontend.auth.login');
    }

    public function showForgotPassword()
    {
        return view('frontend.auth.forgot-password');
    }

    public function sendForgotPassword(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('is_admin', false)
            ->first();

        if ($user !== null) {
            $plainPassword = $this->generateNumericPassword(8);
            $user->password = $plainPassword;
            $user->save();

            $appName = config('app.name', 'Boya Etkinlik');
            $safePw = e($plainPassword);

            $html = <<<HTML
<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.6;padding:16px;">
    <p>Merhaba,</p>
    <p><strong>{$appName}</strong> hesabınız için yeni şifreniz (yalnızca rakamlardan oluşur):</p>
    <p style="font-size:22px;font-weight:700;letter-spacing:0.15em;">{$safePw}</p>
    <p style="color:#64748b;font-size:14px;">Giriş yaptıktan sonra <strong>Hesabım</strong> bölümünden şifrenizi değiştirebilirsiniz.</p>
    <p style="font-size:12px;color:#94a3b8;">Bu e-postayı siz talep etmediyseniz lütfen dikkate almayın.</p>
</body>
</html>
HTML;

            $text = "Merhaba,\n\n{$appName} hesabınız için yeni şifreniz (yalnızca rakamlar):\n\n{$plainPassword}\n\n"
                ."Giriş yaptıktan sonra Hesabım bölümünden şifrenizi değiştirebilirsiniz.\n";

            try {
                SiteMailer::send(
                    $user->email,
                    $appName.' — Yeni şifreniz',
                    $html,
                    $text
                );
            } catch (Throwable $exception) {
                report($exception);

                return back()
                    ->withErrors(['email' => 'Şifre e-postası gönderilemedi. SMTP ayarlarını kontrol edin veya daha sonra tekrar deneyin.'])
                    ->withInput();
            }
        }

        return redirect()
            ->route('member.forgot-password')
            ->with('forgot_status', 'Bu e-posta adresi kayıtlıysa yeni şifreniz gönderildi. Gelen kutunuzu ve spam klasörünü kontrol edin.');
    }

    /**
     * Yalnızca rakam (0–9) içeren şifre üretir; uzunluk en az 6 olmalıdır.
     */
    private function generateNumericPassword(int $length = 8): string
    {
        $length = max(6, min(32, $length));
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()->withErrors(['email' => 'E-posta veya şifre hatalı.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ((bool) $request->user()->is_admin) {
            Auth::logout();
            return back()->withErrors(['email' => 'Bu giriş ekranı üye kullanıcılar içindir.']);
        }

        try {
            $this->issueAndSendMemberCode($request, 'login');
        } catch (Throwable $exception) {
            $prev = $exception->getPrevious();
            Log::error('[member_verify_mail login] '.get_class($exception).': '.$exception->getMessage()
                .($prev ? ' | prev: '.get_class($prev).': '.$prev->getMessage() : ''));
            report($exception);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $msg = VerificationMailFlow::isSessionSaveFailure($exception)
                ? VerificationMailFlow::sessionSaveFailedMessage()
                : 'Giriş doğrulama kodu gönderilemedi. Lütfen tekrar deneyin.';

            return back()->withErrors(['email' => $msg]);
        }

        return redirect()->route('member.login.verify.form');
    }

    public function showLoginVerify(Request $request)
    {
        if (! $request->user() || $request->user()->is_admin) {
            return redirect()->route('member.login');
        }

        if ((bool) $request->session()->get('member_code_verified', false)) {
            return redirect()->route('home');
        }

        return view('frontend.auth.login-verify');
    }

    public function verifyLogin(Request $request): RedirectResponse
    {
        if (! $request->user() || $request->user()->is_admin) {
            return redirect()->route('member.login');
        }

        if ((bool) $request->session()->get('member_code_verified', false)) {
            return redirect()->route('home');
        }

        $check = $this->verifyCodeAttempt($request, 'login');
        if ($check !== true) {
            return $check;
        }

        $request->session()->put('member_code_verified', true);
        $request->session()->forget($this->memberVerificationKeys());

        return redirect()->route('home')->with('success', 'Giriş doğrulaması başarılı.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * @return true|RedirectResponse
     */
    private function verifyCodeAttempt(Request $request, string $flow)
    {
        $normalizedCode = preg_replace('/\D+/', '', (string) $request->input('verification_code', '')) ?? '';
        if (strlen($normalizedCode) !== 6) {
            return back()->withErrors(['verification_code' => 'Doğrulama kodu 6 haneli olmalıdır.']);
        }

        $storedHash = (string) $request->session()->get('member_verification_code_hash', '');
        $expiresAt = (int) $request->session()->get('member_verification_expires_at', 0);
        $storedFlow = (string) $request->session()->get('member_verification_flow', '');

        if ($storedHash === '' || $expiresAt === 0 || $storedFlow !== $flow) {
            return back()->withErrors(['verification_code' => 'Doğrulama verisi bulunamadı. Lütfen tekrar giriş yapın.']);
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget($this->memberVerificationKeys());

            return back()->withErrors(['verification_code' => 'Kod süresi doldu. Lütfen tekrar deneyin.']);
        }

        if (! hash_equals($storedHash, hash('sha256', $normalizedCode))) {
            $failed = (int) $request->session()->get('member_verification_wrong_count', 0) + 1;
            $request->session()->put('member_verification_wrong_count', $failed);

            if ($failed >= 3) {
                $request->session()->forget($this->memberVerificationKeys());
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('member.login')
                    ->withErrors(['email' => '3 kez hatalı kod girildi. Lütfen tekrar giriş yapın.']);
            }

            return back()->withErrors(['verification_code' => 'Kod hatalı. Kalan hakkınız: '.(3 - $failed).'.']);
        }

        return true;
    }

    private function issueAndSendMemberCode(Request $request, string $flow): void
    {
        $recipientEmail = trim((string) $request->user()->email);
        $verificationCode = (string) random_int(100000, 999999);

        if (! $recipientEmail) {
            throw new Exception('Kullanıcı e-postası bulunamadı.');
        }

        $subject = $flow === 'register' ? 'Üyelik doğrulama kodunuz' : 'Giriş doğrulama kodunuz';
        $html = "<p>Doğrulama kodunuz: <strong>{$verificationCode}</strong></p><p>Bu kod 15 dakika geçerlidir.</p>";
        $text = "Doğrulama kodunuz: {$verificationCode}. Bu kod 15 dakika geçerlidir.";

        // php artisan mail:test ile aynı yol (SiteMailer).
        SiteMailer::send($recipientEmail, $subject, $html, $text);

        try {
            $request->session()->put('member_code_verified', false);
            $request->session()->put('member_verification_flow', $flow);
            $request->session()->put('member_verification_code_hash', hash('sha256', $verificationCode));
            $request->session()->put('member_verification_expires_at', now()->addMinutes(15)->timestamp);
            $request->session()->put('member_verification_wrong_count', 0);
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
    private function memberVerificationKeys(): array
    {
        return [
            'member_verification_flow',
            'member_verification_code_hash',
            'member_verification_expires_at',
            'member_verification_wrong_count',
        ];
    }

    private function isDuplicateUserEmailConstraint(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        if ($sqlState === '23000' && $driverCode === 1062) {
            return str_contains($message, 'email');
        }

        if ($sqlState === '23505') {
            return str_contains($message, 'email');
        }

        return str_contains($message, 'unique')
            && (str_contains($message, 'users.email') || str_contains($message, 'users_email'));
    }
}
