# Backend Agent Guide

Fonte oficial: [SPEC.md](SPEC.md)

Antes de alterar código, leia SPEC.md. Não invente endpoint, papel, tabela ou regra que não esteja lá.

## Regras

1. Isolar todas as queries por `establishment_id` do usuário autenticado.
2. Autenticação: Laravel Sanctum.
3. Papéis: `ADMIN`, `MANAGER`, `CASHIER`, `WAITER`, `KITCHEN`.
4. Controllers finos. Regras no Application/Domain.
5. `POST /api/customers` é autenticado.
6. Não implementar WhatsApp, delivery, KDS, Inertia ou Reverb no MVP.
7. Nomes canônicos: `tables`, `commands`, `cash_registers`, `cash_register_shifts`, `cash_movements`, `payments`, `inventory_items`, `product_ingredients`.
8. Se o comportamento mudar, atualize SPEC.md primeiro.
