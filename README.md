## Giriş

Bu proje, kullanıcıların boyama sayfalarını indirebileceği, üyelik sistemiyle kişisel hesaplar oluşturabileceği ve yönetici paneli üzerinden tüm içerik ve kullanıcıları yönetebileceği kapsamlı bir web uygulamasıdır. PHP 8.2 ve Laravel 11.x kullanılarak geliştirilmiştir. Üyeler için özel satın alma ve indirme seçenekleri sunar, ayrıca blog ve iletişim gibi standart web sitesi özelliklerini de barındırır.

## Özellikler

- **Boyama Sayfaları Yönetimi:** Yöneticilerin yeni boyama sayfaları ekleyebilmesi, mevcutları düzenleyebilmesi ve kategorize edebilmesi.
- **Üyelik Sistemi:** Kullanıcıların kayıt olabileceği, giriş yapabileceği, kişisel hesaplarını yönetebileceği, geçmiş satın alımlarını görebileceği ve indirmelerine erişebileceği bir sistem.
- **Yönetici Paneli:** Yönetim ekibinin kullanıcıları, boyama sayfalarını, kategorileri, işlemleri, blog yazılarını ve site ayarlarını tam kontrolle yönetebilmesi.
- **Ödeme ve İndirme Süreçleri:** Kullanıcıların boyama sayfalarını satın alabileceği ve ardından farklı formatlarda (örn. PDF) indirebileceği güvenli bir akış (Shopier entegrasyonu).
- **Blog Sistemi:** Yönetim ekibinin blog yazıları yayınlayabileceği, düzenleyebileceği ve yönetebileceği, kullanıcıların ise bu yazıları okuyabileceği bir bölüm.
- **İletişim ve Geri Bildirim:** Ziyaretçilerin siteyle iletişime geçebileceği ve geri bildirim bırakabileceği formlar.
- **Newsletter Aboneliği:** Ziyaretçilerin e-posta bültenine abone olabileceği sistem.
- **SEO Dostu Yapı:** Sitemap ve robot.txt gibi temel SEO ayarları.
- **Admin Aktivite Günlükleri:** Yönetici panelindeki önemli aktivitelerin takip edilmesi.
- **Çoklu Dil Desteği:** (Bilgi doğrulanmalı)

## Teknolojiler

- **Backend:** PHP 8.2, Laravel 11.x
- **Veritabanı:** İlişkisel veritabanı (MySQL veya PostgreSQL önerilir)
- **Frontend:** HTML, CSS (Tailwind CSS), JavaScript (Alpine.js)
- **Paket Yöneticisi:** Composer (PHP), npm (Node.js)
- **Derleyici/Paketleyici:** Vite
- **E-posta Gönderimi:** PHPMailer
- **PDF Oluşturma:** Setasign FPDF

## Kurulum

Projeyi yerel makinenizde veya sunucunuzda çalıştırmak için aşağıdaki gereksinimleri karşılamanız gerekmektedir:

- PHP >= 8.2
- Composer
- Node.js >= 20.x
- npm
- MySQL veya PostgreSQL gibi bir veritabanı sunucusu
- Nginx veya Apache gibi bir web sunucusu (üretim ortamı için)
- Git

### Yerel Kurulum Adımları

1.  **Projeyi Klonlayın:**
    ```bash
    git clone https://github.com/Burakgul3085/boyaetkinlik.git
    cd boyaetkinlik
    ```

2.  **Bağımlılıkları Yükleyin:**
    ```bash
    composer install
    npm install
    npm run build
    ```

3.  **.env Dosyasını Yapılandırın:**
    `.env.example` dosyasını `cp .env.example .env` komutu ile `.env` olarak kopyalayın ve veritabanı bağlantı bilgilerini, `APP_URL` ve `APP_KEY` gibi temel ayarları güncelleyin.

    ```dotenv
    APP_NAME="Boya Etkinlik"
    APP_ENV=local
    APP_KEY=base64:...
    APP_DEBUG=true
    APP_URL=http://localhost:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=boyaetkinlik
    DB_USERNAME=root
    DB_PASSWORD=
    ```

4.  **Uygulama Anahtarını Oluşturun:**
    ```bash
    php artisan key:generate
    ```

5.  **Veritabanını Oluşturun ve Migrate Edin:**
    Veritabanı sunucunuzda `boyaetkinlik` adında bir veritabanı oluşturun ve ardından migrasyonları çalıştırın:
    ```bash
    php artisan migrate
    ```

6.  **Admin Kullanıcısı ve Temel Ayarları Oluşturun (Opsiyonel):**
    Geliştirme ortamınızda ilk yönetici kullanıcısını ve temel site ayarlarını oluşturmak için seed komutunu çalıştırabilirsiniz:
    ```bash
    php artisan db:seed
    ```
    > **Not:** Varsayılan yönetici giriş bilgileri:
    > E-posta: `admin@boyaetkinlik.test` | Şifre: `12345678`
    > İlk girişten sonra şifrenizi değiştirmeniz şiddetle tavsiye edilir.

7.  **Storage Link Oluşturun:**
    ```bash
    php artisan storage:link
    ```

8.  **Uygulamayı Çalıştırın:**
    Laravel geliştirme sunucusunu başlatın:
    ```bash
    php artisan serve
    ```
    Uygulamanız genellikle `http://localhost:8000` adresinde erişilebilir olacaktır.

### Dağıtım Notları

Üretim ortamı kurulumu ve özel yapılandırmalar (paylaşımlı hosting, Cloudflare, SSL vb.) için [Dağıtım](#dağıtım) bölümüne bakınız.

## Kullanım

Bu uygulama, iki ana kullanıcı grubuna hizmet vermektedir: Yönetici (Admin) ve Üye/Ziyaretçi.

### Yönetici Paneli Kullanımı

Yönetici paneli, uygulamanın tüm içeriğini ve kullanıcılarını yönetmek için kullanılır. Varsayılan yönetici paneli yolu `y981`'dir. 

**Giriş Adresi:** `[UYGULAMA_URL]/y981/giris`

**Varsayılan Yönetici Bilgileri (db:seed sonrası):**
- **E-posta:** `admin@boyaetkinlik.test`
- **Şifre:** `12345678`

İlk girişten sonra şifrenizi değiştirmeniz güvenlik açısından önemlidir.

Yönetici panelinden yapabilecekleriniz:
- Boyama sayfalarını ve kategorilerini ekleme, düzenleme, silme.
- Üyeleri ve işlemlerini yönetme.
- Blog yazılarını oluşturma ve yönetme.
- Site ayarlarını yapılandırma (SMTP, iletişim e-postası vb.).
- Yönetici aktivite günlüklerini inceleme.

### Üye ve Ziyaretçi Kullanımı

Üyeler ve ziyaretçiler, uygulamanın genel web arayüzünü kullanır.

**Ziyaretçiler:**
- Boyama sayfalarına göz atabilir, bilgi alabilir.
- Blog yazılarını okuyabilir.
- İletişim formunu kullanarak siteyle etkileşime geçebilir.
- E-posta bültenine abone olabilir.

**Üyeler:**
- Kayıt olabilir ve kişisel bir hesap oluşturabilir.
- Hesap ayarlarını yönetebilir.
- Boyama sayfalarını satın alabilir ve satın aldığı sayfalara erişebilir.
- Geçmiş satın alımlarını görüntüleyebilir ve indirme seçeneklerini kullanabilir.
- Satın alma desteği için ticket oluşturabilir.

**Shopier Entegrasyonu:**
Ödeme işlemleri Shopier üzerinden gerçekleştirilir. Shopier ödeme geri bildirimi (`POST /shopier/callback`) alındığında:
- Ödeme başarılı olursa işlem `paid` olarak güncellenir.
- Satın alınan boyama sayfası için tek kullanımlık bir indirme tokenı üretilir.
- Kullanıcıya indirme linkini içeren bir e-posta gönderilir.

Uygulama arayüzü sezgisel olup, kolayca gezinilebilir bir yapıya sahiptir.

## Dağıtım

Projenizi üretim ortamına dağıtırken dikkat etmeniz gereken bazı özel notlar ve yapılandırma adımları aşağıda belirtilmiştir.

### Genel Dağıtım Notları

- **Çevre Değişkenleri:** `.env` dosyasında `APP_ENV=production` ve `APP_DEBUG=false` olarak ayarlandığından emin olun.
- **Uygulama URL'si:** `APP_URL` değerini canlı site adresinizle (`https://boyaetkinlik.com` gibi, sonunda `/` olmadan) güncelleyin. `http://` veya `www.` kullanmaktan kaçının; yönlendirmeler genellikle web sunucusu yapılandırması veya Cloudflare gibi hizmetler tarafından yapılır.
- **Kuyruk ve Önbellek:** `QUEUE_CONNECTION` ve `CACHE_DRIVER` için `database` veya `file` gibi üretim ortamına uygun sürücüler kullanın.
- **Dosya Depolama:** Ücretli dosyalar `storage/app/private` altında saklanır ve doğrudan herkese açık erişimi yoktur. Ücretsiz dosyalar `storage/app/public/free-pages` altında bulunur.

### `storage:link` Çalışmazsa Alternatif

Bazı paylaşımlı hosting ortamlarında `php artisan storage:link` komutuyla sembolik link oluşturulamayabilir. Bu durumda:

1.  `storage/app/public` dizini içeriklerini manuel olarak `public/storage` altına kopyalayın.
2.  Uygulamanızın dosya yükleme stratejisini bu manuel kopyalamaya uygun şekilde sabit tuttuğunuzdan emin olun (örneğin, dağıtım betiğiniz bu kopyalama işlemini otomatikleştirebilir).

### Hostinger (hPanel) — SSL ve Tek Adres Yapılandırması

Hostinger'da SSL sertifikası ve alan adı yönlendirmelerini yapılandırmak için:

1.  Hostinger hPanel'de **Web siteleri** bölümünden sitenizi seçin.
2.  **Güvenlik** veya **SSL** menüsü altında SSL sertifikasını etkinleştirin ve mümkünse **“HTTPS’e yönlendir”** / **Force HTTPS** seçeneğini aktif hale getirin.
3.  **Dosya yöneticisi** aracılığıyla sunucudaki `public/.htaccess` dosyasının projenizdeki güncel sürümle aynı olduğundan emin olun (bu dosya genellikle `www` kaldırma ve HTTP'den HTTPS'ye yönlendirme kurallarını içerir).
4.  Proje kökündeki `.env` dosyasında `APP_URL=https://boyaetkinlik.com` gibi doğru `APP_URL` ayarının yapıldığını kontrol edin. Ardından, SSH veya Hostinger **Gelişmiş** → **Terminal** kullanarak `php artisan config:clear` komutu ile önbelleği temizleyin.
5.  Tarayıcınızda `http://alanadiniz.com` ve `https://www.alanadiniz.com` adreslerini test ederek tüm isteklerin `https://alanadiniz.com` adresine **301 (kalıcı)** yönlendirme ile gitmesini sağlayın.

**HSTS (HTTP Strict Transport Security):** Tüm alt yollarınızın ve kaynaklarınızın HTTPS üzerinden sorunsuz çalıştığından kesinlikle eminseniz hPanel'de HSTS'yi etkinleştirebilirsiniz. Aksi takdirde, olası erişim sorunlarını önlemek için bu adımı atlayın.

### Cloudflare Entegrasyonu (Ad Sunucuları Cloudflare ise)

Eğer alan adınızın ad sunucuları Cloudflare'a yönlendirilmişse, DNS ve yönlendirme ayarlarını Cloudflare panelinden yapmanız gerekecektir.

1.  [Cloudflare Dashboard](https://dash.cloudflare.com) adresinden ilgili alan adınızı seçin.
2.  **SSL/TLS** bölümünde, genel SSL modunu **Full (strict)** olarak ayarlayın (origin sunucunuzda geçerli bir SSL sertifikası olması durumunda). Eğer sorun yaşarsanız, önce **Full** modunu deneyip daha sonra **Full (strict)** moduna geçebilirsiniz.
3.  Aynı menüdeki **Edge Certificates** altında **Always Use HTTPS** seçeneğini **Açık** konumuna getirin (bu, tüm HTTP isteklerini otomatik olarak HTTPS'e yönlendirir).
4.  **Rules** → **Redirect Rules** → **Create rule** adımlarını izleyerek `www` önekini kaldıran bir yönlendirme kuralı oluşturun:
    -   **Kural Adı:** `www to apex` (veya istediğiniz başka bir isim)
    -   **Kural Koşulu:** Alan *Hostname* → *equals* → `www.boyaetkinlik.com`
    -   **Eylem:** *Dynamic redirect* → **301 (kalıcı)** → Hedef ifade alanına şu kodu yapıştırın:
        ```
        concat("https://boyaetkinlik.com", http.request.uri.path, if(len(http.request.uri.query) > 0, concat("?", http.request.uri.query), ""))
        ```
        Bu kural, `www` ile başlayan tüm istekleri `https://boyaetkinlik.com` adresine yönlendirirken orijinal yol ve sorgu dizgesini korur.
5.  Kuralı kaydedin. Gerekirse **Caching** → **Configuration** → **Purge Everything** seçeneğini kullanarak Cloudflare önbelleğini temizleyebilirsiniz.
6.  Tarayıcınızda gizli sekme kullanarak `https://www.boyaetkinlik.com/` gibi adresleri test edin. Adres çubuğunun `https://boyaetkinlik.com/` olarak değiştiğini (301 yönlendirmesi ile) ve sitenin düzgün çalıştığını doğrulayın.
7.  Sunucunuzdaki `.env` dosyasında `APP_URL=https://boyaetkinlik.com` ayarının doğru olduğunu ve güncel `public/.htaccess` dosyasının dağıtıldığını kontrol edin. Son olarak `php artisan config:clear` komutunu çalıştırın.

**Döngüsel Yönlendirme (Too Many Redirects) Sorunları:** Eğer döngüsel yönlendirme hatası alırsanız, Cloudflare SSL modunu geçici olarak **Full (strict)** yerine **Full** yapmayı veya **Always Use HTTPS** seçeneğini kapatıp sorunun kaynağını (sunucu veya Cloudflare) ayırmayı deneyin.
