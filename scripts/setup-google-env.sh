#!/usr/bin/env bash
# Sunucu .env dosyasına Google OAuth satırlarını ekler (secret repoya yazılmaz).
set -euo pipefail

ENV_FILE="${1:-.env}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo ".env bulunamadı: $ENV_FILE" >&2
  exit 1
fi

set_var_if_missing() {
  local key="$1"
  local val="$2"
  if grep -q "^${key}=" "$ENV_FILE"; then
    return 0
  fi
  printf '%s=%s\n' "$key" "$val" >> "$ENV_FILE"
}

if ! grep -q "^# Google OAuth" "$ENV_FILE"; then
  printf '\n# Google OAuth (Üye girişi / kayıt)\n' >> "$ENV_FILE"
fi

set_var_if_missing "GOOGLE_CLIENT_ID" ""
set_var_if_missing "GOOGLE_CLIENT_SECRET" ""
set_var_if_missing "GOOGLE_REDIRECT_URI" "https://boyaetkinlik.com/auth/google/callback"

sudo chown "${SUDO_USER:-${USER:-burakadmin}}:www-data" "$ENV_FILE" 2>/dev/null || true
sudo chmod 640 "$ENV_FILE" 2>/dev/null || chmod 640 "$ENV_FILE"

echo "Google OAuth .env satırları hazır."
echo "GOOGLE_CLIENT_ID ve GOOGLE_CLIENT_SECRET değerlerini .env içine elle girin."
