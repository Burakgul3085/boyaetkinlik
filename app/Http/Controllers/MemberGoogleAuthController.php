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
use Laravel\Socialite\Two\InvalidStateException;
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

            return $this->handleGoogleUser($request, $googleUser);
        } catch (InvalidStateException $exception) {
            report($exception);

            return redirect()->route('member.login')
                ->withErrors(['email' => 'Google oturumu sona erdi. Lütfen tekrar Google ile giriş yapın.']);
        } catch (Throwable $exception) {
            Log::error('[google_callback] '.$exception->getMessage(), [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            report($exception);

            return redirect()->route('member.login')
                ->withErrors(['email' => $this->friendlyErrorMessage($exception)]);
        }
    }

    private function handleGoogleUser(Request $request, \Laravel\Socialite\Contracts\User $googleUser): RedirectResponse
    {
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

        $userByEmail = $this->findMemberByEmail($email);

        if ($userByEmail !== null) {
            if ($userByEmail->is_admin) {
                return redirect()->route('member.login')
                    ->withErrors(['email' => 'Bu e-posta yönetici hesabına ait. Üye girişi için farklı bir hesap kullanın.']);
            }

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

        try {
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
        } catch (QueryException $exception) {
            if ($this->isDuplicateEmailError($exception)) {
                $existing = $this->findMemberByEmail($email);
                if ($existing !== null && ! $existing->is_admin) {
                    $existing->forceFill([
                        'google_id' => $googleId,
                        'avatar' => $avatar ?: $existing->avatar,
                        'email_verified_at' => $existing->email_verified_at ?? now(),
                    ])->save();

                    return $this->loginMember($request, $existing->fresh(), false);
                }
            }

            throw $exception;
        }
    }

    private function findMemberByEmail(string $email): ?User
    {
        return User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();
    }

    private function isDuplicateEmailError(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'Duplicate entry')
            && str_contains($message, 'users_email_unique');
    }

    private function friendlyErrorMessage(Throwable $exception): string
    {
        if ($exception instanceof QueryException) {
            $message = $exception->getMessage();

            if (str_contains($message, "Unknown column 'google_id'") || str_contains($message, "Unknown column 'avatar'")) {
                return 'Veritabanı Google girişi için hazır değil. Sunucuda php artisan migrate --force çalıştırın.';
            }

            if ($this->isDuplicateEmailError($exception)) {
                return 'Bu e-posta ile zaten kayıtlı bir hesap var. Normal giriş yapmayı deneyin veya farklı bir Google hesabı kullanın.';
            }
        }

        return 'Google ile giriş tamamlanamadı. Lütfen tekrar deneyin.';
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
