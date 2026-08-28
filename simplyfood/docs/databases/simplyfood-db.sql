-- SimplyFood schema MVP 1.0.0
-- Fonte alinhada a docs/SPEC.md
-- CHECK constraints exigem MySQL 8.0.16+

CREATE TABLE `establishments` (
  `id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `document` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `establishments_name_chk` CHECK (CHAR_LENGTH(`name`) >= 2)
);

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','MANAGER','CASHIER','WAITER','KITCHEN') NOT NULL,
  `status` enum('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_establishment_id_index` (`establishment_id`),
  CONSTRAINT `users_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `users_name_chk` CHECK (CHAR_LENGTH(`name`) >= 2),
  CONSTRAINT `users_email_chk` CHECK (`email` LIKE '%_@_%.__%')
);

CREATE TABLE `customers` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `document` varchar(30) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_establishment_id_index` (`establishment_id`),
  KEY `customers_phone_index` (`phone`),
  UNIQUE KEY `customers_establishment_phone_unique` (`establishment_id`,`phone`),
  CONSTRAINT `customers_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `customers_name_chk` CHECK (CHAR_LENGTH(`name`) >= 2),
  CONSTRAINT `customers_email_chk` CHECK (`email` IS NULL OR `email` LIKE '%_@_%.__%'),
  CONSTRAINT `customers_phone_chk` CHECK (`phone` IS NULL OR CHAR_LENGTH(`phone`) >= 8)
);

CREATE TABLE `categories` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_establishment_id_index` (`establishment_id`),
  CONSTRAINT `categories_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `categories_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `categories_sort_chk` CHECK (`sort_order` >= 0),
  CONSTRAINT `categories_active_chk` CHECK (`active` IN (0, 1))
);

CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `category_id` char(36) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sku` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `preparation_time_minutes` int NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_establishment_id_index` (`establishment_id`),
  KEY `products_category_id_index` (`category_id`),
  UNIQUE KEY `products_establishment_sku_unique` (`establishment_id`,`sku`),
  CONSTRAINT `products_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `products_price_chk` CHECK (`price` >= 0 AND `cost_price` >= 0),
  CONSTRAINT `products_prep_chk` CHECK (`preparation_time_minutes` >= 0),
  CONSTRAINT `products_available_chk` CHECK (`is_available` IN (0, 1))
);

CREATE TABLE `tables` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `number` int NOT NULL,
  `capacity` int NOT NULL DEFAULT 4,
  `status` enum('FREE','OCCUPIED','RESERVED','BILLING') NOT NULL DEFAULT 'FREE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tables_establishment_number_unique` (`establishment_id`,`number`),
  CONSTRAINT `tables_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `tables_number_chk` CHECK (`number` > 0),
  CONSTRAINT `tables_capacity_chk` CHECK (`capacity` > 0)
);

CREATE TABLE `commands` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `table_id` char(36) DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `status` enum('FREE','OPEN','BLOCKED','CLOSED') NOT NULL DEFAULT 'FREE',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `commands_establishment_code_unique` (`establishment_id`,`code`),
  KEY `commands_table_id_index` (`table_id`),
  CONSTRAINT `commands_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `commands_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`),
  CONSTRAINT `commands_code_chk` CHECK (CHAR_LENGTH(`code`) >= 1),
  CONSTRAINT `commands_open_table_chk` CHECK (`status` <> 'OPEN' OR `table_id` IS NOT NULL)
);

CREATE TABLE `cash_registers` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_registers_establishment_id_index` (`establishment_id`),
  CONSTRAINT `cash_registers_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `cash_registers_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `cash_registers_active_chk` CHECK (`is_active` IN (0, 1))
);

CREATE TABLE `cash_register_shifts` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `cash_register_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `opening_balance` decimal(10,2) NOT NULL,
  `closing_balance` decimal(10,2) DEFAULT NULL,
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `closed_at` timestamp NULL DEFAULT NULL,
  `status` enum('OPEN','CLOSED') NOT NULL DEFAULT 'OPEN',
  `notes` text,
  `open_register_key` char(36) GENERATED ALWAYS AS (IF(`status` = 'OPEN' AND `deleted_at` IS NULL, `cash_register_id`, NULL)) STORED,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_establishment_status_index` (`establishment_id`,`status`),
  UNIQUE KEY `shifts_one_open_per_register` (`open_register_key`),
  CONSTRAINT `shifts_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `shifts_register_fk` FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers` (`id`),
  CONSTRAINT `shifts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `shifts_opening_balance_chk` CHECK (`opening_balance` >= 0),
  CONSTRAINT `shifts_closing_balance_chk` CHECK (`closing_balance` IS NULL OR `closing_balance` >= 0),
  CONSTRAINT `shifts_status_shape_chk` CHECK (
    (`status` = 'OPEN' AND `closed_at` IS NULL AND `closing_balance` IS NULL)
    OR (`status` = 'CLOSED' AND `closed_at` IS NOT NULL AND `closing_balance` IS NOT NULL)
  )
);

CREATE TABLE `cash_movements` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `cash_register_shift_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `type` enum('INITIAL_BALANCE','SALE','BLEED','SUPPLEMENT','REFUND') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `movements_shift_id_index` (`cash_register_shift_id`),
  CONSTRAINT `movements_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `movements_shift_fk` FOREIGN KEY (`cash_register_shift_id`) REFERENCES `cash_register_shifts` (`id`),
  CONSTRAINT `movements_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `cash_movements_amount_chk` CHECK (`amount` > 0)
);

CREATE TABLE `orders` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `cash_register_shift_id` char(36) NOT NULL,
  `waiter_id` char(36) DEFAULT NULL,
  `customer_id` char(36) DEFAULT NULL,
  `table_id` char(36) DEFAULT NULL,
  `command_id` char(36) DEFAULT NULL,
  `order_type` enum('TABLE','COMMAND','COUNTER') NOT NULL,
  `status` enum('OPEN','IN_PREPARATION','READY','DELIVERED','CLOSED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_establishment_status_index` (`establishment_id`,`status`),
  KEY `orders_shift_id_index` (`cash_register_shift_id`),
  CONSTRAINT `orders_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `orders_shift_fk` FOREIGN KEY (`cash_register_shift_id`) REFERENCES `cash_register_shifts` (`id`),
  CONSTRAINT `orders_waiter_fk` FOREIGN KEY (`waiter_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `orders_table_fk` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`),
  CONSTRAINT `orders_command_fk` FOREIGN KEY (`command_id`) REFERENCES `commands` (`id`),
  CONSTRAINT `orders_amounts_chk` CHECK (`subtotal` >= 0 AND `discount` >= 0 AND `total_amount` >= 0 AND `discount` <= `subtotal`),
  CONSTRAINT `orders_type_shape_chk` CHECK (
    (`order_type` = 'TABLE' AND `table_id` IS NOT NULL AND `command_id` IS NULL)
    OR (`order_type` = 'COMMAND' AND `table_id` IS NOT NULL AND `command_id` IS NOT NULL)
    OR (`order_type` = 'COUNTER' AND `table_id` IS NULL AND `command_id` IS NULL)
  )
);

CREATE TABLE `order_items` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('WAITING','PREPARING','READY','DELIVERED','CANCELLED') NOT NULL DEFAULT 'WAITING',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  CONSTRAINT `order_items_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `order_items_qty_chk` CHECK (`quantity` > 0),
  CONSTRAINT `order_items_prices_chk` CHECK (`unit_price` >= 0 AND `total_price` >= 0),
  CONSTRAINT `order_items_total_chk` CHECK (`total_price` = `quantity` * `unit_price`)
);

CREATE TABLE `order_status_history` (
  `id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `from_status` enum('OPEN','IN_PREPARATION','READY','DELIVERED','CLOSED','CANCELLED') DEFAULT NULL,
  `to_status` enum('OPEN','IN_PREPARATION','READY','DELIVERED','CLOSED','CANCELLED') NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  PRIMARY KEY (`id`),
  KEY `order_status_history_order_id_index` (`order_id`),
  CONSTRAINT `order_status_history_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_status_history_change_chk` CHECK (`from_status` IS NULL OR `from_status` <> `to_status`)
);

CREATE TABLE `payments` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `order_id` char(36) NOT NULL,
  `cash_register_shift_id` char(36) DEFAULT NULL,
  `payment_method` enum('CASH','CREDIT_CARD','DEBIT_CARD','PIX','VOUCHER') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('CONFIRMED','REFUNDED','FAILED') NOT NULL DEFAULT 'CONFIRMED',
  `transaction_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_index` (`order_id`),
  CONSTRAINT `payments_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `payments_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `payments_amount_chk` CHECK (`amount` > 0)
);

CREATE TABLE `inventory_items` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock_quantity` decimal(10,3) NOT NULL DEFAULT 0.000,
  `min_stock` decimal(10,3) NOT NULL DEFAULT 0.000,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_items_establishment_id_index` (`establishment_id`),
  CONSTRAINT `inventory_items_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `inventory_items_name_chk` CHECK (CHAR_LENGTH(`name`) >= 1),
  CONSTRAINT `inventory_items_unit_chk` CHECK (CHAR_LENGTH(`unit`) >= 1),
  CONSTRAINT `inventory_items_min_stock_chk` CHECK (`min_stock` >= 0),
  CONSTRAINT `inventory_items_cost_chk` CHECK (`cost_price` >= 0)
);

CREATE TABLE `product_ingredients` (
  `product_id` char(36) NOT NULL,
  `inventory_item_id` char(36) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `yield_percentage` decimal(5,2) NOT NULL DEFAULT 100.00,
  PRIMARY KEY (`product_id`,`inventory_item_id`),
  CONSTRAINT `product_ingredients_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `product_ingredients_item_fk` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `product_ingredients_qty_chk` CHECK (`quantity` > 0),
  CONSTRAINT `product_ingredients_yield_chk` CHECK (`yield_percentage` > 0 AND `yield_percentage` <= 100)
);

CREATE TABLE `stock_movements` (
  `id` char(36) NOT NULL,
  `establishment_id` char(36) NOT NULL,
  `inventory_item_id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `order_id` char(36) DEFAULT NULL,
  `type` enum('IN','OUT','ADJUSTMENT','TRANSFER') NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `stock_movements_item_id_index` (`inventory_item_id`),
  CONSTRAINT `stock_movements_establishment_fk` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `stock_movements_item_fk` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `stock_movements_qty_chk` CHECK (`quantity` > 0),
  CONSTRAINT `stock_movements_cost_chk` CHECK (`unit_cost` IS NULL OR `unit_cost` >= 0)
);

-- Triggers: source databases/simplyfood-triggers.sql apos este DDL.
