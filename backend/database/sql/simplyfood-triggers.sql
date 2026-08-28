-- SimplyFood triggers MVP 1.0.0
-- MySQL 8.0+ · fonte: docs/SPEC.md
-- Rodar depois do DDL.

DROP TRIGGER IF EXISTS trg_products_bi;
DROP TRIGGER IF EXISTS trg_products_bu;
DROP TRIGGER IF EXISTS trg_commands_bi;
DROP TRIGGER IF EXISTS trg_commands_bu;
DROP TRIGGER IF EXISTS trg_shifts_bi;
DROP TRIGGER IF EXISTS trg_shifts_bu;
DROP TRIGGER IF EXISTS trg_movements_bi;
DROP TRIGGER IF EXISTS trg_orders_bi;
DROP TRIGGER IF EXISTS trg_orders_bu;
DROP TRIGGER IF EXISTS trg_order_items_bi;
DROP TRIGGER IF EXISTS trg_order_items_bu;
DROP TRIGGER IF EXISTS trg_payments_bi;
DROP TRIGGER IF EXISTS trg_ingredients_bi;
DROP TRIGGER IF EXISTS trg_stock_bi;
DROP TRIGGER IF EXISTS trg_orders_au_stock;

DELIMITER $$

CREATE TRIGGER trg_products_bi
BEFORE INSERT ON products
FOR EACH ROW
BEGIN
  IF NEW.category_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM categories c
       WHERE c.id = NEW.category_id
         AND c.establishment_id = NEW.establishment_id
         AND c.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Categoria deve pertencer ao mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_products_bu
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
  IF NEW.establishment_id <> OLD.establishment_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'establishment_id de produto nao pode ser alterado';
  END IF;
  IF NEW.category_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM categories c
       WHERE c.id = NEW.category_id
         AND c.establishment_id = NEW.establishment_id
         AND c.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Categoria deve pertencer ao mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_commands_bi
BEFORE INSERT ON commands
FOR EACH ROW
BEGIN
  IF NEW.table_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM tables t
       WHERE t.id = NEW.table_id
         AND t.establishment_id = NEW.establishment_id
         AND t.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mesa deve pertencer ao mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_commands_bu
BEFORE UPDATE ON commands
FOR EACH ROW
BEGIN
  IF NEW.establishment_id <> OLD.establishment_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'establishment_id de comanda nao pode ser alterado';
  END IF;
  IF NEW.table_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM tables t
       WHERE t.id = NEW.table_id
         AND t.establishment_id = NEW.establishment_id
         AND t.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mesa deve pertencer ao mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_shifts_bi
BEFORE INSERT ON cash_register_shifts
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM cash_registers r
    WHERE r.id = NEW.cash_register_id
      AND r.establishment_id = NEW.establishment_id
      AND r.is_active = 1
      AND r.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Caixa invalido para o estabelecimento';
  END IF;
  IF NOT EXISTS (
    SELECT 1 FROM users u
    WHERE u.id = NEW.user_id
      AND u.establishment_id = NEW.establishment_id
      AND u.status = 'ACTIVE'
      AND u.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Usuario do turno deve pertencer ao estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_shifts_bu
BEFORE UPDATE ON cash_register_shifts
FOR EACH ROW
BEGIN
  IF NEW.establishment_id <> OLD.establishment_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'establishment_id do turno nao pode ser alterado';
  END IF;
  IF OLD.status = 'CLOSED' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Turno fechado e imutavel';
  END IF;
  IF NEW.status = 'CLOSED' AND OLD.status = 'OPEN' THEN
    IF EXISTS (
      SELECT 1 FROM orders o
      WHERE o.cash_register_shift_id = NEW.id
        AND o.deleted_at IS NULL
        AND o.status NOT IN ('CLOSED', 'CANCELLED')
    ) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nao e possivel fechar o caixa com pedidos ativos';
    END IF;
  END IF;
END$$

CREATE TRIGGER trg_movements_bi
BEFORE INSERT ON cash_movements
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM cash_register_shifts s
    WHERE s.id = NEW.cash_register_shift_id
      AND s.establishment_id = NEW.establishment_id
      AND s.status = 'OPEN'
      AND s.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movimento exige turno aberto do mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_orders_bi
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM cash_register_shifts s
    WHERE s.id = NEW.cash_register_shift_id
      AND s.establishment_id = NEW.establishment_id
      AND s.status = 'OPEN'
      AND s.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pedido exige turno de caixa aberto';
  END IF;
  IF NEW.waiter_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM users u
       WHERE u.id = NEW.waiter_id
         AND u.establishment_id = NEW.establishment_id
         AND u.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Garcom deve pertencer ao estabelecimento';
  END IF;
  IF NEW.customer_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM customers c
       WHERE c.id = NEW.customer_id
         AND c.establishment_id = NEW.establishment_id
         AND c.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cliente deve pertencer ao estabelecimento';
  END IF;
  IF NEW.table_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM tables t
       WHERE t.id = NEW.table_id
         AND t.establishment_id = NEW.establishment_id
         AND t.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mesa deve pertencer ao estabelecimento';
  END IF;
  IF NEW.command_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM commands c
       WHERE c.id = NEW.command_id
         AND c.establishment_id = NEW.establishment_id
         AND c.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Comanda deve pertencer ao estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_orders_bu
BEFORE UPDATE ON orders
FOR EACH ROW
BEGIN
  DECLARE paid decimal(10,2) DEFAULT 0;

  IF NEW.establishment_id <> OLD.establishment_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'establishment_id do pedido nao pode ser alterado';
  END IF;

  IF OLD.status IN ('CLOSED', 'CANCELLED') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pedido encerrado nao pode ser alterado';
  END IF;

  IF NEW.status <> OLD.status THEN
    IF NOT (
      (OLD.status = 'OPEN' AND NEW.status IN ('IN_PREPARATION', 'CANCELLED'))
      OR (OLD.status = 'IN_PREPARATION' AND NEW.status IN ('READY', 'CANCELLED'))
      OR (OLD.status = 'READY' AND NEW.status IN ('DELIVERED', 'CANCELLED'))
      OR (OLD.status = 'DELIVERED' AND NEW.status = 'CLOSED')
    ) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transicao de status de pedido invalida';
    END IF;
  END IF;

  IF NEW.status = 'CLOSED' AND OLD.status <> 'CLOSED' THEN
    SELECT IFNULL(SUM(p.amount), 0) INTO paid
    FROM payments p
    WHERE p.order_id = NEW.id
      AND p.status = 'CONFIRMED'
      AND p.deleted_at IS NULL;

    IF paid < NEW.total_amount THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pedido so fecha se estiver pago';
    END IF;

    IF EXISTS (
      SELECT 1
      FROM order_items oi
      JOIN product_ingredients pi ON pi.product_id = oi.product_id
      JOIN inventory_items ii ON ii.id = pi.inventory_item_id
      WHERE oi.order_id = NEW.id
        AND oi.deleted_at IS NULL
      GROUP BY ii.id, ii.stock_quantity
      HAVING ii.stock_quantity < SUM(oi.quantity * pi.quantity)
    ) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estoque insuficiente para fechar o pedido';
    END IF;
  END IF;
END$$

CREATE TRIGGER trg_orders_au_stock
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
  IF NEW.status = 'CLOSED' AND OLD.status <> 'CLOSED' THEN
    INSERT INTO stock_movements (
      id, establishment_id, inventory_item_id, user_id, order_id,
      type, quantity, unit_cost, reference, notes, created_at
    )
    SELECT
      UUID(),
      NEW.establishment_id,
      pi.inventory_item_id,
      NEW.waiter_id,
      NEW.id,
      'OUT',
      SUM(oi.quantity * pi.quantity),
      NULL,
      NEW.id,
      'baixa automatica no fechamento',
      CURRENT_TIMESTAMP
    FROM order_items oi
    JOIN product_ingredients pi ON pi.product_id = oi.product_id
    WHERE oi.order_id = NEW.id
      AND oi.deleted_at IS NULL
    GROUP BY pi.inventory_item_id;

    UPDATE inventory_items ii
    JOIN (
      SELECT pi.inventory_item_id AS item_id, SUM(oi.quantity * pi.quantity) AS qty
      FROM order_items oi
      JOIN product_ingredients pi ON pi.product_id = oi.product_id
      WHERE oi.order_id = NEW.id
        AND oi.deleted_at IS NULL
      GROUP BY pi.inventory_item_id
    ) x ON x.item_id = ii.id
    SET ii.stock_quantity = ii.stock_quantity - x.qty,
        ii.updated_at = CURRENT_TIMESTAMP;
  END IF;
END$$

CREATE TRIGGER trg_order_items_bi
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM orders o
    WHERE o.id = NEW.order_id
      AND o.establishment_id = NEW.establishment_id
      AND o.status = 'OPEN'
      AND o.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Item so pode ser adicionado em pedido OPEN do mesmo estabelecimento';
  END IF;
  IF NOT EXISTS (
    SELECT 1 FROM products p
    WHERE p.id = NEW.product_id
      AND p.establishment_id = NEW.establishment_id
      AND p.is_available = 1
      AND p.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Produto indisponivel ou de outro estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_order_items_bu
BEFORE UPDATE ON order_items
FOR EACH ROW
BEGIN
  IF NEW.establishment_id <> OLD.establishment_id OR NEW.order_id <> OLD.order_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Item nao pode trocar pedido ou estabelecimento';
  END IF;
  IF NOT EXISTS (
    SELECT 1 FROM orders o
    WHERE o.id = NEW.order_id
      AND o.status = 'OPEN'
      AND o.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Item so pode ser alterado com pedido OPEN';
  END IF;
END$$

CREATE TRIGGER trg_payments_bi
BEFORE INSERT ON payments
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM orders o
    WHERE o.id = NEW.order_id
      AND o.establishment_id = NEW.establishment_id
      AND o.status NOT IN ('CLOSED', 'CANCELLED')
      AND o.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pagamento exige pedido aberto do mesmo estabelecimento';
  END IF;
  IF NEW.cash_register_shift_id IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM cash_register_shifts s
       WHERE s.id = NEW.cash_register_shift_id
         AND s.establishment_id = NEW.establishment_id
         AND s.status = 'OPEN'
         AND s.deleted_at IS NULL
     ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Pagamento exige turno aberto do mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_ingredients_bi
BEFORE INSERT ON product_ingredients
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM products p
    JOIN inventory_items i ON i.establishment_id = p.establishment_id
    WHERE p.id = NEW.product_id
      AND i.id = NEW.inventory_item_id
      AND p.deleted_at IS NULL
      AND i.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ficha tecnica exige produto e ingrediente do mesmo estabelecimento';
  END IF;
END$$

CREATE TRIGGER trg_stock_bi
BEFORE INSERT ON stock_movements
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM inventory_items i
    WHERE i.id = NEW.inventory_item_id
      AND i.establishment_id = NEW.establishment_id
      AND i.deleted_at IS NULL
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movimento de estoque deve ser do mesmo estabelecimento do item';
  END IF;
END$$

DELIMITER ;
