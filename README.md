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

## Requisitos

- Windows 11 com WSL2 (Ubuntu)
- Docker Desktop com integração WSL habilitada na distro Ubuntu
- Git
- Node.js 20+ no Ubuntu apenas se o Vite rodar fora do Compose

## Onde colocar o projeto

O código precisa viver no disco Linux. Bind mount em `/mnt/c` (NTFS) fica lento e quebra permissão de `storage`.

```bash
# certo
~/src/simplyfood

# errado
/mnt/c/Users/SEU_USUARIO/Desktop/simplyfood
```

Clone:

```bash
mkdir -p ~/src
git clone https://github.com/mitaloammon/simplyfood_.git ~/src/simplyfood
cd ~/src/simplyfood
```

No VS Code, abra `\\wsl$\Ubuntu\home\SEU_USUARIO\src\simplyfood` ou, no Ubuntu:

```bash
cd ~/src/simplyfood
code .
```

## Primeira subida

```bash
cd ~/src/simplyfood
cp .env.example .env
```

Se existir `backend/.env.example`:

```bash
cp backend/.env.example backend/.env
```

Pastas graváveis do Laravel:

```bash
mkdir -p backend/storage/framework/{cache,sessions,views} \
         backend/storage/logs \
         backend/bootstrap/cache
chmod -R u+rwX backend/storage backend/bootstrap/cache
```

Subir os containers:

```bash
export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml -f docker-compose.wsl.yml up --build -d
docker compose ps
```

Espere `mysql` em `healthy` e `app` em `Up`.

Triggers no MySQL 8.4 exigem:

```bash
docker compose exec mysql mysql -uroot -proot -e \
  "SET GLOBAL log_bin_trust_function_creators = 1;"
```

No `docker-compose.yml`, o serviço `mysql` deve incluir:

```text
--log-bin-trust-function-creators=1
```

Aplicação e banco:

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate:fresh --seed
```

`migrate:fresh` apaga o banco. Use só na instalação ou quando quiser resetar o seed.

Atalho, se o script existir e for executável:

```bash
chmod +x scripts/*.sh
./scripts/wsl-up.sh
```

## Conferir se subiu

```bash
curl -s http://localhost:8080/api/health
```

Login:

```bash
curl -s http://localhost:8080/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@simplyfood.test","password":"password"}'
```

Usuários do seed (senha de todos: `password`):

| E-mail | Papel |
|---|---|
| admin@simplyfood.test | ADMIN |
| manager@simplyfood.test | MANAGER |
| cashier@simplyfood.test | CASHIER |
| waiter@simplyfood.test | WAITER |
| kitchen@simplyfood.test | KITCHEN |

Produtos do seed: X-Burger, Batata frita, Refrigerante lata.

## Frontend (SPA)

Endereços:

- API / Nginx: http://localhost:8080
- Vite (dev): http://localhost:5173

O serviço `node` do Compose pode falhar no WSL (`exec format error`). Rode o Vite no Ubuntu:

```bash
cd ~/src/simplyfood/frontend
npm install
npm run dev -- --host
```

O token Sanctum fica só na memória da aba. Atualizar a página desloga. Não grave o token em `localStorage` nem `sessionStorage`.

## Portas

| Serviço | Porta no host |
|---|---|
| API (Nginx) | 8080 |
| Vite | 5173 |
| MySQL | 3307 → 3306 |
| Redis | 6380 → 6379 |

## Comandos do dia a dia

```bash
cd ~/src/simplyfood
export HOST_UID=$(id -u) HOST_GID=$(id -g)
docker compose -f docker-compose.yml -f docker-compose.wsl.yml up -d
docker compose logs app --tail=80
docker compose down
```

Não rode `migrate:fresh` em base que já tem operação real.

## Fluxo mínimo da API (smoke)

1. Login e guardar o `data.token`.
2. Abrir caixa: `POST /api/cash/open` com `cash_register_id` do seed e `opening_balance`.
3. Criar pedido: `POST /api/orders` com `order_type: COUNTER` e `product_id` real do seed.
4. Pagar: `POST /api/orders/{id}/payments` com `payment_method` (não `method`) e `amount`.

Pedido sem turno de caixa aberto responde erro de negócio. `KITCHEN` toma 403 nas rotas operacionais.

## Problemas comuns

### `docker: command not found` no Ubuntu

Abra o Docker Desktop no Windows. Em Settings → Resources → WSL integration, marque a distro Ubuntu. Feche e abra o terminal WSL.

### `app` sai com Permission denied em `storage/framework`

```bash
mkdir -p backend/storage/framework/{cache,sessions,views} backend/storage/logs backend/bootstrap/cache
sudo chown -R "$(id -un):$(id -gn)" backend/storage backend/bootstrap
chmod -R u+rwX backend/storage backend/bootstrap/cache
```

Em `docker-compose.wsl.yml`, o serviço `app` não deve ter `user:` nem volume nomeado montado em `storage`.

### Migration falha com erro 1419 (triggers / SUPER)

```bash
docker compose exec mysql mysql -uroot -proot -e \
  "SET GLOBAL log_bin_trust_function_creators = 1;"
docker compose exec app php artisan migrate:fresh --seed
```

### Pest / SQLite quebra em ENUM e CHECK

A schema oficial é MySQL. O critério de aceite do MVP é a API no container, não o SQLite em memória.

### Container `node` com `exec format error`

Ignore o serviço `node` e suba o Vite no host, como na seção Frontend.

### `EACCES` em `frontend/node_modules`

```bash
sudo chown -R "$(id -un):$(id -gn)" frontend
rm -rf frontend/node_modules
cd frontend && npm install
```

## Estrutura

```text
backend/     API Laravel
frontend/    SPA Vue 3 + Vite
docs/        SPEC e changelog
infrastructure/
scripts/     helpers WSL
docker-compose.yml
docker-compose.wsl.yml
```

## Documentação

- `docs/SPEC.md` — contrato do sistema
- `docs/CHANGELOG.md` — mudanças fora das etapas originais (quando existir)

## Licença

Uso interno do projeto SimplyFood, salvo outra licença adicionada a este repositório.
