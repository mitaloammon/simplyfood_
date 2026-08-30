# Arquitetura

## Stack e ambiente

- Backend: PHP 8.2+ e Laravel 12.
- Banco canônico: MySQL 8.4.
- Cache e filas: Redis.
- Frontend: Vue 3, TypeScript, Pinia, Vue Router e Tailwind.
- Execução local: Docker Compose no WSL 2.
- SPA em desenvolvimento: Vite, normalmente em `:5173`.
- API: Nginx, normalmente em `:8080`.

O projeto vive no filesystem Linux do WSL. O Compose orquestra a aplicação, mas a SPA pode rodar pelo Vite no Ubuntu.

## Decisões técnicas

| Tema | Contrato |
| --- | --- |
| Identificadores | UUID v4 |
| Tenant | `establishment_id`; a regra pertence a [Regras](02-regras.md#tenant) |
| Interface | Uma SPA Vue. Sem Inertia no MVP. |
| Realtime | Opcional. REST é a fonte de verdade. |
| Exclusão | Soft delete conforme [Regras](02-regras.md#regras-de-negócio) |
| Idioma da API | Mensagens JSON em português e campos em inglês com `snake_case` |

Services HTTP ficam isolados. Páginas não concentram regra de negócio. Um módulo de frontend representa cada feature do MVP.

Nomes canônicos de dados: `tables`, `cash_register_shifts` e `cash_movements`. Não use `restaurant_tables`, `cash_closings` ou `cash_transactions`.

## Autenticação e autorização

Laravel Sanctum emite Bearer token.

Na SPA, o token existe somente em memória durante a sessão da aba. É proibido persistir o token em `localStorage` ou `sessionStorage`. Atualizar a página encerra a sessão local.

Rotas privadas usam `auth:sanctum`. O middleware `role` restringe papéis por grupo. As permissões pertencem a [Regras](02-regras.md#matriz-de-permissões).

O login usa `throttle:login`, com alvo de 5 requisições por minuto por IP e e-mail. Grupos autenticados usam `throttle:60,1`.

A senha usa bcrypt ou Argon pelo Laravel. CORS aceita apenas a origem da SPA.

## Envelope HTTP

Sucesso:

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

Clientes enviam `Accept: application/json`. Rotas privadas também enviam `Authorization: Bearer {token}`.

## Requisitos não funcionais

- Paginação por cursor ou página: padrão 20, máximo 100.
- Tempo alvo da API: p95 menor que 300 ms em listagens simples.
- Índices em `establishment_id`, status, datas e chaves estrangeiras.
- Filas Redis para jobs, como estoque e e-mail futuro.
- Logs de aplicação. Auditoria detalhada fica fora do MVP.
- Health check público sem dados internos.
- Sem dependência de realtime no MVP. O dashboard pode usar polling de 30 segundos.
