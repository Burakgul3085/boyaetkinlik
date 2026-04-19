#!/usr/bin/env bash
# Hostinger KVM VPS (Ubuntu 24.04) — Laravel kurulumu
# Kullanım (sunucuda, root iken):
#   export SITE_DOMAIN="alanadin.com"
#   export LETSENCRYPT_EMAIL="sizin@email.com"
#   export DB_PASS="guclu_veritabani_sifresi"
#   bash vps-install-ubuntu.sh
#
# Sadece IP ile test (HTTPS Let's Encrypt vermez, HTTP çalışır):
#   export SITE_DOMAIN="187.77.69.109"
#   export SKIP_CERTBOT=1
#   bash vps-install-ubuntu.sh
#
# Betiği sunucuya atma: yerelden
#   scp scripts/vps-install-ubuntu.sh root@SUNUCU_IP:/root/

set -euo pipefail

if [ "${EUID:-0}" -ne 0 ]; then
  echo "Root olarak çalıştırın: sudo bash $0"
  exit 1
fi

SITE_DOMAIN="${SITE_DOMAIN:-}"
LETSENCRYPT_EMAIL="${LETSENCRYPT_EMAIL:-}"
DB_PASS="${DB_PASS:-}"
SKIP_CERTBOT="${SKIP_CERTBOT:-0}"
APP_DIR="${APP_DIR:-/var/www/boyaetkinlik}"
GIT_REPO="${GIT_REPO:-https://github.com/Burakgul3085/boyaetkinlik.git}"
# composer.lock (Symfony 8.x) PHP 8.4 ister; 8.3 ile composer install başarısız olur
PHP_VERSION="${PHP_VERSION:-8.4}"

if [ -z "$SITE_DOMAIN" ]; then
  echo "SITE_DOMAIN boş. Örnek: export SITE_DOMAIN=alanadin.com"
  exit 1
fi

if [ -z "$DB_PASS" ]; then
  echo "DB_PASS boş. Örnek: export DB_PASS='...'"
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive

echo ">>> Sistem güncelleniyor..."
apt-get update -y
apt-get upgrade -y

echo ">>> UFW (Nginx kurulmadan önce 'Nginx Full' profili yok; port kullanıyoruz)..."
apt-get install -y ufw
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo ">>> Temel paketler..."
apt-get install -y nginx git curl unzip software-properties-common ca-certificates

echo ">>> PHP ${PHP_VERSION}..."
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y \
  "php${PHP_VERSION}-fpm" "php${PHP_VERSION}-cli" "php${PHP_VERSION}-mysql" \
  "php${PHP_VERSION}-xml" "php${PHP_VERSION}-mbstring" "php${PHP_VERSION}-curl" \
  "php${PHP_VERSION}-zip" "php${PHP_VERSION}-bcmath" "php${PHP_VERSION}-intl" "php${PHP_VERSION}-gd"

# Composer `php` komutunu kullanır; 8.3+8.4 birlikteyse 8.4 seçilsin
if [ -x "/usr/bin/php${PHP_VERSION}" ]; then
  update-alternatives --install /usr/bin/php php "/usr/bin/php${PHP_VERSION}" 100 2>/dev/null || true
  update-alternatives --set php "/usr/bin/php${PHP_VERSION}" 2>/dev/null || true
fi

echo ">>> MySQL..."
apt-get install -y mysql-server

echo ">>> Composer..."
if [ ! -x /usr/local/bin/composer ]; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo ">>> Node.js 20..."
if ! command -v node >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  apt-get install -y nodejs
fi

echo ">>> Veritabanı oluşturuluyor..."
mysql --protocol=socket -uroot <<SQL
CREATE DATABASE IF NOT EXISTS boyaetkinlik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS 'boya_app'@'localhost';
CREATE USER 'boya_app'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON boyaetkinlik.* TO 'boya_app'@'localhost';
FLUSH PRIVILEGES;
SQL

echo ">>> Uygulama dizini..."
mkdir -p "$(dirname "$APP_DIR")"
if [ ! -d "$APP_DIR/.git" ]; then
  rm -rf "$APP_DIR"
  git clone "$GIT_REPO" "$APP_DIR"
else
  echo "Dizin zaten var, git pull..."
  git -C "$APP_DIR" pull --ff-only || true
fi

cd "$APP_DIR"

echo ">>> Composer & NPM..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction
if [ -f package-lock.json ]; then
  npm ci
else
  npm install
fi
npm run build

if [ ! -f .env ]; then
  cp .env.example .env
fi

APP_URL="https://${SITE_DOMAIN}"
if [ "$SKIP_CERTBOT" = "1" ]; then
  APP_URL="http://${SITE_DOMAIN}"
fi

# Sadece IPv4 ise www. kullanma
if [[ "$SITE_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  NGINX_SERVER_NAMES="$SITE_DOMAIN"
else
  NGINX_SERVER_NAMES="${SITE_DOMAIN} www.${SITE_DOMAIN}"
fi

# .env.example'da DB satırları çoğu # ile yorumlu; sed ^DB_ eşleşmez → mysql + boya_app uygulanmazdu
sed -i '/^DB_CONNECTION=/d' .env
sed -i '/^DB_HOST=/d' .env
sed -i '/^DB_PORT=/d' .env
sed -i '/^DB_DATABASE=/d' .env
sed -i '/^DB_USERNAME=/d' .env
sed -i '/^DB_PASSWORD=/d' .env
sed -i '/^# DB_/d' .env
{
  echo ""
  echo "DB_CONNECTION=mysql"
  echo "DB_HOST=127.0.0.1"
  echo "DB_PORT=3306"
  echo "DB_DATABASE=boyaetkinlik"
  echo "DB_USERNAME=boya_app"
  echo "DB_PASSWORD=${DB_PASS}"
} >> .env

# .env üretim için temel değerler (SMTP vb. panelden girersiniz)
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env

php artisan key:generate --force
php artisan config:clear

php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

PHP_SOCK="/var/run/php/php${PHP_VERSION}-fpm.sock"
NGINX_SITE="/etc/nginx/sites-available/boyaetkinlik"

echo ">>> Nginx site..."
cat >"$NGINX_SITE" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${NGINX_SERVER_NAMES};
    root ${APP_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sf "$NGINX_SITE" /etc/nginx/sites-enabled/boyaetkinlik
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl enable "php${PHP_VERSION}-fpm"
systemctl restart "php${PHP_VERSION}-fpm"

if [ "$SKIP_CERTBOT" = "1" ]; then
  echo ">>> SKIP_CERTBOT=1 — HTTPS atlandı. Alan adı hazır olunca:"
  echo "    apt-get install -y certbot python3-certbot-nginx"
  echo "    certbot --nginx -d ${SITE_DOMAIN} -m ${LETSENCRYPT_EMAIL:-admin@localhost} --agree-tos --non-interactive"
else
  if [ -z "$LETSENCRYPT_EMAIL" ]; then
    echo "LETSENCRYPT_EMAIL boş. HTTPS için e-posta gerekli veya SKIP_CERTBOT=1 kullanın."
    exit 1
  fi
  echo ">>> Certbot..."
  apt-get install -y certbot python3-certbot-nginx
  if [[ "$SITE_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Let's Encrypt genelde ham IP için sertifika vermez; alan adı kullanın veya SKIP_CERTBOT=1 ile HTTP kalın."
  else
    certbot --nginx -d "$SITE_DOMAIN" -d "www.$SITE_DOMAIN" -m "$LETSENCRYPT_EMAIL" --agree-tos --non-interactive --redirect || {
      echo "Certbot başarısız (DNS henüz yönlenmemiş olabilir). Daha sonra:"
      echo "  certbot --nginx -d $SITE_DOMAIN -d www.$SITE_DOMAIN"
    }
  fi
fi

echo ">>> Cron (scheduler)..."
CRON_LINE="* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"
( crontab -l 2>/dev/null | grep -v "artisan schedule:run" ; echo "$CRON_LINE" ) | crontab -

echo ""
echo "=== Bitti ==="
echo "Site: ${APP_URL}"
echo "Admin yolu .env içinde ADMIN_PATH (varsayılan y981) — örn: ${APP_URL}/y981/giris"
echo "Panelden SMTP ve contact_email ayarlarını girin."
echo "Ek: queue worker gerekirse sonra supervisor ile eklenir."
