CREATE DATABASE `deliverymanagement`;

USE `deliverymanagement`;

DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `order_products`;
DROP TABLE IF EXISTS `couriers`;
DROP TABLE IF EXISTS `settings`;

-- Create the `users` table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone_number` VARCHAR(20),
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` BIGINT DEFAULT UNIX_TIMESTAMP(),
`role` VARCHAR(20) NOT NULL,
`address` VARCHAR(255),
  `country` VARCHAR(255),
  `region` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the `products` table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `created_at` BIGINT DEFAULT UNIX_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the `orders` table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `country` VARCHAR(255) NOT NULL,
  `region` VARCHAR(255) NOT NULL,
  `status` VARCHAR(50) NOT NULL,
`product_price` DECIMAL(10, 2) NOT NULL,
`tax` DECIMAL(10, 2) NOT NULL,
`shipping_price` DECIMAL(10, 2) NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `created_at` BIGINT DEFAULT UNIX_TIMESTAMP(),
`last_processed` BIGINT DEFAULT UNIX_TIMESTAMP(),
`courier_id` INT(11) NOT NULL,
  `tracking_number` VARCHAR(100) DEFAULT NULL,
  `delivery_date` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the `order_details` table
CREATE TABLE IF NOT EXISTS `order_products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the `couriers` table
CREATE TABLE IF NOT EXISTS `couriers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `courier_name` VARCHAR(100) NOT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the `settings` table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`key`, `value`) 
VALUES ('email_sending', 'enabled'),
('tax_rate', 10.00),
('shipping_rate', 5.00),
('currency_code', '$');
