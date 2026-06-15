#!/usr/bin/env bash
# Sunucu .env dosyasına Google OAuth satırlarını yoksa ekler (mevcut değerleri silmez).
set -euo pipefail

ENV_FILE="${1:-.env}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo ".env bulunamadı: $ENV_FILE" >&2
  exit 1
fi

ensure_var() {
  local key="$1"
  local default="$2"
  if grep -q "^${key}=" "$ENV_FILE"; then
    return 0
  fi
  printf '%s=%s\n' "$key" "$default" >> "$ENV_FILE"
}

if ! grep -q "^# Google OAuth" "$ENV_FILE"; then
  printf '\n# Google OAuth (Üye girişi / kayıt)\n' >> "$ENV_FILE"
fi

ensure_var "GOOGLE_CLIENT_ID" ""
ensure_var "GOOGLE_CLIENT_SECRET" ""
ensure_var "GOOGLE_REDIRECT_URI" "https://boyaetkinlik.com/auth/google/callback"

echo "Google OAuth .env satırları hazır."
