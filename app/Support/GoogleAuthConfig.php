<?php

namespace App\Support;

use App\Models\Setting;

class GoogleAuthConfig
{
    public static function clientId(): string
    {
        $fromEnv = trim((string) config('services.google.client_id', ''));

        return $fromEnv !== '' ? $fromEnv : trim((string) Setting::getValue('google_client_id', ''));
    }

    public static function clientSecret(): string
    {
        $fromEnv = trim((string) config('services.google.client_secret', ''));

        return $fromEnv !== '' ? $fromEnv : trim((string) Setting::getValue('google_client_secret', ''));
    }

    public static function redirectUri(): string
    {
        $fromEnv = trim((string) config('services.google.redirect', ''));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        $fromSetting = trim((string) Setting::getValue('google_redirect_uri', ''));

        return $fromSetting !== '' ? $fromSetting : url('/auth/google/callback');
    }

    public static function isConfigured(): bool
    {
        return self::clientId() !== '' && self::clientSecret() !== '';
    }

    public static function applyToSocialite(): void
    {
        config([
            'services.google.client_id' => self::clientId(),
            'services.google.client_secret' => self::clientSecret(),
            'services.google.redirect' => self::redirectUri(),
        ]);
    }
}
