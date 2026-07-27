<?php
/**
 * Helper Functions
 * Iranian Office Automation System - 1405
 */

// Persian Date Conversion (Jalali to Gregorian and vice versa)
function jalaali_to_gregorian($j_y, $j_m, $j_d) {
    $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    
    $gy = $j_y - 979;
    $gm = 0;
    $gd = 0;
    $day_no = ($j_y - 1) * 365 + div($j_y, 4) - div($j_y, 100) + div($j_y - 979, 400) - 1;
    
    for ($i = 0; $i < $j_m - 1; ++$i) {
        $day_no += $j_days_in_month[$i];
    }
    $day_no += $j_d;
    
    $n = $day_no % 365;
    $gy += div($day_no, 365);
    
    if ($n > 365) {
        $n = $n % 365;
    }
    
    for ($i = 0; $i < 12 && $n >= $g_days_in_month[$i] + ($i == 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))); $i++) {
        $n -= $g_days_in_month[$i] + ($i == 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 1 : 0);
    }
    
    $gm = $i + 1;
    $gd = $n + 1;
    
    return [$gy, $gm, $gd];
}

function gregorian_to_jalaali($g_y, $g_m, $g_d) {
    $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    
    $gy = $g_y - 1600;
    $gm = $g_m - 1;
    $gd = $g_d - 1;
    
    $day_no = 365 * $gy + div($gy, 4) - div($gy, 100) + div($gy, 400);
    
    for ($i = 0; $i < $gm; ++$i) {
        $day_no += $g_days_in_month[$i];
    }
    
    if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0))) {
        $day_no++;
    }
    
    $day_no += $gd;
    $j_np = $day_no - 79;
    $jy = 979 + 33 * div($j_np, 12053);
    $j_np %= 12053;
    
    $jy += 4 * div($j_np, 1461);
    $j_np %= 1461;
    
    if ($j_np > 365) {
        $jy += div($j_np - 1, 365);
        $j_np = ($j_np - 1) % 365;
    }
    
    for ($i = 0; $i < 11 && $j_np >= $j_days_in_month[$i]; ++$i) {
        $j_np -= $j_days_in_month[$i];
    }
    
    $jm = $i + 1;
    $jd = $j_np + 1;
    
    return [$jy, $jm, $jd];
}

function div($a, $b) {
    return (int)($a / $b);
}

// Format numbers in Persian
function to_persian_number($number) {
    $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $number_str = (string)$number;
    $result = '';
    
    for ($i = 0; $i < strlen($number_str); $i++) {
        $digit = $number_str[$i];
        if (is_numeric($digit)) {
            $result .= $persian_digits[(int)$digit];
        } else {
            $result .= $digit;
        }
    }
    
    return $result;
}

// Format currency with thousand separator
function format_currency($amount) {
    return number_format($amount) . ' ریال';
}

// Format currency to Toman
function format_toman($amount) {
    return number_format($amount / 10) . ' تومان';
}

// Get current active year
function get_active_year($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM years WHERE is_active = TRUE LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

// Get salary standards for a specific year
function get_salary_standards($pdo, $year_id) {
    $stmt = $pdo->prepare("SELECT * FROM salary_standards WHERE year_id = ?");
    $stmt->execute([$year_id]);
    return $stmt->fetch();
}

// Calculate income tax based on brackets
function calculate_tax($amount, $tax_brackets_json) {
    $tax_brackets = json_decode($tax_brackets_json, true);
    $tax = 0;
    $remaining = $amount;
    $previous_max = 0;
    
    foreach ($tax_brackets as $bracket) {
        $min = $bracket['min'];
        $max = $bracket['max'] ?? PHP_INT_MAX;
        $rate = $bracket['rate'] / 100;
        
        if ($amount <= $min) {
            break;
        }
        
        $taxable_in_bracket = min($amount, $max) - $min;
        if ($taxable_in_bracket > 0) {
            $tax += $taxable_in_bracket * $rate;
        }
    }
    
    return $tax;
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit;
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Get current user
function get_current_user() {
    return $_SESSION['user'] ?? null;
}

// Require login
function require_login() {
    session_start();
    if (!is_logged_in()) {
        redirect('/automation/modules/auth/login.php');
    }
}

// CSRF Token Generation
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
