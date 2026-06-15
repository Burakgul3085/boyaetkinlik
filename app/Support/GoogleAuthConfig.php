<?php

namespace App\Support;

use App\Models\Setting;

class GoogleAuthConfig
{
    public static function clientId(): string
    {
        $fromEnv = trim((string) config('services.google.client_id', ''));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        try {
            return trim((string) Setting::getValue('google_client_id', ''));
        } catch (\Throwable) {
            return '';
        }
    }

    public static function clientSecret(): string
    {
        $fromEnv = trim((string) config('services.google.client_secret', ''));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        try {
            return trim((string) Setting::getValue('google_client_secret', ''));
        } catch (\Throwable) {
            return '';
        }
    }

    public static function redirectUri(): string
    {
        $fromEnv = trim((string) config('services.google.redirect', ''));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        try {
            $fromSetting = trim((string) Setting::getValue('google_redirect_uri', ''));
            if ($fromSetting !== '') {
                return $fromSetting;
            }
        } catch (\Throwable) {
            /* */
        }

        return url('/auth/google/callback');
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
