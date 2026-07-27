-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               10.4.0-MariaDB or MySQL 8.0+
-- Database:                     automation_pro
-- Charset:                      utf8mb4
-- Collation:                    utf8mb4_persian_ci
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ایجاد دیتابیس (در صورت نیاز)
CREATE DATABASE IF NOT EXISTS `automation_pro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
USE `automation_pro`;

-- --------------------------------------------------------
-- 1. تنظیمات سیستم و پیکربندی
-- --------------------------------------------------------

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text,
  `type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

INSERT INTO `settings` (`setting_key`, `setting_value`, `type`, `description`) VALUES
('site_title', 'سامانه جامع اتوماسیون اداری', 'string', 'عنوان سامانه'),
('site_url', 'http://localhost/automation', 'string', 'آدرس پایه سامانه'),
('default_lang', 'fa', 'string', 'زبان پیش‌فرض'),
('timezone', 'Asia/Tehran', 'string', 'منطقه زمانی'),
('maintenance_mode', '0', 'boolean', 'حالت تعمیر و نگهداری'),
('ai_provider', 'gapgpt', 'string', 'ارائه‌دهنده هوش مصنوعی'),
('ai_api_key', '', 'string', 'کلید API هوش مصنوعی'),
('ai_model', 'gpt-4', 'string', 'مدل هوش مصنوعی');

-- --------------------------------------------------------
-- 2. مدیریت کاربران و سطوح دسترسی (RBAC)
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `permissions` json DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- اصلاح شده: مقادیر JSON معتبر با Escape صحیح برای MySQL
INSERT INTO `roles` (`name`, `slug`, `permissions`, `is_system`) VALUES
('مدیر کل', 'super-admin', '{"*": "*"}', 1),
('مدیر منابع انسانی', 'hr-manager', '{"hr": "*", "employees": "*", "recruitment": "*"}', 1),
('مدیر مالی', 'finance-manager', '{"payroll": "*", "loans": "*", "reports": "financial"}', 1),
('مدیر واحد', 'department-manager', '{"employees": "view", "attendance": "approve", "leave": "approve"}', 1),
('کارمند', 'employee', '{"profile": "own", "attendance": "own", "leave": "own"}', 1);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `two_factor_secret` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- رمز عبور پیش‌فرض: admin123 (هش شده با bcrypt)
INSERT INTO `users` (`username`, `email`, `password`, `role_id`, `is_active`) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 3. ساختار سازمانی و کارکنان
-- --------------------------------------------------------

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `fk_dept_parent` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

INSERT INTO `departments` (`name`, `description`) VALUES
('مدیریت عامل', 'دفتر مرکزی و مدیریت ارشد'),
('منابع انسانی', 'امور پرسنلی، جذب و آموزش'),
('فناوری اطلاعات', 'زیرساخت، نرم‌افزار و پشتیبانی'),
('مالی و حسابداری', 'حقوق، دستمزد و حسابداری'),
('فروش و بازاریابی', 'توسعه بازار و فروش');

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `father_name` varchar(50) DEFAULT NULL,
  `national_code` varchar(10) NOT NULL UNIQUE,
  `personnel_code` varchar(20) NOT NULL UNIQUE,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT 'male',
  `marital_status` enum('single','married') DEFAULT 'single',
  `children_count` int(2) DEFAULT 0,
  `phone` varchar(15) DEFAULT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `postal_code` varchar(10) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `employment_type` enum('permanent','contract','temporary','part_time','project') DEFAULT 'permanent',
  `hire_date` date NOT NULL,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `insurance_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `status` enum('active','on_leave','suspended','terminated') DEFAULT 'active',
  `termination_date` date DEFAULT NULL,
  `termination_reason` text,
  `photo_path` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_emp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_emp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 4. حضور و غیاب و مرخصی‌ها
-- --------------------------------------------------------

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT 0.00,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `late_minutes` int(11) DEFAULT 0,
  `early_leave_minutes` int(11) DEFAULT 0,
  `status` enum('present','absent','late','half_day','mission','sick_leave','annual_leave','unpaid_leave') DEFAULT 'present',
  `description` text,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `date` (`date`),
  CONSTRAINT `fk_att_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('annual','sick','unpaid','maternity','paternity','bereavement','emergency') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `total_days` decimal(5,2) DEFAULT 0.00,
  `reason` text,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `fk_leave_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `leave_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `annual_total` decimal(5,2) DEFAULT 26.00,
  `annual_used` decimal(5,2) DEFAULT 0.00,
  `sick_total` decimal(5,2) DEFAULT 0.00,
  `sick_used` decimal(5,2) DEFAULT 0.00,
  `unpaid_total` decimal(5,2) DEFAULT 0.00,
  `unpaid_used` decimal(5,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_emp_year` (`employee_id`, `year`),
  CONSTRAINT `fk_bal_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 5. حقوق و دستمزد (استاندارد ۱۴۰۵)
-- --------------------------------------------------------

CREATE TABLE `salary_standards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL UNIQUE,
  `base_salary` decimal(15,2) NOT NULL COMMENT 'حقوق پایه روزانه',
  `housing_allowance` decimal(15,2) NOT NULL COMMENT 'حق مسکن ماهانه',
  `food_allowance` decimal(15,2) NOT NULL COMMENT 'بن خواربار ماهانه',
  `child_allowance` decimal(15,2) NOT NULL COMMENT 'حق اولاد ماهانه برای هر فرزند',
  `min_wage_monthly` decimal(15,2) NOT NULL COMMENT 'حداقل حقوق ماهانه',
  `overtime_hourly_rate` decimal(15,2) NOT NULL COMMENT 'نرخ هر ساعت اضافه کاری',
  `night_shift_bonus` decimal(5,2) DEFAULT 0.35 COMMENT 'درصد فوق‌العاده شب کاری',
  `weekend_bonus` decimal(5,2) DEFAULT 0.40 COMMENT 'درصد فوق‌العاده تعطیل کاری',
  `insurance_rate_employee` decimal(5,2) DEFAULT 7.00 COMMENT 'سهم بیمه کارمند',
  `insurance_rate_employer` decimal(5,2) DEFAULT 23.00 COMMENT 'سهم بیمه کارفرما',
  `tax_brackets` json DEFAULT NULL COMMENT 'جدول مالیاتی پلکانی',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- داده‌های فرضی سال ۱۴۰۵ (قابل بروزرسانی)
-- توجه: اعداد فرضی هستند و باید بر اساس بخشنامه رسمی ۱۴۰۵ بروزرسانی شوند
INSERT INTO `salary_standards` (`year`, `base_salary`, `housing_allowance`, `food_allowance`, `child_allowance`, `min_wage_monthly`, `overtime_hourly_rate`, `tax_brackets`) VALUES
(1405, 2385000.00, 9000000.00, 14000000.00, 1200000.00, 35000000.00, 159000.00, 
'[{"min": 0, "max": 12000000, "rate": 0}, {"min": 12000001, "max": 18000000, "rate": 0.10}, {"min": 18000001, "max": 25000000, "rate": 0.15}, {"min": 25000001, "max": 35000000, "rate": 0.20}, {"min": 35000001, "max": 999999999, "rate": 0.30}]');

CREATE TABLE `payroll_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','calculated','locked','paid') DEFAULT 'open',
  `calculated_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_period` (`year`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `payroll_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `period_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `base_salary` decimal(15,2) DEFAULT 0.00,
  `housing_allowance` decimal(15,2) DEFAULT 0.00,
  `food_allowance` decimal(15,2) DEFAULT 0.00,
  `child_allowance` decimal(15,2) DEFAULT 0.00,
  `overtime_pay` decimal(15,2) DEFAULT 0.00,
  `night_shift_pay` decimal(15,2) DEFAULT 0.00,
  `weekend_pay` decimal(15,2) DEFAULT 0.00,
  `bonus` decimal(15,2) DEFAULT 0.00,
  `gross_salary` decimal(15,2) DEFAULT 0.00,
  `insurance_deduction` decimal(15,2) DEFAULT 0.00,
  `tax_deduction` decimal(15,2) DEFAULT 0.00,
  `loan_deduction` decimal(15,2) DEFAULT 0.00,
  `advance_deduction` decimal(15,2) DEFAULT 0.00,
  `absence_deduction` decimal(15,2) DEFAULT 0.00,
  `total_deductions` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `payment_ref` varchar(50) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `period_id` (`period_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `fk_pay_period` FOREIGN KEY (`period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 6. جذب و استخدام (Recruitment)
-- --------------------------------------------------------

CREATE TABLE `job_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `description` text,
  `requirements` text,
  `status` enum('open','closed','on_hold') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_job_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `cover_letter` text,
  `source` varchar(50) DEFAULT 'website',
  `status` enum('new','screening','interview','offered','hired','rejected') DEFAULT 'new',
  `ai_analysis` text COMMENT 'تحلیل رزومه توسط هوش مصنوعی',
  `ai_score` int(3) DEFAULT NULL COMMENT 'امتیاز دهی هوش مصنوعی',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cand_job` FOREIGN KEY (`job_id`) REFERENCES `job_positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `candidate_id` int(11) NOT NULL,
  `interviewer_id` int(11) NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `type` enum('phone','video','in_person') DEFAULT 'in_person',
  `feedback` text,
  `score` int(11) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_int_cand` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 7. ارزیابی عملکرد (Performance)
-- --------------------------------------------------------

CREATE TABLE `performance_cycles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','active','completed') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `kpi_definitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text,
  `category` varchar(50) DEFAULT 'general',
  `weight` decimal(5,2) DEFAULT 1.00,
  `measurement_unit` varchar(20) DEFAULT 'percent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `performance_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cycle_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_type` enum('self','manager','peer','subordinate') DEFAULT 'manager',
  `kpi_id` int(11) DEFAULT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  `actual_value` decimal(10,2) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `comments` text,
  `ai_summary` text COMMENT 'خلاصه تحلیل هوش مصنوعی',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_perf_cycle` FOREIGN KEY (`cycle_id`) REFERENCES `performance_cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_perf_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_perf_kpi` FOREIGN KEY (`kpi_id`) REFERENCES `kpi_definitions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 8. آموزش و توسعه (LMS)
-- --------------------------------------------------------

CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text,
  `instructor` varchar(100) DEFAULT NULL,
  `duration_hours` decimal(5,2) DEFAULT 0.00,
  `category` varchar(50) DEFAULT NULL,
  `content_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `trainings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('enrolled','in_progress','completed','failed') DEFAULT 'enrolled',
  `certificate_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_train_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_train_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 9. وام‌ها و مساعده (Loans)
-- --------------------------------------------------------

CREATE TABLE `loan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `max_amount` decimal(15,2) DEFAULT 0.00,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `max_installments` int(11) DEFAULT 12,
  `description` text,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

INSERT INTO `loan_types` (`name`, `max_amount`, `max_installments`) VALUES
('وام ضروری', 50000000.00, 12),
('وام خرید کالا', 100000000.00, 24),
('مساعده حقوق', 10000000.00, 3);

CREATE TABLE `loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `loan_type_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `installments` int(11) NOT NULL,
  `monthly_payment` decimal(15,2) NOT NULL,
  `start_date` date NOT NULL,
  `status` enum('pending','approved','active','paid','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_loan_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loan_type` FOREIGN KEY (`loan_type_id`) REFERENCES `loan_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `loan_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payroll_record_id` int(11) DEFAULT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pay_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 10. اموال و دارایی‌ها (Assets)
-- --------------------------------------------------------

CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT 'equipment',
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(15,2) DEFAULT 0.00,
  `current_value` decimal(15,2) DEFAULT 0.00,
  `supplier` varchar(150) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `status` enum('available','assigned','maintenance','retired','lost') DEFAULT 'available',
  `assigned_to` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_asset_emp` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `asset_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `action` enum('assign','return','maintenance','repair','status_change') NOT NULL,
  `from_employee` int(11) DEFAULT NULL,
  `to_employee` int(11) DEFAULT NULL,
  `description` text,
  `performed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_log_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 11. مکاتبات و جلسات (Communication)
-- --------------------------------------------------------

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `description` text,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `minutes` text COMMENT 'صورتمجلس جلسه',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_meet_org` FOREIGN KEY (`organizer_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `meeting_attendees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `status` enum('invited','accepted','declined','attended','absent') DEFAULT 'invited',
  `notes` text,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_att_meet` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_emp_meet` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_type` enum('employee','department','all') DEFAULT 'employee',
  `recipient_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('draft','sent','read','archived') DEFAULT 'draft',
  `attachment_path` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_letter_sender` FOREIGN KEY (`sender_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 12. هوش مصنوعی و لاگ‌ها (AI & Logs)
-- --------------------------------------------------------

CREATE TABLE `ai_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(50) NOT NULL COMMENT 'ماژول درخواست کننده (hr, payroll, etc)',
  `action` varchar(100) NOT NULL COMMENT 'نوع عملیات (analyze_resume, predict_turnover, etc)',
  `prompt` text NOT NULL,
  `response` text,
  `model_used` varchar(50) DEFAULT 'gapgpt',
  `tokens_used` int(11) DEFAULT 0,
  `cost_estimate` decimal(10,4) DEFAULT 0.00,
  `status` enum('success','error','timeout') DEFAULT 'success',
  `error_message` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ai_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) DEFAULT 'system',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- 13. سیستم پلاگین‌ها (Plugin System)
-- --------------------------------------------------------

CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL UNIQUE,
  `name` varchar(150) NOT NULL,
  `version` varchar(20) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `description` text,
  `plugin_json` json DEFAULT NULL COMMENT 'ذخیره محتوای plugin.json',
  `is_active` tinyint(1) DEFAULT 0,
  `is_installed` tinyint(1) DEFAULT 0,
  `installed_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

CREATE TABLE `plugin_hooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_slug` varchar(100) NOT NULL,
  `hook_name` varchar(100) NOT NULL,
  `callback_function` varchar(100) NOT NULL,
  `priority` int(11) DEFAULT 10,
  `arguments` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `hook_name` (`hook_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

COMMIT;
