SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admins, settings, sections, categories, catalogue_groups, testimonials, pages,
  users, bookings, booking_offers, booking_messages, booking_feedback, form_submissions, subscribers, notifications,
  provider_applications;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'admin',
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  skey VARCHAR(100) NOT NULL PRIMARY KEY,
  svalue TEXT NULL,
  label VARCHAR(150) NULL,
  grp VARCHAR(40) NOT NULL DEFAULT 'general',
  input VARCHAR(20) NOT NULL DEFAULT 'text',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  skey VARCHAR(80) NOT NULL UNIQUE,
  page VARCHAR(40) NOT NULL DEFAULT 'home',
  label VARCHAR(120) NOT NULL,
  tag VARCHAR(255) NULL,
  title TEXT NULL,
  emphasis VARCHAR(255) NULL,
  description TEXT NULL,
  data LONGTEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  icon VARCHAR(40) NULL,
  short_desc VARCHAR(255) NULL,
  description TEXT NULL,
  items TEXT NULL,
  meta_title VARCHAR(191) NULL,
  meta_description VARCHAR(300) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE catalogue_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  count_label VARCHAR(120) NULL,
  tone VARCHAR(20) NOT NULL DEFAULT 'green',
  note TEXT NULL,
  body TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  text TEXT NOT NULL,
  author VARCHAR(150) NOT NULL,
  location VARCHAR(150) NULL,
  stars TINYINT NOT NULL DEFAULT 5,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(160) NOT NULL UNIQUE,
  title VARCHAR(191) NOT NULL,
  meta_description VARCHAR(300) NULL,
  content LONGTEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role ENUM('customer','provider') NOT NULL DEFAULT 'customer',
  name VARCHAR(150) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  phone VARCHAR(40) NULL,
  cnic VARCHAR(20) NULL,
  city VARCHAR(80) NULL,
  password VARCHAR(255) NOT NULL,
  category VARCHAR(150) NULL,
  subcategory VARCHAR(150) NULL,
  experience INT NULL,
  service_areas VARCHAR(255) NULL,
  qualifications TEXT NULL,
  status ENUM('pending','active','suspended') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT UNSIGNED NULL,
  provider_id INT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(191) NULL,
  cnic VARCHAR(20) NULL,
  city VARCHAR(80) NULL,
  address VARCHAR(255) NULL,
  map_link VARCHAR(255) NULL,
  category VARCHAR(150) NULL,
  service VARCHAR(150) NULL,
  description TEXT NULL,
  urgency ENUM('normal','urgent','emergency') NOT NULL DEFAULT 'normal',
  preferred_date DATE NULL,
  budget DECIMAL(12,2) NULL,
  quotation_required TINYINT(1) NOT NULL DEFAULT 0,
  payment_method VARCHAR(40) NULL,
  status ENUM('requested','provider_joined','quoted','deal_locked','in_progress','completed','cancelled') NOT NULL DEFAULT 'requested',
  deal_amount DECIMAL(12,2) NULL,
  source VARCHAR(20) NOT NULL DEFAULT 'portal',
  admin_note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_customer (customer_id),
  KEY idx_provider (provider_id),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_offers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL,
  by_role ENUM('customer','provider') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  message VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  role ENUM('customer','provider') NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE booking_feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL UNIQUE,
  rating TINYINT NOT NULL,
  professionalism TINYINT NULL,
  communication TINYINT NULL,
  punctuality TINYINT NULL,
  cleanliness TINYINT NULL,
  behaviour TINYINT NULL,
  recommend TINYINT(1) NOT NULL DEFAULT 1,
  comments TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE form_submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref VARCHAR(30) NOT NULL,
  form_type VARCHAR(40) NOT NULL,
  user_id INT UNSIGNED NULL,
  name VARCHAR(150) NULL,
  email VARCHAR(191) NULL,
  phone VARCHAR(40) NULL,
  data LONGTEXT NULL,
  status ENUM('new','in_review','resolved') NOT NULL DEFAULT 'new',
  admin_note TEXT NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_type (form_type),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscribers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(191) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(191) NOT NULL,
  body VARCHAR(500) NULL,
  link VARCHAR(255) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Professional registration applications (account/register.php?role=provider)
CREATE TABLE provider_applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref VARCHAR(30) NOT NULL UNIQUE,
  user_id INT UNSIGNED NULL,
  provider_type VARCHAR(40) NOT NULL,
  category VARCHAR(150) NOT NULL,
  tier VARCHAR(4) NOT NULL,
  city VARCHAR(80) NULL,
  waitlist TINYINT(1) NOT NULL DEFAULT 0,
  data LONGTEXT NOT NULL,
  documents LONGTEXT NULL,
  fees LONGTEXT NULL,
  status ENUM('submitted','under_review','approved','rejected') NOT NULL DEFAULT 'submitted',
  admin_notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  KEY user_id (user_id),
  KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
