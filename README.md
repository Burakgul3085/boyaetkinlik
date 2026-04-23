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
- Canlı `.env`: `APP_URL=https://boyaetkinlik.com` (sonunda `/` yok). `http://` veya `www.` yazmayın; yönlendirme `public/.htaccess` ile tek adrese toplanır.
- Queue ve cache için `database` veya `file` kullanın.
- Ücretli dosyalar `storage/app/private` altında saklanır ve herkese açık erişim yoktur.
- Ücretsiz dosyalar `storage/app/public/free-pages` altında saklanır.

### `storage:link` çalışmazsa alternatif

Paylaşımlı hostingte symlink kapalıysa:

1. `storage/app/public` içeriklerini `public/storage` altına manuel kopyalayın.
2. Yükleme stratejisini buna göre sabit tutun (deploy betiği ile senkronize edin).

### Hostinger (hPanel) — SSL ve tek adres

1. Sol menüden **Web siteleri** → siteni seç.
2. **Güvenlik** veya **SSL** bölümünde sertifikayı etkinleştir; mümkünse **“HTTPS’e yönlendir”** / **Force HTTPS** seçeneğini aç (çift yönlendirme genelde sorun çıkarmaz; yine de tarayıcıda `http://` ve `www` adreslerini test et).
3. **Dosya yöneticisi** ile sunucudaki `public/.htaccess` dosyasının repodaki sürümle güncel olduğundan emin ol (www kaldırma + HTTP→HTTPS kuralları burada).
4. Proje kökündeki `.env` dosyasında `APP_URL=https://boyaetkinlik.com` olduğunu doğrula, ardından gerekirse **Önbelleği temizle**: `php artisan config:clear` (SSH veya Hostinger **Gelişmiş** → **Terminal**).
5. Tarayıcıda kontrol: `http://boyaetkinlik.com` ve `https://www.boyaetkinlik.com` adresleri `https://boyaetkinlik.com` adresine **301** ile gitmeli.
6. Google Search Console’da aynı mülk altında **2–4 hafta** sonra **Performans → Sayfalar** raporunda gösterimlerin çoğunun tek kanonik URL’de toplanmaya başlamasını bekle.

**HSTS:** Yalnızca tüm alt yolların HTTPS ile sorunsuz çalıştığından eminsen panelde aç; aksi halde atla.

### Cloudflare (ad sunucuları Cloudflare ise — kanonik: `https://boyaetkinlik.com`)

Hostinger **DNS / Yönlendirmeler** bu durumda işe yaramaz; ayarlar Cloudflare’da yapılır. Sayfayı bozmamak için önce SSL’i doğrula, sonra tek kural ekleyip test et.

1. [Cloudflare Dashboard](https://dash.cloudflare.com) → **boyaetkinlik.com** → **SSL/TLS** → genel mod **Full (strict)** (origin’de geçerli sertifika varsa). Önce **Full** deneyip site açılıyorsa strict’e geç.
2. Aynı menüde **Edge Certificates** → **Always Use HTTPS**: **Açık** (HTTP istekleri HTTPS’e döner).
3. **Rules** → **Redirect Rules** → **Create rule**:
   - **Rule name:** `www to apex`
   - **If…:** Alan *Hostname* → *equals* → `www.boyaetkinlik.com`
   - **Then…:** *Dynamic redirect* → **301** → hedef ifade (Expression / dinamik URL alanına yapıştırın):  
     `concat("https://boyaetkinlik.com", http.request.uri.path, if(len(http.request.uri.query) > 0, concat("?", http.request.uri.query), ""))`  
     (Amaç: www’yi kaldırıp yol ve sorgu dizgisini aynen taşımak.)
4. Kaydet. **Önbelleği** bir kez temizlemek için **Caching** → **Configuration** → **Purge Everything** (isteğe bağlı; sorun olursa yap).
5. Tarayıcıda gizli sekmede dene: `https://www.boyaetkinlik.com/` → adres çubuğu **`https://boyaetkinlik.com/`** olmalı (301). Ana sayfa ve bir iç sayfa açılıyor mu kontrol et.
6. Sunucudaki **`.env`**: `APP_URL=https://boyaetkinlik.com` ve güncel `public/.htaccess` deploy edilmiş olsun; sonra `php artisan config:clear`.

**Döngü / “too many redirects” olursa:** Cloudflare SSL modunu **Full (strict)** yerine **Full** yapın veya geçici olarak **Always Use HTTPS** kapatıp hangi katmanda döngü olduğunu ayırın.

## Shopier Notu

Shopier callback rotası: `POST /shopier/callback`

Bu iskelette callback geldikten sonra:

- ödeme başarılıysa işlem `paid` olur,
- tek kullanımlık indirme tokeni üretilir,
- kullanıcıya e-posta ile indirme linki gönderilir.
