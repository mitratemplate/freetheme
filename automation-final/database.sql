-- سامانه جامع اتوماسیون اداری و منابع انسانی - نسخه Enterprise v5.0
-- سازگار با استانداردهای کار ایران ۱۴۰۵
-- دیتابیس: automation_db

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS `automation_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `automation_db`;

-- --------------------------------------------------------
-- جدول پیکربندی سیستم
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'سامانه اتوماسیون اداری'),
('site_version', '5.0.0'),
('maintenance_mode', '0'),
('gapgpt_api_key', ''),
('gapgpt_api_url', 'https://api.gapgpt.app/v1/chat/completions');

-- --------------------------------------------------------
-- جدول استانداردهای حقوق و دستمزد (سال ۱۴۰۵)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `salary_standards`;
CREATE TABLE `salary_standards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `base_salary` decimal(15,0) NOT NULL COMMENT 'حقوق پایه روزانه',
  `housing_allowance` decimal(15,0) NOT NULL COMMENT 'حق مسکن ماهانه',
  `food_allowance` decimal(15,0) NOT NULL COMMENT 'بن خواربار ماهانه',
  `child_allowance` decimal(15,0) NOT NULL COMMENT 'حق اولاد ماهانه برای هر فرزند',
  `min_wage_monthly` decimal(15,0) NOT NULL COMMENT 'حداقل حقوق ماهانه',
  `overtime_hourly_rate` decimal(15,2) NOT NULL COMMENT 'نرخ هر ساعت اضافه کاری',
  `night_shift_diff` decimal(5,2) NOT NULL DEFAULT 35.00 COMMENT 'درصد فوق‌العاده شب کاری',
  `insurance_rate_employee` decimal(5,2) NOT NULL DEFAULT 7.00 COMMENT 'سهم بیمه کارمند',
  `tax_brackets` json DEFAULT NULL COMMENT 'جدول مالیاتی پلکانی',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_standards` (`year`, `base_salary`, `housing_allowance`, `food_allowance`, `child_allowance`, `min_wage_monthly`, `overtime_hourly_rate`, `tax_brackets`) VALUES
(1405, 2386000, 9000000, 1400000, 714000, 35000000, 145000.00, '[{"min": 0, "max": 12000000, "rate": 0}, {"min": 12000001, "max": 16000000, "rate": 10}, {"min": 16000001, "max": 25000000, "rate": 15}, {"min": 25000001, "max": 100000000, "rate": 20}]');

-- --------------------------------------------------------
-- جدول نقش‌ها و دسترسی‌ها
-- --------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `permissions` json NOT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`name`, `slug`, `permissions`, `is_system`) VALUES
('مدیر کل', 'super-admin', '{\"*\": \"*\"}', 1),
('مدیر منابع انسانی', 'hr-manager', '{\"hr\": \"*\", \"employees\": \"*\", \"recruitment\": \"*\"}', 1),
('مدیر مالی', 'finance-manager', '{\"payroll\": \"*\", \"loans\": \"*\", \"reports\": \"financial\"}', 1),
('مدیر واحد', 'department-manager', '{\"employees\": \"view\", \"attendance\": \"approve\", \"leave\": \"approve\"}', 1),
('کارمند', 'employee', '{\"profile\": \"own\", \"attendance\": \"own\", \"leave\": \"own\"}', 1);

-- --------------------------------------------------------
-- جدول کاربران
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- کاربر پیش‌فرض (رمز: admin123)
INSERT INTO `users` (`username`, `password`, `role_id`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- --------------------------------------------------------
-- جدول کارکنان
-- --------------------------------------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `father_name` varchar(50) DEFAULT NULL,
  `national_code` varchar(10) NOT NULL,
  `personnel_code` varchar(20) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `postal_code` varchar(10) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `employment_type` enum('permanent','contract','temporary','part_time') DEFAULT 'contract',
  `hire_date` date DEFAULT NULL,
  `insurance_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','terminated','retired') DEFAULT 'active',
  `photo_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `national_code` (`national_code`),
  UNIQUE KEY `personnel_code` (`personnel_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول حضور و غیاب
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT 0.00,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `status` enum('present','absent','late','half_day','mission','leave') DEFAULT 'present',
  `description` text,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `date` (`date`),
  CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول مرخصی‌ها
-- --------------------------------------------------------
DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('annual','sick','unpaid','maternity','paternity','emergency') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_count` decimal(5,1) NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approver_id` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول فیش حقوقی
-- --------------------------------------------------------
DROP TABLE IF EXISTS `payrolls`;
CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `base_salary` decimal(15,0) DEFAULT 0,
  `allowances` json DEFAULT NULL COMMENT 'مزایا',
  `deductions` json DEFAULT NULL COMMENT 'کسورات',
  `gross_pay` decimal(15,0) DEFAULT 0,
  `net_pay` decimal(15,0) DEFAULT 0,
  `payment_status` enum('pending','paid','transfer') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_payroll` (`employee_id`, `month`, `year`),
  CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول لاگ هوش مصنوعی
-- --------------------------------------------------------
DROP TABLE IF EXISTS `ai_logs`;
CREATE TABLE `ai_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `prompt` text NOT NULL,
  `response` text,
  `model` varchar(50) DEFAULT 'gapgpt-4',
  `tokens_used` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- جدول پلاگین‌ها
-- --------------------------------------------------------
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `version` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `settings` json DEFAULT NULL,
  `installed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
