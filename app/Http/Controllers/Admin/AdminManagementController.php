<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteMailer;
use App\Support\VerificationMailFlow;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('email')
            ->get();

        return view('admin/admin-users/index', compact('admins'));
    }

    public function create()
    {
        return view('admin/admin-users/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
            'phone' => $this->normalizePhone($request->input('phone')),
        ]);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ], [
            'email.unique' => 'Bu e-posta ile kayıtlı bir kullanıcı zaten var.',
            'phone.unique' => 'Bu telefon numarası başka bir hesapta kullanılıyor.',
        ]);

        try {
            $this->issueAndSendSignupCode($request, $data);
        } catch (Throwable $exception) {
            $msg = VerificationMailFlow::isSessionSaveFailure($exception)
                ? VerificationMailFlow::sessionSaveFailedMessage()
                : 'Doğrulama kodu gönderilemedi. SMTP ayarlarını kontrol edin.';

            return back()->withErrors(['email' => $msg])->withInput();
        }

        return redirect()->route('admin.admin-users.create.verify.form')
            ->with('success', 'Doğrulama kodu gönderildi. Kodu girerek yeni admin kaydını tamamlayın.');
    }

    public function showCreateVerify(Request $request)
    {
        $pending = (array) $request->session()->get('admin_signup_pending', []);
        if ($pending === []) {
            return redirect()->route('admin.admin-users.create')
                ->withErrors(['email' => 'Bekleyen admin üyelik talebi bulunamadı.']);
        }

        return view('admin/admin-users/create-verify', [
            'attemptsRemaining' => max(0, 3 - (int) $request->session()->get('admin_signup_wrong_count', 0)),
            'sentToEmail' => (string) $request->session()->get('admin_signup_sent_to', ''),
            'pendingEmail' => (string) ($pending['email'] ?? ''),
        ]);
    }

    public function verifyCreate(Request $request): RedirectResponse
    {
        $normalizedCode = preg_replace('/\D+/', '', (string) $request->input('verification_code', '')) ?? '';
        if (strlen($normalizedCode) !== 6) {
            return back()->withErrors(['verification_code' => 'Doğrulama kodu 6 haneli olmalıdır.']);
        }

        $pending = (array) $request->session()->get('admin_signup_pending', []);
        $hash = (string) $request->session()->get('admin_signup_code_hash', '');
        $expiresAt = (int) $request->session()->get('admin_signup_expires_at', 0);

        if ($pending === [] || $hash === '' || $expiresAt === 0) {
            return back()->withErrors(['verification_code' => 'Doğrulama verisi bulunamadı. Lütfen tekrar deneyin.']);
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget($this->adminSignupSessionKeys());
            return redirect()->route('admin.admin-users.create')
                ->withErrors(['email' => 'Kodun süresi doldu. Lütfen yeniden admin üyeliği başlatın.']);
        }

        if (! hash_equals($hash, hash('sha256', $normalizedCode))) {
            $failed = (int) $request->session()->get('admin_signup_wrong_count', 0) + 1;
            $request->session()->put('admin_signup_wrong_count', $failed);

            if ($failed >= 3) {
                $request->session()->forget($this->adminSignupSessionKeys());
                return redirect()->route('admin.admin-users.create')
                    ->withErrors(['email' => '3 kez hatalı kod girildi. Güvenlik için işlem sıfırlandı.']);
            }

            return back()->withErrors([
                'verification_code' => 'Kod hatalı. Kalan hakkınız: '.(3 - $failed).'.',
            ]);
        }

        try {
            User::query()->create([
                'name' => trim(((string) ($pending['first_name'] ?? '')).' '.((string) ($pending['last_name'] ?? ''))),
                'first_name' => (string) ($pending['first_name'] ?? ''),
                'last_name' => (string) ($pending['last_name'] ?? ''),
                'email' => (string) ($pending['email'] ?? ''),
                'phone' => $this->normalizePhone($pending['phone'] ?? null),
                'password' => (string) ($pending['password_hash'] ?? ''),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateConstraint($exception, 'email')) {
                return redirect()->route('admin.admin-users.create')
                    ->withErrors(['email' => 'Bu e-posta ile kayıtlı bir kullanıcı zaten var.']);
            }

            if ($this->isDuplicateConstraint($exception, 'phone')) {
                return redirect()->route('admin.admin-users.create')
                    ->withErrors(['phone' => 'Bu telefon numarası başka bir hesapta kullanılıyor.']);
            }

            throw $exception;
        }

        $request->session()->forget($this->adminSignupSessionKeys());

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Yeni admin hesabı başarıyla oluşturuldu.');
    }

    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_admin) {
            abort(404);
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:72', 'confirmed'],
        ]);

        $user->password = $data['password'];
        $user->save();

        return redirect()->route('admin.admin-users.index')
            ->with('success', 'Admin şifresi güncellendi: '.$user->display_name);
    }

    private function issueAndSendSignupCode(Request $request, array $payload): void
    {
        $verificationCode = (string) random_int(100000, 999999);
        $recipientEmail = trim((string) (Setting::getValue('contact_email', '') ?? ''));

        if ($recipientEmail === '') {
            throw new Exception('İletişim e-postası (contact_email) ayarı eksik.');
        }

        $pendingFullName = trim($payload['first_name'].' '.$payload['last_name']);
        $safeName = e($pendingFullName);
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
            ."Ad Soyad: {$pendingFullName}\n"
            ."E-posta: {$payload['email']}\n"
            ."Telefon: ".($payload['phone'] ?: '-')."\n\n"
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

    private function normalizePhone(mixed $phone): ?string
    {
        $normalized = trim((string) ($phone ?? ''));
        if ($normalized === '') {
            return null;
        }

        return preg_replace('/\s+/', '', $normalized);
    }
}

