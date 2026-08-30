# SimplyFood

Restaurant POS (MVP): Laravel 12, Vue 3, MySQL 8.4, and Redis, orchestrated with Docker Compose.

Product source of truth: `docs/SPEC.md`.

## What this repository includes

- Sanctum authentication (login, logout, me) with roles ADMIN, MANAGER, CASHIER, WAITER, and KITCHEN
- Categories and products
- Customers
- Cash register (open, current, movements, history, close)
- Tables and checks (comandas)
- Orders and payments
- Metrics dashboard
- Vue 3 SPA (login + operational modules)

Out of this MVP: WhatsApp, payment gateway, KDS, and real password recovery.

## Common requirements

- Git
- Docker Desktop (Windows/macOS) or Docker Engine + Compose (Linux)
- Node.js 20+ only if Vite runs outside Compose

Follow the section for your operating system, then **After the containers are up** (same on every OS).

---

## Windows (WSL2 + Ubuntu) — official development environment

### Requirements

- Windows 11 + WSL2 (Ubuntu)
- Docker Desktop with Settings → Resources → WSL integration → Ubuntu

### Where to clone

The code must live on the Linux filesystem. A bind mount under `/mnt/c` (NTFS) is slow and breaks `storage` permissions.

```bash
# correct
~/src/simplyfood

# wrong
/mnt/c/Users/YOUR_USER/Desktop/simplyfood
```

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

In VS Code: `\\wsl$\Ubuntu\home\YOUR_USER\src\simplyfood` or, in Ubuntu, `code .`

### Start

```bash
cp .env.example .env
cp backend/.env.example backend/.env 2>/dev/null || true

mkdir -p backend/storage/framework/{cache,sessions,views} \
         backend/storage/logs \
         backend/bootstrap/cache
chmod -R u+rwX backend/storage backend/bootstrap/cache

export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml -f docker-compose.wsl.yml up --build -d
docker compose ps
```

Shortcut, if the script exists:

```bash
chmod +x scripts/*.sh
./scripts/wsl-up.sh
```

---

## macOS

### Requirements

- Recent macOS (Intel or Apple Silicon)
- Docker Desktop for Mac installed and the engine running
- Terminal (default zsh)

On Apple Silicon, `linux/amd64` images run through emulation. If the `node` service fails with `exec format error`, run Vite on the host (Frontend section).

### Where to clone

Any user folder is fine. Avoid iCloud Desktop/Documents if Docker complains about file sharing.

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

In Docker Desktop: Settings → Resources → File sharing — make sure `~/src` is shared.

### Start

On a Mac **do not** use `docker-compose.wsl.yml` (it is a WSL UID/volume override).

```bash
cp .env.example .env
cp backend/.env.example backend/.env 2>/dev/null || true

mkdir -p backend/storage/framework/{cache,sessions,views} \
         backend/storage/logs \
         backend/bootstrap/cache
chmod -R u+rwX backend/storage backend/bootstrap/cache

docker compose -f docker-compose.yml up --build -d
docker compose ps
```

If `app` cannot write to `storage` (container UID ≠ Mac UID):

```bash
export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml up -d --force-recreate app
```

Only use the WSL override if someone copied it by hand. On a Mac the base compose file is enough.

---

## Linux (native Ubuntu/Debian)

### Requirements

- Docker Engine + Compose plugin
- Your user in the `docker` group (`sudo usermod -aG docker $USER` then sign in again)

### Where to clone

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

### Start

Same as macOS: **without** `docker-compose.wsl.yml`.

```bash
cp .env.example .env
cp backend/.env.example backend/.env 2>/dev/null || true

mkdir -p backend/storage/framework/{cache,sessions,views} \
         backend/storage/logs \
         backend/bootstrap/cache
chmod -R u+rwX backend/storage backend/bootstrap/cache

export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml up --build -d
docker compose ps
```

---

## After the containers are up (every OS)

Wait until `mysql` is `healthy` and `app` is `Up`.

MySQL 8.4 triggers:

```bash
docker compose exec mysql mysql -uroot -proot -e \
  "SET GLOBAL log_bin_trust_function_creators = 1;"
```

In `docker-compose.yml`, the `mysql` service must include `--log-bin-trust-function-creators=1`.

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

`migrate:fresh` drops the database. Use it only for first install or to reset the seed.

### Check

```bash
curl -s http://localhost:8080/api/health

curl -s http://localhost:8080/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@simplyfood.test","password":"password"}'
```

| Email | Role |
|---|---|
| admin@simplyfood.test | ADMIN |
| manager@simplyfood.test | MANAGER |
| cashier@simplyfood.test | CASHIER |
| waiter@simplyfood.test | WAITER |
| kitchen@simplyfood.test | KITCHEN |

Password for every seed user: `password`.

### Frontend

- API: http://localhost:8080
- Vite: http://localhost:5173

If the Compose `node` service fails:

```bash
cd frontend
npm install
npm run dev -- --host
```

The Sanctum token lives only in tab memory. A refresh logs you out. Do not use `localStorage`.

### Ports

| Service | Host |
|---|---|
| API (Nginx) | 8080 |
| Vite | 5173 |
| MySQL | 3307 → 3306 |
| Redis | 6380 → 6379 |

### Day to day

Windows/WSL:

```bash
export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml -f docker-compose.wsl.yml up -d
```

macOS / Linux:

```bash
docker compose -f docker-compose.yml up -d
```

```bash
docker compose logs app --tail=80
docker compose down
```

## Minimal API flow

1. Log in and keep `data.token`.
2. `POST /api/cash/open` with the seed `cash_register_id`.
3. `POST /api/orders` with `order_type: COUNTER` and a seed `product_id`.
4. `POST /api/orders/{id}/payments` with `payment_method` (not `method`) and `amount`.

An order without an open cash shift fails. `KITCHEN` gets 403 on operational routes.

## Common issues

**`docker` not found in WSL**  
Docker Desktop → WSL integration → Ubuntu.

**`app` Permission denied on storage (Windows/WSL)**  
Create the host directories, `chown` them to your user, and in the WSL override do **not** mount a named volume on `storage` or set `user:` on `app`.

**Error 1419 when creating triggers**  
`SET GLOBAL log_bin_trust_function_creators = 1`.

**Pest/SQLite breaks on ENUM**  
Acceptance is the API on MySQL, not SQLite.

**`node` exec format error (WSL or Apple Silicon)**  
Run Vite on the host.

**Port 8080 already in use on a Mac**  
Another process (or an old Compose stack). Run `docker compose down` or change the mapping in `docker-compose.yml`.

## Layout

```text
backend/                 Laravel API
frontend/                Vue 3 + Vite SPA
docs/                    SPEC and SDD specs
infrastructure/
scripts/                 WSL helpers
docker-compose.yml
docker-compose.wsl.yml   # Windows/WSL only
```

## Documentation

- `docs/SPEC.md` — contract
- `docs/specs/` — SDD by responsibility (when present)
- `docs/CHANGELOG.md` — changes outside stages 1–10

## License

Internal use for the SimplyFood project unless another license is added to this repository.
