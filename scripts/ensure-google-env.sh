#!/usr/bin/env bash
# Sunucu .env dosyasına Google OAuth satırlarını ekler / boş olanları doldurur.
set -euo pipefail

ENV_FILE="${1:-.env}"
GOOGLE_CLIENT_ID_DEFAULT="405759057323-jheql5elql6ir6v01s37jlss6v5gtedt.apps.googleusercontent.com"
GOOGLE_REDIRECT_DEFAULT="https://boyaetkinlik.com/auth/google/callback"

if [[ ! -f "$ENV_FILE" ]]; then
  echo ".env bulunamadı: $ENV_FILE" >&2
  exit 1
fi

set_var_if_missing_or_empty() {
  local key="$1"
  local val="$2"
  if grep -q "^${key}=" "$ENV_FILE"; then
    local current
    current="$(grep "^${key}=" "$ENV_FILE" | head -n1 | cut -d= -f2-)"
    if [[ -n "$current" ]]; then
      return 0
    fi
    sed -i "s|^${key}=.*|${key}=${val}|" "$ENV_FILE"
  else
    printf '%s=%s\n' "$key" "$val" >> "$ENV_FILE"
  fi
}

if ! grep -q "^# Google OAuth" "$ENV_FILE"; then
  printf '\n# Google OAuth (Üye girişi / kayıt)\n' >> "$ENV_FILE"
fi

set_var_if_missing_or_empty "GOOGLE_CLIENT_ID" "$GOOGLE_CLIENT_ID_DEFAULT"
set_var_if_missing_or_empty "GOOGLE_CLIENT_SECRET" ""
set_var_if_missing_or_empty "GOOGLE_REDIRECT_URI" "$GOOGLE_REDIRECT_DEFAULT"

echo "Google OAuth .env satırları hazır."
