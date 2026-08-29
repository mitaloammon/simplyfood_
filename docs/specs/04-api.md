# API

Base: `/api`.

O envelope, os headers e os middlewares pertencem a [Arquitetura](03-arquitetura.md#envelope-http). As permissões pertencem a [Regras](02-regras.md#matriz-de-permissões).

As rotas abaixo foram conferidas em `backend/routes/api.php`.

## Auth e health

### `GET /api/health`

Resposta 200:

```json
{
  "status": "success",
  "data": { "ok": true },
  "message": "OK"
}
```

### `POST /api/auth/login`

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

## Rotas autenticadas

`POST /api/auth/logout` → 200  
`GET /api/auth/me` → usuário atual

### Clientes

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

### Produtos e categorias

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

### Mesas

`GET /api/tables`  
`POST /api/tables` `{ "number": 1, "capacity": 4 }`  
`PATCH /api/tables/{id}`  
`PATCH /api/tables/{id}/status` `{ "status": "FREE|OCCUPIED|RESERVED|BILLING" }`

### Comandas

`GET /api/commands`  
`POST /api/commands` `{ "code": "A-12", "table_id": "uuid" }`  
`PATCH /api/commands/{id}/status` `{ "status": "OPEN|CLOSED|BLOCKED" }`

Status persistido: `FREE`, `OPEN`, `BLOCKED`, `CLOSED`.

### Pedidos

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

### Caixa

`GET /api/cash/current`  
`POST /api/cash/open` `{ "cash_register_id": "uuid", "opening_balance": 150.00 }`  
`POST /api/cash/movements` `{ "type": "BLEED|SUPPLEMENT", "amount": 50.00, "description": "sangria" }`  
`GET /api/cash/history`  
`POST /api/cash/close` `{ "closing_balance": 430.00, "notes": "" }`

### Pagamentos

`POST /api/orders/{id}/payments`

```json
{
  "payment_method": "CASH|CREDIT_CARD|DEBIT_CARD|PIX|VOUCHER",
  "amount": 57.80
}
```

### Dashboard

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


## A verificar no código

As rotas de estoque abaixo estavam no SPEC anterior, mas não estão registradas em `backend/routes/api.php`: **a verificar no código**.

A matriz anterior também prevê CRUD de usuários por ADMIN e MANAGER, mas nenhuma rota de usuários está registrada: **a verificar no código**.


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

## Status HTTP

- 200, 201, 400, 401, 403, 404, 409 (conflito de caixa/mesa), 422, 429, 500

