USE `book-shop-version-2`;
-- ====================================================================
-- Book Shop DB - full DDL + seed + inventory seed (1000/unit)
-- Target: book-shop-version-2
-- Compatible: MySQL 8.0+
-- ====================================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;
SET SQL_NOTES = 0;

-- 1) CREATE DATABASE
CREATE DATABASE IF NOT EXISTS `book-shop-version-2`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `book-shop-version-2`;

-- 2) DROP TABLES (safe re-run)
DROP TABLE IF EXISTS
  `order_batches`, `order_items`, `payments`, `shipments`, `orders`,
  `batch_stocks`, `batches`, `purchase_receipt_items`, `purchase_receipts`,
  `stock_movements`, `stocks`,
  `discount_usages`, `discounts`,
  `favorites`,
  `product_categories`, `product_authors`,
  `products`,
  `publishers`,
  `reviews`,
  `addresses`,
  `role_user`, `roles`,
  `authors`, `categories`,
  `warehouses`,
  `users`,
  `migrations`;

-- 3) BASE TABLES (no FKs to others, or referenced first)
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_phone_index` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `authors` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authors_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `publishers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `publishers_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `warehouses` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) TABLES that reference the above
CREATE TABLE `addresses` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_is_default_index` (`user_id`,`is_default`),
  KEY `addresses_is_default_index` (`is_default`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isbn` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `publisher_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'book',
  `selling_price_vnd` bigint unsigned NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_isbn_unique` (`isbn`),
  KEY `products_publisher_id_status_index` (`publisher_id`,`status`),
  CONSTRAINT `products_publisher_id_foreign` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_authors` (
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`product_id`,`author_id`),
  KEY `product_authors_author_id_foreign` (`author_id`),
  CONSTRAINT `product_authors_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_authors_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_categories` (
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `product_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `favorites` (
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`product_id`),
  KEY `favorites_product_id_user_id_index` (`product_id`,`user_id`),
  CONSTRAINT `favorites_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `discounts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` int unsigned NOT NULL,
  `min_order_value_vnd` bigint unsigned DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discounts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `discount_usages` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discount_usages_user_id_foreign` (`user_id`),
  KEY `discount_usages_discount_id_user_id_index` (`discount_id`,`user_id`),
  KEY `discount_usages_order_id_index` (`order_id`),
  CONSTRAINT `discount_usages_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discount_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory docs / purchasing
CREATE TABLE `purchase_receipts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `publisher_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `received_at` timestamp NOT NULL,
  `name_of_delivery_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_of_delivery_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_note_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_identification_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_total_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_receipts_publisher_id_foreign` (`publisher_id`),
  KEY `purchase_receipts_created_by_foreign` (`created_by`),
  KEY `purchase_receipts_warehouse_id_received_at_index` (`warehouse_id`,`received_at`),
  CONSTRAINT `purchase_receipts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_receipts_publisher_id_foreign` FOREIGN KEY (`publisher_id`) REFERENCES `publishers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_receipts_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purchase_receipt_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_receipt_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `import_price_vnd` bigint unsigned NOT NULL,
  `qty_doc` int unsigned NOT NULL,
  `qty_actual` int unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_receipt_items_product_id_foreign` (`product_id`),
  KEY `purchase_receipt_items_purchase_receipt_id_index` (`purchase_receipt_id`),
  CONSTRAINT `purchase_receipt_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_receipt_items_purchase_receipt_id_foreign` FOREIGN KEY (`purchase_receipt_id`) REFERENCES `purchase_receipts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `batches` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_receipt_item_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `import_price_vnd` bigint unsigned NOT NULL,
  `import_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `batches_purchase_receipt_item_id_foreign` (`purchase_receipt_item_id`),
  KEY `batches_warehouse_id_foreign` (`warehouse_id`),
  KEY `batches_product_id_warehouse_id_index` (`product_id`,`warehouse_id`),
  CONSTRAINT `batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batches_purchase_receipt_item_id_foreign` FOREIGN KEY (`purchase_receipt_item_id`) REFERENCES `purchase_receipt_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batches_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `batch_stocks` (
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `on_hand` bigint NOT NULL DEFAULT '0',
  `reserved` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`batch_id`),
  KEY `batch_stocks_warehouse_id_foreign` (`warehouse_id`),
  KEY `batch_stocks_product_id_warehouse_id_index` (`product_id`,`warehouse_id`),
  CONSTRAINT `batch_stocks_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batch_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `batch_stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stocks` (
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `on_hand` bigint NOT NULL DEFAULT '0',
  `reserved` bigint NOT NULL DEFAULT '0',
  `reorder_point` int unsigned DEFAULT NULL,
  `reorder_qty` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`,`warehouse_id`),
  KEY `stocks_warehouse_id_product_id_index` (`warehouse_id`,`product_id`),
  CONSTRAINT `stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_movements` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int NOT NULL,
  `unit_cost_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_warehouse_id_foreign` (`warehouse_id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_product_id_warehouse_id_created_at_index` (`product_id`,`warehouse_id`,`created_at`),
  KEY `stock_movements_batch_id_index` (`batch_id`),
  KEY `stock_movements_related_id_index` (`related_id`),
  CONSTRAINT `stock_movements_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_movements_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders
CREATE TABLE `orders` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items_count` int unsigned NOT NULL DEFAULT '0',
  `subtotal_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `discount_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `tax_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `shipping_fee_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `grand_total_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `discount_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_note` text COLLATE utf8mb4_unicode_ci,
  `placed_at` timestamp NOT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_code_unique` (`code`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_discount_id_foreign` (`discount_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_method_index` (`payment_method`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_placed_at_index` (`placed_at`),
  CONSTRAINT `orders_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_title_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isbn13_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price_vnd` bigint unsigned NOT NULL,
  `discount_amount_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `tax_rate` decimal(5,2) DEFAULT NULL,
  `tax_amount_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `unit_cost_snapshot_vnd` bigint unsigned NOT NULL,
  `total_price_vnd` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_warehouse_id_foreign` (`warehouse_id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_batches` (
  `order_item_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_cost_vnd` bigint unsigned NOT NULL,
  PRIMARY KEY (`order_item_id`,`batch_id`),
  KEY `order_batches_batch_id_index` (`batch_id`),
  CONSTRAINT `order_batches_batch_id_foreign` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_batches_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `courier_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carrier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_cost_actual_vnd` bigint unsigned DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `picked_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipments_order_id_unique` (`order_id`),
  KEY `shipments_courier_id_foreign` (`courier_id`),
  KEY `shipments_status_index` (`status`),
  CONSTRAINT `shipments_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `txn_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_vnd` bigint unsigned NOT NULL,
  `fee_amount_vnd` bigint unsigned DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `raw_gateway_payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_paid_at_index` (`order_id`,`paid_at`),
  KEY `payments_status_index` (`status`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Misc
CREATE TABLE `reviews` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `reply` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_product_id_is_active_index` (`product_id`,`is_active`),
  KEY `reviews_is_active_index` (`is_active`),
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_user` (
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) SEED DATA (copy y nguyên từ dump, + giữ nguyên IDs)
INSERT INTO `authors` (`id`,`name`,`slug`,`image`,`description`,`status`,`created_at`,`updated_at`) VALUES
('9ffacafe-82b8-485d-9fd0-31caf61bee7a','Ho Huu Thuan','ho-huu-thuan','GC5x6DfYr8cgGBOBqNnj8hcYf305W3AUzQKWDxCB.png','xx','ACTIVE','2025-09-27 17:42:43','2025-09-27 17:42:43'),
('9ffc7aa4-68a2-4c4d-88ca-ff11ab2547ca','Tác giả 2','tac-gia-2','C2t6fYVjMjuXWMEcHsRwnrEN9BgHoqkoyJ75ZiW5.png','Mô tả','ACTIVE','2025-09-28 13:49:41','2025-10-05 18:55:35');

INSERT INTO `categories` (`id`,`name`,`slug`,`description`,`status`,`created_at`,`updated_at`) VALUES
('9ffad2ba-2124-4abf-a555-66921630aae5','Tiểu thuyết','tieu-thuyet','no','ACTIVE','2025-09-27 18:04:20','2025-10-20 14:20:55'),
('9ffad2d9-d3ae-4332-9169-0b18ceaf4dbc','Kinh doanh','kinh-doanh','no','ACTIVE','2025-09-27 18:04:41','2025-10-20 14:21:08'),
('a028c7b7-5a7f-45f1-874f-0cb1c2adcb89','Tâm lý','tam-ly','no','ACTIVE','2025-10-20 14:21:38','2025-10-20 14:21:38'),
('a028c7c9-1fc2-4bed-83ed-9a06a69ba715','Thiếu nhi','thieu-nhi','no','ACTIVE','2025-10-20 14:21:49','2025-10-20 14:21:49'),
('a02aec3e-a112-40ff-a8b6-8aa6fe1337bb','Danh mục 1','danh-muc-1','NO','ACTIVE','2025-10-21 15:55:25','2025-10-21 15:55:25'),
('a02aec4b-04dd-41d4-bf28-9db8d7c44d30','Danh mục 2','danh-muc-2','NO','ACTIVE','2025-10-21 15:55:34','2025-10-21 15:55:34');

INSERT INTO `publishers` (`id`,`name`,`slug`,`logo`,`description`,`status`,`created_at`,`updated_at`) VALUES
('9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Ho Huu Thuan','ho-huu-thuan','GTd82amYX5ozh3pRpjT3zYAz4iTtiRL9VdLwe3Q2.png','Mô tả','ACTIVE','2025-09-28 13:49:11','2025-09-28 13:49:11');

INSERT INTO `products`
(`id`,`code`,`title`,`slug`,`isbn`,`description`,`publisher_id`,`unit`,`selling_price_vnd`,`image`,`status`,`created_at`,`updated_at`) VALUES
('a00abb28-6a82-4ed8-80b0-870a99ffeab5','CODE','Các để đi đến thành công','cac-de-di-den-thanh-cong','1111','Mô tả sản phẩm nè','9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Bộ',150000000,'64Md85UpCU4EiSmQQJ3xDxmX9AwTCyGNrtcQLoD0.png','ACTIVE','2025-10-05 15:51:41','2025-10-20 17:03:49'),
('a00ac32b-cb44-4f59-b37c-6cb764bf0acb','CODE1','Không hiểu gì hết','khong-hieu-gi-het','11111','Mô tả','9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Bộ',1500000,'wDVedQxYUQZi7xzfVEkentOiBWvNUairVImKgUB2.jpg','ACTIVE','2025-10-05 16:14:05','2025-10-20 17:03:30'),
('a00ad142-fa6c-4cdc-85b0-16452f5153a6','CODE2','Chúa tể của những chiếc nhẫn','chua-te-cua-nhung-chiec-nhan','2222','Mô tả sản phẩm','9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Bộ',1500000,'O18f4EW66LolBMh9z9WOySd8ElyX206ZOlhXEVFB.png','ACTIVE','2025-10-05 16:53:29','2025-10-20 17:03:14'),
('a00ad1d5-758b-47c8-9f5b-bc33898c5af9','CODE3','Sauron nè','sauron-ne','3333','Mô tả','9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Bộ',1500000,'mno2u40k9LnLA7HZ4u0GAkm5CRjnWaqV1NoMCbbM.png','ACTIVE','2025-10-05 16:55:05','2025-10-20 17:02:38'),
('a00ad242-ccc5-43b3-90b0-b8f5d6fd9a72','CODE4','Chả lá lốt nè','cha-la-lot-ne','4444','Mô tả','9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Bộ',15000000,'zXlrYfkajI2bEuROJwqHJjvI7lpzL95xNEjDDHSe.jpg','ACTIVE','2025-10-05 16:56:17','2025-10-20 17:02:22'),
('a00adfb5-df2d-4fe4-9bca-558173aa4c73','CODENOIBO','Tiểu thuyết 1','tieu-thuyet-1','11112003','Mô tả','9ffc7a76-0a3f-40bd-bf9f-bb488d5ea19d','Bộ',25000000000,'o9jVHYZOiHCl8xVp6H3FC1oMRYPoFZK0z4jnikr6.png','ACTIVE','2025-10-05 17:33:53','2025-10-20 17:02:04');

INSERT INTO `product_authors` (`product_id`,`author_id`,`role`) VALUES
('a00abb28-6a82-4ed8-80b0-870a99ffeab5','9ffacafe-82b8-485d-9fd0-31caf61bee7a',NULL),
('a00abb28-6a82-4ed8-80b0-870a99ffeab5','9ffc7aa4-68a2-4c4d-88ca-ff11ab2547ca',NULL),
('a00ac32b-cb44-4f59-b37c-6cb764bf0acb','9ffacafe-82b8-485d-9fd0-31caf61bee7a',NULL),
('a00ac32b-cb44-4f59-b37c-6cb764bf0acb','9ffc7aa4-68a2-4c4d-88ca-ff11ab2547ca',NULL),
('a00ad142-fa6c-4cdc-85b0-16452f5153a6','9ffacafe-82b8-485d-9fd0-31caf61bee7a',NULL),
('a00ad142-fa6c-4cdc-85b0-16452f5153a6','9ffc7aa4-68a2-4c4d-88ca-ff11ab2547ca',NULL),
('a00ad1d5-758b-47c8-9f5b-bc33898c5af9','9ffacafe-82b8-485d-9fd0-31caf61bee7a',NULL),
('a00ad242-ccc5-43b3-90b0-b8f5d6fd9a72','9ffc7aa4-68a2-4c4d-88ca-ff11ab2547ca',NULL),
('a00adfb5-df2d-4fe4-9bca-558173aa4c73','9ffacafe-82b8-485d-9fd0-31caf61bee7a',NULL),
('a00adfb5-df2d-4fe4-9bca-558173aa4c73','9ffc7aa4-68a2-4c4d-88ca-ff11ab2547ca',NULL);

INSERT INTO `product_categories` (`product_id`,`category_id`) VALUES
('a00ac32b-cb44-4f59-b37c-6cb764bf0acb','9ffad2ba-2124-4abf-a555-66921630aae5'),
('a00ad142-fa6c-4cdc-85b0-16452f5153a6','9ffad2ba-2124-4abf-a555-66921630aae5'),
('a00ad1d5-758b-47c8-9f5b-bc33898c5af9','9ffad2ba-2124-4abf-a555-66921630aae5'),
('a00ad242-ccc5-43b3-90b0-b8f5d6fd9a72','9ffad2ba-2124-4abf-a555-66921630aae5'),
('a00adfb5-df2d-4fe4-9bca-558173aa4c73','9ffad2ba-2124-4abf-a555-66921630aae5'),
('a00abb28-6a82-4ed8-80b0-870a99ffeab5','9ffad2d9-d3ae-4332-9169-0b18ceaf4dbc'),
('a00ac32b-cb44-4f59-b37c-6cb764bf0acb','9ffad2d9-d3ae-4332-9169-0b18ceaf4dbc'),
('a00ad142-fa6c-4cdc-85b0-16452f5153a6','9ffad2d9-d3ae-4332-9169-0b18ceaf4dbc'),
('a00ad1d5-758b-47c8-9f5b-bc33898c5af9','9ffad2d9-d3ae-4332-9169-0b18ceaf4dbc'),
('a00adfb5-df2d-4fe4-9bca-558173aa4c73','9ffad2d9-d3ae-4332-9169-0b18ceaf4dbc');

INSERT INTO `favorites` (`user_id`,`product_id`,`created_at`,`updated_at`) VALUES
('9ffa3b13-7f5c-4535-a2e6-f42566c649c7','a00adfb5-df2d-4fe4-9bca-558173aa4c73','2025-11-07 20:29:29','2025-11-07 20:29:29');

INSERT INTO `orders`
(`id`,`code`,`user_id`,`status`,`payment_method`,`payment_status`,`items_count`,`subtotal_vnd`,`discount_vnd`,`tax_vnd`,`shipping_fee_vnd`,`grand_total_vnd`,`discount_id`,`buyer_note`,`placed_at`,`delivered_at`,`cancelled_at`,`created_at`,`updated_at`) VALUES
('9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','CODE','9ffa3b13-7f5c-4535-a2e6-f42566c649c7','PENDING','CODE','UNPAID',3,200000000,0,0,0,0,NULL,NULL,'2024-12-31 17:00:00',NULL,NULL,NULL,NULL);

INSERT INTO `shipments`
(`id`,`order_id`,`name`,`phone`,`email`,`address`,`courier_id`,`carrier`,`tracking_no`,`status`,`shipping_cost_actual_vnd`,`assigned_at`,`picked_at`,`shipped_at`,`delivered_at`,`created_at`,`updated_at`) VALUES
('9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','Thuan','0345486622','thuan@gmail.com','Địa chỉ',NULL,NULL,NULL,'PENDING',NULL,NULL,NULL,NULL,NULL,NULL,NULL);

INSERT INTO `roles` (`id`,`name`,`description`,`created_at`,`updated_at`) VALUES
('9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','Admin','Full access','2025-09-27 10:59:29','2025-09-27 10:59:29'),
('9ffa3ac9-e696-4336-8aa0-1117d2683b7b','Customer','Customer','2025-09-27 10:59:29','2025-09-27 10:59:29');

INSERT INTO `users`
(`id`,`name`,`email`,`phone`,`avatar`,`email_verified_at`,`password`,`status`,`remember_token`,`created_at`,`updated_at`) VALUES
('9ffa3b13-7f5c-4535-a2e6-f42566c649c7','Ho Huu Thuan','hohuuthuan@gmail.com','0321654987','vozvZ32VntXeWrBAkMdzA0VUSh6uTM9Fcjt1Yvcm.jpg',NULL,'$2y$12$V99NaFOi2mUrN7yqlnONd.4TTxjzBvZRrV1FdeXR66VUgql17TMfK','ACTIVE','kjkckBxqqDkvNUfUBQ8FerdOfuVIVNFPKKmq9LCNPbfILa7mfhUHN1KSyJPo','2025-09-27 11:00:17','2025-10-05 14:58:00'),
('9ffb1565-f8fe-47b4-9d52-27cb054d4fb2','dev1','dev1@gmail.com','0321654987',NULL,NULL,'$2y$12$tXuRYT6JqZMuYEsy8vFk0uaCtWw8r8HWyGA/D/WGRwy0vvNdPCUbu','ACTIVE','9SMlKn4ENPUEESqDkE5d1qEg7lDceVALyGDR3loxdCVNIjJnkwx6iZHhq5bn','2025-09-27 21:10:46','2025-10-05 14:58:00'),
('9ffb1ca9-a217-4833-bddf-7a4e335c0e4d','Ho Huu Thuan','hohuuthuan1@gmail.com','0321654987','avatars/fFvuhj2KGkBrjEw92cJSzu8UtiUGDl9RKk2519zi.png',NULL,'$2y$12$D8.fcfDZ6iO96o503M7/DuBaRtLSMkQhkwNjY4EWmo.Y6JOE0MnNW','ACTIVE',NULL,'2025-09-27 21:31:04','2025-10-05 18:35:09');

INSERT INTO `role_user` (`role_id`,`user_id`) VALUES
('9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','9ffa3b13-7f5c-4535-a2e6-f42566c649c7'),
('9ffa3ac9-e696-4336-8aa0-1117d2683b7b','9ffa3b13-7f5c-4535-a2e6-f42566c649c7'),
('9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','9ffb1565-f8fe-47b4-9d52-27cb054d4fb2'),
('9ffa3ac9-e696-4336-8aa0-1117d2683b7b','9ffb1565-f8fe-47b4-9d52-27cb054d4fb2'),
('9ffa3ac9-e4df-4a59-aa82-fd4de160b2a7','9ffb1ca9-a217-4833-bddf-7a4e335c0e4d'),
('9ffa3ac9-e696-4336-8aa0-1117d2683b7b','9ffb1ca9-a217-4833-bddf-7a4e335c0e4d');

-- migrations (giữ nguyên để Artisan không chạy lại sai batch)
INSERT INTO `migrations` (`id`,`migration`,`batch`) VALUES
(1,'2025_09_14_000001_create_users_table',1),
(2,'2025_09_14_000002_create_roles_table',1),
(3,'2025_09_14_000003_create_role_user_table',1),
(4,'2025_09_14_000004_create_addresses_table',1),
(5,'2025_09_14_000005_create_authors_table',1),
(6,'2025_09_14_000006_create_categories_table',1),
(7,'2025_09_14_000007_create_publishers_table',1),
(8,'2025_09_14_000008_create_products_table',1),
(9,'2025_09_14_000009_create_product_authors_table',1),
(10,'2025_09_14_000010_create_product_categories_table',1),
(11,'2025_09_14_000011_create_reviews_table',1),
(12,'2025_09_14_000012_create_discounts_table',1),
(13,'2025_09_14_000013_create_discount_usages_table',1),
(14,'2025_09_14_000014_create_warehouses_table',1),
(15,'2025_09_14_000015_create_purchase_receipts_table',1),
(16,'2025_09_14_000016_create_purchase_receipt_items_table',1),
(17,'2025_09_14_000017_create_batches_table',1),
(18,'2025_09_14_000018_create_stock_movements_table',1),
(19,'2025_09_14_000019_create_stocks_table',1),
(20,'2025_09_14_000020_create_batch_stocks_table',1),
(21,'2025_09_14_000021_create_orders_table',1),
(22,'2025_09_14_000022_create_order_items_table',1),
(23,'2025_09_14_000023_create_order_batches_table',1),
(24,'2025_09_14_000024_create_shipments_table',1),
(25,'2025_09_14_000025_create_payments_table',1),
(26,'2025_09_14_000019_create_favorites_table',2);

-- =========================
-- 6) INVENTORY SEED (to 1000)
-- =========================
SET @now := NOW();

-- Bảo đảm có kho MAIN-HCM (tạo nếu chưa có)
INSERT INTO warehouses (id, name, code, address, created_at, updated_at)
SELECT UUID(), 'Kho Chính HCM', 'MAIN-HCM', 'Hồ Chí Minh', @now, @now
WHERE NOT EXISTS (SELECT 1 FROM warehouses WHERE code = 'MAIN-HCM');

-- Lấy id kho dùng để seed
SET @warehouse_id := (SELECT id FROM warehouses WHERE code = 'MAIN-HCM' LIMIT 1);

-- Chọn created_by (user đầu tiên có sẵn)
SET @created_by := (SELECT id FROM users ORDER BY created_at IS NULL, created_at LIMIT 1);

-- Nếu chưa có user nào thì tạo 1 user giả
INSERT INTO users (id, name, email, phone, avatar, email_verified_at, password, status, remember_token, created_at, updated_at)
SELECT UUID(), 'Seed Admin', 'seed-admin@example.com', NULL, NULL, NULL,
       '$2y$12$2r7o3Vwq7p0eVlf1b2iG5e4q1yPq1xJks1mJKb2g8Zbq6q9mH7pC2', 'ACTIVE', NULL, @now, @now
WHERE @created_by IS NULL;

-- Cập nhật lại created_by
SET @created_by := (SELECT id FROM users ORDER BY created_at IS NULL, created_at LIMIT 1);

-- Gom dữ liệu sản phẩm & tính phần cần nhập để đạt 1000
DROP TEMPORARY TABLE IF EXISTS tmp_products_need;
CREATE TEMPORARY TABLE tmp_products_need AS
SELECT
  p.id                                        AS product_id,
  COALESCE(s.on_hand, 0)                      AS current_on_hand,
  GREATEST(0, 1000 - COALESCE(s.on_hand, 0))  AS needed_qty,
  GREATEST(10000, ROUND(COALESCE(p.selling_price_vnd, 20000) * 0.60)) AS import_price_vnd
FROM products p
LEFT JOIN stocks s
  ON s.product_id = p.id AND s.warehouse_id = @warehouse_id;

-- Tạo phiếu nhập gom các dòng cần seed
SET @receipt_id := UUID();

INSERT INTO purchase_receipts (
  id, publisher_id, warehouse_id, received_at,
  name_of_delivery_person, delivery_unit, address_of_delivery_person,
  delivery_note_number, tax_identification_number, sub_total_vnd,
  created_by, created_at, updated_at
)
VALUES (
  @receipt_id, NULL, @warehouse_id, @now,
  NULL, 'Internal Seed', NULL,
  CONCAT('SEED-SET-1000-', DATE_FORMAT(@now,'%Y%m%d%H%i%s')), NULL, 0,
  @created_by, @now, @now
);

-- Chi tiết phiếu nhập cho những sản phẩm còn thiếu
DROP TEMPORARY TABLE IF EXISTS tmp_items;
CREATE TEMPORARY TABLE tmp_items AS
SELECT
  UUID()                          AS pri_id,
  @receipt_id                     AS receipt_id,
  t.product_id,
  t.import_price_vnd,
  t.needed_qty
FROM tmp_products_need t
WHERE t.needed_qty > 0;

INSERT INTO purchase_receipt_items (
  id, purchase_receipt_id, product_id, import_price_vnd,
  qty_doc, qty_actual, notes, created_at, updated_at
)
SELECT
  x.pri_id, x.receipt_id, x.product_id, x.import_price_vnd,
  x.needed_qty, x.needed_qty, 'Seed set stock to 1000', @now, @now
FROM tmp_items x;

-- Tạo batches tương ứng
DROP TEMPORARY TABLE IF EXISTS tmp_batches;
CREATE TEMPORARY TABLE tmp_batches AS
SELECT
  UUID()              AS batch_id,
  i.pri_id            AS purchase_receipt_item_id,
  i.product_id,
  @warehouse_id       AS warehouse_id,
  i.needed_qty        AS quantity,
  i.import_price_vnd,
  CURDATE()           AS import_date,
  @now                AS created_at,
  @now                AS updated_at
FROM tmp_items i;

INSERT INTO batches (
  id, purchase_receipt_item_id, product_id, warehouse_id,
  quantity, import_price_vnd, import_date, created_at, updated_at
)
SELECT
  b.batch_id, b.purchase_receipt_item_id, b.product_id, b.warehouse_id,
  b.quantity, b.import_price_vnd, b.import_date, b.created_at, b.updated_at
FROM tmp_batches b;

-- batch_stocks
INSERT INTO batch_stocks (
  batch_id, product_id, warehouse_id, on_hand, reserved, created_at, updated_at
)
SELECT
  b.batch_id, b.product_id, b.warehouse_id, b.quantity, 0, b.created_at, b.updated_at
FROM tmp_batches b;

-- stocks (cộng thêm phần thiếu để đạt đúng 1000)
INSERT INTO stocks (
  product_id, warehouse_id, on_hand, reserved, reorder_point, reorder_qty, created_at, updated_at
)
SELECT
  t.product_id, @warehouse_id, t.needed_qty, 0, NULL, NULL, @now, @now
FROM tmp_products_need t
WHERE t.needed_qty > 0
ON DUPLICATE KEY UPDATE
  on_hand = on_hand + VALUES(on_hand),
  updated_at = VALUES(updated_at);

-- stock_movements (log nhập kho)
INSERT INTO stock_movements (
  id, product_id, warehouse_id, batch_id,
  type, qty, unit_cost_vnd, related_type, related_id,
  note, created_by, created_at, updated_at
)
SELECT
  UUID(), b.product_id, b.warehouse_id, b.batch_id,
  'IMPORT', b.quantity, b.import_price_vnd, 'purchase_receipts', @receipt_id,
  'Seed set stock to 1000', @created_by, @now, @now
FROM tmp_batches b;

-- Cập nhật sub_total_vnd của phiếu nhập
UPDATE purchase_receipts pr
JOIN (
  SELECT pri.purchase_receipt_id AS rid, SUM(pri.import_price_vnd * pri.qty_actual) AS subtotal
  FROM purchase_receipt_items pri
  WHERE pri.purchase_receipt_id = @receipt_id
  GROUP BY pri.purchase_receipt_id
) x ON x.rid = pr.id
SET pr.sub_total_vnd = COALESCE(x.subtotal, 0)
WHERE pr.id = @receipt_id;

-- Cleanup tạm
DROP TEMPORARY TABLE IF EXISTS tmp_products_need;
DROP TEMPORARY TABLE IF EXISTS tmp_items;
DROP TEMPORARY TABLE IF EXISTS tmp_batches;

-- 7) FINISH
SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
SET SQL_NOTES = 1;
