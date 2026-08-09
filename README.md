## Proje Adı ve Açıklama

Bu proje, kullanıcıların boyama sayfalarına göz atabileceği, indirebileceği (ücretsiz olanları) veya satın alabileceği (ücretli olanları) bir platform sunar. Yönetim paneli üzerinden boyama sayfaları, kategoriler, üyeler ve işlemler gibi birçok farklı öğe yönetilebilir. Amaç, dijital boyama içeriği arayan kullanıcılara zengin bir deneyim sağlamaktır.

## Özellikler

- **Boyama Sayfaları Yönetimi**: Yönetim paneli üzerinden kolayca boyama sayfaları ekleyebilir, düzenleyebilir ve silebilirsiniz.
- **Kategori Sistemi**: Boyama sayfalarını kategorilere ayırarak düzenli bir yapı oluşturabilirsiniz.
- **Ücretsiz ve Ücretli İçerikler**: Kullanıcılara hem ücretsiz indirme seçenekleri sunabilir hem de ücretli içeriklerle gelir elde edebilirsiniz.
- **Shopier Entegrasyonu**: Ücretli boyama sayfaları için Shopier ödeme sistemi entegrasyonu mevcuttur.
- **E-posta İle Gönderme**: Kullanıcılar ücretsiz boyama sayfalarını doğrudan e-posta adreslerine gönderebilirler.
- **Kullanıcı ve Üye Yönetimi**: Yönetim panelinden üyeleri ve yetkilerini yönetebilirsiniz.
- **İşlem Takibi**: Yapılan satın alma işlemlerini ve finansal hareketleri takip edebilirsiniz.
- **Blog Sistemi**: İçerik pazarlaması için entegre bir blog sistemi bulunur.
- **Ziyaretçi Geri Bildirimleri**: Kullanıcılardan gelen geri bildirimleri yönetebilirsiniz.
- **E-posta Abonelikleri**: Bülten aboneliklerini yöneterek kullanıcılarla iletişiminizi sürdürebilirsiniz.
- **Genel Ayarlar**: Siteye ait çeşitli ayarları (SMTP, uygulama adları vb.) yönetim panelinden yapılandırabilirsiniz.
- **Reklam Yönetimi**: Reklam alanlarını kontrol edebilirsiniz.

## Teknolojiler

Proje aşağıdaki temel teknolojileri kullanmaktadır:

-   **Backend**: PHP (>=8.2), Laravel (>=11.31)
-   **Veritabanı**: MySQL, PostgreSQL veya SQLite (Laravel'in desteklediği herhangi biri)
-   **Frontend**: Tailwind CSS, Alpine.js, Vite
-   **E-posta Gönderimi**: PHPMailer
-   **PDF İşleme**: setasign/fpdf

## Kurulum

Bu projeyi yerel ortamınızda çalıştırmak için aşağıdaki adımları izleyin:

### Ön Koşullar

Başlamadan önce sisteminizde aşağıdaki yazılımların kurulu olduğundan emin olun:

*   **PHP** (8.2 veya üzeri)
*   **Composer**
*   **Node.js** (20.x veya üzeri)
*   **npm** veya **yarn**
*   **Veritabanı Sunucusu** (MySQL, PostgreSQL veya SQLite)
*   **Web Sunucusu** (Nginx veya Apache)

### Adımlar

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

    `.env.example` dosyasını `cp .env.example .env` komutu ile kopyalayın ve veritabanı bağlantı bilgilerini, `APP_URL` ve isteğe bağlı olarak `ADMIN_PATH` gibi ayarları düzenleyin.

    ```dotenv
    APP_NAME="Boya Etkinlik"
    APP_ENV=local
    APP_KEY=
    APP_DEBUG=true
    APP_URL=http://localhost:8000

    LOG_CHANNEL=stack
    LOG_DEPRECATIONS_CHANNEL=null
    LOG_LEVEL=debug

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=boyaetkinlik
    DB_USERNAME=root
    DB_PASSWORD=

    BROADCAST_DRIVER=log
    CACHE_DRIVER=file
    FILESYSTEM_DISK=local
    QUEUE_CONNECTION=sync
    SESSION_DRIVER=file
    SESSION_LIFETIME=120

    MEMCACHED_HOST=127.0.0.1

    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379

    MAIL_MAILER=smtp
    MAIL_HOST=mailpit
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS="hello@example.com"
    MAIL_FROM_NAME="${APP_NAME}"

    AWS_ACCESS_KEY_ID=
    AWS_SECRET_ACCESS_KEY=
    AWS_DEFAULT_REGION=us-east-1
    AWS_BUCKET=
    AWS_USE_PATH_STYLE_ENDPOINT=false

    PUSHER_APP_ID=
    PUSHER_APP_KEY=
    PUSHER_APP_SECRET=
    PUSHER_HOST=
    PUSHER_PORT=443
    PUSHER_SCHEME=https
    PUSHER_APP_CLUSTER=mt1

    VITE_APP_NAME="Boya Etkinlik"

    # Yönetim paneli giriş yolu. Güvenlik için değiştirin.
    ADMIN_PATH=y981

    # Ücretsiz sayfa indirildikten sonra e-posta gönderimi için PHPmailer SMTP ayarları
    # Bunlar production ortamında yönetim panelinden de yapılandırılabilir.
    # SMTP_HOST=
    # SMTP_PORT=587
    # SMTP_USERNAME=
    # SMTP_PASSWORD=
    # SMTP_ENCRYPTION=tls
    # SMTP_FROM_EMAIL=
    # SMTP_FROM_NAME="Boya Etkinlik"
    ```

4.  **Uygulama Anahtarını Oluşturun:**

    ```bash
    php artisan key:generate
    ```

5.  **Veritabanını Oluşturun ve Migrasyonları Çalıştırın:**

    `.env` dosyasında yapılandırdığınız veritabanını oluşturun ve ardından migrasyonları çalıştırın:

    ```bash
    php artisan migrate
    ```

6.  **Depolama Bağlantısını Oluşturun:**

    ```bash
    php artisan storage:link
    ```

7.  **NPM Bağımlılıklarını Yükleyin ve Frontend Assetlerini Derleyin:**

    ```bash
    npm install
    npm run build
    ```

8.  **Uygulamayı Çalıştırın:**

    Geliştirme sunucusunu başlatın:

    ```bash
    php artisan serve
    ```

    Tarayıcınızda `http://localhost:8000` adresine giderek uygulamayı görüntüleyebilirsiniz. Yönetim paneli için `.env` dosyasında belirttiğiniz `ADMIN_PATH` (varsayılan `y981`) ile `http://localhost:8000/y981/giris` adresini kullanın.

### Cron Job Ayarı (Laravel Scheduler)

Bazı Laravel görevlerinin (örneğin e-posta gönderimi) düzenli olarak çalışması için bir cron job ayarlamanız gerekebilir. Aşağıdaki komutu `crontab -e` ile ekleyin:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

`/path/to/your/project` kısmını projenizin gerçek yolu ile değiştirmeyi unutmayın.

## Kullanım

### Kullanıcılar İçin

Kullanıcılar, ana sayfadaki kategoriler veya arama çubuğu aracılığıyla boyama sayfalarına göz atabilirler. Ücretsiz sayfaları doğrudan indirebilir veya e-posta adreslerine gönderebilirler. Ücretli sayfalar için Shopier aracılığıyla güvenli bir şekilde satın alma işlemi gerçekleştirebilirler.

### Yöneticiler İçin

Yönetim paneline, `.env` dosyasında tanımlanan `ADMIN_PATH` (varsayılan: `y981`) üzerinden erişilebilir. Örnek: `http://localhost:8000/y981/giris`.

Yönetim panelinde şunları yapabilirsiniz:

*   **Boyama Sayfaları**: Yeni boyama sayfaları ekleyebilir, mevcutları düzenleyebilir veya silebilirsiniz.
*   **Kategoriler**: Boyama sayfalarını organize etmek için kategoriler oluşturabilir ve yönetebilirsiniz.
*   **Üyeler**: Kullanıcı hesaplarını görüntüleyebilir, düzenleyebilir veya silebilirsiniz.
*   **İşlemler**: Yapılan tüm satın alma işlemlerini ve bunların detaylarını takip edebilirsiniz.
*   **Ayarlar**: SMTP ayarları, site adı gibi genel site ayarlarını yapılandırabilirsiniz.
*   **Blog**: Yeni blog gönderileri oluşturabilir, yayınlayabilir ve yönetebilirsiniz.
*   **Geri Bildirimler**: Ziyaretçilerden gelen geri bildirimleri inceleyebilirsiniz.
*   **Bülten Aboneleri**: E-posta bülteni abonelerini yönetebilirsiniz.
*   **Satın Alma Doğrulamaları**: Shopier üzerinden yapılan satın alma doğrulamalarını manuel olarak yönetebilirsiniz.

## Katkıda Bulunma

Projeye katkıda bulunmak isterseniz, lütfen aşağıdaki adımları izleyin:

1.  Depoyu forklayın.
2.  Yeni bir özellik veya hata düzeltmesi için dal (branch) oluşturun (`git checkout -b feature/AmazingFeature`).
3.  Değişikliklerinizi yapın ve commit edin (`git commit -m 'Add some AmazingFeature'`).
4.  Dalınızı uzak depoya itin (`git push origin feature/AmazingFeature`).
5.  Bir Pull Request (Çekme İsteği) açın.

## Lisans

Bu proje MIT Lisansı altında lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına bakınız.
