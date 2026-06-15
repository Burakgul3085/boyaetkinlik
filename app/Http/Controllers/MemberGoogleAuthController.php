<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->googleConfigured()) {
            return redirect()->route('member.login')
                ->withErrors(['email' => 'Google ile giriş şu an yapılandırılmamış.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('member.login')
                ->withErrors(['email' => 'Google doğrulaması tamamlanamadı. Lütfen tekrar deneyin.']);
        }

        $googleId = trim((string) $googleUser->getId());
        $email = Str::lower(trim((string) $googleUser->getEmail()));

        if ($googleId === '' || $email === '') {
            return redirect()->route('member.login')
                ->withErrors(['email' => 'Google hesabından e-posta bilgisi alınamadı.']);
        }

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
                'avatar' => $googleUser->getAvatar() ?: $userByEmail->avatar,
                'email_verified_at' => $userByEmail->email_verified_at ?? now(),
            ])->save();

            return $this->loginMember($request, $userByEmail->fresh(), false);
        }

        [$firstName, $lastName] = $this->splitName($googleUser);

        $user = User::query()->create([
            'name' => trim($firstName.' '.$lastName) ?: $googleUser->getName() ?: 'Üye',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'google_id' => $googleId,
            'avatar' => $googleUser->getAvatar(),
            'password' => null,
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        return $this->loginMember($request, $user, true);
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
        return (string) config('services.google.client_id') !== ''
            && (string) config('services.google.client_secret') !== '';
    }
}
