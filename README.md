## Özet

Bu depo, kullanıcıların boyama sayfalarını indirebileceği, üyelik sistemi, yönetici paneli ve ödeme entegrasyonu (Shopier) içeren bir web uygulamasıdır. Uygulama, blog yazıları, kategori bazlı içerik yönetimi ve ziyaretçi geri bildirimleri gibi özellikler sunmaktadır.

## Teknolojiler

Bu proje aşağıdaki ana teknolojileri kullanmaktadır:

*   **Backend:**
    *   PHP (8.2 ve üzeri)
    *   Laravel (11.x)
    *   PHPMailer (E-posta gönderimi için)
    *   FPDF (PDF oluşturmak için)
*   **Frontend:**
    *   Vite
    *   Tailwind CSS
    *   Alpine.js
*   **Veritabanı:** MySQL (veya desteklenen diğer Laravel veritabanları)
*   **Ödeme Sistemi:** Shopier entegrasyonu

## Gereksinimler

Projeyi yerel olarak çalıştırmak için aşağıdaki gereksinimlere ihtiyacınız olacaktır:

*   PHP 8.2 veya üstü
*   Composer
*   Node.js ve npm (veya Yarn)
*   MySQL veya uyumlu bir veritabanı sunucusu
*   Web sunucusu (Apache, Nginx vb.)

## Kurulum

Projeyi yerel makinenizde kurmak ve çalıştırmak için aşağıdaki adımları izleyin:

1.  **Depoyu Klonlayın:**
    ```bash
    git clone https://github.com/Burakgul3085/boyaetkinlik.git
    cd boyaetkinlik
    ```

2.  **Composer Bağımlılıklarını Kurun:**
    ```bash
    composer install
    ```

3.  **.env Dosyasını Oluşturun:**
    `.env.example` dosyasını kopyalayarak `.env` adında yeni bir dosya oluşturun:
    ```bash
    cp .env.example .env
    ```

4.  **Uygulama Anahtarını Oluşturun:**
    ```bash
    php artisan key:generate
    ```

5.  **.env Dosyasını Yapılandırın:**
    `.env` dosyasını açın ve veritabanı bağlantı bilgilerinizi, Shopier API anahtarlarınızı (eğer kullanılıyorsa) ve diğer çevresel değişkenleri güncelleyin.
    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password

    # Shopier Entegrasyonu (Eğer Kullanılıyorsa)
    SHOPIER_API_KEY=YOUR_SHOPIER_API_KEY
    SHOPIER_SECRET_KEY=YOUR_SHOPIER_SECRET_KEY
    ```

6.  **Veritabanı Geçişlerini Çalıştırın:**
    ```bash
    php artisan migrate
    ```

7.  **Depolama Bağlantısını Oluşturun (Opsiyonel):**
    Eğer uygulamanız genel olarak erişilebilen depolama dizinine ihtiyacı varsa:
    ```bash
    php artisan storage:link
    ```

8.  **NPM Bağımlılıklarını Kurun ve Frontend Varlıklarını Derleyin:**
    ```bash
    npm install
    npm run dev
    # veya üretim için
    # npm run build
    ```

9.  **Uygulamayı Çalıştırın:**
    ```bash
    php artisan serve
    ```
    Uygulama genellikle `http://127.0.0.1:8000` adresinde erişilebilir olacaktır.

## Özellikler

-   **Kullanıcı Yönetimi:** Üye kayıt, giriş, şifre sıfırlama ve profil yönetimi.
-   **Yönetici Paneli:** Yönetici kullanıcıları, boyama sayfaları, kategoriler, blog yazıları, aboneler ve işlemler üzerinde tam kontrol.
-   **Boyama Sayfası Yönetimi:** Boyama sayfaları oluşturma, düzenleme, silme ve kategorilere ayırma.
-   **E-ticaret Entegrasyonu:** Shopier üzerinden güvenli ödeme işlemleri.
-   **Blog Sistemi:** Blog yazıları oluşturma, düzenleme ve yönetme.
-   **Haber Bülteni:** Kullanıcıların haber bültenine abone olmasını sağlama ve aboneleri yönetme.
-   **İletişim ve Geri Bildirim:** Ziyaretçilerden gelen geri bildirimleri toplama ve yönetme.
-   **Çoklu Dosya Formatı İndirme:** Boyama sayfaları için farklı dosya formatları sunma (örn. PDF).
-   **Mobil Uyumlu Tasarım:** Tailwind CSS ve Alpine.js ile duyarlı arayüz.
