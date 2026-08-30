# Testes e aceite

## Ambiente de aceite

O schema canônico usa MySQL 8.4 e inclui `ENUM`, `CHECK` e triggers. O aceite funcional ocorre por curl contra a API no Compose.

Pest com SQLite em memória não é critério de aceite. A incompatibilidade de `ENUM` e `CHECK` não autoriza reescrever o schema MySQL.

`migrate:fresh` apaga os dados. Use apenas em instalação ou reset intencional. Não faz parte do smoke em uma base validada.

## Smoke mínimo

1. `GET /api/health`.
2. `POST /api/auth/login` e obtenha o Bearer token.
3. `POST /api/cash/open`.
4. `POST /api/orders` com `order_type: COUNTER`.
5. `POST /api/orders/{id}/payments` com `payment_method` e `amount`.

Pedido sem turno de caixa `OPEN` deve falhar. Pagamento total deve permitir o fechamento conforme [Regras](02-regras.md#regras-de-negócio).

## Suíte automatizada

- Pest no backend.
- Vitest no frontend.
- Cobertura mínima de 80% em Application e Domain.
- Todo endpoint do MVP deve testar autorização por papel e isolamento por `establishment_id`.
- As regras de turno único, turno aberto para pedido, pagamento total, fechamento de caixa e baixa de estoque exigem teste.
