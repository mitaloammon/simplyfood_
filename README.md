# SimplyFood

Sistema de gestao para operacao de food service com foco em atendimento, pedidos, clientes, produtos e observabilidade operacional.

> Fonte oficial de especificacao tecnica: [backend/AGENTS.md](backend/AGENTS.md) e [frontend/AGENTS.md](frontend/AGENTS.md).

## Visao Geral

O projeto e composto por:
- [backend](backend): API Laravel com Service Layer, Repository Pattern e modulos por dominio.
- [frontend](frontend): SPA Vue 3 + TypeScript organizada por Feature Layer.
- [infrastructure](infrastructure): Dockerfile e configuracao Nginx.
- [docker-compose.yml](docker-compose.yml): orquestracao local de app, web, mysql, redis e node.

Escopo funcional atual:
- autenticacao e autorizacao por middleware e role
- clientes por escopo de usuario autenticado
- pedidos por escopo de usuario autenticado
- produtos ativos e criacao de produtos
- metricas de dashboard por usuario autenticado

## Arquitetura

### Visao de camadas

- Presentation Layer: rotas, controllers, requests/resources e pages/components.
- Application Layer: services e casos de uso.
- Domain Layer: entidades e regras centrais.
- Infrastructure Layer: repositories e integracoes.
- Persistence Layer: migrations, indices e constraints.

### Ambientes de negocio

- Ambiente Administrativo: governanca de cadastros e monitoramento gerencial (ativo parcial, com modulos planejados).
- Ambiente Operacional: dashboard operacional, criacao de clientes, abertura e gestao de pedidos proprios (ativo).

## Tecnologias

### Backend
- Laravel 12
- PHP ^8.2
- REST API
- Inertia Laravel v3 (fluxo web /dashboard)
- Redis (cache/queue support)

### Frontend
- Vue 3
- Composition API
- TypeScript
- Vite
- Pinia
- Vue Router
- Tailwind CSS

### Banco
- MySQL 8.4

### Infraestrutura
- Docker
- Docker Compose
- Nginx

### Qualidade
- Pest / PHPUnit (backend)
- Vitest (frontend)

## Requisitos

- Docker Engine/Desktop 24+
- Docker Compose v2
- Node.js 20+ (quando executar frontend fora do container)
- PHP 8.2+ e Composer (quando executar backend fora do container)

## Instalacao

### 1. Clonar repositorio

```bash
git clone https://github.com/mitaloammon/simplyfood_
cd simplyfood
```

### 2. Preparar variaveis de ambiente do backend

```bash
cp backend/.env.example backend/.env
```

PowerShell:

```powershell
Copy-Item backend/.env.example backend/.env
```

## Docker

### Subir stack completa

```bash
docker compose up --build -d
```

### Parar stack

```bash
docker compose down
```

### Reiniciar stack

```bash
docker compose restart
```

### Ver status

```bash
docker compose ps
```

## Variaveis de Ambiente

Principais variaveis usadas no compose:

- APP_URL (padrao: http://localhost:8080)
- DB_HOST (padrao: mysql)
- DB_DATABASE (padrao: simplyfood)
- DB_USERNAME (padrao: simplyfood)
- DB_PASSWORD (padrao: simplyfood)
- REDIS_HOST (padrao: redis)
- REDIS_PORT (padrao: 6379)

Variaveis frontend relevantes:
- VITE_API_URL (SPA frontend)
- VITE_API_BASE_URL (container node no build)

## Containers

Servicos definidos em [docker-compose.yml](docker-compose.yml):

- app: PHP-FPM Laravel
- web: Nginx (porta 8080)
- mysql: MySQL 8.4 (porta 3307 host)
- redis: Redis 7 (porta 6380 host)
- node: build do frontend

## Banco de Dados

Modelo atual inclui:
- users
- customers
- addresses
- categories
- products
- orders
- order_items
- deliveries
- payment_transactions
- tickets
- whatsapp_messages
- tabelas de infraestrutura (jobs/cache/sessions)

## Migracoes

Executar migracoes:

```bash
docker compose exec app php artisan migrate
```

Migracoes relevantes de escopo/performance ja aplicadas:
- user_id em customers
- user_id em orders
- indices para dashboard e listagens operacionais

## Seeders

```bash
docker compose exec app php artisan db:seed
```

Ou migrar com seed:

```bash
docker compose exec app php artisan migrate --seed
```

## Executando Backend

### Via Docker (recomendado)

```bash
docker compose run --rm app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

API disponivel em:
- http://localhost:8080/api
- health: http://localhost:8080/api/health

### Fora do container (opcional)

```bash
cd backend
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

## Executando Frontend

### Via stack Docker

O container `node` gera build de producao consumido pelo Nginx.

### Desenvolvimento local (Vite)

```bash
cd frontend
npm install
npm run dev
```

URL padrao:
- http://localhost:5173

## Estrutura do Projeto

```text
simplyfood/
	backend/
	frontend/
	infrastructure/
	docker-compose.yml
	README.md
```

## Arquitetura

### Principios adotados

- Spec-Driven Development (SDD)
- Clean Architecture
- SOLID
- Service Layer
- Repository Pattern
- Feature Layer
- Dependency Injection

### Regras de evolucao

- manter contratos API estaveis
- evitar logica de negocio em controllers/pages
- documentar alteracoes incrementalmente nos AGENTS

## Fluxo Git

Fluxo recomendado:

1. criar branch por feature/fix
2. implementar mudanca incremental
3. validar testes/build
4. atualizar documentacao
5. abrir PR para revisao

## Branches

Modelo sugerido:
- main: producao
- develop: integracao
- feature/*: novas funcionalidades
- fix/*: correcoes
- chore/*: manutencao tecnica

## Convencao de Commits

Padrao recomendado:

- feat: nova funcionalidade
- fix: correcao de bug
- refactor: refatoracao sem mudanca funcional
- docs: atualizacao documental
- test: ajustes de testes
- chore: manutencao/infra

## Roadmap

Curto prazo:
- consolidar cobertura de testes de auth e orders
- estabilizar governanca documental entre frontend/backend

Medio prazo:
- evoluir realtime/event-driven (Reverb/WebSockets)
- ampliar modulos administrativos planejados

Longo prazo:
- caixa completo, KDS e relatorios gerenciais avancados

## Contribuicao

Antes de contribuir:

1. ler [backend/AGENTS.md](backend/AGENTS.md)
2. ler [frontend/AGENTS.md](frontend/AGENTS.md)
3. confirmar compatibilidade com arquitetura e contratos
4. incluir validacao (testes/build) e atualizacao documental

## FAQ

### Posso alterar contratos da API livremente?
Nao. Mudancas de contrato exigem justificativa tecnica e compatibilidade controlada.

### O projeto usa apenas SPA frontend?
Nao. A interface principal usa SPA em [frontend](frontend), e existe fluxo Inertia no backend para `/dashboard`.

### Quais modulos estao totalmente ativos no fluxo principal?
Auth, Dashboard, Customers e Orders.

## Troubleshooting

### Erro ao subir containers

```bash
docker compose down
docker compose up --build -d
docker compose ps
```

### Erro de permissao em storage/cache

```bash
docker compose exec app chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache
```

### Erro de conexao com MySQL

- validar se `mysql` esta healthy
- revisar DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD em `backend/.env`

### Frontend nao consome API local

- verificar valor de `VITE_API_URL`
- validar se API esta acessivel em `http://localhost:8080/api/health`

## Licenca

Uso interno do projeto SimplyFood. Ajustar licenciamento oficial conforme politica do repositorio.
