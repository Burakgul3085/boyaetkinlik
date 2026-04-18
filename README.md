# Boya Etkinlik Platformu (Laravel 11)

Bu proje, ücretsiz ve ücretli boyama sayfalarının listelendiği, satın alınabildiği ve token bazlı indirilebildiği bir platform iskeletidir.

## Kurulum

1. `cp .env.example .env`
2. Veritabanı ayarlarını `.env` içinde doldurun.
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `npm install && npm run build`

## Varsayılan Admin

- E-posta: `admin@boyaetkinlik.test`
- Şifre: `12345678`

## Paylaşımlı Hosting Notları

- `APP_ENV=production`, `APP_DEBUG=false`
- Queue ve cache için `database` veya `file` kullanın.
- Ücretli dosyalar `storage/app/private` altında saklanır ve herkese açık erişim yoktur.
- Ücretsiz dosyalar `storage/app/public/free-pages` altında saklanır.

### `storage:link` çalışmazsa alternatif

Paylaşımlı hostingte symlink kapalıysa:

1. `storage/app/public` içeriklerini `public/storage` altına manuel kopyalayın.
2. Yükleme stratejisini buna göre sabit tutun (deploy betiği ile senkronize edin).

## Shopier Notu

Shopier callback rotası: `POST /shopier/callback`

Bu iskelette callback geldikten sonra:

- ödeme başarılıysa işlem `paid` olur,
- tek kullanımlık indirme tokeni üretilir,
- kullanıcıya e-posta ile indirme linki gönderilir.
