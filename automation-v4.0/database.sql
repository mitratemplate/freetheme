-- =====================================================
-- سامانه جامع اتوماسیون اداری و مدیریت منابع انسانی
-- نسخه Enterprise v4.0
-- استاندارد سال 1405 ایران
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4 COLLATE utf8mb4_persian_ci;

-- =====================================================
-- ساخت پایگاه داده
-- =====================================================
CREATE DATABASE IF NOT EXISTS `automation_pro` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
USE `automation_pro`;

-- =====================================================
-- جدول استانداردهای حقوق و دستمزد (سال 1405)
-- قابل بروزرسانی برای سال‌های جدید
-- =====================================================
CREATE TABLE `ap_salary_standards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year` INT NOT NULL COMMENT 'سال شمسی',
    `base_salary` DECIMAL(15,0) NOT NULL COMMENT 'حقوق پایه ماهانه به ریال',
    `housing_allowance` DECIMAL(15,0) NOT NULL COMMENT 'حق مسکن به ریال',
    `food_allowance` DECIMAL(15,0) NOT NULL COMMENT 'بن خواربار به ریال',
    `child_benefit_rate` DECIMAL(5,2) NOT NULL DEFAULT 10.00 COMMENT 'درصد حق اولاد',
    `max_children` INT NOT NULL DEFAULT 4 COMMENT 'حداکثر تعداد فرزندان مشمول',
    `overtime_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.40 COMMENT 'ضریب اضافه کاری عادی',
    `night_work_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.60 COMMENT 'ضریب شب کاری',
    `holiday_work_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 2.00 COMMENT 'ضریب تعطیل کاری',
    `insurance_employee_rate` DECIMAL(5,2) NOT NULL DEFAULT 7.00 COMMENT 'درصد بیمه سهم کارمند',
    `insurance_employer_rate` DECIMAL(5,2) NOT NULL DEFAULT 23.00 COMMENT 'درصد بیمه سهم کارفرما',
    `tax_brackets` JSON NOT NULL COMMENT 'جدول پلکانی مالیات',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_year` (`year`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- داده‌های پیش‌فرض سال 1405
INSERT INTO `ap_salary_standards` (`year`, `base_salary`, `housing_allowance`, `food_allowance`, `tax_brackets`) VALUES
(1405, 85000000, 25000000, 15000000, '[
    {"min": 0, "max": 120000000, "rate": 0},
    {"min": 120000000, "max": 160000000, "rate": 0.10},
    {"min": 160000000, "max": 220000000, "rate": 0.15},
    {"min": 220000000, "max": 300000000, "rate": 0.20},
    {"min": 300000000, "max": null, "rate": 0.25}
]');

-- =====================================================
-- جدول واحدهای سازمانی
-- =====================================================
CREATE TABLE `ap_departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) UNIQUE,
    `parent_id` INT NULL,
    `manager_id` INT NULL,
    `description` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول سمت‌های سازمانی
-- =====================================================
CREATE TABLE `ap_positions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) UNIQUE,
    `department_id` INT,
    `level` ENUM('entry', 'mid', 'senior', 'expert', 'manager', 'director') DEFAULT 'entry',
    `min_salary` DECIMAL(15,0),
    `max_salary` DECIMAL(15,0),
    `requirements` JSON,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
    INDEX `idx_department` (`department_id`),
    INDEX `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول کارکنان
-- =====================================================
CREATE TABLE `ap_employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_code` VARCHAR(20) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `father_name` VARCHAR(50),
    `national_code` VARCHAR(10) UNIQUE NOT NULL,
    `birth_date` DATE,
    `gender` ENUM('male', 'female'),
    `marital_status` ENUM('single', 'married'),
    `children_count` INT DEFAULT 0,
    `education_level` ENUM('under_diploma', 'diploma', 'associate', 'bachelor', 'master', 'phd'),
    `field_of_study` VARCHAR(100),
    `university` VARCHAR(100),
    `phone` VARCHAR(15),
    `mobile` VARCHAR(15),
    `email` VARCHAR(100),
    `address` TEXT,
    `postal_code` VARCHAR(10),
    `emergency_contact_name` VARCHAR(100),
    `emergency_contact_phone` VARCHAR(15),
    `department_id` INT,
    `position_id` INT,
    `hire_date` DATE NOT NULL,
    `contract_type` ENUM('permanent', 'temporary', 'contractual', 'part_time', 'project') DEFAULT 'permanent',
    `contract_start` DATE,
    `contract_end` DATE,
    `probation_end` DATE,
    `base_salary` DECIMAL(15,0),
    `bank_name` VARCHAR(50),
    `bank_account` VARCHAR(20),
    `sheba_number` VARCHAR(24),
    `photo_path` VARCHAR(255),
    `resume_path` VARCHAR(255),
    `documents` JSON,
    `is_active` TINYINT(1) DEFAULT 1,
    `termination_date` DATE,
    `termination_reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`position_id`) REFERENCES `ap_positions`(`id`) ON DELETE SET NULL,
    INDEX `idx_employee_code` (`employee_code`),
    INDEX `idx_national_code` (`national_code`),
    INDEX `idx_department` (`department_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول نقش‌ها
-- =====================================================
CREATE TABLE `ap_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) UNIQUE NOT NULL,
    `permissions` JSON NOT NULL,
    `is_system` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- داده‌های پیش‌فرض نقش‌ها
INSERT INTO `ap_roles` (`name`, `slug`, `permissions`, `is_system`) VALUES
('مدیر کل', 'super-admin', '{"*": "*"}', 1),
('مدیر منابع انسانی', 'hr-manager', '{"hr": "*", "employees": "*", "recruitment": "*", "performance": "*", "training": "*"}', 1),
('مدیر مالی', 'finance-manager', '{"payroll": "*", "loans": "*", "reports": "financial", "assets": "view"}', 1),
('مدیر واحد', 'department-manager', '{"employees": "view", "attendance": "approve", "leave": "approve", "performance": "view"}', 1),
('کارمند', 'employee', '{"profile": "own", "attendance": "own", "leave": "own", "payslip": "own"}', 1);

-- =====================================================
-- جدول کاربران سیستم
-- =====================================================
CREATE TABLE `ap_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NULL,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `phone` VARCHAR(15),
    `is_active` TINYINT(1) DEFAULT 1,
    `is_locked` TINYINT(1) DEFAULT 0,
    `locked_until` DATETIME NULL,
    `failed_attempts` INT DEFAULT 0,
    `last_login` DATETIME NULL,
    `last_password_change` DATETIME NULL,
    `must_change_password` TINYINT(1) DEFAULT 0,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `two_factor_secret` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`role_id`) REFERENCES `ap_roles`(`id`) ON DELETE RESTRICT,
    INDEX `idx_username` (`username`),
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- کاربر پیش‌فرض admin (رمز: admin123)
INSERT INTO `ap_users` (`employee_id`, `username`, `password_hash`, `role_id`, `full_name`, `email`, `is_active`) VALUES
(NULL, 'admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4.G.2fXNlOj.u.', 1, 'مدیر سیستم', 'admin@example.com', 1);

-- =====================================================
-- جدول انواع مرخصی
-- =====================================================
CREATE TABLE `ap_leave_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `code` VARCHAR(20) UNIQUE,
    `is_paid` TINYINT(1) DEFAULT 1,
    `requires_document` TINYINT(1) DEFAULT 0,
    `max_days_per_year` INT,
    `carry_over_allowed` TINYINT(1) DEFAULT 0,
    `description` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- انواع مرخصی پیش‌فرض
INSERT INTO `ap_leave_types` (`name`, `code`, `is_paid`, `requires_document`, `max_days_per_year`, `carry_over_allowed`) VALUES
('مرخصی استحقاقی', 'annual', 1, 0, 26, 1),
('مرخصی استعلاجی', 'sick', 1, 1, 30, 0),
('مرخصی بدون حقوق', 'unpaid', 0, 0, 90, 0),
('مرخصی ازدواج', 'marriage', 1, 0, 5, 0),
('مرخصی فوت', 'bereavement', 1, 0, 3, 0),
('مرخصی زایمان', 'maternity', 1, 1, 90, 0),
('مرخصی پدری', 'paternity', 1, 0, 3, 0),
('مرخصی ساعتی', 'hourly', 1, 0, 60, 0);

-- =====================================================
-- جدول درخواست‌های مرخصی
-- =====================================================
CREATE TABLE `ap_leave_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `leave_type_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `start_time` TIME NULL,
    `end_time` TIME NULL,
    `total_days` DECIMAL(5,2),
    `reason` TEXT,
    `document_path` VARCHAR(255),
    `status` ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `rejection_reason` TEXT,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`leave_type_id`) REFERENCES `ap_leave_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول مانده مرخصی سالانه
-- =====================================================
CREATE TABLE `ap_leave_balances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `year` INT NOT NULL,
    `leave_type_id` INT NOT NULL,
    `opening_balance` DECIMAL(5,2) DEFAULT 0,
    `accrued` DECIMAL(5,2) DEFAULT 0,
    `taken` DECIMAL(5,2) DEFAULT 0,
    `carried_from_previous` DECIMAL(5,2) DEFAULT 0,
    `carried_to_next` DECIMAL(5,2) DEFAULT 0,
    `closing_balance` DECIMAL(5,2) DEFAULT 0,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`leave_type_id`) REFERENCES `ap_leave_types`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY `unique_employee_year_type` (`employee_id`, `year`, `leave_type_id`),
    INDEX `idx_employee_year` (`employee_id`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول حضور و غیاب
-- =====================================================
CREATE TABLE `ap_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `check_date` DATE NOT NULL,
    `check_in` DATETIME NULL,
    `check_out` DATETIME NULL,
    `late_minutes` INT DEFAULT 0,
    `early_departure_minutes` INT DEFAULT 0,
    `overtime_minutes` INT DEFAULT 0,
    `status` ENUM('present', 'absent', 'leave', 'mission', 'holiday') DEFAULT 'present',
    `notes` TEXT,
    `device_id` VARCHAR(50),
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_employee_date` (`employee_id`, `check_date`),
    INDEX `idx_check_date` (`check_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول ماموریت‌ها
-- =====================================================
CREATE TABLE `ap_missions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `destination` VARCHAR(200),
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `start_time` TIME,
    `end_time` TIME,
    `transportation` ENUM('car', 'plane', 'train', 'bus', 'other'),
    `purpose` TEXT,
    `estimated_cost` DECIMAL(15,0),
    `actual_cost` DECIMAL(15,0),
    `status` ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `report` TEXT,
    `attachments` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول فیش‌های حقوقی
-- =====================================================
CREATE TABLE `ap_payrolls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `year` INT NOT NULL,
    `month` INT NOT NULL,
    `work_days` INT DEFAULT 30,
    `worked_days` INT DEFAULT 30,
    `base_salary` DECIMAL(15,0) NOT NULL,
    `housing_allowance` DECIMAL(15,0) DEFAULT 0,
    `food_allowance` DECIMAL(15,0) DEFAULT 0,
    `child_benefit` DECIMAL(15,0) DEFAULT 0,
    `overtime_pay` DECIMAL(15,0) DEFAULT 0,
    `night_work_pay` DECIMAL(15,0) DEFAULT 0,
    `holiday_work_pay` DECIMAL(15,0) DEFAULT 0,
    `bonus` DECIMAL(15,0) DEFAULT 0,
    `other_additions` DECIMAL(15,0) DEFAULT 0,
    `gross_salary` DECIMAL(15,0) NOT NULL,
    `insurance_employee` DECIMAL(15,0) DEFAULT 0,
    `income_tax` DECIMAL(15,0) DEFAULT 0,
    `loan_deduction` DECIMAL(15,0) DEFAULT 0,
    `advance_deduction` DECIMAL(15,0) DEFAULT 0,
    `absence_deduction` DECIMAL(15,0) DEFAULT 0,
    `other_deductions` DECIMAL(15,0) DEFAULT 0,
    `total_deductions` DECIMAL(15,0) DEFAULT 0,
    `net_salary` DECIMAL(15,0) NOT NULL,
    `insurance_employer` DECIMAL(15,0) DEFAULT 0,
    `payment_date` DATE NULL,
    `payment_reference` VARCHAR(50),
    `is_paid` TINYINT(1) DEFAULT 0,
    `notes` TEXT,
    `prepared_by` INT,
    `approved_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`prepared_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_employee_month` (`employee_id`, `year`, `month`),
    INDEX `idx_year_month` (`year`, `month`),
    INDEX `idx_is_paid` (`is_paid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول جزئیات اضافه کاری
-- =====================================================
CREATE TABLE `ap_overtime_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `payroll_id` INT NULL,
    `date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `total_minutes` INT NOT NULL,
    `is_holiday` TINYINT(1) DEFAULT 0,
    `is_night` TINYINT(1) DEFAULT 0,
    `multiplier` DECIMAL(3,2) DEFAULT 1.40,
    `hourly_rate` DECIMAL(15,0),
    `total_amount` DECIMAL(15,0),
    `approved_by` INT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payroll_id`) REFERENCES `ap_payrolls`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_employee_date` (`employee_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول وام‌ها و مساعده
-- =====================================================
CREATE TABLE `ap_loans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `loan_type` ENUM('housing', 'marriage', 'emergency', 'salary_advance', 'other') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `amount` DECIMAL(15,0) NOT NULL,
    `interest_rate` DECIMAL(5,2) DEFAULT 0,
    `installments_count` INT NOT NULL,
    `installment_amount` DECIMAL(15,0) NOT NULL,
    `first_installment_date` DATE,
    `remaining_amount` DECIMAL(15,0),
    `paid_amount` DECIMAL(15,0) DEFAULT 0,
    `status` ENUM('pending', 'approved', 'rejected', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `description` TEXT,
    `guarantor_info` JSON,
    `documents` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول اقساط وام
-- =====================================================
CREATE TABLE `ap_loan_installments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `loan_id` INT NOT NULL,
    `installment_number` INT NOT NULL,
    `due_date` DATE NOT NULL,
    `amount` DECIMAL(15,0) NOT NULL,
    `paid_date` DATE NULL,
    `deducted_from_payroll` TINYINT(1) DEFAULT 0,
    `payroll_id` INT NULL,
    `status` ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`loan_id`) REFERENCES `ap_loans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`payroll_id`) REFERENCES `ap_payrolls`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_loan_installment` (`loan_id`, `installment_number`),
    INDEX `idx_due_date` (`due_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول آگهی‌های استخدامی
-- =====================================================
CREATE TABLE `ap_job_postings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `position_id` INT NULL,
    `department_id` INT NULL,
    `employment_type` ENUM('permanent', 'temporary', 'contractual', 'part_time', 'project'),
    `location` VARCHAR(200),
    `min_experience_years` INT DEFAULT 0,
    `education_required` ENUM('under_diploma', 'diploma', 'associate', 'bachelor', 'master', 'phd'),
    `skills_required` JSON,
    `responsibilities` TEXT,
    `qualifications` TEXT,
    `salary_range_min` DECIMAL(15,0),
    `salary_range_max` DECIMAL(15,0),
    `benefits` JSON,
    `application_deadline` DATE,
    `status` ENUM('draft', 'published', 'closed', 'paused') DEFAULT 'draft',
    `views_count` INT DEFAULT 0,
    `applications_count` INT DEFAULT 0,
    `published_by` INT,
    `published_at` DATETIME NULL,
    `closed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`position_id`) REFERENCES `ap_positions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`department_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`published_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_deadline` (`application_deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول متقاضیان استخدام
-- =====================================================
CREATE TABLE `ap_job_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_posting_id` INT NOT NULL,
    `applicant_name` VARCHAR(100) NOT NULL,
    `applicant_email` VARCHAR(100),
    `applicant_phone` VARCHAR(15),
    `national_code` VARCHAR(10),
    `birth_date` DATE,
    `gender` ENUM('male', 'female'),
    `education_level` ENUM('under_diploma', 'diploma', 'associate', 'bachelor', 'master', 'phd'),
    `field_of_study` VARCHAR(100),
    `university` VARCHAR(100),
    `experience_years` INT DEFAULT 0,
    `current_company` VARCHAR(100),
    `current_position` VARCHAR(100),
    `expected_salary` DECIMAL(15,0),
    `resume_path` VARCHAR(255) NOT NULL,
    `cover_letter` TEXT,
    `portfolio_url` VARCHAR(255),
    `linkedin_url` VARCHAR(255),
    `source` ENUM('website', 'job_board', 'referral', 'social_media', 'other'),
    `referred_by` INT NULL,
    `status` ENUM('new', 'screening', 'interview', 'assessment', 'offered', 'hired', 'rejected', 'withdrawn') DEFAULT 'new',
    `screening_notes` TEXT,
    `interview_date` DATETIME NULL,
    `interview_score` DECIMAL(5,2),
    `interviewer_id` INT NULL,
    `assessment_result` TEXT,
    `offer_amount` DECIMAL(15,0),
    `hire_date` DATE NULL,
    `employee_id` INT NULL,
    `rejection_reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`job_posting_id`) REFERENCES `ap_job_postings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`referred_by`) REFERENCES `ap_employees`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`interviewer_id`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE SET NULL,
    INDEX `idx_job_posting` (`job_posting_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول مراحل مصاحبه
-- =====================================================
CREATE TABLE `ap_interviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `interview_type` ENUM('phone', 'video', 'in_person', 'panel') DEFAULT 'in_person',
    `scheduled_date` DATETIME NOT NULL,
    `duration_minutes` INT DEFAULT 60,
    `location` VARCHAR(200),
    `meeting_link` VARCHAR(255),
    `interviewer_ids` JSON,
    `questions` JSON,
    `score` DECIMAL(5,2),
    `feedback` TEXT,
    `recommendation` ENUM('strong_yes', 'yes', 'maybe', 'no', 'strong_no'),
    `status` ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    `reschedule_reason` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `ap_job_applications`(`id`) ON DELETE CASCADE,
    INDEX `idx_application` (`application_id`),
    INDEX `idx_scheduled_date` (`scheduled_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول ارزیابی عملکرد
-- =====================================================
CREATE TABLE `ap_performance_reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `reviewer_id` INT NOT NULL,
    `review_period_start` DATE NOT NULL,
    `review_period_end` DATE NOT NULL,
    `review_type` ENUM('self', 'manager', 'peer', 'subordinate', '360') DEFAULT 'manager',
    `overall_score` DECIMAL(5,2),
    `strengths` TEXT,
    `weaknesses` TEXT,
    `goals_achieved` TEXT,
    `development_areas` TEXT,
    `training_recommendations` TEXT,
    `promotion_recommendation` ENUM('strongly_recommended', 'recommended', 'not_ready', 'not_applicable'),
    `salary_increase_recommendation` DECIMAL(5,2),
    `comments` TEXT,
    `status` ENUM('draft', 'submitted', 'acknowledged', 'completed') DEFAULT 'draft',
    `employee_comments` TEXT,
    `acknowledged_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewer_id`) REFERENCES `ap_users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_reviewer` (`reviewer_id`),
    INDEX `idx_period` (`review_period_start`, `review_period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول شاخص‌های عملکرد (KPI)
-- =====================================================
CREATE TABLE `ap_kpis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `category` ENUM('quality', 'quantity', 'time', 'cost', 'behavior', 'skill'),
    `measurement_unit` VARCHAR(50),
    `target_value` DECIMAL(10,2),
    `weight` DECIMAL(5,2) DEFAULT 1.00,
    `evaluation_method` ENUM('numeric', 'scale_1_5', 'scale_1_10', 'percentage', 'yes_no'),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول اهداف فردی
-- =====================================================
CREATE TABLE `ap_employee_goals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `goal_type` ENUM('development', 'performance', 'learning', 'career'),
    `priority` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    `start_date` DATE,
    `target_date` DATE,
    `completion_date` DATE NULL,
    `status` ENUM('not_started', 'in_progress', 'completed', 'on_hold', 'cancelled') DEFAULT 'not_started',
    `progress_percentage` INT DEFAULT 0,
    `success_criteria` TEXT,
    `obstacles` TEXT,
    `support_needed` TEXT,
    `manager_comments` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_target_date` (`target_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول دوره‌های آموزشی
-- =====================================================
CREATE TABLE `ap_training_courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `code` VARCHAR(20) UNIQUE,
    `category` ENUM('technical', 'soft_skills', 'compliance', 'leadership', 'safety', 'other'),
    `description` TEXT,
    `objectives` JSON,
    `prerequisites` TEXT,
    `duration_hours` DECIMAL(5,2),
    `delivery_method` ENUM('in_person', 'online', 'blended', 'self_paced'),
    `provider` VARCHAR(200),
    `instructor` VARCHAR(100),
    `location` VARCHAR(200),
    `max_participants` INT,
    `cost` DECIMAL(15,0),
    `certificate_provided` TINYINT(1) DEFAULT 0,
    `start_date` DATE,
    `end_date` DATE,
    `registration_deadline` DATE,
    `status` ENUM('draft', 'published', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft',
    `enrolled_count` INT DEFAULT 0,
    `completed_count` INT DEFAULT 0,
    `average_score` DECIMAL(5,2),
    `materials` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_status` (`status`),
    INDEX `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول ثبت‌نام در دوره‌های آموزشی
-- =====================================================
CREATE TABLE `ap_training_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `enrollment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('enrolled', 'in_progress', 'completed', 'dropped', 'failed') DEFAULT 'enrolled',
    `attendance_percentage` DECIMAL(5,2),
    `final_score` DECIMAL(5,2),
    `feedback` TEXT,
    `certificate_path` VARCHAR(255),
    `completion_date` DATE NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`course_id`) REFERENCES `ap_training_courses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `ap_employees`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_enrollment` (`course_id`, `employee_id`),
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول اموال و دارایی‌ها
-- =====================================================
CREATE TABLE `ap_assets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `asset_code` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `category` ENUM('laptop', 'desktop', 'mobile', 'tablet', 'furniture', 'vehicle', 'equipment', 'other'),
    `brand` VARCHAR(100),
    `model` VARCHAR(100),
    `serial_number` VARCHAR(100),
    `purchase_date` DATE,
    `purchase_price` DECIMAL(15,0),
    `supplier` VARCHAR(200),
    `warranty_until` DATE,
    `current_value` DECIMAL(15,0),
    `depreciation_rate` DECIMAL(5,2) DEFAULT 0,
    `condition` ENUM('new', 'good', 'fair', 'poor', 'broken') DEFAULT 'good',
    `assigned_to` INT NULL,
    `location` VARCHAR(200),
    `status` ENUM('available', 'assigned', 'maintenance', 'retired', 'lost', 'stolen') DEFAULT 'available',
    `specifications` JSON,
    `attachments` JSON,
    `maintenance_history` JSON,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_to`) REFERENCES `ap_employees`(`id`) ON DELETE SET NULL,
    INDEX `idx_asset_code` (`asset_code`),
    INDEX `idx_category` (`category`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول جلسات
-- =====================================================
CREATE TABLE `ap_meetings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `meeting_type` ENUM('team', 'one_on_one', 'interview', 'training', 'client', 'board', 'other'),
    `organizer_id` INT NOT NULL,
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `location` VARCHAR(200),
    `meeting_link` VARCHAR(255),
    `agenda` TEXT,
    `participant_ids` JSON,
    `required_attendees` JSON,
    `optional_attendees` JSON,
    `attachments` JSON,
    `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled', 'postponed') DEFAULT 'scheduled',
    `minutes` TEXT,
    `action_items` JSON,
    `recording_path` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`organizer_id`) REFERENCES `ap_users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_organizer` (`organizer_id`),
    INDEX `idx_start_datetime` (`start_datetime`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول مکاتبات اداری
-- =====================================================
CREATE TABLE `ap_correspondences` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reference_number` VARCHAR(50) UNIQUE NOT NULL,
    `subject` VARCHAR(300) NOT NULL,
    `correspondence_type` ENUM('incoming', 'outgoing', 'internal') NOT NULL,
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `from_party` VARCHAR(200),
    `from_organization` VARCHAR(200),
    `to_party` VARCHAR(200),
    `to_department_id` INT NULL,
    `content` TEXT,
    `attachments` JSON,
    `related_to` VARCHAR(100),
    `related_id` INT NULL,
    `status` ENUM('draft', 'sent', 'received', 'in_review', 'approved', 'rejected', 'archived') DEFAULT 'draft',
    `received_date` DATETIME NULL,
    `response_deadline` DATE NULL,
    `response_sent_date` DATETIME NULL,
    `tracking_number` VARCHAR(50),
    `notes` TEXT,
    `created_by` INT,
    `approved_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`to_department_id`) REFERENCES `ap_departments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_reference` (`reference_number`),
    INDEX `idx_type` (`correspondence_type`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول اطلاعیه‌ها
-- =====================================================
CREATE TABLE `ap_announcements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `announcement_type` ENUM('general', 'hr', 'finance', 'it', 'safety', 'event') DEFAULT 'general',
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `target_audience` ENUM('all', 'employees', 'managers', 'specific_departments', 'specific_roles') DEFAULT 'all',
    `target_department_ids` JSON,
    `target_role_ids` JSON,
    `publish_date` DATETIME,
    `expiry_date` DATETIME,
    `is_pinned` TINYINT(1) DEFAULT 0,
    `require_acknowledgment` TINYINT(1) DEFAULT 0,
    `attachments` JSON,
    `author_id` INT,
    `status` ENUM('draft', 'scheduled', 'published', 'expired', 'archived') DEFAULT 'draft',
    `views_count` INT DEFAULT 0,
    `acknowledged_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_type` (`announcement_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_publish_date` (`publish_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول پلاگین‌ها
-- =====================================================
CREATE TABLE `ap_plugins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) UNIQUE NOT NULL,
    `version` VARCHAR(20) NOT NULL,
    `description` TEXT,
    `author` VARCHAR(100),
    `author_url` VARCHAR(255),
    `plugin_url` VARCHAR(255),
    `license` VARCHAR(50),
    `requires_php` VARCHAR(10),
    `requires_wp` VARCHAR(10),
    `dependencies` JSON,
    `settings` JSON,
    `is_active` TINYINT(1) DEFAULT 0,
    `is_installed` TINYINT(1) DEFAULT 0,
    `installed_at` DATETIME NULL,
    `activated_at` DATETIME NULL,
    `last_updated` DATETIME NULL,
    `update_available` TINYINT(1) DEFAULT 0,
    `latest_version` VARCHAR(20),
    `download_url` VARCHAR(255),
    `license_key` VARCHAR(100),
    `license_status` ENUM('valid', 'invalid', 'expired', 'missing') DEFAULT 'missing',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول لاگ ورود
-- =====================================================
CREATE TABLE `ap_login_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `username_attempt` VARCHAR(50),
    `login_time` DATETIME NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `status` ENUM('success', 'failed') NOT NULL,
    `failure_reason` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_login_time` (`login_time`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول لاگ فعالیت‌ها
-- =====================================================
CREATE TABLE `ap_activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(50) NOT NULL,
    `table_name` VARCHAR(50),
    `record_id` INT,
    `description` TEXT,
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_table` (`table_name`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- جدول تنظیمات سیستم
-- =====================================================
CREATE TABLE `ap_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string',
    `group_name` VARCHAR(50) DEFAULT 'general',
    `label` VARCHAR(200),
    `description` TEXT,
    `is_public` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`setting_key`),
    INDEX `idx_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- داده‌های پیش‌فرض تنظیمات
INSERT INTO `ap_settings` (`setting_key`, `setting_value`, `setting_type`, `group_name`, `label`) VALUES
('company_name', 'شرکت نمونه', 'string', 'general', 'نام شرکت'),
('company_logo', '', 'string', 'general', 'لوگو شرکت'),
('fiscal_year_start', '1', 'number', 'financial', 'ماه شروع سال مالی'),
('working_days_per_week', '6', 'number', 'attendance', 'روزهای کاری در هفته'),
('work_start_time', '08:00', 'string', 'attendance', 'ساعت شروع کار'),
('work_end_time', '17:00', 'string', 'attendance', 'ساعت پایان کار'),
('grace_period_minutes', '15', 'number', 'attendance', 'دوره سماحت دقیقه'),
('enable_ai_assistant', '1', 'boolean', 'ai', 'فعال‌سازی دستیار هوشمند'),
('gapgpt_api_key', '', 'string', 'ai', 'کلید API GapGPT'),
('gapgpt_api_url', 'https://api.gapgpt.app/v1/chat/completions', 'string', 'ai', 'آدرس API GapGPT');

-- =====================================================
-- جدول درخواست‌های پشتیبانی
-- =====================================================
CREATE TABLE `ap_support_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(20) UNIQUE NOT NULL,
    `requester_id` INT NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `category` ENUM('technical', 'hr', 'finance', 'it', 'facility', 'other'),
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `status` ENUM('open', 'in_progress', 'waiting_customer', 'resolved', 'closed') DEFAULT 'open',
    `assigned_to` INT NULL,
    `resolution` TEXT,
    `resolved_at` DATETIME NULL,
    `resolved_by` INT NULL,
    `customer_satisfaction` ENUM('very_unsatisfied', 'unsatisfied', 'neutral', 'satisfied', 'very_satisfied') NULL,
    `customer_feedback` TEXT,
    `attachments` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`requester_id`) REFERENCES `ap_users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`assigned_to`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`resolved_by`) REFERENCES `ap_users`(`id`) ON DELETE SET NULL,
    INDEX `idx_ticket_number` (`ticket_number`),
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- =====================================================
-- داده‌های نمونه برای تست
-- =====================================================

-- واحدهای سازمانی نمونه
INSERT INTO `ap_departments` (`name`, `code`, `description`) VALUES
('مدیریت عامل', 'CEO', 'دفتر مدیریت عامل'),
('منابع انسانی', 'HR', 'مدیریت منابع انسانی'),
('مالی و حسابداری', 'FIN', 'مدیریت مالی و حسابداری'),
('فناوری اطلاعات', 'IT', 'مدیریت فناوری اطلاعات'),
('فروش و بازاریابی', 'SLS', 'مدیریت فروش و بازاریابی'),
('تولید', 'PRD', 'مدیریت تولید');

-- سمت‌های نمونه
INSERT INTO `ap_positions` (`title`, `code`, `department_id`, `level`) VALUES
('مدیر عامل', 'CEO-001', 1, 'director'),
('مدیر منابع انسانی', 'HR-MGR-001', 2, 'manager'),
('کارشناس منابع انسانی', 'HR-EXP-001', 2, 'mid'),
('مدیر مالی', 'FIN-MGR-001', 3, 'manager'),
('حسابدار', 'FIN-ACC-001', 3, 'entry'),
('مدیر IT', 'IT-MGR-001', 4, 'manager'),
('برنامه‌نویس', 'IT-DEV-001', 4, 'mid');

COMMIT;
