## Özet

Bu depo, kullanıcıların boyama sayfalarını ve ilgili etkinlikleri keşfedebileceği, satın alabileceği ve indirebileceği tam teşekküllü bir web uygulamasıdır. PHP tabanlı Laravel Framework 11.x kullanılarak geliştirilmiştir ve modern web teknolojileriyle zenginleştirilmiştir. Uygulama, hem yönetici hem de üye paneli işlevselliği sunar, kullanıcı etkileşimini, içerik yönetimini ve ödeme süreçlerini kolaylaştırır. Özellikle çocuklara ve yaratıcı etkinlik arayanlara hitap eden dijital boyama içerikleri sunmayı amaçlar.

## Özellikler

*   **Ücretsiz İçerik İndirme ve E-posta ile Gönderme:** Kullanıcılar ücretsiz boyama sayfalarını doğrudan indirebilir veya e-posta adreslerine gönderilmesini talep edebilirler. Çeşitli dosya formatı seçenekleri mevcuttur.
*   **Ücretli İçerik Satın Alma:** Ücretli boyama sayfaları için Shopier üzerinden güvenli ödeme ve satın alma imkanı sunulur.
*   **Çoklu Dosya Formatı Desteği:** Boyama sayfaları için farklı dosya formatlarında indirme ve yazdırma seçenekleri (örn. PDF, PNG) mevcuttur.
*   **Görsel Önizleme:** Ürün sayfalarında boyama sayfalarının önizleme görselleri sunulur.
*   **Yönetici Paneli:** İçerik yönetimi (boyama sayfaları, kategoriler), üye yönetimi, blog gönderileri, işlem takibi, bülten aboneleri ve site ayarlarının yapılandırılabileceği kapsamlı bir yönetici paneli.
*   **Üye Sistemi:** Kullanıcıların hesap oluşturup yönetebildiği, satın alımlarını takip edebildiği ve geçmiş siparişlerine erişebildiği bir üye sistemi.
*   **İletişim ve Geri Bildirim:** Ziyaretçilerin site yöneticileriyle iletişime geçebileceği veya geri bildirim bırakabileceği iletişim formları.
*   **SEO Dostu Yapı:** Arama motorları için optimize edilmiş URL'ler ve içerik yapısı.
*   **Güvenlik:** Kullanıcı ve yönetici oturumları için güvenli kimlik doğrulama ve yetkilendirme mekanizmaları.

## Teknolojiler

Bu proje, aşağıdaki temel teknolojiler kullanılarak geliştirilmiştir:

*   **Backend:** PHP 8.2+, Laravel Framework 11.x
*   **Veritabanı:** MySQL (veya desteklenen herhangi bir Laravel uyumlu veritabanı)
*   **Frontend:** HTML, CSS (Tailwind CSS), JavaScript (Alpine.js), Vite
*   **E-posta Gönderimi:** PHPMailer
*   **PDF İşleme:** Setasign/FPDF
*   **Versiyon Kontrol Sistemi:** Git

## Gereksinimler

Projeyi yerel ortamınızda çalıştırmak için aşağıdaki gereksinimlere sahip olmanız gerekir:

*   PHP 8.2 veya üzeri
*   Composer
*   Node.js ve npm (veya Yarn)
*   MySQL, PostgreSQL veya SQLite gibi bir veritabanı sunucusu
*   Web sunucusu (Apache, Nginx veya Laravel Sail)

## Kurulum

Projeyi yerel ortamınızda kurmak için aşağıdaki adımları izleyin:

1.  **Depoyu Klonlayın:**

    ```bash
    git clone https://github.com/Burakgul3085/boyaetkinlik.git
    cd boyaetkinlik
    ```

2.  **Composer Bağımlılıklarını Yükleyin:**

    ```bash
    composer install
    ```

3.  **Ortam Dosyasını Yapılandırın:**

    `.env.example` dosyasını `.env` olarak kopyalayın ve kendi veritabanı ayarlarınızı ve diğer gerekli ortam değişkenlerinizi yapılandırın.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Veritabanını Oluşturun ve Migrasyonları Çalıştırın:**

    `.env` dosyasında veritabanı bağlantı bilgilerini ayarladıktan sonra veritabanı migrasyonlarını çalıştırın:

    ```bash
    php artisan migrate
    ```

5.  **Frontend Bağımlılıklarını Yükleyin ve Derleyin:**

    ```bash
    npm install
    npm run dev   # Geliştirme ortamı için
    # veya
    npm run build # Üretim ortamı için
    ```

6.  **Uygulamayı Çalıştırın:**

    ```bash
    php artisan serve
    ```

    Uygulama genellikle `http://127.0.0.1:8000` adresinde erişilebilir olacaktır.
