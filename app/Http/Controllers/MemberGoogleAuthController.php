<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\GoogleAuthConfig;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class MemberGoogleAuthController extends Controller
{
    public function redirect(Request $request): SymfonyRedirectResponse|RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return redirect()
                ->route($request->query('from') === 'register' ? 'member.register' : 'member.login')
                ->withErrors(['email' => 'Google ile giriş şu an yapılandırılmamış.']);
        }

        $request->session()->put('member_google_intent', $request->query('from') === 'register' ? 'register' : 'login');

        GoogleAuthConfig::applyToSocialite();

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return redirect()->route('member.login')
                ->withErrors(['email' => 'Google ile giriş şu an yapılandırılmamış.']);
        }

        try {
            GoogleAuthConfig::applyToSocialite();
            $googleUser = Socialite::driver('google')->user();

            $googleId = trim((string) $googleUser->getId());
            $email = Str::lower(trim((string) $googleUser->getEmail()));

            if ($googleId === '' || $email === '') {
                return redirect()->route('member.login')
                    ->withErrors(['email' => 'Google hesabından e-posta bilgisi alınamadı.']);
            }

            $avatar = $this->normalizeAvatar($googleUser->getAvatar());

            $userByGoogle = User::query()->where('google_id', $googleId)->first();

            if ($userByGoogle !== null) {
                return $this->loginMember($request, $userByGoogle, false);
            }

            $userByEmail = User::query()
                ->where('email', $email)
                ->where('is_admin', false)
                ->first();

            if ($userByEmail !== null) {
                if ($userByEmail->google_id !== null && $userByEmail->google_id !== $googleId) {
                    return redirect()->route('member.login')
                        ->withErrors(['email' => 'Bu e-posta farklı bir Google hesabına bağlı.']);
                }

                $userByEmail->forceFill([
                    'google_id' => $googleId,
                    'avatar' => $avatar ?: $userByEmail->avatar,
                    'email_verified_at' => $userByEmail->email_verified_at ?? now(),
                ])->save();

                return $this->loginMember($request, $userByEmail->fresh(), false);
            }

            [$firstName, $lastName] = $this->splitName($googleUser);

            $user = User::query()->create([
                'name' => trim($firstName.' '.$lastName) ?: $googleUser->getName() ?: 'Üye',
                'first_name' => $firstName !== '' ? $firstName : 'Üye',
                'last_name' => $lastName,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $avatar,
                'is_admin' => false,
            ]);

            $user->forceFill(['email_verified_at' => now()])->save();

            return $this->loginMember($request, $user->fresh(), true);
        } catch (Throwable $exception) {
            Log::error('[google_callback] '.$exception->getMessage(), [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            report($exception);

            $message = 'Google ile giriş tamamlanamadı. Lütfen tekrar deneyin.';
            if ($exception instanceof QueryException && str_contains($exception->getMessage(), 'google_id')) {
                $message = 'Veritabanı Google girişi için hazır değil. Sunucuda php artisan migrate --force çalıştırın.';
            }

            return redirect()->route('member.login')->withErrors(['email' => $message]);
        }
    }

    private function loginMember(Request $request, User $user, bool $isNewGoogleAccount): RedirectResponse
    {
        if ($user->is_admin) {
            return redirect()->route('member.login')
                ->withErrors(['email' => 'Bu giriş ekranı üye kullanıcılar içindir.']);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('member_code_verified', true);

        $request->session()->forget([
            'member_verification_flow',
            'member_verification_code_hash',
            'member_verification_expires_at',
            'member_verification_wrong_count',
            'member_google_intent',
        ]);

        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $message = $isNewGoogleAccount
            ? 'Google ile üyeliğiniz oluşturuldu. Hoş geldiniz.'
            : 'Google ile giriş başarılı. Hoş geldiniz.';

        return redirect()->route('home')->with('success', $message);
    }

    private function normalizeAvatar(mixed $avatar): ?string
    {
        $value = trim((string) ($avatar ?? ''));

        return $value !== '' ? $value : null;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(\Laravel\Socialite\Contracts\User $googleUser): array
    {
        $full = trim((string) ($googleUser->getName() ?? ''));
        if ($full === '') {
            $email = (string) ($googleUser->getEmail() ?? '');
            $local = $email !== '' ? Str::before($email, '@') : 'Üye';

            return [$local, ''];
        }

        $parts = preg_split('/\s+/u', $full, 2) ?: [];

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    private function googleConfigured(): bool
    {
        return GoogleAuthConfig::isConfigured();
    }
}
