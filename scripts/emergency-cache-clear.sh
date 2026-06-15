#!/usr/bin/env bash
# 500 hatası: önbellek ve izin temizliği (en sık neden)
set -u

cd /var/www/boyaetkinlik
DEPLOY_USER="${SUDO_USER:-${USER:-burakadmin}}"

echo "==> İzinler..."
sudo mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache
sudo chown -R "${DEPLOY_USER}:www-data" storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
if [[ -f .env ]]; then
  sudo chown "${DEPLOY_USER}:www-data" .env
  sudo chmod 640 .env
  echo ".env izinleri www-data okuyacak şekilde ayarlandı (640)."
fi

echo "==> Önbellek siliniyor..."
sudo rm -f bootstrap/cache/*.php
sudo rm -rf storage/framework/views/*
php artisan optimize:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

echo "==> Bitti. Siteyi Ctrl+F5 ile yenileyin."
echo "Tanı için: php artisan site:diagnose"
echo "veya tarayıcıda: https://boyaetkinlik.com/__sistem-durumu"
