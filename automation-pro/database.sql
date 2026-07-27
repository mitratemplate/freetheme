-- ========================================
-- سیستم جامع اتوماسیون اداری و مدیریت منابع انسانی
-- نسخه سازمانی (Enterprise) - سال ۱۴۰۵
-- با پشتیبانی از هوش مصنوعی GapGPT
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ========================================
-- تنظیمات اصلی سیستم
-- ========================================

CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT,
  `type` ENUM('string','number','boolean','json','array') DEFAULT 'string',
  `group_name` VARCHAR(50) DEFAULT 'general',
  `description` TEXT,
  `is_public` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_group (`group_name`),
  INDEX idx_key (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `description`) VALUES
('system_name', 'سامانه جامع اتوماسیون اداری', 'string', 'general', 'نام سیستم'),
('system_version', '2.0.0-enterprise', 'string', 'general', 'نسخه سیستم'),
('company_name', 'شرکت نمونه', 'string', 'company', 'نام شرکت'),
('fiscal_year_start', '1405-01-01', 'string', 'fiscal', 'شروع سال مالی'),
('ai_enabled', '1', 'boolean', 'ai', 'فعال‌سازی هوش مصنوعی'),
('ai_api_key', '', 'string', 'ai', 'کلید API هوش مصنوعی'),
('ai_provider', 'gapgpt', 'string', 'ai', 'ارائه‌دهنده هوش مصنوعی');

-- ========================================
-- کاربران و سطوح دسترسی
-- ========================================

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `national_code` VARCHAR(10) UNIQUE,
  `phone` VARCHAR(15),
  `mobile` VARCHAR(15),
  `avatar_path` VARCHAR(255),
  `role_id` BIGINT UNSIGNED,
  `department_id` BIGINT UNSIGNED,
  `employee_id` BIGINT UNSIGNED,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login_at` TIMESTAMP NULL,
  `two_factor_enabled` TINYINT(1) DEFAULT 0,
  `preferences` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (`role_id`),
  INDEX idx_department (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `permissions` JSON,
  `is_system` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`name`, `slug`, `permissions`, `is_system`) VALUES
('مدیر کل', 'super-admin', '*', 1),
('مدیر منابع انسانی', 'hr-manager', '{"hr": "*", "employees": "*", "recruitment": "*"}', 1),
('مدیر مالی', 'finance-manager', '{"payroll": "*", "loans": "*", "reports": "financial"}', 1),
('مدیر واحد', 'department-manager', '{"employees": "view", "attendance": "approve", "leave": "approve"}', 1),
('کارمند', 'employee', '{"profile": "own", "attendance": "own", "leave": "own"}', 1);

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50),
  `model` VARCHAR(100),
  `model_id` BIGINT UNSIGNED,
  `old_values` JSON,
  `new_values` JSON,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (`user_id`),
  INDEX idx_module (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ساختار سازمانی
-- ========================================

CREATE TABLE `departments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `parent_id` BIGINT UNSIGNED NULL,
  `manager_id` BIGINT UNSIGNED NULL,
  `budget` DECIMAL(15,2) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_parent (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `positions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `department_id` BIGINT UNSIGNED,
  `job_level` ENUM('executive','manager','senior','mid','junior','intern') DEFAULT 'junior',
  `salary_range_min` DECIMAL(15,2),
  `salary_range_max` DECIMAL(15,2),
  `requirements` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_department (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- کارکنان
-- ========================================

CREATE TABLE `employees` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_number` VARCHAR(20) UNIQUE NOT NULL,
  `user_id` BIGINT UNSIGNED UNIQUE,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `national_code` VARCHAR(10) UNIQUE NOT NULL,
  `birth_date` DATE,
  `gender` ENUM('male','female'),
  `marital_status` ENUM('single','married'),
  `children_count` INT DEFAULT 0,
  `education_level` ENUM('under_diploma','diploma','associate','bachelor','master','phd'),
  `phone` VARCHAR(15),
  `mobile` VARCHAR(15),
  `email` VARCHAR(100) UNIQUE,
  `emergency_contact_name` VARCHAR(100),
  `emergency_contact_phone` VARCHAR(15),
  `address` TEXT,
  `photo_path` VARCHAR(255),
  `department_id` BIGINT UNSIGNED,
  `position_id` BIGINT UNSIGNED,
  `employment_type` ENUM('permanent','contract','temporary','part_time','project','intern'),
  `employment_status` ENUM('active','on_leave','suspended','terminated','retired') DEFAULT 'active',
  `hire_date` DATE,
  `probation_end_date` DATE,
  `termination_date` DATE,
  `work_location` VARCHAR(100),
  `shift_type` ENUM('morning','afternoon','night','rotating','flexible'),
  `bank_name` VARCHAR(50),
  `bank_account` VARCHAR(20),
  `iban` VARCHAR(26),
  `insurance_number` VARCHAR(20),
  `tax_code` VARCHAR(20),
  `salary_basis` DECIMAL(15,2),
  `skills` JSON,
  `certifications` JSON,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL,
  INDEX idx_employee_number (`employee_number`),
  INDEX idx_national_code (`national_code`),
  INDEX idx_department (`department_id`),
  INDEX idx_position (`position_id`),
  INDEX idx_status (`employment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_documents` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `document_type` ENUM('national_id','education','certificate','contract','insurance','resume','other'),
  `title` VARCHAR(200),
  `file_path` VARCHAR(255) NOT NULL,
  `issue_date` DATE,
  `expiry_date` DATE,
  `verification_status` ENUM('pending','verified','rejected','expired'),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`),
  INDEX idx_type (`document_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_history` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `change_type` ENUM('promotion','transfer','salary_change','department_change','status_change'),
  `old_value` JSON,
  `new_value` JSON,
  `reason` TEXT,
  `effective_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- استانداردهای حقوق و دستمزد ۱۴۰۵
-- ========================================

CREATE TABLE `salary_standards` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `year` SMALLINT UNSIGNED NOT NULL,
  `year_label` VARCHAR(10),
  `effective_from` DATE NOT NULL,
  `effective_to` DATE,
  `base_salary` DECIMAL(15,2) NOT NULL,
  `daily_allowance` DECIMAL(15,2),
  `food_allowance` DECIMAL(15,2),
  `child_allowance` DECIMAL(15,2),
  `spouse_allowance` DECIMAL(15,2),
  `hardship_allowance` DECIMAL(15,2),
  `overtime_hourly_rate` DECIMAL(15,2),
  `night_shift_bonus` DECIMAL(5,2) DEFAULT 35.00,
  `holiday_bonus` DECIMAL(5,2) DEFAULT 40.00,
  `insurance_ceiling` DECIMAL(15,2),
  `insurance_rate_employee` DECIMAL(5,2) DEFAULT 7.00,
  `insurance_rate_employer` DECIMAL(5,2) DEFAULT 23.00,
  `tax_brackets` JSON,
  `minimum_working_hours` INT DEFAULT 44,
  `annual_leave_days` INT DEFAULT 26,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_year (`year`),
  INDEX idx_effective (`effective_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- استانداردهای سال ۱۴۰۵
INSERT INTO `salary_standards` (`year`, `year_label`, `effective_from`, `base_salary`, `daily_allowance`, `food_allowance`, `child_allowance`, `spouse_allowance`, `hardship_allowance`, `overtime_hourly_rate`, `insurance_ceiling`, `tax_brackets`, `is_active`) VALUES
(1405, '۱۴۰۵', '1405-01-01', 
  2388000, 900000, 1400000, 716400, 500000, 300000, 159200, 45000000,
  '[{"min":0,"max":12000000,"rate":0},{"min":12000000,"max":18000000,"rate":10},{"min":18000000,"max":30000000,"rate":15},{"min":30000000,"max":50000000,"rate":20},{"min":50000000,"max":null,"rate":30}]',
  1);

CREATE TABLE `salary_components` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `type` ENUM('earning','deduction') NOT NULL,
  `category` ENUM('fixed','variable','statutory','custom'),
  `calculation_method` ENUM('fixed','percentage','formula','hours','days'),
  `formula` TEXT,
  `is_taxable` TINYINT(1) DEFAULT 1,
  `is_insurable` TINYINT(1) DEFAULT 0,
  `display_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_type (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_components` (`name`, `code`, `type`, `category`, `calculation_method`, `is_taxable`, `is_insurable`) VALUES
('حقوق پایه', 'base_salary', 'earning', 'statutory', 'fixed', 1, 1),
('حق مسکن', 'housing_allowance', 'earning', 'statutory', 'fixed', 1, 1),
('بن خواربار', 'food_allowance', 'earning', 'statutory', 'fixed', 1, 1),
('حق اولاد', 'child_allowance', 'earning', 'statutory', 'formula', 0, 0),
('اضافه کاری', 'overtime', 'earning', 'variable', 'hours', 1, 1),
('شب کاری', 'night_shift', 'earning', 'variable', 'percentage', 1, 1),
('سهم بیمه کارمند', 'insurance_employee', 'deduction', 'statutory', 'percentage', 0, 0),
('مالیات بر درآمد', 'income_tax', 'deduction', 'statutory', 'formula', 0, 0);

CREATE TABLE `employee_salary_config` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `salary_standard_id` INT UNSIGNED,
  `base_salary` DECIMAL(15,2) NOT NULL,
  `housing_allowance` DECIMAL(15,2) DEFAULT 0,
  `food_allowance` DECIMAL(15,2) DEFAULT 0,
  `child_allowance_per_child` DECIMAL(15,2) DEFAULT 0,
  `custom_allowances` JSON,
  `custom_deductions` JSON,
  `effective_from` DATE,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_employee (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_periods` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `year` SMALLINT UNSIGNED NOT NULL,
  `month` TINYINT UNSIGNED NOT NULL,
  `month_label` VARCHAR(20),
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `working_days` INT,
  `status` ENUM('draft','calculating','approved','paid','closed') DEFAULT 'draft',
  `calculated_at` TIMESTAMP NULL,
  `approved_at` TIMESTAMP NULL,
  `paid_at` TIMESTAMP NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_period (`year`, `month`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payroll_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payroll_period_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `employee_number` VARCHAR(20),
  `employee_name` VARCHAR(100),
  `work_days` DECIMAL(5,2),
  `absence_days` DECIMAL(5,2),
  `leave_days` DECIMAL(5,2),
  `overtime_hours` DECIMAL(6,2) DEFAULT 0,
  `earnings` JSON NOT NULL,
  `deductions` JSON NOT NULL,
  `gross_salary` DECIMAL(15,2) NOT NULL,
  `total_deductions` DECIMAL(15,2) NOT NULL,
  `net_salary` DECIMAL(15,2) NOT NULL,
  `taxable_income` DECIMAL(15,2),
  `insurable_income` DECIMAL(15,2),
  `insurance_amount` DECIMAL(15,2),
  `tax_amount` DECIMAL(15,2),
  `payment_status` ENUM('pending','paid','cancelled') DEFAULT 'pending',
  `payment_date` DATE,
  `payment_reference` VARCHAR(50),
  `slip_file_path` VARCHAR(255),
  `notes` TEXT,
  `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `approved_at` TIMESTAMP NULL,
  INDEX idx_period (`payroll_period_id`),
  INDEX idx_employee (`employee_id`),
  INDEX idx_payment_status (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- حضور و غیاب
-- ========================================

CREATE TABLE `attendance_devices` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `device_id` VARCHAR(50) UNIQUE,
  `type` ENUM('fingerprint','face_recognition','card','mobile','web'),
  `location` VARCHAR(100),
  `ip_address` VARCHAR(45),
  `status` ENUM('active','inactive','maintenance') DEFAULT 'active',
  `last_sync_at` TIMESTAMP NULL,
  `config` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_device_id (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance_records` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `attendance_date` DATE NOT NULL,
  `check_in_time` TIME,
  `check_out_time` TIME,
  `break_start_time` TIME,
  `break_end_time` TIME,
  `total_hours` DECIMAL(5,2),
  `work_hours` DECIMAL(5,2),
  `overtime_hours` DECIMAL(5,2) DEFAULT 0,
  `late_minutes` INT DEFAULT 0,
  `absence_type` ENUM('none','unauthorized','sick','mission','other'),
  `device_id` BIGINT UNSIGNED,
  `latitude` DECIMAL(10,8),
  `longitude` DECIMAL(11,8),
  `status` ENUM('pending','approved','rejected','corrected') DEFAULT 'pending',
  `approved_by` BIGINT UNSIGNED,
  `approved_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_attendance (`employee_id`, `attendance_date`),
  INDEX idx_date (`attendance_date`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance_corrections` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attendance_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `correction_type` ENUM('check_in','check_out','both'),
  `original_check_in` TIME,
  `corrected_check_in` TIME,
  `original_check_out` TIME,
  `corrected_check_out` TIME,
  `reason` TEXT NOT NULL,
  `attachment_path` VARCHAR(255),
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` BIGINT UNSIGNED,
  `reviewed_at` TIMESTAMP NULL,
  `review_notes` TEXT,
  INDEX idx_attendance (`attendance_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shift_schedules` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `type` ENUM('fixed','rotating','flexible'),
  `work_days` JSON,
  `shifts` JSON,
  `rotation_pattern` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employee_shifts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `shift_schedule_id` BIGINT UNSIGNED,
  `date` DATE NOT NULL,
  `shift_type` ENUM('morning','afternoon','night','off'),
  `start_time` TIME,
  `end_time` TIME,
  `is_holiday` TINYINT(1) DEFAULT 0,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_employee_date (`employee_id`, `date`),
  INDEX idx_date (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- مرخصی‌ها و ماموریت‌ها
-- ========================================

CREATE TABLE `leave_types` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `type` ENUM('annual','sick','unpaid','maternity','paternity','bereavement','marriage','exam','emergency','other'),
  `is_paid` TINYINT(1) DEFAULT 1,
  `requires_document` TINYINT(1) DEFAULT 0,
  `max_days_per_year` INT,
  `accrual_rate` DECIMAL(5,2),
  `carry_over_allowed` TINYINT(1) DEFAULT 0,
  `approval_required` TINYINT(1) DEFAULT 1,
  `color` VARCHAR(20),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_type (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `leave_types` (`name`, `code`, `type`, `is_paid`, `requires_document`, `max_days_per_year`, `color`) VALUES
('مرخصی استحقاقی', 'annual', 'annual', 1, 0, 26, '#3B82F6'),
('مرخصی استعلاجی', 'sick', 'sick', 1, 1, 30, '#EF4444'),
('مرخصی بدون حقوق', 'unpaid', 'unpaid', 0, 0, 15, '#6B7280'),
('مرخصی زایمان', 'maternity', 'maternity', 1, 1, 90, '#EC4899'),
('مرخصی پدری', 'paternity', 'paternity', 1, 0, 3, '#8B5CF6'),
('مرخصی فوت بستگان', 'bereavement', 'bereavement', 1, 0, 3, '#1F2937'),
('مرخصی ازدواج', 'marriage', 'marriage', 1, 0, 3, '#F59E0B');

CREATE TABLE `leave_balances` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `leave_type_id` BIGINT UNSIGNED NOT NULL,
  `year` SMALLINT UNSIGNED NOT NULL,
  `opening_balance` DECIMAL(6,2) DEFAULT 0,
  `accrued_days` DECIMAL(6,2) DEFAULT 0,
  `used_days` DECIMAL(6,2) DEFAULT 0,
  `approved_days` DECIMAL(6,2) DEFAULT 0,
  `pending_days` DECIMAL(6,2) DEFAULT 0,
  `closing_balance` DECIMAL(6,2) DEFAULT 0,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_employee_type_year (`employee_id`, `leave_type_id`, `year`),
  INDEX idx_employee (`employee_id`),
  INDEX idx_year (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_requests` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `request_number` VARCHAR(20) UNIQUE,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `leave_type_id` BIGINT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` DECIMAL(6,2),
  `reason` TEXT,
  `replacement_employee_id` BIGINT UNSIGNED,
  `document_paths` JSON,
  `contact_info` VARCHAR(100),
  `status` ENUM('draft','pending','approved','rejected','cancelled','completed') DEFAULT 'draft',
  `current_approver_id` BIGINT UNSIGNED,
  `workflow` JSON,
  `submitted_at` TIMESTAMP NULL,
  `approved_at` TIMESTAMP NULL,
  `rejected_at` TIMESTAMP NULL,
  `rejection_reason` TEXT,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`),
  INDEX idx_status (`status`),
  INDEX idx_dates (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leave_approvals` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `leave_request_id` BIGINT UNSIGNED NOT NULL,
  `approver_id` BIGINT UNSIGNED NOT NULL,
  `level` INT DEFAULT 1,
  `action` ENUM('approve','reject','delegate'),
  `comments` TEXT,
  `delegated_to` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_request (`leave_request_id`),
  INDEX idx_approver (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `missions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `mission_number` VARCHAR(20) UNIQUE,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `mission_type` ENUM('domestic','international'),
  `purpose` TEXT NOT NULL,
  `destination` VARCHAR(200),
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `transportation_type` ENUM('personal','company','public'),
  `advance_amount` DECIMAL(15,2) DEFAULT 0,
  `estimated_expenses` DECIMAL(15,2),
  `status` ENUM('draft','pending','approved','in_progress','completed','cancelled') DEFAULT 'draft',
  `approved_by` BIGINT UNSIGNED,
  `approved_at` TIMESTAMP NULL,
  `actual_expenses` DECIMAL(15,2),
  `settlement_status` ENUM('pending','settled','overpaid','underpaid'),
  `report_attachment` VARCHAR(255),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- استخدام و جذب نیرو
-- ========================================

CREATE TABLE `job_requisitions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `requisition_number` VARCHAR(20) UNIQUE,
  `department_id` BIGINT UNSIGNED,
  `position_id` BIGINT UNSIGNED,
  `position_title` VARCHAR(100),
  `employment_type` ENUM('permanent','contract','temporary','part_time','project'),
  `headcount` INT DEFAULT 1,
  `filled_count` INT DEFAULT 0,
  `reason` ENUM('new_position','replacement','temporary','other'),
  `justification` TEXT,
  `requirements` JSON,
  `skills_required` JSON,
  `experience_years` INT,
  `education_required` VARCHAR(100),
  `salary_range_min` DECIMAL(15,2),
  `salary_range_max` DECIMAL(15,2),
  `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
  `target_start_date` DATE,
  `status` ENUM('draft','pending','approved','rejected','on_hold','closed') DEFAULT 'draft',
  `requested_by` BIGINT UNSIGNED,
  `approved_by` BIGINT UNSIGNED,
  `approved_at` TIMESTAMP NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_department (`department_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_postings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `posting_number` VARCHAR(20) UNIQUE,
  `requisition_id` BIGINT UNSIGNED,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `requirements` JSON,
  `responsibilities` JSON,
  `qualifications` JSON,
  `benefits` JSON,
  `location` VARCHAR(100),
  `employment_type` ENUM('permanent','contract','temporary','part_time','project'),
  `salary_range` VARCHAR(100),
  `application_deadline` DATE,
  `publish_channels` JSON,
  `status` ENUM('draft','published','paused','closed') DEFAULT 'draft',
  `published_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `views_count` INT DEFAULT 0,
  `applications_count` INT DEFAULT 0,
  `created_by` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (`status`),
  INDEX idx_deadline (`application_deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `candidates` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `candidate_number` VARCHAR(20) UNIQUE,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100),
  `phone` VARCHAR(15),
  `mobile` VARCHAR(15),
  `national_code` VARCHAR(10),
  `birth_date` DATE,
  `gender` ENUM('male','female'),
  `education_level` VARCHAR(50),
  `education_field` VARCHAR(100),
  `university` VARCHAR(100),
  `graduation_year` SMALLINT,
  `total_experience_years` DECIMAL(4,2),
  `current_company` VARCHAR(100),
  `current_position` VARCHAR(100),
  `expected_salary` DECIMAL(15,2),
  `notice_period_days` INT,
  `resume_path` VARCHAR(255),
  `cover_letter` TEXT,
  `portfolio_urls` JSON,
  `source` ENUM('website','linkedin','job_board','referral','agency','direct','other'),
  `referred_by` BIGINT UNSIGNED,
  `tags` JSON,
  `notes` TEXT,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (`email`),
  INDEX idx_source (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `applications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_number` VARCHAR(20) UNIQUE,
  `job_posting_id` BIGINT UNSIGNED,
  `candidate_id` BIGINT UNSIGNED NOT NULL,
  `stage` ENUM('applied','screening','phone_interview','interview','assessment','reference_check','offer','hired','rejected','withdrawn') DEFAULT 'applied',
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `assigned_to` BIGINT UNSIGNED,
  `priority` ENUM('low','medium','high'),
  `rating` TINYINT,
  `screening_notes` TEXT,
  `interview_feedback` JSON,
  `assessment_results` JSON,
  `offer_details` JSON,
  `rejection_reason` VARCHAR(255),
  `next_action` VARCHAR(200),
  `next_action_date` DATE,
  `converted_to_employee_id` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_job (`job_posting_id`),
  INDEX idx_candidate (`candidate_id`),
  INDEX idx_stage (`stage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `interviews` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `application_id` BIGINT UNSIGNED NOT NULL,
  `interview_type` ENUM('phone','video','in_person','panel','technical','hr'),
  `scheduled_date` DATE NOT NULL,
  `start_time` TIME,
  `end_time` TIME,
  `location` VARCHAR(200),
  `meeting_link` VARCHAR(255),
  `interviewers` JSON,
  `questions` JSON,
  `status` ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
  `overall_rating` TINYINT,
  `recommendation` ENUM('strong_yes','yes','maybe','no','strong_no'),
  `comments` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_application (`application_id`),
  INDEX idx_date (`scheduled_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `assessments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `type` ENUM('technical','psychometric','personality','aptitude','language','other'),
  `description` TEXT,
  `duration_minutes` INT,
  `passing_score` INT,
  `questions` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `assessment_results` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `assessment_id` BIGINT UNSIGNED NOT NULL,
  `application_id` BIGINT UNSIGNED,
  `candidate_id` BIGINT UNSIGNED NOT NULL,
  `score` INT,
  `max_score` INT,
  `percentage` DECIMAL(5,2),
  `passed` TINYINT(1),
  `answers` JSON,
  `time_taken_minutes` INT,
  `evaluated_by` BIGINT UNSIGNED,
  `evaluation_notes` TEXT,
  `taken_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_assessment (`assessment_id`),
  INDEX idx_candidate (`candidate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ارزیابی عملکرد
-- ========================================

CREATE TABLE `performance_cycles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `cycle_type` ENUM('monthly','quarterly','semi_annual','annual','project'),
  `year` SMALLINT UNSIGNED,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `self_evaluation_start` DATE,
  `self_evaluation_end` DATE,
  `manager_evaluation_start` DATE,
  `manager_evaluation_end` DATE,
  `status` ENUM('draft','active','completed','closed') DEFAULT 'draft',
  `template_id` BIGINT UNSIGNED,
  `settings` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cycle_type (`cycle_type`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `performance_templates` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `sections` JSON,
  `rating_scale` JSON,
  `weight_distribution` JSON,
  `is_default` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `performance_evaluations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `evaluation_number` VARCHAR(20) UNIQUE,
  `cycle_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `evaluator_id` BIGINT UNSIGNED NOT NULL,
  `evaluation_type` ENUM('self','manager','peer','subordinate','360'),
  `overall_rating` DECIMAL(4,2),
  `ratings` JSON,
  `strengths` TEXT,
  `weaknesses` TEXT,
  `achievements` TEXT,
  `development_areas` TEXT,
  `goals_next_period` JSON,
  `training_recommendations` JSON,
  `promotion_recommendation` ENUM('promote','maintain','demote','terminate'),
  `salary_adjustment_recommendation` DECIMAL(5,2),
  `employee_comments` TEXT,
  `status` ENUM('not_started','in_progress','submitted','reviewed','acknowledged','disputed'),
  `submitted_at` TIMESTAMP NULL,
  `reviewed_at` TIMESTAMP NULL,
  `acknowledged_at` TIMESTAMP NULL,
  `meeting_scheduled_at` TIMESTAMP NULL,
  `meeting_completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cycle (`cycle_id`),
  INDEX idx_employee (`employee_id`),
  INDEX idx_evaluator (`evaluator_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `performance_goals` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `cycle_id` BIGINT UNSIGNED,
  `parent_goal_id` BIGINT UNSIGNED,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `category` ENUM('business','development','behavioral','project'),
  `kpi_type` ENUM('quantitative','qualitative','milestone'),
  `target_value` VARCHAR(100),
  `unit` VARCHAR(50),
  `weight` DECIMAL(5,2),
  `start_date` DATE,
  `due_date` DATE,
  `progress` INT DEFAULT 0,
  `status` ENUM('not_started','in_progress','completed','on_hold','cancelled'),
  `achievement_evidence` JSON,
  `manager_comments` TEXT,
  `final_rating` TINYINT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`),
  INDEX idx_cycle (`cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `competency_models` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `competencies` JSON,
  `levels` JSON,
  `for_positions` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- آموزش و توسعه
-- ========================================

CREATE TABLE `training_courses` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) UNIQUE,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `category` ENUM('technical','soft_skills','compliance','leadership','safety','onboarding','other'),
  `type` ENUM('internal','external','online','offline','blended'),
  `provider` VARCHAR(100),
  `instructor` VARCHAR(100),
  `duration_hours` DECIMAL(5,2),
  `credits` INT,
  `prerequisites` JSON,
  `target_audience` JSON,
  `learning_objectives` JSON,
  `max_participants` INT,
  `cost` DECIMAL(15,2),
  `certificate_provided` TINYINT(1) DEFAULT 0,
  `is_mandatory` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_category (`category`),
  INDEX idx_type (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_sessions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` BIGINT UNSIGNED NOT NULL,
  `session_code` VARCHAR(20) UNIQUE,
  `start_date` DATE NOT NULL,
  `end_date` DATE,
  `start_time` TIME,
  `end_time` TIME,
  `location` VARCHAR(200),
  `online_link` VARCHAR(255),
  `trainer` VARCHAR(100),
  `max_participants` INT,
  `enrolled_count` INT DEFAULT 0,
  `status` ENUM('planned','open','in_progress','completed','cancelled') DEFAULT 'planned',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_course (`course_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_enrollments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `nomination_type` ENUM('self','manager','hr','mandatory'),
  `nominated_by` BIGINT UNSIGNED,
  `enrollment_status` ENUM('registered','confirmed','waitlist','cancelled','completed','dropped'),
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `confirmation_date` TIMESTAMP NULL,
  `attendance_status` ENUM('attended','absent','partial'),
  `completion_status` ENUM('not_started','in_progress','completed','failed'),
  `assessment_score` INT,
  `feedback_rating` TINYINT,
  `feedback_comments` TEXT,
  `certificate_issued` TINYINT(1) DEFAULT 0,
  `certificate_path` VARCHAR(255),
  `cost_charged` DECIMAL(15,2),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_session_employee (`session_id`, `employee_id`),
  INDEX idx_employee (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `training_needs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `employee_id` BIGINT UNSIGNED,
  `department_id` BIGINT UNSIGNED,
  `skill_gap` VARCHAR(200),
  `priority` ENUM('low','medium','high','critical'),
  `recommended_courses` JSON,
  `identified_by` ENUM('self_assessment','performance_review','manager','hr','compliance'),
  `target_completion_date` DATE,
  `status` ENUM('identified','planned','in_progress','completed','deferred'),
  `budget_allocated` DECIMAL(15,2),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- اموال و دارایی‌ها
-- ========================================

CREATE TABLE `asset_categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `parent_id` BIGINT UNSIGNED,
  `depreciation_method` ENUM('straight_line','declining_balance','units_of_production'),
  `useful_life_years` INT,
  `salvage_value_percentage` DECIMAL(5,2),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_parent (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `assets` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `asset_number` VARCHAR(30) UNIQUE NOT NULL,
  `barcode` VARCHAR(50) UNIQUE,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `category_id` BIGINT UNSIGNED,
  `serial_number` VARCHAR(100),
  `manufacturer` VARCHAR(100),
  `model` VARCHAR(100),
  `purchase_date` DATE,
  `purchase_cost` DECIMAL(15,2),
  `supplier` VARCHAR(100),
  `warranty_period_months` INT,
  `warranty_expiry_date` DATE,
  `location` VARCHAR(100),
  `department_id` BIGINT UNSIGNED,
  `assigned_to` BIGINT UNSIGNED,
  `status` ENUM('available','assigned','maintenance','retired','disposed','lost','stolen') DEFAULT 'available',
  `condition` ENUM('new','good','fair','poor','damaged'),
  `current_value` DECIMAL(15,2),
  `accumulated_depreciation` DECIMAL(15,2) DEFAULT 0,
  `last_maintenance_date` DATE,
  `next_maintenance_date` DATE,
  `images` JSON,
  `documents` JSON,
  `notes` TEXT,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (`category_id`),
  INDEX idx_status (`status`),
  INDEX idx_assigned (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `asset_assignments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `asset_id` BIGINT UNSIGNED NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `assignment_type` ENUM('permanent','temporary','loan'),
  `assigned_date` DATE NOT NULL,
  `expected_return_date` DATE,
  `actual_return_date` DATE,
  `condition_at_assignment` TEXT,
  `condition_at_return` TEXT,
  `notes` TEXT,
  `assigned_by` BIGINT UNSIGNED,
  `returned_to` BIGINT UNSIGNED,
  `status` ENUM('active','returned','overdue','lost') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_asset (`asset_id`),
  INDEX idx_employee (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `asset_maintenance` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `asset_id` BIGINT UNSIGNED NOT NULL,
  `maintenance_type` ENUM('preventive','corrective','emergency','upgrade'),
  `title` VARCHAR(200),
  `description` TEXT,
  `scheduled_date` DATE,
  `completed_date` DATE,
  `vendor` VARCHAR(100),
  `technician` VARCHAR(100),
  `cost` DECIMAL(15,2),
  `parts_replaced` JSON,
  `work_done` TEXT,
  `next_due_date` DATE,
  `status` ENUM('scheduled','in_progress','completed','cancelled'),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_asset (`asset_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- وام‌ها و مساعده
-- ========================================

CREATE TABLE `loan_types` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `type` ENUM('loan','advance','grant'),
  `max_amount` DECIMAL(15,2),
  `min_amount` DECIMAL(15,2),
  `interest_rate` DECIMAL(5,2) DEFAULT 0,
  `max_installments` INT,
  `grace_period_months` INT DEFAULT 0,
  `eligibility_criteria` JSON,
  `required_documents` JSON,
  `approval_workflow` JSON,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `loan_types` (`name`, `code`, `type`, `max_amount`, `interest_rate`, `max_installments`) VALUES
('وام ضروری', 'emergency_loan', 'loan', 50000000, 4.00, 24),
('وام مسکن', 'housing_loan', 'loan', 500000000, 6.00, 60),
('وام خودرو', 'car_loan', 'loan', 300000000, 8.00, 48),
('مساعده حقوق', 'salary_advance', 'advance', 10000000, 0, 3),
('کمک هزینه درمان', 'medical_grant', 'grant', 20000000, 0, 1);

CREATE TABLE `loans` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `loan_number` VARCHAR(30) UNIQUE NOT NULL,
  `employee_id` BIGINT UNSIGNED NOT NULL,
  `loan_type_id` BIGINT UNSIGNED NOT NULL,
  `requested_amount` DECIMAL(15,2) NOT NULL,
  `approved_amount` DECIMAL(15,2),
  `interest_rate` DECIMAL(5,2),
  `installment_count` INT,
  `installment_amount` DECIMAL(15,2),
  `grace_period_months` INT,
  `first_installment_date` DATE,
  `last_installment_date` DATE,
  `purpose` TEXT,
  `guarantor_ids` JSON,
  `documents` JSON,
  `status` ENUM('draft','pending','approved','rejected','disbursed','active','completed','defaulted','cancelled') DEFAULT 'draft',
  `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `approved_at` TIMESTAMP NULL,
  `approved_by` BIGINT UNSIGNED,
  `disbursed_at` TIMESTAMP NULL,
  `disbursement_reference` VARCHAR(50),
  `total_paid` DECIMAL(15,2) DEFAULT 0,
  `remaining_balance` DECIMAL(15,2),
  `next_installment_date` DATE,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_employee (`employee_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loan_installments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `loan_id` BIGINT UNSIGNED NOT NULL,
  `installment_number` INT NOT NULL,
  `due_date` DATE NOT NULL,
  `principal_amount` DECIMAL(15,2),
  `interest_amount` DECIMAL(15,2),
  `total_amount` DECIMAL(15,2),
  `paid_amount` DECIMAL(15,2) DEFAULT 0,
  `paid_date` DATE,
  `payment_reference` VARCHAR(50),
  `status` ENUM('pending','paid','partial','overdue','waived') DEFAULT 'pending',
  `late_fee` DECIMAL(15,2) DEFAULT 0,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_loan_installment (`loan_id`, `installment_number`),
  INDEX idx_due_date (`due_date`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- جلسات و تقویم
-- ========================================

CREATE TABLE `meetings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `meeting_number` VARCHAR(20) UNIQUE,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `meeting_type` ENUM('regular','emergency','board','committee','team','one_on_one','interview','training','other'),
  `organizer_id` BIGINT UNSIGNED NOT NULL,
  `start_datetime` DATETIME NOT NULL,
  `end_datetime` DATETIME,
  `location` VARCHAR(200),
  `online_link` VARCHAR(255),
  `recurrence_pattern` JSON,
  `agenda` JSON,
  `attachments` JSON,
  `status` ENUM('scheduled','in_progress','completed','cancelled','postponed') DEFAULT 'scheduled',
  `minutes` TEXT,
  `decisions` JSON,
  `action_items` JSON,
  `recording_path` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_organizer (`organizer_id`),
  INDEX idx_datetime (`start_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `meeting_attendees` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` BIGINT UNSIGNED NOT NULL,
  `attendee_id` BIGINT UNSIGNED NOT NULL,
  `attendee_type` ENUM('employee','external','guest'),
  `role` ENUM('required','optional','observer'),
  `invitation_status` ENUM('pending','accepted','declined','tentative') DEFAULT 'pending',
  `attendance_status` ENUM('present','absent','late','left_early') DEFAULT 'absent',
  `check_in_time` DATETIME,
  `check_out_time` DATETIME,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_meeting_attendee (`meeting_id`, `attendee_id`),
  INDEX idx_meeting (`meeting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `calendar_events` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `event_type` ENUM('holiday','deadline','meeting','reminder','birthday','anniversary','other'),
  `organizer_id` BIGINT UNSIGNED,
  `start_datetime` DATETIME NOT NULL,
  `end_datetime` DATETIME,
  `all_day` TINYINT(1) DEFAULT 0,
  `location` VARCHAR(200),
  `recurrence_pattern` JSON,
  `reminder_minutes` JSON,
  `visibility` ENUM('public','private','department','team') DEFAULT 'private',
  `related_model` VARCHAR(50),
  `related_id` BIGINT UNSIGNED,
  `color` VARCHAR(20),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_organizer (`organizer_id`),
  INDEX idx_datetime (`start_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- مکاتبات و مستندات
-- ========================================

CREATE TABLE `document_categories` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) UNIQUE,
  `parent_id` BIGINT UNSIGNED,
  `retention_period_years` INT,
  `confidentiality_level` ENUM('public','internal','confidential','restricted'),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_parent (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `documents` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `document_number` VARCHAR(30) UNIQUE,
  `title` VARCHAR(300) NOT NULL,
  `category_id` BIGINT UNSIGNED,
  `document_type` ENUM('letter','memo','report','contract','policy','procedure','form','other'),
  `content` LONGTEXT,
  `file_path` VARCHAR(255),
  `file_size` INT UNSIGNED,
  `mime_type` VARCHAR(50),
  `version` VARCHAR(20) DEFAULT '1.0',
  `author_id` BIGINT UNSIGNED,
  `owner_department_id` BIGINT UNSIGNED,
  `confidentiality_level` ENUM('public','internal','confidential','restricted'),
  `tags` JSON,
  `related_model` VARCHAR(50),
  `related_id` BIGINT UNSIGNED,
  `status` ENUM('draft','pending_approval','approved','published','archived','obsolete') DEFAULT 'draft',
  `approved_by` BIGINT UNSIGNED,
  `approved_at` TIMESTAMP NULL,
  `published_at` TIMESTAMP NULL,
  `expiry_date` DATE,
  `access_control` JSON,
  `download_count` INT DEFAULT 0,
  `view_count` INT DEFAULT 0,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (`category_id`),
  INDEX idx_author (`author_id`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `document_workflow` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `document_id` BIGINT UNSIGNED NOT NULL,
  `step_number` INT,
  `action` ENUM('review','approve','sign','acknowledge'),
  `assigned_to` BIGINT UNSIGNED,
  `assigned_role` VARCHAR(50),
  `status` ENUM('pending','completed','rejected','skipped'),
  `comments` TEXT,
  `completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_document (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `notification_type` ENUM('info','success','warning','error','reminder'),
  `channel` ENUM('in_app','email','sms','push'),
  `recipient_id` BIGINT UNSIGNED,
  `recipient_type` ENUM('user','employee','role','department','all'),
  `subject` VARCHAR(200),
  `message` TEXT NOT NULL,
  `action_url` VARCHAR(255),
  `related_model` VARCHAR(50),
  `related_id` BIGINT UNSIGNED,
  `data` JSON,
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` TIMESTAMP NULL,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL,
  INDEX idx_recipient (`recipient_id`),
  INDEX idx_is_read (`is_read`),
  INDEX idx_sent (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_preferences` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `notification_type` VARCHAR(50),
  `email_enabled` TINYINT(1) DEFAULT 1,
  `sms_enabled` TINYINT(1) DEFAULT 0,
  `push_enabled` TINYINT(1) DEFAULT 1,
  `in_app_enabled` TINYINT(1) DEFAULT 1,
  `quiet_hours_start` TIME,
  `quiet_hours_end` TIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_type (`user_id`, `notification_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- هوش مصنوعی و تحلیل
-- ========================================

CREATE TABLE `ai_prompts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) UNIQUE,
  `category` ENUM('hr_analytics','payroll_analysis','recruitment','performance','training','general'),
  `prompt_template` TEXT NOT NULL,
  `variables` JSON,
  `model` VARCHAR(50) DEFAULT 'gapgpt-4',
  `temperature` DECIMAL(3,2) DEFAULT 0.7,
  `max_tokens` INT DEFAULT 2000,
  `system_message` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `usage_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_category (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ai_prompts` (`name`, `code`, `category`, `prompt_template`, `variables`) VALUES
('تحلیل ترک خدمت', 'turnover_analysis', 'hr_analytics', 'با توجه به داده‌های زیر، دلایل احتمالی ترک خدمت کارکنان را تحلیل کرده و راهکارهایی برای کاهش آن ارائه دهید: {{data}}', '{"data": "employee_turnover_data"}'),
('پیشنهاد سوال مصاحبه', 'interview_questions', 'recruitment', 'برای موقعیت شغلی {{position}} با نیازمندی‌های {{requirements}}، ۱۰ سوال مصاحبه تخصصی طراحی کن.', '{"position": "job_title", "requirements": "job_requirements"}'),
('تحلیل عملکرد', 'performance_analysis', 'performance', 'عملکرد کارمند {{employee_name}} را بر اساس داده‌های زیر تحلیل کرده و نقاط قوت و زمینه‌های بهبود را مشخص کن: {{data}}', '{"employee_name": "name", "data": "performance_data"}'),
('پیشنهاد آموزش', 'training_recommendation', 'training', 'با توجه به شکاف مهارتی {{skill_gap}} و نقش شغلی {{position}}، دوره‌های آموزشی مناسب را پیشنهاد بده.', '{"skill_gap": "gap", "position": "job_title"}'),
('خلاصه رزومه', 'resume_summary', 'recruitment', 'این رزومه را خلاصه کرده و نقاط کلیدی، مهارت‌های اصلی و تناسب با موقعیت شغلی را استخراج کن: {{resume_text}}', '{"resume_text": "resume_content"}'),
('تحلیل حقوق و دستمزد', 'payroll_insights', 'payroll_analysis', 'با توجه به داده‌های حقوق و دستمزد زیر، الگوها، ناهنجاری‌ها و پیشنهادات بهینه‌سازی را ارائه بده: {{payroll_data}}', '{"payroll_data": "payroll_records"}'),
('پیش‌بینی نیاز به نیرو', 'workforce_planning', 'hr_analytics', 'با تحلیل روند کسب‌وکار و داده‌های پرسنلی، نیاز به نیروی انسانی در {{period}} آینده را پیش‌بینی کن.', '{"period": "time_period"}');

CREATE TABLE `ai_conversations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `session_id` VARCHAR(100),
  `prompt_id` BIGINT UNSIGNED,
  `user_message` TEXT NOT NULL,
  `ai_response` LONGTEXT,
  `context_data` JSON,
  `tokens_used` INT,
  `cost` DECIMAL(10,4),
  `model_used` VARCHAR(50),
  `response_time_ms` INT,
  `feedback_rating` TINYINT,
  `feedback_comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (`user_id`),
  INDEX idx_prompt (`prompt_id`),
  INDEX idx_created (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ai_analytics_cache` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cache_key` VARCHAR(100) UNIQUE,
  `analysis_type` VARCHAR(50),
  `parameters` JSON,
  `result` JSON NOT NULL,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP,
  `access_count` INT DEFAULT 0,
  INDEX idx_cache_key (`cache_key`),
  INDEX idx_expires (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- پلاگین‌ها و افزونه‌ها
-- ========================================

CREATE TABLE `plugins` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(50) UNIQUE NOT NULL,
  `version` VARCHAR(20),
  `description` TEXT,
  `author` VARCHAR(100),
  `author_url` VARCHAR(255),
  `plugin_url` VARCHAR(255),
  `license` VARCHAR(50),
  `license_key` VARCHAR(100),
  `min_php_version` VARCHAR(10),
  `min_system_version` VARCHAR(10),
  `dependencies` JSON,
  `settings` JSON,
  `status` ENUM('inactive','active','update_available','incompatible') DEFAULT 'inactive',
  `installed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `activated_at` TIMESTAMP NULL,
  `last_updated_at` TIMESTAMP NULL,
  `update_checked_at` TIMESTAMP NULL,
  INDEX idx_slug (`slug`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `plugin_hooks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `plugin_id` BIGINT UNSIGNED NOT NULL,
  `hook_name` VARCHAR(100) NOT NULL,
  `callback` VARCHAR(255) NOT NULL,
  `priority` INT DEFAULT 10,
  `arguments` INT DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_plugin (`plugin_id`),
  INDEX idx_hook (`hook_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `marketplace_products` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` VARCHAR(50) UNIQUE,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(100) UNIQUE,
  `type` ENUM('plugin','theme','integration','service'),
  `category` VARCHAR(50),
  `short_description` VARCHAR(500),
  `full_description` TEXT,
  `features` JSON,
  `screenshots` JSON,
  `version` VARCHAR(20),
  `price` DECIMAL(15,2),
  `currency` VARCHAR(10) DEFAULT 'IRR',
  `license_type` ENUM('single','multi','unlimited','subscription'),
  `license_duration_months` INT,
  `rating` DECIMAL(3,2) DEFAULT 0,
  `reviews_count` INT DEFAULT 0,
  `downloads_count` INT DEFAULT 0,
  `compatibility` JSON,
  `requirements` JSON,
  `changelog` JSON,
  `demo_url` VARCHAR(255),
  `documentation_url` VARCHAR(255),
  `support_url` VARCHAR(255),
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_verified` TINYINT(1) DEFAULT 0,
  `last_synced_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_type (`type`),
  INDEX idx_category (`category`),
  INDEX idx_featured (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `licenses` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `license_key` VARCHAR(100) UNIQUE NOT NULL,
  `product_id` VARCHAR(50),
  `customer_email` VARCHAR(100),
  `customer_name` VARCHAR(100),
  `company_name` VARCHAR(200),
  `license_type` ENUM('single','multi','unlimited','subscription'),
  `status` ENUM('active','inactive','expired','suspended','cancelled'),
  `activated_domains` JSON,
  `max_activations` INT DEFAULT 1,
  `purchased_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `activated_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `last_checked_at` TIMESTAMP NULL,
  `metadata` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_license_key (`license_key`),
  INDEX idx_status (`status`),
  INDEX idx_expires (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- گزارش‌سازی و داشبورد
-- ========================================

CREATE TABLE `report_templates` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `code` VARCHAR(50) UNIQUE,
  `category` ENUM('hr','payroll','attendance','recruitment','performance','training','assets','financial','custom'),
  `description` TEXT,
  `query_sql` TEXT,
  `parameters` JSON,
  `columns` JSON,
  `filters` JSON,
  `charts` JSON,
  `schedule` JSON,
  `recipients` JSON,
  `is_public` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_category (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `saved_reports` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `template_id` BIGINT UNSIGNED,
  `name` VARCHAR(200) NOT NULL,
  `parameters` JSON,
  `filters_applied` JSON,
  `result_cache` JSON,
  `export_format` ENUM('pdf','excel','csv','html'),
  `export_path` VARCHAR(255),
  `created_by` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_template (`template_id`),
  INDEX idx_creator (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dashboard_widgets` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED,
  `role_id` BIGINT UNSIGNED,
  `widget_type` VARCHAR(50) NOT NULL,
  `widget_config` JSON,
  `title` VARCHAR(100),
  `position_x` INT DEFAULT 0,
  `position_y` INT DEFAULT 0,
  `width` INT DEFAULT 6,
  `height` INT DEFAULT 4,
  `is_visible` TINYINT(1) DEFAULT 1,
  `refresh_interval` INT DEFAULT 300,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_user (`user_id`),
  INDEX idx_role (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kpi_definitions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `code` VARCHAR(50) UNIQUE,
  `category` VARCHAR(50),
  `description` TEXT,
  `formula` TEXT NOT NULL,
  `unit` VARCHAR(50),
  `target_value` DECIMAL(15,2),
  `warning_threshold` DECIMAL(15,2),
  `critical_threshold` DECIMAL(15,2),
  `comparison_type` ENUM('higher_better','lower_better','range'),
  `data_source` VARCHAR(100),
  `refresh_frequency` ENUM('real_time','hourly','daily','weekly','monthly'),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_category (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kpi_definitions` (`name`, `code`, `category`, `formula`, `unit`, `target_value`, `comparison_type`, `refresh_frequency`) VALUES
('نرخ ترک خدمت', 'turnover_rate', 'hr', '(terminations / avg_headcount) * 100', '%', 10, 'lower_better', 'monthly'),
('نرخ غیبت', 'absence_rate', 'attendance', '(absence_days / total_work_days) * 100', '%', 3, 'lower_better', 'daily'),
('زمان متوسط استخدام', 'time_to_hire', 'recruitment', 'AVG(hire_date - application_date)', 'days', 30, 'lower_better', 'weekly'),
('رضایت کارکنان', 'employee_satisfaction', 'hr', 'AVG(survey_scores)', 'score', 80, 'higher_better', 'quarterly'),
('هزینه هر استخدام', 'cost_per_hire', 'recruitment', 'total_recruitment_cost / hires_count', 'IRR', 50000000, 'lower_better', 'monthly'),
('نرخ بهره‌وری', 'productivity_rate', 'performance', '(output / input) * 100', '%', 85, 'higher_better', 'weekly');

CREATE TABLE `kpi_values` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kpi_id` BIGINT UNSIGNED NOT NULL,
  `period_start` DATE,
  `period_end` DATE,
  `value` DECIMAL(15,4),
  `target_value` DECIMAL(15,4),
  `variance` DECIMAL(15,4),
  `variance_percentage` DECIMAL(8,2),
  `status` ENUM('on_track','warning','critical'),
  `trend` ENUM('improving','stable','declining'),
  `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_kpi (`kpi_id`),
  INDEX idx_period (`period_start`, `period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- کاربران پیش‌فرض
-- ========================================

INSERT INTO `users` (`username`, `email`, `password_hash`, `first_name`, `last_name`, `role_id`, `is_active`) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر', 'سیستم', 1, 1);

COMMIT;
