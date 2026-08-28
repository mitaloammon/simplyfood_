# SimplyFood — Especificação Canônica

> Fonte oficial de verdade do sistema.  
> Em conflito com qualquer outro arquivo, este documento prevalece.  
> Versão: 1.0.0 · Data: 2026-08-18

## 1. Objetivo

SimplyFood é um sistema de gestão operacional para um estabelecimento de food service: atendimento presencial, pedidos, caixa, cardápio, clientes e estoque básico.

Não é, no MVP, uma plataforma de delivery marketplace, CRM de WhatsApp ou KDS completo.

## 2. Decisões travadas

| Tema | Decisão |
| --- | --- |
| Multi-tenant | Um estabelecimento por contexto. Toda entidade operacional tem `establishment_id`. |
| Identificadores | UUID v4 |
| Autenticação | Laravel Sanctum (Bearer token). Na SPA, o token existe somente em memória. |
| Interface | Apenas SPA Vue 3. Inertia `/dashboard` está fora do MVP. |
| Realtime | Opcional. REST é a fonte de verdade. Reverb pode ser adicionado depois sem mudar contratos. |
| Soft delete | Sim, nas entidades de negócio |
| Idioma da API | JSON em português nas mensagens; campos em `snake_case` em inglês |

## 3. Escopo

### 3.1 MVP (implementar agora)

- Autenticação (login, logout, me)
- Contexto do estabelecimento
- Clientes
- Categorias e produtos
- Mesas
- Comandas
- Pedidos e itens
- Caixa (turno, movimentações) e pagamentos
- Dashboard operacional
- Baixa de estoque no fechamento do pedido (quando houver ficha)

### 3.2 Fora do MVP (não implementar agora)

- WhatsApp
- Gateway de pagamento
- Delivery com geolocalização
- KDS / telas de cozinha
- Grupos de opções de produto (adicionais)
- Promoções
- Módulo separado de receitas além de `product_ingredients`
- Relatórios gerenciais avançados
- Multi-estabelecimento por usuário
- Super-admin de plataforma

### 3.3 Reservado no banco, sem API no MVP

Tabelas de cozinha, promoções, notificações e auditoria podem existir no schema futuro, mas **não** entram em migration/API do MVP.

## 4. Papéis e permissões

Papéis únicos do sistema:

| Role | Descrição |
| --- | --- |
| `ADMIN` | Dono/gestor máximo do estabelecimento |
| `MANAGER` | Operação, catálogo, usuários (exceto ADMIN) e caixa |
| `CASHIER` | Caixa, pagamentos e pedidos |
| `WAITER` | Mesas, comandas, pedidos e clientes |
| `KITCHEN` | Reservado. Sem telas no MVP. Recusar acesso às rotas do MVP com 403. |

### Matriz do MVP

| Ação | ADMIN | MANAGER | CASHIER | WAITER | KITCHEN |
| --- | --- | --- | --- | --- | --- |
| Login / me | sim | sim | sim | sim | sim |
| Dashboard | sim | sim | sim | sim | não |
| CRUD usuários | sim | sim* | não | não | não |
| Categorias / produtos | sim | sim | leitura | leitura | não |
| Clientes | sim | sim | leitura | sim | não |
| Mesas / comandas | sim | sim | leitura | sim | não |
| Pedidos | sim | sim | sim | sim | não |
| Abrir/fechar caixa | sim | sim | sim | não | não |
| Sangria / suprimento | sim | sim | sim | não | não |
| Registrar pagamento | sim | sim | sim | não | não |
| Cancelar pedido | sim | sim | sim | próprio aberto | não |

\* MANAGER não altera nem remove ADMIN.

## 5. Modelo de tenant

- `establishments` é a raiz.
- `users.establishment_id` é obrigatório.
- Toda query operacional filtra por `establishment_id` do usuário autenticado.
- Não existe isolamento por `user_id` de dono do pedido. Pedidos pertencem ao estabelecimento; `waiter_id` e `user_id` são auditoria, não tenant.
- Canal realtime futuro: `establishment.{establishmentId}` apenas.

## 6. Regras de negócio

1. Usuário `INACTIVE` não autentica.
2. Só existe um turno `OPEN` por caixa.
3. Pedido só é criado se existir turno de caixa `OPEN` no estabelecimento.
4. `order_type`:
   - `TABLE`: exige `table_id`; mesa deve estar `OCCUPIED` ou passar de `FREE` para `OCCUPIED`.
   - `COMMAND`: exige `command_id` e `table_id`; comanda deve estar `OPEN`.
   - `COUNTER`: sem mesa/comanda.
   - `DELIVERY`: fora do MVP; rejeitar com 422.
5. Abrir comanda: status `FREE` → `OPEN`, vincular à mesa, mesa → `OCCUPIED`.
6. Fechar comanda: somente se não houver pedido `OPEN` ou `IN_PREPARATION` vinculado.
7. Fechar mesa: somente se não houver comanda `OPEN` nem pedido ativo.
8. Item de pedido inicia em `WAITING`.
9. Status de pedido permitido: `OPEN` → `IN_PREPARATION` → `READY` → `DELIVERED` → `CLOSED`. `CANCELLED` a partir de `OPEN`, `IN_PREPARATION` ou `READY`.
10. Pedido só vai para `CLOSED` se `sum(payments CONFIRMED) >= total_amount`.
11. Fechar turno de caixa: todos os pedidos do turno devem estar `CLOSED` ou `CANCELLED`.
12. Ao fechar pedido, para cada item com ficha em `product_ingredients`, gerar `stock_movements` tipo `OUT`. Não fechar se estoque ficar negativo, salvo ajuste explícito de ADMIN/MANAGER.
13. Soft delete não remove histórico financeiro.
14. Preços no pedido são copiados do produto no momento da inclusão (não recalcular com preço atual).

## 7. Requisitos não funcionais

- PHP 8.2+, Laravel 12, MySQL 8.4, Redis, Vue 3 + TypeScript
- Paginação cursor ou page padrão: 20, máximo 100
- Timeout de API alvo: p95 < 300 ms em listagens simples
- Índices em `establishment_id`, status, datas e FKs
- Filas Redis para jobs (estoque, e-mail futuro)
- Logs de aplicação; auditoria detalhada fora do MVP
- CORS apenas para origem do SPA
- Senha: hash bcrypt/argon via Laravel
- Rate limit: 5 req/min em login por IP+email; 60 req/min nas rotas autenticadas
- Health check público, sem dados internos

## 8. Contratos de API

Base: `/api`  
Header: `Authorization: Bearer {token}`  
Header: `Accept: application/json`  
Envelope:

```json
{
  "status": "success",
  "data": {},
  "message": "string"
}
```

Erro:

```json
{
  "status": "error",
  "message": "string",
  "errors": {}
}
```

### 8.1 Públicos

#### `GET /api/health`

Resposta 200:

```json
{
  "status": "success",
  "data": { "ok": true },
  "message": "OK"
}
```

#### `POST /api/auth/login`

```json
{ "email": "admin@simplyfood.test", "password": "********" }
```

200:

```json
{
  "status": "success",
  "data": {
    "token": "1|...",
    "token_type": "Bearer",
    "user": {
      "id": "uuid",
      "name": "Administrador",
      "email": "admin@simplyfood.test",
      "role": "ADMIN",
      "establishment_id": "uuid"
    }
  },
  "message": "Login realizado com sucesso"
}
```

401 se credencial inválida.

Na SPA, o token Sanctum é mantido somente em memória durante a sessão da aba.

É proibido persistir o Bearer token em `localStorage` ou `sessionStorage`.

#### `POST /api/auth/register`

Fora do MVP público. Criação de usuário é autenticada por ADMIN/MANAGER.

### 8.2 Autenticados

`POST /api/auth/logout` → 200  
`GET /api/auth/me` → usuário atual

#### Clientes

`GET /api/customers` query: `q`, `page`  
`POST /api/customers` — **autenticado** (não público)

```json
{
  "name": "Maria Silva",
  "phone": "11999999999",
  "email": "maria@email.com",
  "document": "12345678900",
  "address": "Rua A, 10"
}
```

`GET /api/customers/{id}`  
`PATCH /api/customers/{id}`  
`DELETE /api/customers/{id}` soft delete

#### Produtos e categorias

`GET|POST /api/categories`  
`PATCH|DELETE /api/categories/{id}`

`GET /api/products` query: `category_id`, `is_available`, `q`  
`POST /api/products`

```json
{
  "category_id": "uuid",
  "name": "X-Burger",
  "description": "Pão, carne, queijo",
  "price": 28.90,
  "cost_price": 12.00,
  "sku": "XB-01",
  "is_available": true,
  "preparation_time_minutes": 12
}
```

`GET|PATCH|DELETE /api/products/{id}`

#### Mesas

`GET /api/tables`  
`POST /api/tables` `{ "number": 1, "capacity": 4 }`  
`PATCH /api/tables/{id}`  
`PATCH /api/tables/{id}/status` `{ "status": "FREE|OCCUPIED|RESERVED|BILLING" }`

#### Comandas

`GET /api/commands`  
`POST /api/commands` `{ "code": "A-12", "table_id": "uuid" }`  
`PATCH /api/commands/{id}/status` `{ "status": "OPEN|CLOSED|BLOCKED" }`

Status persistido: `FREE`, `OPEN`, `BLOCKED`, `CLOSED`.

#### Pedidos

`GET /api/orders` query: `status`, `table_id`, `command_id`  
`POST /api/orders`

```json
{
  "order_type": "TABLE",
  "table_id": "uuid",
  "command_id": null,
  "customer_id": null,
  "items": [
    { "product_id": "uuid", "quantity": 2, "notes": "sem cebola" }
  ]
}
```

`GET /api/orders/{id}`  
`POST /api/orders/{id}/items`  
`PATCH /api/orders/{id}/status` `{ "status": "IN_PREPARATION" }`  
`DELETE /api/orders/{id}/items/{itemId}` somente se pedido `OPEN`

#### Caixa

`GET /api/cash/current`  
`POST /api/cash/open` `{ "cash_register_id": "uuid", "opening_balance": 150.00 }`  
`POST /api/cash/movements` `{ "type": "BLEED|SUPPLEMENT", "amount": 50.00, "description": "sangria" }`  
`GET /api/cash/history`  
`POST /api/cash/close` `{ "closing_balance": 430.00, "notes": "" }`

#### Pagamentos

`POST /api/orders/{id}/payments`

```json
{
  "payment_method": "CASH|CREDIT_CARD|DEBIT_CARD|PIX|VOUCHER",
  "amount": 57.80
}
```

#### Dashboard

`GET /api/dashboard/metrics`

```json
{
  "status": "success",
  "data": {
    "open_orders": 4,
    "occupied_tables": 6,
    "today_revenue": 1520.40,
    "open_shift": true
  },
  "message": "OK"
}
```

#### Estoque (mínimo)

`GET /api/inventory-items`  
`POST /api/inventory-items`  
`PUT /api/products/{id}/ingredients` — substitui a ficha

```json
{
  "items": [
    { "inventory_item_id": "uuid", "quantity": 0.180, "unit": "kg" }
  ]
}
```

### 8.3 Status HTTP

- 200, 201, 400, 401, 403, 404, 409 (conflito de caixa/mesa), 422, 429, 500

## 9. Modelo de dados (MVP)

DDL executável: `databases/simplyfood-db.sql`.

Relacionamentos:

```text
establishments
  ├── users
  ├── customers
  ├── categories
  │     └── products
  │           └── product_ingredients → inventory_items
  ├── tables
  │     └── commands
  ├── cash_registers
  │     └── cash_register_shifts
  │           ├── cash_movements
  │           └── orders
  │                 ├── order_items → products
  │                 ├── order_status_history
  │                 └── payments
  ├── inventory_items
  └── stock_movements
```

### 9.1 establishments

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK, UUID |
| name | varchar(150) | obrigatório |
| document | varchar(30) | nullable |
| phone | varchar(20) | nullable |
| address | text | nullable |
| status | enum(ACTIVE, INACTIVE) | default ACTIVE |
| created_at, updated_at, deleted_at | timestamp | soft delete |

### 9.2 users

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments, obrigatório |
| name | varchar(150) | obrigatório |
| email | varchar(255) | único |
| password | varchar(255) | hash Laravel |
| role | enum(ADMIN, MANAGER, CASHIER, WAITER, KITCHEN) | obrigatório |
| status | enum(ACTIVE, INACTIVE) | default ACTIVE |
| created_at, updated_at, deleted_at | timestamp | |

### 9.3 customers

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| name | varchar(150) | obrigatório |
| phone | varchar(20) | index |
| email | varchar(255) | nullable |
| document | varchar(30) | nullable |
| address | text | nullable |
| created_at, updated_at, deleted_at | timestamp | |

### 9.4 categories

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| name | varchar(100) | obrigatório |
| sort_order | int | default 0 |
| active | boolean | default true |
| created_at, updated_at, deleted_at | timestamp | |

### 9.5 products

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| category_id | char(36) | FK categories, nullable |
| name | varchar(150) | obrigatório |
| description | text | nullable |
| price | decimal(10,2) | obrigatório |
| cost_price | decimal(10,2) | default 0 |
| sku | varchar(50) | nullable |
| is_available | boolean | default true |
| preparation_time_minutes | int | default 0 |
| created_at, updated_at, deleted_at | timestamp | |

### 9.6 tables

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| number | int | único por estabelecimento |
| capacity | int | default 4 |
| status | enum(FREE, OCCUPIED, RESERVED, BILLING) | default FREE |
| created_at, updated_at, deleted_at | timestamp | |

### 9.7 commands

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| table_id | char(36) | FK tables, nullable |
| code | varchar(50) | único por estabelecimento |
| status | enum(FREE, OPEN, BLOCKED, CLOSED) | default FREE |
| created_at, updated_at, deleted_at | timestamp | |

### 9.8 cash_registers

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| name | varchar(100) | obrigatório |
| location | varchar(150) | nullable |
| is_active | boolean | default true |
| created_at, updated_at, deleted_at | timestamp | |

### 9.9 cash_register_shifts

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| cash_register_id | char(36) | FK cash_registers |
| user_id | char(36) | FK users (quem abriu) |
| opening_balance | decimal(10,2) | obrigatório |
| closing_balance | decimal(10,2) | nullable até fechar |
| opened_at | timestamp | |
| closed_at | timestamp | nullable |
| status | enum(OPEN, CLOSED) | default OPEN |
| notes | text | nullable |
| created_at, updated_at, deleted_at | timestamp | |

Um turno `OPEN` por `cash_register_id`.

### 9.10 cash_movements

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| cash_register_shift_id | char(36) | FK cash_register_shifts |
| user_id | char(36) | FK users |
| type | enum(INITIAL_BALANCE, SALE, BLEED, SUPPLEMENT, REFUND) | |
| amount | decimal(10,2) | obrigatório |
| description | varchar(255) | nullable |
| created_at | timestamp | sem soft delete |

### 9.11 orders

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| cash_register_shift_id | char(36) | FK cash_register_shifts, obrigatório |
| waiter_id | char(36) | FK users, nullable |
| customer_id | char(36) | FK customers, nullable |
| table_id | char(36) | FK tables; obrigatório se TABLE ou COMMAND |
| command_id | char(36) | FK commands; obrigatório se COMMAND |
| order_type | enum(TABLE, COMMAND, COUNTER) | sem DELIVERY |
| status | enum(OPEN, IN_PREPARATION, READY, DELIVERED, CLOSED, CANCELLED) | default OPEN |
| subtotal | decimal(10,2) | default 0 |
| discount | decimal(10,2) | default 0 |
| total_amount | decimal(10,2) | default 0 |
| created_at, updated_at, deleted_at | timestamp | |

### 9.12 order_items

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | |
| order_id | char(36) | FK orders |
| product_id | char(36) | FK products |
| quantity | int | > 0 |
| unit_price | decimal(10,2) | copiado do produto na inclusão |
| total_price | decimal(10,2) | quantity * unit_price |
| status | enum(WAITING, PREPARING, READY, DELIVERED, CANCELLED) | default WAITING |
| notes | varchar(255) | nullable |
| created_at, deleted_at | timestamp | |

### 9.13 order_status_history

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| order_id | char(36) | FK orders |
| changed_by | char(36) | FK users, nullable |
| from_status | enum de order.status | nullable na abertura |
| to_status | enum de order.status | obrigatório |
| changed_at | timestamp | |
| notes | text | nullable |

### 9.14 payments

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| order_id | char(36) | FK orders |
| cash_register_shift_id | char(36) | FK shifts, nullable |
| payment_method | enum(CASH, CREDIT_CARD, DEBIT_CARD, PIX, VOUCHER) | |
| amount | decimal(10,2) | > 0 |
| status | enum(CONFIRMED, REFUNDED, FAILED) | default CONFIRMED |
| transaction_code | varchar(100) | nullable |
| created_at, deleted_at | timestamp | |

### 9.15 inventory_items

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| name | varchar(150) | obrigatório |
| unit | varchar(30) | ex: kg, un, L |
| category | varchar(100) | nullable |
| stock_quantity | decimal(10,3) | default 0 |
| min_stock | decimal(10,3) | default 0 |
| cost_price | decimal(10,2) | default 0 |
| created_at, updated_at, deleted_at | timestamp | |

### 9.16 product_ingredients

| Coluna | Tipo | Regras |
| --- | --- | --- |
| product_id | char(36) | PK composta, FK products |
| inventory_item_id | char(36) | PK composta, FK inventory_items |
| quantity | decimal(10,3) | consumo por unidade do produto |
| unit | varchar(30) | nullable |
| yield_percentage | decimal(5,2) | default 100 |

### 9.17 stock_movements

| Coluna | Tipo | Regras |
| --- | --- | --- |
| id | char(36) | PK |
| establishment_id | char(36) | FK establishments |
| inventory_item_id | char(36) | FK inventory_items |
| user_id | char(36) | FK users, nullable |
| order_id | char(36) | FK orders, nullable (preenchido na baixa) |
| type | enum(IN, OUT, ADJUSTMENT, TRANSFER) | |
| quantity | decimal(10,3) | obrigatório |
| unit_cost | decimal(10,2) | nullable |
| reference | varchar(100) | nullable |
| notes | text | nullable |
| created_at | timestamp | sem soft delete |

### 9.18 Script DDL completo

Requer MySQL 8.0.16+ (`CHECK` enforced).

```sql
-- SimplyFood schema MVP 1.0.0
-- Fonte alinhada a docs/SPEC.md
-- CHECK constraints exigem MySQL 8.0.16+

CREATE TABLE `establishments` (
  `id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `document` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `establishments_name_chk` CHECK (CHAR_LENGTH(`name`) >= 2)
);

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','MANAGER','CASHIER','WAITER','KITCHEN') NOT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_establishment_id_index` (`establishment_id`),
  CONSTRAINT `users_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `users_name_chk` CHECK (CHAR_LENGTH(`name`) >= 2),
  CONSTRAINT `users_email_chk` CHECK (`email` LIKE '%_@_%.__%')
);

CREATE TABLE `customers` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `document` varchar(30) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_establishment_id_index` (`establishment_id`),
  KEY `customers_phone_index` (`phone`),
  UNIQUE KEY `customers_establishment_phone_unique` (`establishment_id`,`phone`),
  CONSTRAINT `customers_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `customers_name_chk` CHECK (CHAR_LENGTH(`name`) >= 2),
  CONSTRAINT `customers_email_chk` CHECK (`email` IS NULL OR `email` LIKE '%_@_%.__%'),
  CONSTRAINT `customers_phone_chk` CHECK (`phone` IS NULL OR CHAR_LENGTH(`phone`) >= 8)
);

CREATE TABLE `categories` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_establishment_id_index` (`establishment_id`),
  CONSTRAINT `categories_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `categories_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `categories_sort_chk` CHECK (`sort_order` >= 0),
  CONSTRAINT `categories_active_chk` CHECK (`active` IN (0, 1))
);

CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `category_id` char(36) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sku` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `preparation_time_minutes` int NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_establishment_id_index` (`establishment_id`),
  KEY `products_category_id_index` (`category_id`),
  UNIQUE KEY `products_establishment_sku_unique` (`establishment_id`,`sku`),
  CONSTRAINT `products_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `products_price_chk` CHECK (`price` >= 0 AND `cost_price` >= 0),
  CONSTRAINT `products_prep_chk` CHECK (`preparation_time_minutes` >= 0),
  CONSTRAINT `products_available_chk` CHECK (`is_available` IN (0, 1))
);

CREATE TABLE `tables` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `number` int NOT NULL,
  `capacity` int NOT NULL DEFAULT 4,
  `status` enum('FREE','OCCUPIED','RESERVED','BILLING') NOT NULL DEFAULT 'FREE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tables_establishment_number_unique` (`establishment_id`,`number`),
  CONSTRAINT `tables_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `tables_number_chk` CHECK (`number` > 0),
  CONSTRAINT `tables_capacity_chk` CHECK (`capacity` > 0)
);

CREATE TABLE `commands` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `table_id` char(36) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `status` enum('FREE','OPEN','BLOCKED','CLOSED') NOT NULL DEFAULT 'FREE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `commands_establishment_code_unique` (`establishment_id`,`code`),
  KEY `commands_table_id_index` (`table_id`),
  CONSTRAINT `commands_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `commands_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`),
  CONSTRAINT `commands_code_chk` CHECK (CHAR_LENGTH(`code`) >= 1),
  CONSTRAINT `commands_open_table_chk` CHECK (`status` <> 'OPEN' OR `table_id` IS NOT NULL)
);

CREATE TABLE `cash_registers` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_registers_establishment_id_index` (`establishment_id`),
  CONSTRAINT `cash_registers_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `cash_registers_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `cash_registers_active_chk` CHECK (`is_active` IN (0, 1))
);

CREATE TABLE `cash_register_shifts` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `cash_register_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `opening_balance` decimal(10,2) NOT NULL,
  `closing_balance` decimal(10,2) DEFAULT NULL,
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL,
  `status` enum('OPEN','CLOSED') NOT NULL DEFAULT 'OPEN',
  `notes` text,
  `open_register_key` char(36) GENERATED ALWAYS AS (IF(`status` = 'OPEN' AND `deleted_at` IS NULL, `cash_register_id`, NULL)) STORED,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_establishment_status_index` (`establishment_id`,`status`),
  UNIQUE KEY `shifts_one_open_per_register` (`open_register_key`),
  CONSTRAINT `shifts_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `shifts_register_fk` FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers` (`id`),
  CONSTRAINT `shifts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `shifts_opening_balance_chk` CHECK (`opening_balance` >= 0),
  CONSTRAINT `shifts_closing_balance_chk` CHECK (`closing_balance` IS NULL OR `closing_balance` >= 0),
  CONSTRAINT `shifts_status_shape_chk` CHECK (
    (`status` = 'OPEN' AND `closed_at` IS NULL AND `closing_balance` IS NULL)
    OR (`status` = 'CLOSED' AND `closed_at` IS NOT NULL AND `closing_balance` IS NOT NULL)
  )
);

CREATE TABLE `cash_movements` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `cash_register_shift_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `type` enum('INITIAL_BALANCE','SALE','BLEED','SUPPLEMENT','REFUND') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `movements_shift_id_index` (`cash_register_shift_id`),
  CONSTRAINT `movements_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `movements_shift_fk` FOREIGN KEY (`cash_register_shift_id`) REFERENCES `cash_register_shifts` (`id`),
  CONSTRAINT `movements_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cash_movements_amount_chk` CHECK (`amount` > 0)
);

CREATE TABLE `orders` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `cash_register_shift_id` char(36) NOT NULL,
  `waiter_id` char(36) DEFAULT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `table_id` char(36) DEFAULT NULL,
  `command_id` char(36) DEFAULT NULL,
  `order_type` enum('TABLE','COMMAND','COUNTER') NOT NULL,
  `status` enum('OPEN','IN_PREPARATION','READY','DELIVERED','CLOSED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_establishment_status_index` (`establishment_id`,`status`),
  KEY `orders_shift_id_index` (`cash_register_shift_id`),
  CONSTRAINT `orders_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `orders_shift_fk` FOREIGN KEY (`cash_register_shift_id`) REFERENCES `cash_register_shifts` (`id`),
  CONSTRAINT `orders_waiter_fk` FOREIGN KEY (`waiter_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `orders_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`),
  CONSTRAINT `orders_command_fk` FOREIGN KEY (`command_id`) REFERENCES `commands` (`id`),
  CONSTRAINT `orders_amounts_chk` CHECK (`subtotal` >= 0 AND `discount` >= 0 AND `total_amount` >= 0 AND `discount` <= `subtotal`),
  CONSTRAINT `orders_type_shape_chk` CHECK (
    (`order_type` = 'TABLE' AND `table_id` IS NOT NULL AND `command_id` IS NULL)
    OR (`order_type` = 'COMMAND' AND `table_id` IS NOT NULL AND `command_id` IS NOT NULL)
    OR (`order_type` = 'COUNTER' AND `table_id` IS NULL AND `command_id` IS NULL)
  )
);

CREATE TABLE `order_items` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('WAITING','PREPARING','READY','DELIVERED','CANCELLED') NOT NULL DEFAULT 'WAITING',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `order_items_qty_chk` CHECK (`quantity` > 0),
  CONSTRAINT `order_items_prices_chk` CHECK (`unit_price` >= 0 AND `total_price` >= 0),
  CONSTRAINT `order_items_total_chk` CHECK (`total_price` = `quantity` * `unit_price`)
);

CREATE TABLE `order_status_history` (
  `id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `from_status` enum('OPEN','IN_PREPARATION','READY','DELIVERED','CLOSED','CANCELLED') DEFAULT NULL,
  `to_status` enum('OPEN','IN_PREPARATION','READY','DELIVERED','CLOSED','CANCELLED') NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `order_status_history_order_id_index` (`order_id`),
  CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_status_history_change_chk` CHECK (`from_status` IS NULL OR `from_status` <> `to_status`)
);

CREATE TABLE `payments` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `cash_register_shift_id` char(36) DEFAULT NULL,
  `payment_method` enum('CASH','CREDIT_CARD','DEBIT_CARD','PIX','VOUCHER') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('CONFIRMED','REFUNDED','FAILED') NOT NULL DEFAULT 'CONFIRMED',
  `transaction_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_index` (`order_id`),
  CONSTRAINT `payments_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `payments_amount_chk` CHECK (`amount` > 0)
);

CREATE TABLE `inventory_items` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock_quantity` decimal(10,3) NOT NULL DEFAULT 0.000,
  `min_stock` decimal(10,3) NOT NULL DEFAULT 0.000,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_items_establishment_id_index` (`establishment_id`),
  CONSTRAINT `inventory_items_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `inventory_items_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `inventory_items_unit_chk` CHECK (CHAR_LENGTH(`unit`) >= 1),
  CONSTRAINT `inventory_items_min_stock_chk` CHECK (`min_stock` >= 0),
  CONSTRAINT `inventory_items_cost_chk` CHECK (`cost_price` >= 0)
);

CREATE TABLE `product_ingredients` (
  `product_id` char(36) NOT NULL,
  `inventory_item_id` char(36) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `yield_percentage` decimal(5,2) NOT NULL DEFAULT 100.00,
  PRIMARY KEY (`product_id`,`inventory_item_id`),
  CONSTRAINT `product_ingredients_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_ingredients_item_fk` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `product_ingredients_qty_chk` CHECK (`quantity` > 0),
  CONSTRAINT `product_ingredients_yield_chk` CHECK (`yield_percentage` > 0 AND `yield_percentage` <= 100)
);

CREATE TABLE `stock_movements` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `inventory_item_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `order_id` char(36) DEFAULT NULL,
  `type` enum('IN','OUT','ADJUSTMENT','TRANSFER') NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `stock_movements_item_id_index` (`inventory_item_id`),
  CONSTRAINT `stock_movements_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `stock_movements_item_fk` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `stock_movements_qty_chk` CHECK (`quantity` > 0),
  CONSTRAINT `stock_movements_cost_chk` CHECK (`unit_cost` IS NULL OR `unit_cost` >= 0)
);
```


### 9.19 Triggers (validação complexa)

Arquivo: `databases/simplyfood-triggers.sql`.

| Trigger | Evento | Garante |
| --- | --- | --- |
| trg_products_bi/bu | products | categoria do mesmo estabelecimento; tenant imutável |
| trg_commands_bi/bu | commands | mesa do mesmo estabelecimento |
| trg_shifts_bi/bu | cash_register_shifts | caixa/usuário do tenant; turno fechado imutável; sem pedidos ativos no close |
| trg_movements_bi | cash_movements | turno OPEN do mesmo tenant |
| trg_orders_bi | orders | turno OPEN; waiter/customer/table/command do mesmo tenant |
| trg_orders_bu | orders | transição de status; CLOSED só se pago; estoque suficiente |
| trg_orders_au_stock | orders AFTER UPDATE | baixa `stock_movements` + decrementa `inventory_items` |
| trg_order_items_bi/bu | order_items | item só em pedido OPEN; produto disponível do tenant |
| trg_payments_bi | payments | pedido e turno abertos do tenant |
| trg_ingredients_bi | product_ingredients | produto e ingrediente do mesmo tenant |
| trg_stock_bi | stock_movements | item do mesmo tenant |

Erro padrão: `SIGNAL SQLSTATE '45000'`.

## 10. Frontend

- Vue 3 + TypeScript + Pinia + Vue Router + Tailwind
- Um módulo por feature do MVP
- Services HTTP isolados; páginas sem regra de negócio
- Guard de rota por autenticação e role
- Fonte global: Inter
- Login responsivo em duas colunas: atmosfera visual à esquerda e formulário à direita
- Não usar no login os cards promocionais `01`, `100%`, `MVP` nem o chip “Gestão para restaurantes”
- Sem dependência de realtime no MVP; polling opcional no dashboard (30s)

Rotas UI:

| Rota | Roles |
| --- | --- |
| `/login` | pública |
| `/dashboard` | ADMIN, MANAGER, CASHIER, WAITER |
| `/customers` | ADMIN, MANAGER, WAITER |
| `/products` | ADMIN, MANAGER |
| `/tables` | ADMIN, MANAGER, WAITER |
| `/orders` | ADMIN, MANAGER, CASHIER, WAITER |
| `/cash` | ADMIN, MANAGER, CASHIER |
| `/inventory` | ADMIN, MANAGER |

## 11. Qualidade

- Pest no backend; Vitest no frontend
- O schema canônico e as migrations do backend têm como alvo MySQL
- Pest em SQLite não é critério de aceite quando houver incompatibilidade do schema MySQL, como `ENUM`
- Cobertura mínima 80% em Application/Domain
- Todo endpoint do MVP tem teste de autorização por role e de isolamento por `establishment_id`
- Teste obrigatório das regras 2, 3, 10, 11 e 12

## 12. Ordem de implementação

1. Schema + seed (1 estabelecimento, usuários por role, 2 mesas, 3 produtos)
2. Auth Sanctum
3. Categories/Products
4. Customers
5. Cash open/current/close
6. Tables/Commands
7. Orders + items + status
8. Payments + close order + stock
9. Dashboard
10. SPA nas rotas acima

## 13. O que o agente não deve fazer

- Não inventar WhatsApp, delivery, KDS, Inertia ou Reverb no MVP
- Não isolar dados por `user_id` no lugar de `establishment_id`
- Não criar `POST /api/customers` público
- Não usar papéis `OPERATOR`
- Não nomear mesas como `restaurant_tables` nem caixa como `cash_closings` / `cash_transactions`
- Não implementar `order_type = DELIVERY`
