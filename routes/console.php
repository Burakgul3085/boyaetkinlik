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
