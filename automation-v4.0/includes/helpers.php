<?php
/**
 * توابع کمکی سامانه
 * شامل توابع تاریخ شمسی، مالیات، بیمه و سایر محاسبات
 * نسخه Enterprise v4.0
 */

/**
 * تبدیل تاریخ میلادی به شمسی
 */
function gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $jy = ($gy <= 1600) ? 0 : 979;
    $gy -= ($gy <= 1600) ? 621 : 1600;
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * ((int)($days / 12053));
    $days %= 12053;
    $jy += 4 * ((int)($days / 1461));
    $days %= 1461;
    $jy += (int)(($days - 1) / 365);
    if ($days > 365) $days = ($days - 1) % 365;
    $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    return [$jy, $jm, $jd];
}

/**
 * نمایش تاریخ شمسی
 */
function display_date_shamsi($date = null) {
    if (!$date) $date = date('Y-m-d');
    list($gy, $gm, $gd) = explode('-', $date);
    list($jy, $jm, $jd) = gregorian_to_jalali((int)$gy, (int)$gm, (int)$gd);
    $months = ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    return sprintf("%d %s %d", $jd, $months[$jm], $jy);
}

/**
 * نمایش اعداد به فارسی
 */
function to_persian_digits($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace(range(0, 9), $persianDigits, (string)$number);
}

/**
 * فرمت کردن مبلغ به ریال با جداکننده
 */
function format_currency($amount, $currency = 'ریال') {
    return number_format($amount) . ' ' . $currency;
}

/**
 * محاسبه مالیات بر درآمد حقوق سال 1405
 * مقادیر قابل بروزرسانی از دیتابیس
 */
function calculate_income_tax($monthly_salary) {
    // نرخ‌های مالیاتی سال 1405 (قابل بروزرسانی از دیتابیس)
    $brackets = [
        ['min' => 0, 'max' => 120000000, 'rate' => 0],
        ['min' => 120000000, 'max' => 160000000, 'rate' => 0.10],
        ['min' => 160000000, 'max' => 220000000, 'rate' => 0.15],
        ['min' => 220000000, 'max' => 300000000, 'rate' => 0.20],
        ['min' => 300000000, 'max' => null, 'rate' => 0.25],
    ];
    
    $tax = 0;
    foreach ($brackets as $bracket) {
        if ($monthly_salary <= $bracket['min']) continue;
        
        $taxable_amount = $bracket['max'] === null 
            ? $monthly_salary - $bracket['min']
            : min($monthly_salary, $bracket['max']) - $bracket['min'];
            
        $tax += $taxable_amount * $bracket['rate'];
    }
    
    return $tax;
}

/**
 * محاسبه حق بیمه سهم کارمند (7%)
 */
function calculate_insurance_employee($salary) {
    return $salary * 0.07;
}

/**
 * محاسبه حق بیمه سهم کارفرما (23%)
 */
function calculate_insurance_employer($salary) {
    return $salary * 0.23;
}

/**
 * محاسبه اضافه کاری
 */
function calculate_overtime($hourly_rate, $hours, $is_holiday = false, $is_night = false) {
    $multiplier = 1.4; // ضریب اضافه کاری عادی
    if ($is_holiday) $multiplier = 2.0; // تعطیل کاری
    if ($is_night && !$is_holiday) $multiplier = 1.6; // شب کاری
    
    return $hourly_rate * $hours * $multiplier;
}

/**
 * محاسبه حق اولاد
 */
function calculate_child_benefit($base_salary, $children_count) {
    if ($children_count > 4) $children_count = 4; // حداکثر 4 فرزند
    return $base_salary * 0.10 * $children_count; // 10% برای هر فرزند
}

/**
 * تولید توکن CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * بررسی توکن CSRF
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * هش کردن رمز عبور
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * بررسی رمز عبور
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * بررسی دسترسی کاربر
 */
function check_permission($module, $action = 'view') {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $permissions = $_SESSION['user_permissions'] ?? [];
    
    if (isset($permissions['*'])) {
        return true;
    }
    
    if (!isset($permissions[$module])) {
        return false;
    }
    
    $module_perms = $permissions[$module];
    if ($module_perms === '*') {
        return true;
    }
    
    if (is_array($module_perms)) {
        return in_array($action, $module_perms) || in_array('*', $module_perms);
    }
    
    return $module_perms === $action;
}

/**
 * ریدایرکت امن
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * نمایش پیام فلش
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash_message($type = null) {
    if ($type) {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    $messages = $_SESSION['flash'] ?? [];
    $_SESSION['flash'] = [];
    return $messages;
}

/**
 * پاکسازی ورودی‌ها
 */
function sanitize_input($input) {
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * آپلود فایل
 */
function upload_file($file, $directory, $allowed_types = []) {
    if (!isset($file['error']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'خطا در آپلود فایل'];
    }
    
    $default_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $allowed_types = empty($allowed_types) ? $default_types : $allowed_types;
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'نوع فایل مجاز نیست'];
    }
    
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'حجم فایل بیش از حد مجاز است'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = rtrim($directory, '/') . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'خطا در ذخیره فایل'];
    }
    
    return ['success' => true, 'filepath' => $filepath, 'filename' => $filename];
}
