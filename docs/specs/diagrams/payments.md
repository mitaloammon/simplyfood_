# Registrar pagamento

## Ator e papéis permitidos / 403

`ADMIN`, `MANAGER`, `CASHIER`. `WAITER` e `KITCHEN` recebem 403.

## Pré-condições

Bearer válido. O pedido pertence ao tenant, não está `CLOSED`/`CANCELLED` e o turno ligado ao pedido continua `OPEN`. O campo é `payment_method`; `method` não é aceito.

## Rota canônica

`POST /api/orders/{order}/payments`

## Sequência

```mermaid
sequenceDiagram
    actor A as SPA ou curl
    participant R as POST /api/orders/{order}/payments
    participant C as PaymentController
    participant S as PaymentService
    participant DB as MySQL
    A->>R: Bearer + payment_method + amount
    R->>C: store(StorePaymentRequest, order)
    C->>S: create(user, order, dados validados)
    S->>DB: Busca pedido e turno OPEN por establishment_id
    DB-->>S: pedido e turno
    S->>DB: Cria payment CONFIRMED no tenant
    S->>DB: Soma pagamentos CONFIRMED do pedido no tenant
    DB-->>S: paid_amount
    S-->>C: payment, paid_amount, remaining_amount, fully_paid
    C-->>A: envelope JSON 201
```

## Pós-condição

Pagamento `CONFIRMED` é criado. `fully_paid` é verdadeiro quando `paid_amount >= total_amount`; portanto também é verdadeiro na igualdade pedida pelo contrato.

## Erros existentes

- 401: Bearer ausente ou inválido.
- 403: papel sem permissão.
- 409: pedido encerrado ou turno do pedido não está aberto.
- 422: `payment_method` ausente/fora da enumeração, uso de `method`, ou `amount` inválido.
