-- FLCar database skeleton
-- Target: MariaDB 11+ / MySQL 8+
-- This script relies on the currently authenticated MySQL user.
-- No CREATE USER / GRANT to a specific account is required.

CREATE DATABASE IF NOT EXISTS flcar_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE flcar_db;

SET NAMES utf8mb4;

-- =========================
-- 1) AUTH / ADMIN
-- =========================
CREATE TABLE IF NOT EXISTS admins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  role ENUM('super_admin','editor','sales') NOT NULL DEFAULT 'editor',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admins_username (username),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB;

-- =========================
-- 2) MASTER DATA (CAR)
-- =========================
CREATE TABLE IF NOT EXISTS brands (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  country VARCHAR(80) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_brands_name (name),
  UNIQUE KEY uq_brands_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS car_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_car_categories_name (name),
  UNIQUE KEY uq_car_categories_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cars (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL,                -- XE-001
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  brand_id BIGINT UNSIGNED NOT NULL,
  category_id BIGINT UNSIGNED NOT NULL,
  model_year SMALLINT UNSIGNED NOT NULL,
  price DECIMAL(15,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  status ENUM('available','reserved','sold') NOT NULL DEFAULT 'available',
  transmission VARCHAR(120) NULL,
  drivetrain VARCHAR(120) NULL,
  engine VARCHAR(180) NULL,
  power_hp SMALLINT UNSIGNED NULL,
  acceleration_0_100 DECIMAL(4,2) NULL,
  top_speed_kmh SMALLINT UNSIGNED NULL,
  seats TINYINT UNSIGNED NULL,
  fuel_type VARCHAR(50) NULL,
  hero_image VARCHAR(255) NULL,
  short_description VARCHAR(255) NULL,
  description TEXT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cars_code (code),
  UNIQUE KEY uq_cars_slug (slug),
  KEY idx_cars_brand (brand_id),
  KEY idx_cars_category (category_id),
  KEY idx_cars_status (status),
  KEY idx_cars_price (price),
  KEY idx_cars_year (model_year),
  CONSTRAINT fk_cars_brand FOREIGN KEY (brand_id) REFERENCES brands(id),
  CONSTRAINT fk_cars_category FOREIGN KEY (category_id) REFERENCES car_categories(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS car_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  car_id BIGINT UNSIGNED NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  alt_text VARCHAR(180) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_cover TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_car_images_car (car_id),
  KEY idx_car_images_cover (car_id, is_cover),
  CONSTRAINT fk_car_images_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- 3) CUSTOMER / ORDER FLOW
-- =========================
CREATE TABLE IF NOT EXISTS customers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NULL,
  phone VARCHAR(30) NULL,
  tier ENUM('new','regular','vip') NOT NULL DEFAULT 'new',
  source VARCHAR(80) NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_customers_tier (tier),
  KEY idx_customers_name (full_name),
  UNIQUE KEY uq_customers_email (email),
  UNIQUE KEY uq_customers_phone (phone)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_no VARCHAR(30) NOT NULL,            -- DH-001
  customer_id BIGINT UNSIGNED NOT NULL,
  car_id BIGINT UNSIGNED NOT NULL,
  order_type ENUM('purchase','deposit','consultation','test_drive','installment') NOT NULL DEFAULT 'purchase',
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(15,2) NOT NULL,
  total_amount DECIMAL(15,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'USD',
  status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  payment_status ENUM('unpaid','deposit_paid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  deposit_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  note TEXT NULL,
  created_by_admin_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_orders_order_no (order_no),
  KEY idx_orders_customer (customer_id),
  KEY idx_orders_car (car_id),
  KEY idx_orders_status (status),
  KEY idx_orders_date (order_date),
  CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_orders_car FOREIGN KEY (car_id) REFERENCES cars(id),
  CONSTRAINT fk_orders_admin FOREIGN KEY (created_by_admin_id) REFERENCES admins(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_status_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  old_status ENUM('pending','confirmed','cancelled','completed') NULL,
  new_status ENUM('pending','confirmed','cancelled','completed') NOT NULL,
  changed_by_admin_id BIGINT UNSIGNED NULL,
  changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  note VARCHAR(255) NULL,
  KEY idx_order_status_logs_order (order_id),
  CONSTRAINT fk_order_status_logs_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_status_logs_admin FOREIGN KEY (changed_by_admin_id) REFERENCES admins(id)
) ENGINE=InnoDB;

-- =========================
-- 4) LEADS / CONTACT
-- =========================
CREATE TABLE IF NOT EXISTS car_inquiries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NULL,
  car_id BIGINT UNSIGNED NULL,
  inquiry_type ENUM('installment','test_drive','price_quote','general') NOT NULL DEFAULT 'general',
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(150) NULL,
  message TEXT NULL,
  status ENUM('new','contacted','won','lost','spam') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_car_inquiries_status (status),
  KEY idx_car_inquiries_car (car_id),
  CONSTRAINT fk_car_inquiries_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
  CONSTRAINT fk_car_inquiries_car FOREIGN KEY (car_id) REFERENCES cars(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NULL,
  subject VARCHAR(180) NULL,
  message TEXT NOT NULL,
  status ENUM('new','read','replied','spam') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_contact_messages_status (status),
  KEY idx_contact_messages_created_at (created_at)
) ENGINE=InnoDB;

-- =========================
-- 5) NEWS / CONTENT
-- =========================
CREATE TABLE IF NOT EXISTS news_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_news_categories_name (name),
  UNIQUE KEY uq_news_categories_slug (slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS news_posts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id BIGINT UNSIGNED NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(280) NOT NULL,
  excerpt TEXT NULL,
  content LONGTEXT NULL,
  featured_image VARCHAR(255) NULL,
  published_at DATETIME NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  author_admin_id BIGINT UNSIGNED NULL,
  view_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_news_posts_slug (slug),
  KEY idx_news_posts_status (status),
  KEY idx_news_posts_published_at (published_at),
  KEY idx_news_posts_category (category_id),
  CONSTRAINT fk_news_posts_category FOREIGN KEY (category_id) REFERENCES news_categories(id),
  CONSTRAINT fk_news_posts_author FOREIGN KEY (author_admin_id) REFERENCES admins(id)
) ENGINE=InnoDB;

-- =========================
-- 6) APP SETTINGS
-- =========================
CREATE TABLE IF NOT EXISTS app_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NULL,
  description VARCHAR(255) NULL,
  updated_by_admin_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_app_settings_key (setting_key),
  CONSTRAINT fk_app_settings_admin FOREIGN KEY (updated_by_admin_id) REFERENCES admins(id)
) ENGINE=InnoDB;

-- Optional bootstrap values
INSERT IGNORE INTO app_settings (setting_key, setting_value, description)
VALUES
('APP_NAME', 'FLCar', 'Tên website/app'),
('CONTACT_EMAIL', 'info@flcar.vn', 'Email liên h? chính'),
('CONTACT_PHONE', '0900 000 000', 'Hotline liên h? chính');


-- =========================
-- 7) ORDER DETAILS (CHI TIET HOA DON)
-- =========================
CREATE TABLE IF NOT EXISTS order_details (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  car_id BIGINT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(15,2) NOT NULL,
  discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  total_price DECIMAL(15,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_order_details_order (order_id),
  KEY idx_order_details_car (car_id),
  CONSTRAINT fk_order_details_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_details_car FOREIGN KEY (car_id) REFERENCES cars(id)
) ENGINE=InnoDB;

-- =========================
-- 8) EMPLOYEES (NHAN VIEN)
-- =========================
CREATE TABLE IF NOT EXISTS employees (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NULL,
  position VARCHAR(100) NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_employees_code (code),
  UNIQUE KEY uq_employees_email (email)
) ENGINE=InnoDB;

-- =========================
-- 9) WARRANTIES (BAO HANH)
-- =========================
CREATE TABLE IF NOT EXISTS warranties (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  warranty_code VARCHAR(50) NOT NULL,
  order_id BIGINT UNSIGNED NULL,
  car_id BIGINT UNSIGNED NOT NULL,
  customer_id BIGINT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  terms TEXT NULL,
  status ENUM('active','expired','void') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_warranties_code (warranty_code),
  KEY idx_warranties_order (order_id),
  KEY idx_warranties_car (car_id),
  KEY idx_warranties_customer (customer_id),
  KEY idx_warranties_dates (start_date, end_date),
  CONSTRAINT fk_warranties_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_warranties_car FOREIGN KEY (car_id) REFERENCES cars(id),
  CONSTRAINT fk_warranties_customer FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB;
