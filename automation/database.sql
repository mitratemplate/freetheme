-- =====================================================
-- سامانه اتوماسیون اداری حرفه‌ای - Enterprise v3.0
-- پایگاه داده MySQL - استانداردهای سال ۱۴۰۵
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- ایجاد پایگاه داده
-- -----------------------------------------------------
CREATE DATABASE IF NOT EXISTS `automation_pro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `automation_pro`;

-- -----------------------------------------------------
-- جدول نقش‌ها (Roles)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_roles`;
CREATE TABLE `ap_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL UNIQUE,
  `permissions` json DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ap_roles` (`name`, `slug`, `permissions`, `is_system`) VALUES
('مدیر کل', 'super-admin', CAST('{"*": "*"}' AS JSON), 1),
('مدیر منابع انسانی', 'hr-manager', CAST('{"hr": "*", "employees": "*", "recruitment": "*", "attendance": "*", "leave": "*"}' AS JSON), 1),
('مدیر مالی', 'finance-manager', CAST('{"payroll": "*", "loans": "*", "reports": "financial"}' AS JSON), 1),
('مدیر واحد', 'department-manager', CAST('{"employees": "view", "attendance": "approve", "leave": "approve"}' AS JSON), 1),
('کارمند', 'employee', CAST('{"profile": "own", "attendance": "own", "leave": "own"}' AS JSON), 1);

-- -----------------------------------------------------
-- جدول کاربران (Users)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_users`;
CREATE TABLE `ap_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role_id` varchar(50) NOT NULL DEFAULT 'employee',
  `employee_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `ap_roles`(`slug`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- کاربر پیش‌فرض admin / admin123
INSERT INTO `ap_users` (`username`, `password`, `full_name`, `email`, `role_id`, `is_active`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', 'admin@example.com', 'super-admin', 1);

-- -----------------------------------------------------
-- جدول واحدهای سازمانی (Departments)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_departments`;
CREATE TABLE `ap_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`parent_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ap_departments` (`name`, `description`) VALUES
('مدیریت عامل', 'واحد مدیریت ارشد'),
('منابع انسانی', 'امور پرسنلی و جذب نیرو'),
('مالی و حسابداری', 'امور مالی، حقوق و دستمزد'),
('فناوری اطلاعات', 'زیرساخت و پشتیبانی فنی'),
('فروش و بازاریابی', 'امور فروش و توسعه بازار'),
('تولید', 'واحد تولید و عملیات');

-- -----------------------------------------------------
-- جدول کارکنان (Employees)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_employees`;
CREATE TABLE `ap_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `father_name` varchar(50) DEFAULT NULL,
  `national_code` varchar(10) NOT NULL UNIQUE,
  `personnel_code` varchar(20) NOT NULL UNIQUE,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `contract_type` enum('permanent','contract','temporary','parttime') DEFAULT 'contract',
  `base_salary` decimal(15,2) DEFAULT 0,
  `hire_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `education` varchar(50) DEFAULT NULL,
  `marital_status` enum('single','married') DEFAULT 'single',
  `children_count` int(2) DEFAULT 0,
  `insurance_number` varchar(20) DEFAULT NULL,
  `tax_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `resume` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`department_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
  INDEX `idx_national_code` (`national_code`),
  INDEX `idx_personnel_code` (`personnel_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول حضور و غیاب (Attendance)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_attendance`;
CREATE TABLE `ap_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `status` enum('present','absent','late','half_day','mission','leave') DEFAULT 'present',
  `overtime_hours` decimal(5,2) DEFAULT 0,
  `undertime_hours` decimal(5,2) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_date` (`employee_id`, `date`),
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول درخواست‌های مرخصی (Leave Requests)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_leave_requests`;
CREATE TABLE `ap_leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('annual','sick','unpaid','maternity','paternity','emergency','pilgrimage') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_count` int(3) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول مانده مرخصی (Leave Balances)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_leave_balances`;
CREATE TABLE `ap_leave_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `annual_days` decimal(5,2) DEFAULT 26,
  `used_days` decimal(5,2) DEFAULT 0,
  `remaining_days` decimal(5,2) DEFAULT 26,
  `carried_over` decimal(5,2) DEFAULT 0,
  `expired_days` decimal(5,2) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_year` (`employee_id`, `year`),
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول استانداردهای حقوق (Salary Standards)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_salary_standards`;
CREATE TABLE `ap_salary_standards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `base_salary` decimal(15,2) NOT NULL DEFAULT 12000000,
  `housing_allowance` decimal(15,2) NOT NULL DEFAULT 3000000,
  `food_allowance` decimal(15,2) NOT NULL DEFAULT 1500000,
  `child_allowance` decimal(15,2) NOT NULL DEFAULT 800000,
  `overtime_hourly` decimal(15,2) NOT NULL DEFAULT 55000,
  `night_shift_bonus` decimal(5,4) NOT NULL DEFAULT 0.35,
  `holiday_bonus` decimal(5,4) NOT NULL DEFAULT 0.40,
  `min_wage` decimal(15,2) NOT NULL DEFAULT 12000000,
  `insurance_ceiling` decimal(15,2) NOT NULL DEFAULT 25000000,
  `tax_exemption` decimal(15,2) NOT NULL DEFAULT 100000000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ap_salary_standards` (`year`, `base_salary`, `housing_allowance`, `food_allowance`, `child_allowance`, `overtime_hourly`, `night_shift_bonus`, `holiday_bonus`, `min_wage`, `insurance_ceiling`, `tax_exemption`) VALUES
(1405, 12000000, 3000000, 1500000, 800000, 55000, 0.35, 0.40, 12000000, 25000000, 100000000);

-- -----------------------------------------------------
-- جدول فیش حقوقی (Payroll)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_payroll`;
CREATE TABLE `ap_payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `month` char(7) NOT NULL,
  `year` int(4) NOT NULL,
  `base_salary` decimal(15,2) NOT NULL DEFAULT 0,
  `housing_allowance` decimal(15,2) DEFAULT 0,
  `food_allowance` decimal(15,2) DEFAULT 0,
  `child_allowance` decimal(15,2) DEFAULT 0,
  `overtime_pay` decimal(15,2) DEFAULT 0,
  `overtime_hours` decimal(5,2) DEFAULT 0,
  `night_shift_pay` decimal(15,2) DEFAULT 0,
  `holiday_pay` decimal(15,2) DEFAULT 0,
  `bonus` decimal(15,2) DEFAULT 0,
  `gross_salary` decimal(15,2) DEFAULT 0,
  `insurance_deduction` decimal(15,2) DEFAULT 0,
  `tax_deduction` decimal(15,2) DEFAULT 0,
  `loan_deduction` decimal(15,2) DEFAULT 0,
  `advance_deduction` decimal(15,2) DEFAULT 0,
  `other_deductions` decimal(15,2) DEFAULT 0,
  `total_deductions` decimal(15,2) DEFAULT 0,
  `net_salary` decimal(15,2) DEFAULT 0,
  `payment_date` date DEFAULT NULL,
  `payment_ref` varchar(50) DEFAULT NULL,
  `status` enum('draft','calculated','approved','paid','cancelled') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_month` (`employee_id`, `month`, `year`),
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_month_year` (`month`, `year`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول وام‌ها (Loans)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_loans`;
CREATE TABLE `ap_loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `loan_type` enum('housing','marriage','emergency','vehicle','other') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0,
  `installments` int(3) NOT NULL,
  `paid_installments` int(3) DEFAULT 0,
  `monthly_installment` decimal(15,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','approved','active','completed','cancelled') DEFAULT 'pending',
  `guarantor` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول پرداخت اقساط وام (Loan Payments)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_loan_payments`;
CREATE TABLE `ap_loan_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `installment_number` int(3) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('pending','paid','late','cancelled') DEFAULT 'pending',
  `deducted_from_payroll` tinyint(1) DEFAULT 0,
  `payroll_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`loan_id`) REFERENCES `ap_loans`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payroll_id`) REFERENCES `ap_payroll`(`id`) ON DELETE SET NULL,
  INDEX `idx_due_date` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول پلاگین‌ها (Plugins)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_plugins`;
CREATE TABLE `ap_plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL UNIQUE,
  `name` varchar(200) NOT NULL,
  `version` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `license` varchar(50) DEFAULT 'proprietary',
  `license_key` varchar(100) DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `is_installed` tinyint(1) DEFAULT 0,
  `installed_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول لاگ رویدادها (Activity Logs)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_activity_logs`;
CREATE TABLE `ap_activity_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_action` (`action`),
  INDEX `idx_entity` (`entity_type`, `entity_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول جلسات (Meetings)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_meetings`;
CREATE TABLE `ap_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `meeting_type` enum('regular','emergency','board','staff','training') DEFAULT 'regular',
  `location` varchar(100) DEFAULT NULL,
  `online_link` varchar(255) DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `organizer_id` int(11) NOT NULL,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `minutes` text DEFAULT NULL,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organizer_id`) REFERENCES `ap_users`(`id`) ON DELETE CASCADE,
  INDEX `idx_datetime` (`start_datetime`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول شرکت‌کنندگان جلسه (Meeting Attendees)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_meeting_attendees`;
CREATE TABLE `ap_meeting_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `status` enum('invited','accepted','declined','attended','absent') DEFAULT 'invited',
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`meeting_id`) REFERENCES `ap_meetings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_meeting_employee` (`meeting_id`, `employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول اطلاعیه‌ها (Announcements)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_announcements`;
CREATE TABLE `ap_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `type` enum('info','warning','urgent','success') DEFAULT 'info',
  `priority` int(2) DEFAULT 0,
  `target_audience` enum('all','employees','managers','specific') DEFAULT 'all',
  `target_ids` json DEFAULT NULL,
  `publish_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` datetime DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `ap_users`(`id`) ON DELETE CASCADE,
  INDEX `idx_publish` (`publish_date`, `expiry_date`),
  INDEX `idx_priority` (`priority`, `is_pinned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول مستندات (Documents)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_documents`;
CREATE TABLE `ap_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT 0,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `access_level` enum('public','internal','confidential','restricted') DEFAULT 'internal',
  `download_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `ap_users`(`id`) ON DELETE CASCADE,
  INDEX `idx_category` (`category`),
  INDEX `idx_access` (`access_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول ارزیابی عملکرد (Performance Reviews)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_performance_reviews`;
CREATE TABLE `ap_performance_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_period_start` date NOT NULL,
  `review_period_end` date NOT NULL,
  `review_type` enum('self','manager','peer','subordinate','360') DEFAULT 'manager',
  `overall_score` decimal(3,2) DEFAULT 0,
  `strengths` text DEFAULT NULL,
  `weaknesses` text DEFAULT NULL,
  `goals` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` enum('draft','submitted','acknowledged','completed') DEFAULT 'draft',
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_id`) REFERENCES `ap_users`(`id`) ON DELETE CASCADE,
  INDEX `idx_period` (`review_period_start`, `review_period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول شاخص‌های عملکرد (KPIs)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_kpis`;
CREATE TABLE `ap_kpis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `review_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT 1,
  `target_value` decimal(10,2) DEFAULT NULL,
  `actual_value` decimal(10,2) DEFAULT NULL,
  `score` decimal(3,2) DEFAULT 0,
  `comments` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`review_id`) REFERENCES `ap_performance_reviews`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول آموزش‌ها (Trainings)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_trainings`;
CREATE TABLE `ap_trainings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `trainer` varchar(100) DEFAULT NULL,
  `training_type` enum('internal','external','online','workshop','seminar') DEFAULT 'internal',
  `category` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `duration_hours` decimal(5,2) DEFAULT 0,
  `location` varchar(100) DEFAULT NULL,
  `max_participants` int(5) DEFAULT 0,
  `cost` decimal(15,2) DEFAULT 0,
  `certificate` tinyint(1) DEFAULT 0,
  `status` enum('planned','ongoing','completed','cancelled') DEFAULT 'planned',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول شرکت‌کنندگان آموزش (Training Participants)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_training_participants`;
CREATE TABLE `ap_training_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `registration_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `attendance_status` enum('registered','attended','absent','dropped') DEFAULT 'registered',
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0,
  `certificate_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`training_id`) REFERENCES `ap_trainings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_training_employee` (`training_id`, `employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول استخدام (Recruitment)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_recruitments`;
CREATE TABLE `ap_recruitments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_title` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `positions` int(3) DEFAULT 1,
  `filled_positions` int(3) DEFAULT 0,
  `job_description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `salary_range_min` decimal(15,2) DEFAULT NULL,
  `salary_range_max` decimal(15,2) DEFAULT NULL,
  `employment_type` enum('fulltime','parttime','contract','internship') DEFAULT 'fulltime',
  `status` enum('open','closed','on_hold','filled') DEFAULT 'open',
  `published_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`department_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`posted_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول کارجویان (Applicants)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `applicants`;
CREATE TABLE `applicants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recruitment_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `national_code` varchar(10) DEFAULT NULL,
  `education` varchar(50) DEFAULT NULL,
  `experience_years` int(2) DEFAULT 0,
  `resume_path` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `current_status` enum('new','screening','interview','offered','hired','rejected','withdrawn') DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `applied_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`recruitment_id`) REFERENCES `ap_recruitments`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`current_status`),
  INDEX `idx_applied` (`applied_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول مصاحبه‌ها (Interviews)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_interviews`;
CREATE TABLE `ap_interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applicant_id` int(11) NOT NULL,
  `interviewer_id` int(11) NOT NULL,
  `interview_type` enum('phone','video','in_person','panel') DEFAULT 'in_person',
  `scheduled_date` datetime NOT NULL,
  `duration_minutes` int(3) DEFAULT 60,
  `location` varchar(100) DEFAULT NULL,
  `online_link` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
  `score` decimal(3,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `recommendation` enum('strong_yes','yes','maybe','no','strong_no') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`applicant_id`) REFERENCES `applicants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`interviewer_id`) REFERENCES `ap_users`(`id`) ON DELETE CASCADE,
  INDEX `idx_scheduled` (`scheduled_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول اموال (Assets)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_assets`;
CREATE TABLE `ap_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(50) NOT NULL UNIQUE,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'equipment',
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(15,2) DEFAULT 0,
  `current_value` decimal(15,2) DEFAULT 0,
  `warranty_expiry` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `status` enum('available','assigned','maintenance','retired','lost') DEFAULT 'available',
  `condition` enum('excellent','good','fair','poor') DEFAULT 'good',
  `location` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`assigned_to`) REFERENCES `ap_employees`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- جدول تنظیمات سیستم (Settings)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ap_settings`;
CREATE TABLE `ap_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json','array') DEFAULT 'string',
  `group_name` varchar(50) DEFAULT 'general',
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ap_settings` (`setting_key`, `setting_value`, `setting_type`, `group_name`, `description`) VALUES
('company_name', 'شرکت نمونه', 'string', 'general', 'نام شرکت'),
('company_logo', '', 'string', 'general', 'لوگو شرکت'),
('fiscal_year_start', '01-01', 'string', 'financial', 'شروع سال مالی'),
('working_days_per_week', '6', 'number', 'attendance', 'تعداد روزهای کاری هفته'),
('work_start_time', '08:00', 'string', 'attendance', 'ساعت شروع کار'),
('work_end_time', '16:00', 'string', 'attendance', 'ساعت پایان کار'),
('grace_period_minutes', '15', 'number', 'attendance', 'مهلت تأخیر مجاز'),
('enable_ai_features', '1', 'boolean', 'ai', 'فعال‌سازی هوش مصنوعی'),
('gapgpt_api_key', '', 'string', 'ai', 'کلید API GapGPT');

COMMIT;
