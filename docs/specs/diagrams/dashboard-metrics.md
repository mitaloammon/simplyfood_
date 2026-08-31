# Consultar métricas do dashboard

## Ator e papéis permitidos / 403

`ADMIN`, `MANAGER`, `CASHIER`, `WAITER`. `KITCHEN` recebe 403.

## Pré-condições

Bearer Sanctum válido e usuário ligado a um `establishment_id`.

## Rota canônica

`GET /api/dashboard/metrics`

## Sequência

```mermaid
sequenceDiagram
    actor A as SPA ou curl
    participant R as GET /api/dashboard/metrics
    participant C as DashboardController
    participant S as DashboardMetricsService
    participant DB as MySQL
    A->>R: GET + Bearer
    R->>C: metrics(request)
    C->>S: get(user)
    S->>DB: Agrega pedidos abertos por establishment_id
    S->>DB: Conta mesas OCCUPIED por establishment_id
    S->>DB: Soma pagamentos CONFIRMED de hoje por establishment_id
    S->>DB: Verifica turno OPEN por establishment_id
    DB-->>S: agregados do tenant
    S-->>C: métricas
    C-->>A: envelope JSON 200
```

## Pós-condição

Nenhum dado é alterado. A resposta contém `open_orders`, `occupied_tables`, `today_revenue` e `open_shift` somente da loja autenticada.

## Erros existentes

- 401: Bearer ausente ou inválido.
- 403: papel sem permissão.
- 409 e 422: não são produzidos pelas regras desta rota.
