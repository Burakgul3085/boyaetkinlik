#!/usr/bin/env bash
# Site 500 sonrası kurtarma — adım adım, hata olsa da devam eder
set -u

cd /var/www/boyaetkinlik

DEPLOY_USER="${SUDO_USER:-${USER:-burakadmin}}"

run() {
  echo ""
  echo "==> $*"
  if ! "$@"; then
    echo "!! UYARI: Komut başarısız (devam ediliyor): $*"
  fi
}

echo "==> İzinler düzeltiliyor (${DEPLOY_USER} + www-data)..."
sudo mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache public/build
sudo chown -R "${DEPLOY_USER}:www-data" storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo rm -f storage/logs/laravel.log 2>/dev/null || true

echo "==> Eski önbellek dosyaları siliniyor..."
sudo rm -f bootstrap/cache/*.php 2>/dev/null || true
sudo rm -rf storage/framework/views/* 2>/dev/null || true
rm -f bootstrap/cache/*.php 2>/dev/null || true
run php artisan optimize:clear
run php artisan view:clear

echo "==> Composer..."
run composer install --no-dev --optimize-autoloader

echo "==> Migration..."
run php artisan migrate --force

echo "==> Frontend build..."
run npm run build

run php artisan config:clear
run php artisan route:clear

echo ""
echo "==> Tanı:"
run php artisan site:diagnose

echo ""
echo "Kurtarma tamamlandı. Tarayıcıda Ctrl+F5 ile siteyi yenileyin."
