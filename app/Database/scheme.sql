-- ==============================================================================
-- JatheStride Footwear - TiDB Cloud & MySQL 8.0+ Database Schema
-- Compatible with TiDB Serverless, MySQL 8.0+, MariaDB, PlanetScale, Aiven
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `test`;
USE `test`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. Users Table (Admin Authentication)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(30) DEFAULT 'admin',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. Categories Table (Includes Kids, Casual, Sneakers, Running, Boots, Formal)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-shoe-prints',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. Shoes Table (Main Products Catalog)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shoes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `category_id` INT UNSIGNED NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `sale_price` DECIMAL(10,2) NULL,
    `description` TEXT NOT NULL,
    `image_url` VARCHAR(1000) NOT NULL,
    `gallery_images` TEXT NULL,
    `cloudinary_public_id` VARCHAR(255) NULL,
    `sizes_available` VARCHAR(150) DEFAULT '38,39,40,41,42,43,44,45',
    `colors_available` VARCHAR(255) NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `stock_quantity` INT NOT NULL DEFAULT 10,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_featured` (`is_featured`),
    CONSTRAINT `fk_shoe_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe column additions if `shoes` table was created earlier without these columns
ALTER TABLE `shoes` ADD COLUMN IF NOT EXISTS `gallery_images` TEXT NULL AFTER `image_url`;
ALTER TABLE `shoes` ADD COLUMN IF NOT EXISTS `cloudinary_public_id` VARCHAR(255) NULL AFTER `gallery_images`;
ALTER TABLE `shoes` ADD COLUMN IF NOT EXISTS `colors_available` VARCHAR(255) NULL AFTER `sizes_available`;

-- ------------------------------------------------------------------------------
-- 4. Shoe Size Stock Table (Inventory breakdown per EU size)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shoe_size_stock` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shoe_id` INT UNSIGNED NOT NULL,
    `size` VARCHAR(20) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 0,
    UNIQUE KEY `uq_shoe_size_stock` (`shoe_id`, `size`),
    CONSTRAINT `fk_shoe_size_stock_shoe` FOREIGN KEY (`shoe_id`) REFERENCES `shoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- SEED DATA
-- ==============================================================================

-- Seed Admin User (Email: admin@solestore.com | Password: AdminSecurePass2025!)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`) VALUES
(1, 'Store Admin', 'admin@solestore.com', '$2y$10$kI4XLT.u88CF5JIMPct6r..Mw9i.Raf/Gw6CVpXaTx5y/swvhrszi', 'admin')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Seed All Categories (Including Kids & Casual)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Sneakers', 'sneakers', 'Low-profile and high-top street lifestyle footwear.', 'fa-bolt'),
(2, 'Running', 'running', 'Ultralight responsive carbon-plated and cushioned runners.', 'fa-person-running'),
(3, 'Boots', 'boots', 'Rugged, water-resistant handcrafted leather explorer boots.', 'fa-mountain'),
(4, 'Formal', 'formal', 'Sleek Goodyear-welted oxfords, brogues and derby dress shoes.', 'fa-briefcase'),
(5, 'Casual', 'casual', 'Comfortable everyday shoes for relaxed, versatile wear.', 'fa-shoe-prints'),
(6, 'Kids', 'kids', 'Comfortable, durable footwear for children and toddlers.', 'fa-child')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `icon`=VALUES(`icon`);

-- Seed Shoes Products (with Kids and Casual footwear)
INSERT INTO `shoes` (`id`, `name`, `slug`, `category_id`, `price`, `sale_price`, `description`, `image_url`, `is_featured`, `stock_quantity`, `sizes_available`, `colors_available`) VALUES
(1, 'Apex Volt HyperGlide Runner', 'apex-volt-hyperglide-runner', 2, 179.99, 159.99, 'Ultra-responsive carbon fiber sprint plate paired with dual-density nitrogen foam for effortless energy return over marathon distances.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80', 1, 35, '39,40,41,42,43,44,45', 'Red, Black, Volt'),
(2, 'Retro High Court Classic', 'retro-high-court-classic', 1, 139.99, NULL, 'Premium tumble leather silhouette with iconic color blocking, perforated toe box, and vintage off-white vulcanized rubber outsole.', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=80', 1, 18, '38,39,40,41,42,43,44', 'White, Blue, Red'),
(3, 'Junior Stride Runner (Kids)', 'junior-stride-runner-kids', 6, 89.99, 74.99, 'Ultra-durable, breathable mesh upper with hook-and-loop strap for easy on/off. Cushioned midsole absorbs impact for active children at school and playground.', 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=1000&q=80', 1, 25, '28,29,30,31,32,33,34,35', 'Blue, Orange, Pink'),
(4, 'IronClad Heritage Waterproof Boot', 'ironclad-heritage-waterproof-boot', 3, 219.99, 199.99, 'Full-grain waxed nubuck leather with storm-welt construction and Vibram commando lug tread built to conquer rugged mountain terrain.', 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=80', 1, 12, '40,41,42,43,44,45', 'Brown, Tan, Black'),
(5, 'Kensington Hand-Burnished Oxford', 'kensington-hand-burnished-oxford', 4, 249.99, NULL, 'Crafted from full-grain Italian calfskin with a classic cap toe, closed lacing system, and hand-finished dark cognac patina.', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80', 0, 9, '40,41,42,43,44', 'Cognac, Dark Brown, Black'),
(6, 'Urban Slip-On Ease (Casual)', 'urban-slip-on-ease-casual', 5, 99.00, 85.00, 'Lightweight slip-on footwear with memory foam insole and flexible knit collar. Effortless comfort for daily commutes, travel, and casual outings.', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=1000&q=80', 1, 30, '39,40,41,42,43,44', 'Grey, Black, Navy'),
(7, 'Mini All-Star Sneaker (Kids)', 'mini-all-star-sneaker-kids', 6, 79.00, NULL, 'Iconic mini low-top canvas sneaker built with reinforced rubber toe cap and non-marking outsole for toddler boys and girls.', 'https://images.unsplash.com/photo-1507464098880-e367bc5d2c08?auto=format&fit=crop&w=1000&q=80', 0, 20, '26,27,28,29,30,31,32', 'Black, White, Red'),
(8, 'AirPulse Velocity Pro', 'airpulse-velocity-pro', 2, 164.50, NULL, 'Breathable engineered mesh upper with dynamic FlyWire lockdown cables and sculpted aerodynamic heel crash pads.', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=80', 0, 24, '39,40,41,42,43,44,45', 'Black, Lime, White')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `price`=VALUES(`price`), `sale_price`=VALUES(`sale_price`), `stock_quantity`=VALUES(`stock_quantity`);

-- Seed Initial Size Stock Breakdown
INSERT INTO `shoe_size_stock` (`shoe_id`, `size`, `quantity`) VALUES
(1, '39', 5), (1, '40', 5), (1, '41', 5), (1, '42', 5), (1, '43', 5), (1, '44', 5), (1, '45', 5),
(2, '38', 2), (2, '39', 3), (2, '40', 3), (2, '41', 3), (2, '42', 3), (2, '43', 2), (2, '44', 2),
(3, '28', 3), (3, '29', 3), (3, '30', 4), (3, '31', 4), (3, '32', 4), (3, '33', 3), (3, '34', 2), (3, '35', 2),
(4, '40', 2), (4, '41', 2), (4, '42', 3), (4, '43', 2), (4, '44', 2), (4, '45', 1),
(5, '40', 2), (5, '41', 2), (5, '42', 2), (5, '43', 2), (5, '44', 1),
(6, '39', 5), (6, '40', 5), (6, '41', 5), (6, '42', 5), (6, '43', 5), (6, '44', 5),
(7, '26', 3), (7, '27', 3), (7, '28', 3), (7, '29', 3), (7, '30', 3), (7, '31', 3), (7, '32', 2),
(8, '39', 3), (8, '40', 4), (8, '41', 4), (8, '42', 4), (8, '43', 4), (8, '44', 3), (8, '45', 2)
ON DUPLICATE KEY UPDATE `quantity`=VALUES(`quantity`);

SET FOREIGN_KEY_CHECKS = 1;
