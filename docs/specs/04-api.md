# Registered API Routes

This inventory is derived from `backend/routes/api.php`. It records route declarations and middleware, not full request/response schemas or runtime results. The API base path is `/api` as configured by the Laravel application; active deployment routing has not been verified.

## Middleware groups

- `GET /health`: public.
- `POST /auth/login`: public, `throttle:login`.
- `POST /auth/logout`: `auth:sanctum` only.
- `GET /auth/me`: `auth:sanctum`, `account.active`.
- Commercial routes: `auth:sanctum`, `account.active`, `installation.ready`, then route-specific `throttle:60,1` and `role` allowlist.

`account.active` and `installation.ready` are registered middleware aliases. See [architecture and readiness](03-arquitetura.md#authentication-and-authorization). The precise order is the order declared in the nested route groups, subject to Laravel middleware resolution; runtime ordering has not been exercised here.

## Routes by capability allowlist

### ADMIN, MANAGER, CASHIER, WAITER

- Categories: `GET /categories`
- Products: `GET /products`, `GET /products/{product}`
- Customers: `GET /customers`, `GET /customers/{customer}`
- Tables: `GET /tables`
- Commands: `GET /commands`
- Dashboard: `GET /dashboard/metrics`
- Orders: `GET /orders`, `POST /orders`, `GET /orders/{order}`, `POST /orders/{order}/items`, `PATCH /orders/{order}/status`, `DELETE /orders/{order}/items/{item}`

### ADMIN, MANAGER

- Categories: `POST /categories`, `PATCH /categories/{category}`, `DELETE /categories/{category}`
- Products: `POST /products`, `PATCH /products/{product}`, `DELETE /products/{product}`

### ADMIN, MANAGER, WAITER

- Customers: `POST /customers`, `PATCH /customers/{customer}`, `DELETE /customers/{customer}`
- Tables: `POST /tables`, `PATCH /tables/{table}`, `PATCH /tables/{table}/status`
- Commands: `POST /commands`, `PATCH /commands/{command}/status`

### ADMIN, MANAGER, CASHIER

- Cash: `POST /cash/open`, `GET /cash/current`, `POST /cash/movements`, `GET /cash/history`, `POST /cash/close`
- Payments: `POST /orders/{order}/payments`

`KITCHEN` is present in the role middleware allowlist but is not included in these commercial route groups. No user management or inventory-management API route is declared in this file.

## Authentication behavior observed in controller

Login validates email/password, requires an `ACTIVE` user, and issues a Sanctum token. `/auth/me` returns user data and a separately computed `installation_ready` boolean. Logout attempts to delete only the current access token. These static facts do not establish token expiration configuration or global revocation.

## Contract gaps

The route file alone does not define all validation, response schemas, transaction boundaries, or tenant guarantees. Consult Form Requests, controllers, application services, models, and SQL triggers before relying on operation semantics. There are no registered routes here for RETAIL sales, returns, refunds, stock ledger operations, or gateway payments.
