#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

MODE="${1:-follow}"

case "$MODE" in
  follow|-f)
    echo "==> PHP-FPM (app) + laravel.log"
    docker compose logs -f --tail=200 app &
    pid=$!
    trap 'kill $pid 2>/dev/null || true' INT TERM
    docker compose exec -T app sh -c 'touch storage/logs/laravel.log; tail -f storage/logs/laravel.log' || true
    wait $pid || true
    ;;
  fpm)
    docker compose logs -f --tail=200 app
    ;;
  laravel)
    docker compose exec app sh -c 'touch storage/logs/laravel.log; tail -n 200 -f storage/logs/laravel.log'
    ;;
  last)
    docker compose logs --tail=200 app
    docker compose exec -T app sh -c 'tail -n 80 storage/logs/laravel.log 2>/dev/null || true'
    ;;
  *)
    echo "uso: $0 [follow|fpm|laravel|last]"
    exit 1
    ;;
esac
