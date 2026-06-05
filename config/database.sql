DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `courier_locations`;
DROP TABLE IF EXISTS `order_products`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;

CREATE TABLE IF NOT EXISTS `users` (
                                       `id` INT(11) NOT NULL AUTO_INCREMENT,
                                       `name` VARCHAR(100) NOT NULL,
                                       `email` VARCHAR(100) NOT NULL,
                                       `phone_number` VARCHAR(20),
                                       `password_hash` VARCHAR(255) NOT NULL,
                                       `created_at` BIGINT DEFAULT 0,
                                       `role` VARCHAR(20) NOT NULL,
                                       `address` VARCHAR(255),
                                       `country` VARCHAR(255),
                                       `region` VARCHAR(255),
                                       `photo_path` VARCHAR(255),
                                       PRIMARY KEY (`id`),
                                       UNIQUE KEY `email` (`email`),
                                       KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `products` (
                                          `id` INT(11) NOT NULL AUTO_INCREMENT,
                                          `name` VARCHAR(100) NOT NULL,
                                          `description` TEXT DEFAULT NULL,
                                          `price` DECIMAL(10, 2) NOT NULL,
                                          `stock` INT NOT NULL DEFAULT 0,
                                          `created_at` BIGINT DEFAULT 0,
                                          PRIMARY KEY (`id`),
                                          KEY `name` (`name`),
                                          KEY `stock` (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `orders` (
                                        `id` INT(11) NOT NULL AUTO_INCREMENT,
                                        `user_id` INT(11) NULL,
                                        `address` VARCHAR(255) NOT NULL,
                                        `country` VARCHAR(255) NOT NULL,
                                        `region` VARCHAR(255) NOT NULL,
                                        `status` VARCHAR(50) NOT NULL,
                                        `product_price` DECIMAL(10, 2) NOT NULL,
                                        `tax` DECIMAL(10, 2) NOT NULL,
                                        `shipping_price` DECIMAL(10, 2) NOT NULL,
                                        `total_amount` DECIMAL(10, 2) NOT NULL,
                                        `created_at` BIGINT DEFAULT 0,
                                        `last_processed` BIGINT DEFAULT 0,
                                        `courier_id` INT(11) NULL,
                                        `tracking_number` VARCHAR(100) DEFAULT NULL,
                                        `delivery_date` BIGINT DEFAULT NULL,
                                        PRIMARY KEY (`id`),
                                        KEY `user_id` (`user_id`),
                                        KEY `courier_id` (`courier_id`),
                                        KEY `status` (`status`),
                                        KEY `tracking_number` (`tracking_number`),
                                        KEY `delivery_date` (`delivery_date`),
                                        CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
                                        CONSTRAINT `fk_orders_courier` FOREIGN KEY (`courier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `order_products` (
                                                `id` INT(11) NOT NULL AUTO_INCREMENT,
                                                `order_id` INT(11) NULL,
                                                `product_id` INT(11) NULL,
                                                `quantity` INT(11) NOT NULL,
                                                `price` DECIMAL(10, 2) NOT NULL,
                                                `subtotal` DECIMAL(10, 2) NOT NULL,
                                                PRIMARY KEY (`id`),
                                                KEY `order_id` (`order_id`),
                                                KEY `product_id` (`product_id`),
                                                CONSTRAINT `fk_order_products_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
                                                CONSTRAINT `fk_order_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `courier_locations` (
                                     `id` INT(11) NOT NULL AUTO_INCREMENT,
                                     `user_id` INT(11) NULL,
                                     `latitude` DECIMAL(10, 8) NOT NULL,
                                     `longitude` DECIMAL(11, 8) NOT NULL,
                                     `timestamp` BIGINT DEFAULT 0,
                                     PRIMARY KEY (`id`),
                                     KEY `user_id` (`user_id`),
                                     KEY `timestamp` (`timestamp`),
                                     CONSTRAINT `fk_courier_locations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `settings` (
                                          `id` INT(11) NOT NULL AUTO_INCREMENT,
                                          `key` VARCHAR(255) NOT NULL,
                                          `value` VARCHAR(255) NOT NULL,
                                          PRIMARY KEY (`id`),
                                          UNIQUE KEY `setting_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notifications` (
                                 `id` INT(11) NOT NULL AUTO_INCREMENT,
                                 `user_id` INT(11) NULL,
                                 `message` TEXT NOT NULL,
                                 `link` VARCHAR(255) NULL,
                                 `is_seen` TINYINT(1) DEFAULT 0,
                                 `created_at` BIGINT DEFAULT 0,
                                 PRIMARY KEY (`id`),
                                 KEY `user_id` (`user_id`),
                                 KEY `is_seen` (`is_seen`),
                                 KEY `created_at` (`created_at`),
                                 CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`key`, `value`)
VALUES ('email_sending', 'enabled'),
       ('tax_rate', 10.00),
       ('shipping_rate', 5.00),
       ('currency_code', '$'),
       ('timezone', 'Europe/Sofia'),
       ('date_format', 'm/d/Y');