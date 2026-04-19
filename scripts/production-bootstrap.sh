#!/usr/bin/env bash
# Canlı sunucuda (SSH, root veya www-data yazma izni olan kullanıcı) bir kez çalıştırın.
# Örnek: APP_URL=http://187.77.69.109 bash production-bootstrap.sh
set -euo pipefail
APP_DIR="${APP_DIR:-/var/www/boyaetkinlik}"
cd "$APP_DIR"

APP_URL="${APP_URL:-http://187.77.69.109}"
echo ">>> APP_URL=$APP_URL"
sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env

php artisan config:clear
echo ">>> db:seed (admin + temel ayarlar)"
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo ""
echo "=== Tamam ==="
echo "Admin giriş: ${APP_URL}/y981/giris"
echo "E-posta: admin@boyaetkinlik.test  |  Şifre: 12345678"
echo "İlk girişten sonra Hesabım veya panelden şifreyi değiştirin."
echo "Ayarlar → İletişim e-posta + SMTP (Gmail uygulama şifresi) doldurun."
