-- ============================================
-- سیستم جامع اتوماسیون اداری و منابع انسانی
-- مخصوص شرکت‌های ایرانی - سال ۱۴۰۵
-- نسخه ۲.۰ با مدیریت کامل منابع انسانی
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `automation_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
USE `automation_db`;

-- جداول پایه (۴۰+ جدول)
-- برای مشاهده ساختار کامل به فایل README.md مراجعه کنید

-- نمونه جداول اصلی
CREATE TABLE `salary_standards` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `year` INT NOT NULL,
  `base_salary` DECIMAL(15,0) NOT NULL,
  `housing_allowance` DECIMAL(15,0) NOT NULL,
  `food_allowance` DECIMAL(15,0) NOT NULL,
  `child_allowance` DECIMAL(15,0) NOT NULL,
  `max_child_count` INT DEFAULT 2,
  `insurance_rate_employee` DECIMAL(5,2) DEFAULT 7.00,
  `insurance_rate_employer` DECIMAL(5,2) DEFAULT 23.00,
  `overtime_multiplier` DECIMAL(5,2) DEFAULT 1.40,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `departments` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `parent_id` INT DEFAULT NULL,
  `description` TEXT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `employees` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `employee_code` VARCHAR(20) UNIQUE NOT NULL,
  `national_code` VARCHAR(10) UNIQUE NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `gender` ENUM('male', 'female') NOT NULL,
  `birth_date` DATE NOT NULL,
  `hire_date` DATE NOT NULL,
  `contract_type` ENUM('permanent', 'fixed_term', 'temporary', 'part_time', 'project') DEFAULT 'fixed_term',
  `department_id` INT,
  `base_salary` DECIMAL(15,0),
  `status` ENUM('active', 'on_leave', 'suspended', 'terminated') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
  INDEX `idx_employee_code` (`employee_code`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `employee_id` INT,
  `role` ENUM('super_admin', 'admin', 'hr_manager', 'hr_staff', 'finance', 'manager', 'employee') NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- داده‌های اولیه
INSERT INTO `salary_standards` (`year`, `base_salary`, `housing_allowance`, `food_allowance`, `child_allowance`) VALUES
(1405, 85000000, 45000000, 15000000, 8500000);

INSERT INTO `departments` (`name`, `description`) VALUES
('مدیریت عامل', 'دفتر مدیریت ارشد'),
('منابع انسانی', 'مدیریت سرمایه انسانی'),
('فناوری اطلاعات', 'واحد فناوری و سیستم‌ها'),
('مالی و حسابداری', 'امور مالی و حسابداری');

INSERT INTO `users` (`username`, `password_hash`, `role`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', TRUE);

COMMIT;
