#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

if grep -qi microsoft /proc/version 2>/dev/null; then
  echo "WSL detectado."
  case "$PWD" in
    /mnt/*)
      echo "ERRO: projeto está em $PWD (NTFS)."
      echo "Mova para o disco Linux, ex: ~/src/simplyfood"
      exit 1
      ;;
  esac
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

# atualiza UID/GID sem quebrar o restante do .env
if grep -q '^HOST_UID=' .env; then
  sed -i "s/^HOST_UID=.*/HOST_UID=$(id -u)/" .env
  sed -i "s/^HOST_GID=.*/HOST_GID=$(id -g)/" .env
else
  echo "HOST_UID=$(id -u)" >> .env
  echo "HOST_GID=$(id -g)" >> .env
fi

export HOST_UID=$(id -u)
export HOST_GID=$(id -g)

docker compose -f docker-compose.yml -f docker-compose.wsl.yml up --build -d
echo "Stack no ar. API: http://localhost:8080/api/health"
