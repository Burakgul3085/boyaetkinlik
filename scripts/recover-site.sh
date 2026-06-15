#!/usr/bin/env bash
# Site 500 sonrası hızlı kurtarma
set -euo pipefail

cd /var/www/boyaetkinlik

echo "==> İzinler düzeltiliyor..."
sudo mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
sudo chown -R "$USER:www-data" storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "==> Önbellek temizleniyor..."
php artisan optimize:clear 2>/dev/null || true
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/services.php 2>/dev/null || true

echo "==> Composer..."
composer install --no-dev --optimize-autoloader

echo "==> Migration..."
php artisan migrate --force

echo "==> Google ayarları..."
php artisan google:sync-settings 2>/dev/null || true

echo "==> Build..."
npm run build

php artisan view:clear
php artisan config:clear

echo "==> Son log satırları:"
tail -n 15 storage/logs/laravel.log 2>/dev/null || echo "(log yok)"

echo "Kurtarma tamamlandı."
