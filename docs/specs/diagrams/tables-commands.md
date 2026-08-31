# Gerenciar mesas e comandas

## Ator e papéis permitidos / 403

Criar mesa, abrir comanda e alterar status: `ADMIN`, `MANAGER`, `WAITER`. `CASHIER` e `KITCHEN` recebem 403. As consultas permitem também `CASHIER`, mas não fazem parte das rotas desenhadas abaixo.

## Pré-condições

Bearer válido. Mesa e comanda pertencem ao tenant. A abertura exige `code` único por loja e `table_id`; a mesa não pode estar `BILLING`.

## Rotas canônicas

- `POST /api/tables`
- `PATCH /api/tables/{table}/status`
- `POST /api/commands`
- `PATCH /api/commands/{command}/status`

## Sequência

```mermaid
sequenceDiagram
    actor A as SPA ou curl
    participant R as Rotas de mesas/comandas
    participant C as TableController / CommandController
    participant S as TableService / CommandService
    participant DB as MySQL
    A->>R: POST mesa/comanda ou PATCH status + Bearer
    R->>C: store(request) ou updateStatus(request, id)
    C->>S: create/open/updateStatus(user, dados)
    S->>DB: Query por establishment_id
    alt Criar mesa
        DB-->>S: mesa criada com status FREE
    else Abrir comanda
        S->>DB: INSERT comanda OPEN e UPDATE mesa OCCUPIED
        DB-->>S: comanda com mesa
    else Alterar status
        DB-->>S: mesa ou comanda atualizada após bloqueios
    end
    S-->>C: model do tenant
    C-->>A: envelope JSON (200 ou 201)
```

## Pós-condição

Mesa nova fica `FREE`. Comanda nova fica `OPEN`, sempre vinculada a `table_id`, e a mesa fica `OCCUPIED`. Alterações de status persistem após as verificações de pedidos/comandas ativos.

## Erros existentes

- 401: Bearer ausente ou inválido.
- 403: papel sem permissão.
- 409: abrir comanda em mesa `BILLING`; liberar mesa com comanda ou pedido ativo; fechar comanda com pedido `OPEN`/`IN_PREPARATION`; reabertura fora das condições do service.
- 422: payload/status inválido, mesa fora da loja ou `code` repetido na loja.
