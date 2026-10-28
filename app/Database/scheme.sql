-- ==============================================================================
-- ApexStride Shoe E-commerce Schema (CodeIgniter 4 on Vercel)
-- Compatible with MySQL 8.0+, MariaDB, PlanetScale, TiDB, Supabase, Aiven
-- ==============================================================================

-- 1. Users Table (Admin authentication)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(30) DEFAULT 'admin',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-shoe-prints',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Shoes Table
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
    `sizes_available` VARCHAR(150) DEFAULT '7,8,8.5,9,9.5,10,10.5,11,12',
    `colors_available` VARCHAR(255) NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `stock_quantity` INT NOT NULL DEFAULT 10,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category_id`),
    INDEX `idx_featured` (`is_featured`),
    CONSTRAINT `fk_shoe_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Seed Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Sneakers', 'sneakers', 'Low-profile and high-top street lifestyle footwear.', 'fa-bolt'),
(2, 'Running', 'running', 'Ultralight responsive carbon-plated and cushioned runners.', 'fa-person-running'),
(3, 'Boots', 'boots', 'Rugged, water-resistant handcrafted leather explorer boots.', 'fa-mountain'),
(4, 'Formal', 'formal', 'Sleek Goodyear-welted oxfords, brogues and derby dress shoes.', 'fa-briefcase'),
(5, 'Casual', 'casual', 'Comfortable everyday shoes for relaxed, versatile wear.', 'fa-shoe-prints')
ON DUPLICATE KEY UPDATE `name`=`name`;

-- Seed Shoes
INSERT INTO `shoes` (`id`, `name`, `slug`, `category_id`, `price`, `sale_price`, `description`, `image_url`, `is_featured`, `stock_quantity`, `sizes_available`) VALUES
(1, 'Apex Volt HyperGlide Runner', 'apex-volt-hyperglide-runner', 2, 179.99, 159.99, 'Ultra-responsive carbon fiber sprint plate paired with dual-density nitrogen foam for effortless energy return over marathon distances.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1000&q=80', 1, 35, '8,8.5,9,9.5,10,10.5,11,12'),
(2, 'Retro High Court Classic', 'retro-high-court-classic', 1, 139.99, NULL, 'Premium tumble leather silhouette with iconic color blocking, perforated toe box, and vintage off-white vulcanized rubber outsole.', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1000&q=80', 1, 18, '7,8,8.5,9,9.5,10,11,12'),
(3, 'IronClad Heritage Waterproof Boot', 'ironclad-heritage-waterproof-boot', 3, 219.99, 199.99, 'Full-grain waxed nubuck leather with storm-welt construction and Vibram commando lug tread built to conquer rugged mountain terrain.', 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?auto=format&fit=crop&w=1000&q=80', 1, 12, '8,9,9.5,10,10.5,11,12'),
(4, 'Kensington Hand-Burnished Oxford', 'kensington-hand-burnished-oxford', 4, 249.99, NULL, 'Crafted from full-grain Italian calfskin with a classic cap toe, closed lacing system, and hand-finished dark cognac patina.', 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80', 0, 9, '8,8.5,9,9.5,10,10.5,11'),
(5, 'AirPulse Velocity Pro', 'airpulse-velocity-pro', 2, 164.50, NULL, 'Breathable engineered mesh upper with dynamic FlyWire lockdown cables and sculpted aerodynamic heel crash pads.', 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?auto=format&fit=crop&w=1000&q=80', 0, 24, '7.5,8,8.5,9,9.5,10,10.5,11,12'),
(6, 'Urban Nomad Minimalist Sneaker', 'urban-nomad-minimalist-sneaker', 1, 119.00, 99.00, 'Ultra-clean monochromatic low-top sneaker lined in vegetable-tanned leather with cushioned Ortholite memory insoles.', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=1000&q=80', 1, 40, '7,8,8.5,9,10,11,12'),
(7, 'Timberland Scout Alpine Hiker', 'timberland-scout-alpine-hiker', 3, 189.99, NULL, 'Thinsulate thermal lining with rust-proof D-ring hardware and sealed waterproof bootie construction for snow and wet dirt.', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=1000&q=80', 0, 15, '8,8.5,9,9.5,10,10.5,11,12'),
(8, 'Savile Row Double Monk Strap', 'savile-row-double-monk-strap', 4, 229.00, 199.00, 'Polished midnight black leather with silver-finish buckle hardware, channeled leather sole, and gentleman bevel waist.', 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?auto=format&fit=crop&w=1000&q=80', 0, 7, '8,9,9.5,10,10.5,11')
ON DUPLICATE KEY UPDATE `name`=`name`;
