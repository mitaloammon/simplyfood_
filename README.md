# Simplify Food

Sistema web corporativo para gerenciamento operacional, financeiro e logístico de lanchonetes, hamburguerias, pizzarias, cafeterias e restaurantes.

---

> [!IMPORTANT]
> **Atenção Agentes de IA e Desenvolvedores:**
> Este projeto possui um guia de regras de contexto e fonte única de verdade definido em [AGENTS.md](file:///C:/Users/MITALO/.gemini/antigravity-ide/scratch/simplify-food/AGENTS.md). Favor ler e seguir rigorosamente as regras definidas nele antes de qualquer alteração ou implementação.

---

## Visão Geral

O Simplify Food centraliza em tempo real:
- Gestão de clientes e endereços
- Gestão de pedidos e fluxo de produção
- Gestão de cardápio (Categorias, Produtos e Menus)
- Gestão financeira, faturamento e fluxo de caixa
- Integração WhatsApp Business API
- Geolocalização e logística de entregas

---

## Arquitetura & Diretórios

Este projeto segue os princípios de **Clean Architecture**, **SOLID**, **Domain Driven Design (DDD)**, e **TDD**.

- **`/backend`**: Laravel 12 (PHP 8.3) estruturado em camadas de `Domains`, `Application` e `Infrastructure`.
- **`/frontend`**: Vue 3 (TypeScript) estruturado em módulos auto-contidos no diretório `src/modules`.
- **`/infrastructure`**: Arquivos de docker, nginx e scripts auxiliares.
- **`/docs`**: Regras de negócio, Swagger/OpenAPI e diagramas da aplicação.

---

## Configuração Local

### Requisitos
- Docker & Docker Compose
- PHP 8.3+ (opcional para execução local fora do container)
- Node.js 20+

### Passo a Passo

1. **Clonar o Repositório**
2. **Subir os Containers Docker**
   ```bash
   docker compose up -d
   ```
3. **Configuração do Backend**
   ```bash
   cd backend
   cp .env.example .env
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```
4. **Configuração do Frontend**
   ```bash
   cd frontend
   npm install
   npm run dev
   ```

### Fluxo Diário com Docker

```bash
# subir a stack
docker compose up -d

# parar a stack
docker compose down

# reiniciar após alterações de configuração
docker compose down --remove-orphans
docker compose up -d
```

> A aplicação fica disponível em http://localhost:8080.

---

## Testes

A cobertura de testes mínima exigida para qualquer nova funcionalidade é de **80%**.

### Rodando Testes do Backend
```bash
cd backend
vendor/bin/pest
```

### Rodando Testes do Frontend
```bash
cd frontend
npm run test:unit
npm run test:e2e
```
