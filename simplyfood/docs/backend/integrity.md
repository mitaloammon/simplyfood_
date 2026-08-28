# Restrições de integridade — SimplyFood MVP

Complemento do [SPEC.md](../SPEC.md) seção 9. Em conflito, o SPEC prevalece; este arquivo detalha o que o banco deve recusar.

## 1. O que o DDL já garante

### Chaves primárias
Toda tabela tem PK. `product_ingredients` usa PK composta `(product_id, inventory_item_id)`.

### Unicidade
| Tabela | Unique | Efeito |
| --- | --- | --- |
| users | email | e-mail global, não por estabelecimento |
| tables | (establishment_id, number) | número de mesa único na loja |
| commands | (establishment_id, code) | código de comanda único na loja |

### Foreign keys (RESTRICT implícito)

| Filho | Pai |
| --- | --- |
| users, customers, categories, products, tables, commands, cash_registers, shifts, movements, orders, payments, inventory_items, stock_movements | establishments |
| products | categories |
| commands | tables |
| cash_register_shifts | cash_registers, users |
| cash_movements | cash_register_shifts, users |
| orders | shifts, users (waiter), customers, tables, commands |
| order_items | orders, products |
| order_status_history | orders |
| payments | orders |
| product_ingredients | products, inventory_items |
| stock_movements | inventory_items |

Não há `ON DELETE CASCADE`. Excluir estabelecimento, produto ou pedido com dependentes falha no banco.

### Domínios (ENUM)
Papéis, status de mesa/comanda/pedido/item, tipos de movimento e pagamento estão fechados no DDL.

## 2. Buracos de integridade

### 2.1 Tenant cruzado (crítico)
As FKs não exigem o mesmo `establishment_id` no pai e no filho.

Um pedido da loja A pode apontar para mesa da loja B, produto da loja B ou turno da loja B. O banco aceita. Isso só cai se a aplicação filtrar — insuficiente.

Correção: chave única auxiliar `(id, establishment_id)` no pai e FK composta no filho.

### 2.2 FKs ausentes
- `order_items.establishment_id` → sem FK
- `order_status_history.changed_by` → sem FK para `users`
- `payments.cash_register_shift_id` → sem FK
- `stock_movements.user_id` e `stock_movements.order_id` → sem FK

### 2.3 CHECK e UNIQUE no DDL (MySQL 8.0.16+)

Já no `databases/simplyfood-db.sql`: valores (preço, quantidade, amount), forma do pedido, comanda OPEN com mesa, formato do turno, um OPEN por caixa, SKU/telefone únicos por loja, `total_price = quantity * unit_price`.

Ainda só na aplicação: pedido exige turno OPEN; CLOSED exige pagamento; transição de status; tenant cruzado (FK composta); override de estoque negativo.

### 2.4 Soft delete vs FK
`deleted_at` não impede referência. Pedido pode apontar para produto ou cliente “apagado”. Integridade de negócio fica na Application Layer.

### 2.5 E-mail global
`users.email` único no sistema inteiro. Correto para login Sanctum. Impede o mesmo e-mail em dois estabelecimentos.

### 2.6 ENUM no MySQL
ENUM não é restrição forte (inserção inválida pode virar `''` conforme sql_mode). Preferir lookup ou CHECK em MySQL 8.0.16+.

## 3. Restrições recomendadas no DDL

```sql
-- Unicidade de negócio
ALTER TABLE products
  ADD UNIQUE KEY products_establishment_sku_unique (establishment_id, sku);

ALTER TABLE customers
  ADD UNIQUE KEY customers_establishment_phone_unique (establishment_id, phone);

-- Um turno aberto por caixa (coluna gerada)
ALTER TABLE cash_register_shifts
  ADD COLUMN open_register_key char(36)
    GENERATED ALWAYS AS (IF(status = 'OPEN' AND deleted_at IS NULL, cash_register_id, NULL)) STORED,
  ADD UNIQUE KEY shifts_one_open_per_register (open_register_key);

-- Sinais
ALTER TABLE products
  ADD CONSTRAINT products_price_chk CHECK (price >= 0 AND cost_price >= 0),
  ADD CONSTRAINT products_prep_chk CHECK (preparation_time_minutes >= 0);

ALTER TABLE order_items
  ADD CONSTRAINT order_items_qty_chk CHECK (quantity > 0 AND unit_price >= 0 AND total_price >= 0);

ALTER TABLE payments
  ADD CONSTRAINT payments_amount_chk CHECK (amount > 0);

ALTER TABLE cash_movements
  ADD CONSTRAINT cash_movements_amount_chk CHECK (amount > 0);

ALTER TABLE inventory_items
  ADD CONSTRAINT inventory_min_stock_chk CHECK (min_stock >= 0);

-- Tipo de pedido vs mesa/comanda
ALTER TABLE orders
  ADD CONSTRAINT orders_type_shape_chk CHECK (
    (order_type = 'TABLE' AND table_id IS NOT NULL AND command_id IS NULL)
    OR (order_type = 'COMMAND' AND table_id IS NOT NULL AND command_id IS NOT NULL)
    OR (order_type = 'COUNTER' AND table_id IS NULL AND command_id IS NULL)
  );
```

FK composta (exemplo produtos → categorias):

```sql
ALTER TABLE categories
  ADD UNIQUE KEY categories_id_establishment_unique (id, establishment_id);

ALTER TABLE products
  ADD CONSTRAINT products_category_tenant_fk
    FOREIGN KEY (category_id, establishment_id)
    REFERENCES categories (id, establishment_id);
```

Repetir o padrão em: commands→tables, orders→tables/commands/shifts/customers/waiters, order_items→orders/products, payments→orders, product_ingredients e stock_movements.

## 4. O que permanece na aplicação

Mesmo com o DDL reforçado, estas regras não cabem bem em CHECK:

- transição de status (`OPEN` → `IN_PREPARATION` → …)
- fechar pedido somente se `SUM(payments.amount)` dos `CONFIRMED` >= `total_amount`
- fechar turno somente se não houver pedido ativo
- recusar estoque negativo no `CLOSED` (salvo ADMIN/MANAGER)
- soft delete sem quebrar histórico financeiro
- autorização por papel

Essas regras exigem transação + lock (`SELECT … FOR UPDATE` no turno e na mesa).

## 5. Política de exclusão

| Recurso | Política |
| --- | --- |
| Estabelecimento, pedido, turno, pagamento, movimento | RESTRICT + soft delete |
| waiter_id, customer_id em order | SET NULL no hard delete (não usar no MVP) |
| Produto com item de pedido | RESTRICT; desativar `is_available` |
| Mesa com comanda/pedido | RESTRICT |

Nunca usar CASCADE em tabelas financeiras.
