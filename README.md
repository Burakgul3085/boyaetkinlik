## Proje Özeti

`Boya Etkinlik` projesi, Laravel 11 Framework kullanılarak geliştirilmiş, kullanıcılara çeşitli boyama sayfaları sunan bir web platformudur. Üyeler, beğendikleri boyama sayfalarını indirebilir, satın alabilir ve kişiselleştirilmiş bir deneyim yaşayabilirler. Proje, PHPMailer ve FPDF gibi kütüphanelerle zenginleştirilmiş olup, yönetim paneli sayesinde içerik ve kullanıcı yönetimi kolaylıkla yapılabilmektedir. Modern ön yüz teknolojileri olan Tailwind CSS, Alpine.js ve Vite ile hızlı ve dinamik bir kullanıcı arayüzü sunar.

## Özellikler

- **Kullanıcı Yönetimi:** Üye kayıt, giriş, profil yönetimi ve satın alma geçmişi takibi.
- **Boyama Sayfaları:** Çeşitli kategorilerde boyama sayfaları sunumu, indirme ve satın alma imkanı.
- **Kategori Yönetimi:** Boyama sayfalarını kategorize etme ve kolay gezinme.
- **Blog Modülü:** Makaleler ve güncellemeler için blog bölümü.
- **Yönetim Paneli:** Yöneticilerin kullanıcıları, boyama sayfalarını, kategorileri, blog yazılarını, duyuruları ve sistem ayarlarını yönetebilmesi.
- **E-posta Aboneliği:** Haber bültenleri için e-posta aboneliği sistemi.
- **İletişim Formu:** Ziyaretçilerin geri bildirim ve sorularını iletebileceği iletişim modülü.
- **Shopier Entegrasyonu:** Güvenli ve hızlı ödeme işlemleri için Shopier ile entegrasyon.
- **PDF Desteği:** Boyama sayfalarının FPDF kütüphanesi ile PDF olarak indirilmesi.
- **E-posta Bildirimleri:** PHPMailer ile otomatik e-posta bildirimleri (örn. satın alma onayı, şifre sıfırlama).

## Gereksinimler

Projenin düzgün bir şekilde çalışabilmesi için aşağıdaki gereksinimlerin karşılanması gerekmektedir:

- PHP `^8.2`
- Composer
- Node.js & npm (Ön yüz bağımlılıkları için)
- MySQL veya PostgreSQL gibi bir veritabanı
- Apache veya Nginx gibi bir web sunucusu
- Laravel'in gerektirdiği diğer standart PHP eklentileri

## Kurulum

Projenin yerel ortamda kurulumu için aşağıdaki adımları takip edin:

1.  **Depoyu Klonlayın:**
    ```bash
    git clone https://github.com/Burakgul3085/boyaetkinlik.git
    cd boyaetkinlik
    ```

2.  **Composer Bağımlılıklarını Yükleyin:**
    ```bash
    composer install
    ```

3.  **.env Dosyasını Yapılandırın:**
    `cp .env.example .env` komutunu çalıştırın ve yeni `.env` dosyasını veritabanı bağlantı bilgilerinizle güncelleyin. Ayrıca `APP_KEY` değerini oluşturun:
    ```bash
    php artisan key:generate
    ```

4.  **Veritabanını Oluşturun ve Migrate Edin:**
    Veritabanınızı oluşturduktan sonra migrasyonları çalıştırın:
    ```bash
    php artisan migrate
    ```

5.  **Depolama Bağlantısını Oluşturun:**
    ```bash
    php artisan storage:link
    ```

6.  **Ön Yüz Bağımlılıklarını Yükleyin ve Derleyin:**
    ```bash
    npm install
    npm run dev
    ```
    veya üretim için:
    ```bash
    npm run build
    ```

7.  **Uygulamayı Başlatın:**
    ```bash
    php artisan serve
    ```
    Uygulama genellikle `http://127.0.0.1:8000` adresinde çalışacaktır.

## Kullanım

Yönetim paneline erişmek için aşağıdaki varsayılan yönetici hesabı kullanılabilir:

- **E-posta:** `admin@boyaetkinlik.test`
- **Şifre:** `12345678`

**Güvenlik Notu:** Canlı ortamda bu varsayılan bilgileri değiştirmeyi unutmayın.

## Dağıtım

Projenin canlı ortama dağıtımı için aşağıdaki noktalara dikkat edilmesi önerilir:

### Genel Dağıtım Ayarları
- `.env` dosyasında `APP_ENV=production` ve `APP_DEBUG=false` olarak ayarlanmalıdır.
- `APP_URL` değeri canlı sitenizin tam URL'si olmalıdır (örn. `https://boyaetkinlik.com`, sonunda `/` veya `www.` olmadan).
- Kuyruk ve önbellek sürücüleri için `database` veya `file` gibi uygun seçenekler kullanılmalıdır.
- Ücretli dosyalar `storage/app/private` altında, ücretsiz dosyalar ise `storage/app/public/free-pages` altında saklanır.

### `storage:link` Alternatifi
Paylaşımlı hosting ortamlarında `php artisan storage:link` komutu çalışmayabilir. Bu durumda:
1.  `storage/app/public` içeriklerini manuel olarak `public/storage` altına kopyalayın.
2.  Dosya yükleme stratejinizi bu duruma göre sabitleyin.

### Hostinger (hPanel) — SSL ve Tek Adres Yapılandırması
1.  Hostinger panelinizde **Web siteleri** → sitenizi seçin.
2.  **Güvenlik** veya **SSL** bölümünden sertifikayı etkinleştirin ve mümkünse **“HTTPS’e yönlendir”** seçeneğini açın.
3.  Sunucudaki `public/.htaccess` dosyasının projedeki güncel sürümle aynı olduğundan emin olun (www kaldırma ve HTTP→HTTPS kuralları için).
4.  `.env` dosyasında `APP_URL=https://boyaetkinlik.com` değerini doğrulayın ve `php artisan config:clear` komutuyla önbelleği temizleyin.
5.  Tarayıcıda `http://boyaetkinlik.com` ve `https://www.boyaetkinlik.com` adreslerinin `https://boyaetkinlik.com` adresine **301** ile yönlendiğini kontrol edin.

### Cloudflare (DNS Cloudflare Üzerindense — Kanonik: `https://boyaetkinlik.com`)
1.  [Cloudflare Dashboard](https://dash.cloudflare.com) üzerinden sitenizi seçin.
2.  **SSL/TLS** menüsünden genel modu **Full (strict)** olarak ayarlayın (origin sunucunuzda geçerli bir SSL sertifikası varsa).
3.  **Edge Certificates** altında **Always Use HTTPS** seçeneğini **Açık** konuma getirin.
4.  **Rules** → **Redirect Rules** → **Create rule** ile `www` adresini ana domaine yönlendirmek için bir kural oluşturun:
    -   **Kural adı:** `www to apex`
    -   **Koşul:** Alan *Hostname* → *equals* → `www.boyaetkinlik.com`
    -   **Sonra:** *Dynamic redirect* → **301** → hedef ifade:
        `concat("https://boyaetkinlik.com", http.request.uri.path, if(len(http.request.uri.query) > 0, concat("?", http.request.uri.query), ""))`
5.  Değişiklikleri kaydedin ve isterseniz Cloudflare önbelleğini temizleyin.
6.  Sunucudaki `.env` dosyasında `APP_URL=https://boyaetkinlik.com` değerini doğrulayın ve `php artisan config:clear` komutuyla önbelleği temizleyin.
7.  Tarayıcıda yönlendirmelerin doğru çalıştığını kontrol edin.

## Teknolojiler

Bu proje, aşağıdaki ana teknolojiler ve kütüphaneler kullanılarak geliştirilmiştir:

-   **Backend:**
    -   [PHP](https://www.php.net/) `^8.2`
    -   [Laravel Framework](https://laravel.com/) `^11.31`
    -   [PHPMailer](https://github.com/PHPMailer/PHPMailer) `^7.0` (E-posta gönderimi için)
    -   [FPDF](http://www.fpdf.org/) `^1.8` (PDF oluşturma için)

-   **Frontend:**
    -   [Tailwind CSS](https://tailwindcss.com/) (CSS çatısı)
    -   [Alpine.js](https://alpinejs.dev/) (Hafif JavaScript çatısı)
    -   [Vite](https://vitejs.dev/) (Ön yüz geliştirme aracı)

-   **Veritabanı:**
    -   MySQL / PostgreSQL (veya uyumlu bir veritabanı)

## Shopier Entegrasyonu

Shopier entegrasyonu aşağıdaki gibi çalışır:

-   **Shopier Callback Rotası:** `POST /shopier/callback`
-   Bu rota, Shopier'dan ödeme bildirimi aldığında tetiklenir.
-   Ödeme başarılı olursa, ilgili işlem `paid` olarak işaretlenir.
-   Kullanıcı için tek kullanımlık bir indirme tokeni üretilir.
-   Üretilen indirme linki, kullanıcıya e-posta ile gönderilir.
