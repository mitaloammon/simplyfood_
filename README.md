# SimplyFood — esqueleto Docker (WSL)

Fonte: `docs/SPEC.md`.

## WSL (obrigatório)

Coloque o projeto no disco Linux, não em `/mnt/c`:

```bash
# certo
~/src/simplyfood

# errado (NTFS: permissão lenta e quebrada)
/mnt/c/Users/voce/simplyfood
```

Docker Desktop: Settings → Resources → WSL Integration → sua distro.

## Subir

```bash
cp .env.example .env
# no WSL o script preenche UID/GID
./scripts/wsl-up.sh
```

Manual:

```bash
export HOST_UID=$(id -u)
export HOST_GID=$(id -g)
docker compose -f docker-compose.yml -f docker-compose.wsl.yml up --build -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

- API: http://localhost:8080/api/health
- SPA (dev): http://localhost:5173

`vendor` e `node_modules` ficam em volumes nomeados (não no bind mount).
