# Operar turno de caixa

## Ator e papéis permitidos / 403

`ADMIN`, `MANAGER`, `CASHIER`. `WAITER` e `KITCHEN` recebem 403.

## Pré-condições

Bearer válido. O caixa informado na abertura deve estar ativo e pertencer à loja. Movimento e fechamento exigem turno `OPEN` no tenant.

## Rotas canônicas

- `POST /api/cash/open`
- `GET /api/cash/current`
- `POST /api/cash/movements`
- `POST /api/cash/close`

## Sequência

```mermaid
sequenceDiagram
    actor A as SPA ou curl
    participant R as Rota /api/cash
    participant C as CashController
    participant S as CashService
    participant DB as MySQL
    A->>R: open, current, movements ou close + Bearer
    R->>C: open/current/movement/close(request)
    C->>S: operação(user, dados validados)
    S->>DB: Query transacional por establishment_id
    alt Abrir
        DB-->>S: caixa ativo e ausência de turno OPEN nele
    else Consultar ou movimentar
        DB-->>S: turno OPEN mais recente do tenant
    else Fechar
        DB-->>S: turno OPEN sem pedido ativo vinculado
    end
    S-->>C: turno, movimento ou null
    C-->>A: envelope JSON (200 ou 201)
```

## Pós-condição

A abertura cria turno `OPEN`; o movimento cria `BLEED` ou `SUPPLEMENT`; o fechamento grava saldo, horário e status `CLOSED`. `current` não altera dados.

## Erros existentes

- 401: Bearer ausente ou inválido.
- 403: papel sem permissão.
- 409: caixa já possui turno `OPEN`; movimento/fechamento sem turno aberto; fechamento com pedido ativo.
- 422: caixa inválido/de outra loja/inativo ou payload financeiro inválido.
