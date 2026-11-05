CREATE DATABASE IF NOT EXISTS `wrestling_club_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `wrestling_club_db`;

-- جدول کاربران و مدیران سیستم
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'staff') DEFAULT 'admin',
    `photo` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- کاربر پیش‌فرض: admin / رمز: admin123
INSERT INTO `users` (`full_name`, `username`, `password`, `role`) VALUES
('مدیر عمومی', 'admin', '$2y$10$wE73i8xGg8vMtzYjVz00O.s6YqXQx6U3YyN4Qe4yXjK6k3j1jVf3.', 'admin');

-- جدول استادان (مربیان)
CREATE TABLE IF NOT EXISTS `coaches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `specialization` VARCHAR(150) NULL,
    `photo` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول گروه‌ها و تایم‌های تمرینی
CREATE TABLE IF NOT EXISTS `training_groups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `level` ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    `coach_id` INT NULL,
    `days` ENUM('odd', 'even', 'everyday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`coach_id`) REFERENCES `coaches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول ورزشکاران (شاگردان)
CREATE TABLE IF NOT EXISTS `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_code` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `father_name` VARCHAR(100) NULL,
    `phone` VARCHAR(20) NOT NULL,
    `birth_day` VARCHAR(2) NULL,
    `birth_month` VARCHAR(2) NULL,
    `birth_year` VARCHAR(4) NULL,
    `weight` DECIMAL(5,2) NULL,
    `photo` VARCHAR(255) NULL,
    `group_id` INT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `registered_at` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`group_id`) REFERENCES `training_groups`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول حاضری روزانه
CREATE TABLE IF NOT EXISTS `attendances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `shamsi_date` VARCHAR(10) NOT NULL, -- فرمت: 1405/06/24
    `status` ENUM('present', 'absent') DEFAULT 'present',
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- جدول فیس و پرداخت‌ها (با منطق داخله 500، فیس 400 و تخفیف)
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `payment_type` ENUM('first_time', 'monthly', 'admission_only') NOT NULL,
    `admission_amount` DECIMAL(10,2) DEFAULT 0.00,
    `monthly_amount` DECIMAL(10,2) DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
    `total_paid` DECIMAL(10,2) NOT NULL,
    `shamsi_month` VARCHAR(2) NOT NULL, -- مثلاً: 06 (سنبله)
    `shamsi_year` VARCHAR(4) NOT NULL,  -- مثلاً: 1405
    `shamsi_payment_date` VARCHAR(10) NOT NULL,
    `status` ENUM('paid', 'pending') DEFAULT 'paid',
    `note` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;