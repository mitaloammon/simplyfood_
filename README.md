# SimplyFood

PDV de restaurante (MVP): Laravel 12, Vue 3, MySQL 8.4 e Redis, orquestrados com Docker Compose.

Fonte de verdade do produto: `docs/SPEC.md`.

## O que este repositório entrega

- Autenticação Sanctum (login, logout, me) com papéis ADMIN, MANAGER, CASHIER, WAITER e KITCHEN
- Categorias e produtos
- Clientes
- Caixa (abrir, atual, movimentações, histórico, fechar)
- Mesas e comandas
- Pedidos e pagamentos
- Dashboard de métricas
- SPA Vue 3 (login + módulos operacionais)

Fora deste MVP: WhatsApp, gateway de pagamento, KDS e recuperação real de senha.

## Requisitos comuns

- Git
- Docker Desktop (Windows/macOS) ou Docker Engine + Compose (Linux)
- Node.js 20+ apenas se o Vite rodar fora do Compose

Siga a seção do seu sistema operacional e depois a seção **Depois que os containers subirem** (igual para todos).

---

## Windows (WSL2 + Ubuntu) — ambiente de desenvolvimento oficial

### Requisitos

- Windows 11 + WSL2 (Ubuntu)
- Docker Desktop com Settings → Resources → WSL integration → Ubuntu

### Onde clonar

O código precisa viver no disco Linux. Bind mount em `/mnt/c` (NTFS) fica lento e quebra permissão de `storage`.

```bash
# certo
~/src/simplyfood

# errado
/mnt/c/Users/SEU_USUARIO/Desktop/simplyfood
```

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

No VS Code: `\\wsl$\Ubuntu\home\SEU_USUARIO\src\simplyfood` ou, no Ubuntu, `code .`

### Subir

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

Atalho, se o script existir:

```bash
chmod +x scripts/*.sh
./scripts/wsl-up.sh
```

---

## macOS

### Requisitos

- macOS recente (Intel ou Apple Silicon)
- Docker Desktop for Mac instalado e com o engine rodando
- Terminal (zsh padrão)

No Apple Silicon as imagens `linux/amd64` funcionam via emulação. Se o serviço `node` falhar com `exec format error`, rode o Vite no host (seção Frontend).

### Onde clonar

Pode clonar em qualquer pasta do usuário. Evite iCloud Desktop/Documents se o Docker reclamar de file sharing.

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

No Docker Desktop: Settings → Resources → File sharing — garanta que `~/src` está compartilhado.

### Subir

No Mac **não** use `docker-compose.wsl.yml` (é override de UID/volume do WSL).

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

Se o `app` não gravar em `storage` (UID do container ≠ do Mac):

```bash
export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml up -d --force-recreate app
```

Só use o override WSL se alguém copiar o compose à mão. No Mac o arquivo base basta.

---

## Linux (Ubuntu/Debian nativo)

### Requisitos

- Docker Engine + plugin Compose
- Seu usuário no grupo `docker` (`sudo usermod -aG docker $USER` e relogin)

### Onde clonar

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

### Subir

Igual ao macOS: **sem** `docker-compose.wsl.yml`.

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

## Depois que os containers subirem (todos os SOs)

Espere `mysql` em `healthy` e `app` em `Up`.

Triggers no MySQL 8.4:

```bash
docker compose exec mysql mysql -uroot -proot -e \
  "SET GLOBAL log_bin_trust_function_creators = 1;"
```

No `docker-compose.yml`, o serviço `mysql` deve incluir `--log-bin-trust-function-creators=1`.

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

`migrate:fresh` apaga o banco. Use só na instalação ou para resetar o seed.

### Conferir

```bash
curl -s http://localhost:8080/api/health

curl -s http://localhost:8080/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@simplyfood.test","password":"password"}'
```

| E-mail | Papel |
|---|---|
| admin@simplyfood.test | ADMIN |
| manager@simplyfood.test | MANAGER |
| cashier@simplyfood.test | CASHIER |
| waiter@simplyfood.test | WAITER |
| kitchen@simplyfood.test | KITCHEN |

Senha de todos: `password`.

### Frontend

- API: http://localhost:8080
- Vite: http://localhost:5173

Se o serviço `node` do Compose falhar:

```bash
cd frontend
npm install
npm run dev -- --host
```

O token Sanctum fica só na memória da aba. F5 desloga. Não use `localStorage`.

### Portas

| Serviço | Host |
|---|---|
| API (Nginx) | 8080 |
| Vite | 5173 |
| MySQL | 3307 → 3306 |
| Redis | 6380 → 6379 |

### Dia a dia

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

## Fluxo mínimo da API

1. Login e guardar `data.token`.
2. `POST /api/cash/open` com `cash_register_id` do seed.
3. `POST /api/orders` com `order_type: COUNTER` e `product_id` do seed.
4. `POST /api/orders/{id}/payments` com `payment_method` (não `method`) e `amount`.

Pedido sem caixa aberto falha. `KITCHEN` toma 403 nas rotas operacionais.

## Problemas comuns

**Docker não encontrado no WSL**  
Docker Desktop → WSL integration → Ubuntu.

**`app` Permission denied em storage (Windows/WSL)**  
Crie as pastas no host, `chown` no seu usuário, e no override WSL **não** monte volume nomeado em `storage` nem force `user:` no `app`.

**Erro 1419 ao criar triggers**  
`SET GLOBAL log_bin_trust_function_creators = 1`.

**Pest/SQLite quebra em ENUM**  
Aceite é a API no MySQL, não SQLite.

**`node` com exec format error (WSL ou Apple Silicon)**  
Vite no host.

**Porta 8080 ocupada no Mac**  
Outro processo (ou Compose antigo). `docker compose down` ou troque o mapeamento em `docker-compose.yml`.

## Estrutura

```text
backend/     API Laravel
frontend/    SPA Vue 3 + Vite
docs/        SPEC e specs SDD
infrastructure/
scripts/     helpers WSL
docker-compose.yml
docker-compose.wsl.yml   # só Windows/WSL
```

## Documentação

- `docs/SPEC.md` — contrato
- `docs/specs/` — SDD por responsabilidade (quando existir)
- `docs/CHANGELOG.md` — mudanças fora das etapas 1–10

## Licença

Uso interno do projeto SimplyFood, salvo outra licença neste repositório.
