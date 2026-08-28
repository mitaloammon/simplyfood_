#!/bin/sh
set -e
cd /var/www/backend

UID_TO_USE="${HOST_UID:-1000}"
GID_TO_USE="${HOST_GID:-1000}"

# Alinha www-data ao UID do host (WSL) para o bind mount gravar storage/.
if [ "$(id -u)" = "0" ]; then
  groupmod -o -g "$GID_TO_USE" www-data 2>/dev/null || true
  usermod -o -u "$UID_TO_USE" www-data 2>/dev/null || true
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

if [ "$(id -u)" = "0" ]; then
  chown -R www-data:www-data storage bootstrap/cache || true
fi

exec "$@"
