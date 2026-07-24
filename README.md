# Simplify Food

Sistema web corporativo para gerenciamento operacional, financeiro e logístico de lanchonetes, hamburguerias, pizzarias, cafeterias e restaurantes.

---

> [!IMPORTANT]
> **Atenção agentes de IA e desenvolvedores:**
> As regras principais de arquitetura, camada de aplicação, estrutura de pastas e convenções do projeto estão consolidadas em [backend/AGENTS.md](backend/AGENTS.md) e [frontend/AGENTS.md](frontend/AGENTS.md). Consulte esses arquivos antes de alterar qualquer parte do sistema.

---

## Visão Geral

O Simplify Food centraliza, em tempo real, as operações de:
- gestão de clientes e endereços
- gestão de pedidos e fluxo operacional
- catálogo de produtos e categorias
- financeiro, faturamento e fluxo de caixa
- integração com WhatsApp Business
- logística e entregas

A aplicação é composta por:
- [backend](backend): API em Laravel 12 com arquitetura limpa
- [frontend](frontend): interface em Vue 3 + TypeScript
- [infrastructure](infrastructure): configuração Docker e Nginx

---

## Requisitos

Antes de iniciar, certifique-se de ter instalado:
- Docker Desktop ou Docker Engine + Docker Compose v2
- Node.js 20+
- PHP 8.2+ e Composer (apenas se for executar o backend fora do container)

---

## Execução local recomendada com Docker

O fluxo mais simples e alinhado com a estrutura atual do projeto é usar Docker Compose.

### 1) Clone o repositório

```bash
git clone <url-do-repositorio>
cd simplyfood
```

### 2) Configure o ambiente do backend

```bash
cp backend/.env.example backend/.env
```

No Windows PowerShell:

```powershell
Copy-Item backend/.env.example backend/.env
```

> O arquivo de ambiente já é consumido pela stack Docker definida em [docker-compose.yml](docker-compose.yml).

### 3) Suba a stack local

```bash
docker compose up --build -d
```

### 4) Instale dependências do backend e prepare o banco

```bash
docker compose run --rm app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

### 5) Acesse a aplicação

- Frontend e API: http://localhost:8080
- Health check da API: http://localhost:8080/api/health

---

## Comandos úteis do dia a dia

### Subir e parar a stack

```bash
# subir
docker compose up -d

# parar
docker compose down

# reiniciar
docker compose restart
```

### Verificar status dos containers

```bash
docker compose ps
```

### Visualizar logs

```bash
# logs gerais
docker compose logs -f

# logs do backend
docker compose logs -f app
```

### Executar comandos Artisan

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan test
```

### Executar comandos Composer

```bash
docker compose run --rm app composer install
docker compose run --rm app composer dump-autoload
```

---

## Execução local do frontend sem Docker

Se você quiser trabalhar no frontend diretamente localmente, use:

```bash
cd frontend
npm install
npm run dev
```

A aplicação ficará disponível em:
- http://localhost:5173

> Para isso, o backend precisa continuar rodando via Docker em http://localhost:8080.

---

## Testes

### Backend

```bash
cd backend
vendor/bin/pest
```

### Frontend

```bash
cd frontend
npm run test
```

---

## Observações

- Esta README centraliza os passos de execução local e uso diário do projeto.
- Informações específicas de infraestrutura mais detalhadas foram consolidadas em [DOCKER.md](DOCKER.md) e [HOW_TO_USE.md](HOW_TO_USE.md), mas o fluxo principal de execução deve seguir este documento.
- Novas features devem respeitar as diretrizes definidas em [backend/AGENTS.md](backend/AGENTS.md) e [frontend/AGENTS.md](frontend/AGENTS.md).
