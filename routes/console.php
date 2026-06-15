<?php

use App\Models\Setting;
use App\Support\SiteMailer;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email? : Alıcı adres (boşsa contact_email)}', function (?string $email = null) {
    $to = trim((string) ($email ?? ''));
    if ($to === '') {
        $to = trim((string) (Setting::getValue('contact_email', '') ?? ''));
    }
    if ($to === '') {
        $this->error('Alıcı yok. Ayarlarda contact_email tanımlayın veya: php artisan mail:test ornek@gmail.com');

        return 1;
    }

    try {
        SiteMailer::send(
            $to,
            'Boya Etkinlik SMTP test',
            '<p>Bu bir test e-postasıdır. SMTP ayarları çalışıyor.</p>',
            'Bu bir test e-postasıdır. SMTP ayarları çalışıyor.'
        );
        $this->info('Gönderildi: '.$to);

        return 0;
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return 1;
    }
})->purpose('Veritabanı SMTP ayarlarıyla test e-postası gönderir (sunucu tanısı için)');

Artisan::command('mail:diagnostics', function () {
    $this->line('SAPI: '.PHP_SAPI);
    $this->line('disable_functions: '.(ini_get('disable_functions') ?: '(yok)'));
    $this->line('open_basedir: '.(ini_get('open_basedir') ?: '(yok)'));

    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client('tcp://smtp.gmail.com:587', $errno, $errstr, 12, STREAM_CLIENT_CONNECT);
    if (is_resource($fp)) {
        fclose($fp);
        $this->info('Bağlantı: smtp.gmail.com:587 (TCP) — OK');
    } else {
        $this->error("Bağlantı: smtp.gmail.com:587 — HATA [{$errno}] {$errstr}");
    }

    $to = trim((string) (Setting::getValue('contact_email', '') ?? ''));
    if ($to === '') {
        $this->warn('contact_email boş; SiteMailer testi atlandı.');

        return 1;
    }

    try {
        SiteMailer::send(
            $to,
            'mail:diagnostics',
            '<p>CLI diagnostics</p>',
            'CLI diagnostics'
        );
        $this->info('SiteMailer::send — OK → '.$to);
    } catch (\Throwable $e) {
        $this->error('SiteMailer::send — '.$e->getMessage());

        return 1;
    }

    return 0;
})->purpose('CLI ortamında SMTP soket ve SiteMailer denemesi');

Artisan::command('site:diagnose', function () {
    $this->line('PHP: '.PHP_VERSION.' ('.PHP_SAPI.')');
    $this->line('APP_ENV: '.config('app.env'));
    $this->line('APP_DEBUG: '.(config('app.debug') ? 'true' : 'false'));
    $this->line('APP_KEY: '.(config('app.key') ? 'tanımlı' : 'EKSİK'));
    $this->line('APP_URL: '.config('app.url'));

    foreach (['storage/logs', 'storage/framework/views', 'storage/framework/sessions', 'bootstrap/cache'] as $dir) {
        $writable = is_writable(base_path($dir));
        $this->line(($writable ? '[OK]' : '[HATA]')." yazılabilir: {$dir}");
    }

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $this->info('[OK] Veritabanı bağlantısı');
    } catch (\Throwable $e) {
        $this->error('[HATA] Veritabanı: '.$e->getMessage());
    }

    try {
        $siteName = Setting::getValue('header_site_name', 'Boya Etkinlik');
        $this->info('[OK] settings sorgusu: '.$siteName);
    } catch (\Throwable $e) {
        $this->error('[HATA] settings: '.$e->getMessage());
    }

    try {
        view('frontend.hakkimizda')->render();
        $this->info('[OK] Blade sayfa render (hakkimizda)');
    } catch (\Throwable $e) {
        $this->error('[HATA] Blade render: '.$e->getMessage());
    }

    $manifest = base_path('public/build/manifest.json');
    $this->line((is_file($manifest) ? '[OK]' : '[HATA]').' public/build/manifest.json');

    $log = storage_path('logs/laravel.log');
    if (is_file($log)) {
        $this->newLine();
        $this->warn('Son hata özeti (laravel.log):');
        $lines = @file($log, FILE_IGNORE_NEW_LINES) ?: [];
        $errorLines = array_values(array_filter($lines, fn ($line) => str_contains($line, 'production.ERROR') || str_contains($line, 'local.ERROR')));
        if ($errorLines !== []) {
            foreach (array_slice($errorLines, -3) as $line) {
                $this->line($line);
            }
        } else {
            foreach (array_slice($lines, -12) as $line) {
                $this->line($line);
            }
        }
    } else {
        $this->warn('laravel.log yok (izin veya henüz hata yazılmamış).');
    }

    return 0;
})->purpose('500 hatası için sunucu tanısı (DB, izin, paket, log)');
