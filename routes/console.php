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
