# Boya Etkinlik Platformu (Laravel 11)

Bu proje, ucretsiz ve ucretli boyama sayfalarinin listelendigi, satin alinabildigi ve token bazli indirilebildigi bir platform iskeletidir.

## Kurulum

1. `cp .env.example .env`
2. Veritabani ayarlarini `.env` icinde doldurun.
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `npm install && npm run build`

## Varsayilan Admin

- E-posta: `admin@boyaetkinlik.test`
- Sifre: `12345678`

## Shared Hosting Notlari

- `APP_ENV=production`, `APP_DEBUG=false`
- Queue ve cache icin `database` veya `file` kullanin.
- Ucretli dosyalar `storage/app/private` altinda saklanir ve public erisim yoktur.
- Ucretsiz dosyalar `storage/app/public/free-pages` altinda saklanir.

### `storage:link` calismazsa alternatif

Shared hostingte symlink kapaliysa:

1. `storage/app/public` iceriklerini `public/storage` altina manuel kopyalayin.
2. Yukleme stratejisini buna gore sabit tutun (deploy script ile senkronize edin).

## Shopier Notu

Shopier callback rotasi: `POST /shopier/callback`

Bu iskelette callback geldikten sonra:
- odeme basariliysa islem `paid` olur,
- tek kullanimlik indirme tokeni uretilir,
- kullaniciya e-posta ile indirme linki gonderilir.
