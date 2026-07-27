<?php
/**
 * Helper Functions
 * Enterprise HR Automation System v2.0
 */

/**
 * Convert English numbers to Persian
 */
function toPersianNumber($number): string
{
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace(range(0, 9), $persianDigits, $number);
}

/**
 * Format number with commas and convert to Persian
 */
function formatNumber($number): string
{
    return toPersianNumber(number_format($number));
}

/**
 * Format currency (Rials)
 */
function formatCurrency($amount, $currency = 'IRR'): string
{
    $formatted = number_format($amount);
    $symbols = [
        'IRR' => 'ریال',
        'TOMAN' => 'تومان'
    ];
    
    if ($currency === 'TOMAN') {
        $amount = $amount / 10;
        $formatted = number_format($amount);
    }
    
    return formatCurrency($amount) . ' ' . ($symbols[$currency] ?? $currency);
}

/**
 * Get current Jalali date
 */
function jalaliDate($format = 'Y/m/d', $timestamp = null): string
{
    $timestamp = $timestamp ?? time();
    
    // Simple conversion (for production use jdf.php library)
    $gregorian = date('Y-m-d', $timestamp);
    list($gy, $gm, $gd) = explode('-', $gregorian);
    
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
    if ($days > 365) {
        $days = ($days - 1) % 365;
    }
    
    $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    
    $jDate = sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    
    if ($format === 'Y/m/d') {
        return toPersianNumber($jDate);
    }
    
    return $jDate;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF token field for forms
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="' . generateCsrfToken() . '">';
}

/**
 * Check if user is authenticated
 */
function isAuthenticated(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user
 */
function currentUser(): ?array
{
    if (!isAuthenticated()) {
        return null;
    }
    
    require_once APP_ROOT . '/app/Core/Database.php';
    $db = App\Core\Database::getInstance();
    
    return $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

/**
 * Check user permission
 */
function hasPermission(string $module, string $action = '*'): bool
{
    $user = currentUser();
    if (!$user) {
        return false;
    }
    
    require_once APP_ROOT . '/app/Core/Database.php';
    $db = App\Core\Database::getInstance();
    
    $role = $db->fetch("SELECT permissions FROM roles WHERE id = ?", [$user['role_id']]);
    
    if (!$role || !$role['permissions']) {
        return false;
    }
    
    $permissions = json_decode($role['permissions'], true);
    
    // Super admin has all permissions
    if ($permissions === '*' || (isset($permissions['*']) && $permissions['*'] === '*')) {
        return true;
    }
    
    // Check specific permission
    if (isset($permissions[$module])) {
        if ($permissions[$module] === '*') {
            return true;
        }
        if (is_array($permissions[$module]) && in_array($action, $permissions[$module])) {
            return true;
        }
        if ($permissions[$module] === $action) {
            return true;
        }
    }
    
    return false;
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Sanitize input
 */
function sanitizeInput(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Log activity
 */
function logActivity(string $action, string $module, $model = null, $modelId = null, $oldValues = null, $newValues = null): void
{
    require_once APP_ROOT . '/app/Core/Database.php';
    $db = App\Core\Database::getInstance();
    
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    $db->insert('activity_logs', [
        'user_id' => $userId,
        'action' => $action,
        'module' => $module,
        'model' => $model,
        'model_id' => $modelId,
        'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
        'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
        'ip_address' => $ipAddress
    ]);
}

/**
 * Upload file
 */
function uploadFile(array $file, string $directory = 'uploads'): array
{
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطا در آپلود فایل'];
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'نوع فایل مجاز نیست'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'حجم فایل بیش از حد مجاز است'];
    }
    
    $uploadDir = APP_ROOT . '/public/' . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'path' => '/' . $directory . '/' . $filename,
            'filename' => $filename
        ];
    }
    
    return ['success' => false, 'message' => 'خطا در ذخیره فایل'];
}

/**
 * Calculate salary components based on 1405 standards
 */
function calculateSalaryComponents(int $year = 1405): array
{
    require_once APP_ROOT . '/app/Core/Database.php';
    $db = App\Core\Database::getInstance();
    
    $standards = $db->fetch("SELECT * FROM salary_standards WHERE year = ?", [$year]);
    
    if (!$standards) {
        // Default values for 1405
        return [
            'base_salary' => 85000000,
            'housing_allowance' => 15000000,
            'food_allowance' => 8500000,
            'child_allowance_per_child' => 3500000,
            'minimum_hourly_rate' => 380000,
            'overtime_multiplier' => 1.4,
            'night_shift_multiplier' => 1.35,
            'holiday_multiplier' => 1.4,
            'insurance_rate_employee' => 0.07,
            'insurance_rate_employer' => 0.23,
            'tax_brackets' => [
                ['limit' => 120000000, 'rate' => 0.0],
                ['limit' => 180000000, 'rate' => 0.10],
                ['limit' => 300000000, 'rate' => 0.15],
                ['limit' => 500000000, 'rate' => 0.20],
                ['limit' => null, 'rate' => 0.30]
            ]
        ];
    }
    
    return [
        'base_salary' => (int) $standards['base_salary'],
        'housing_allowance' => (int) $standards['housing_allowance'],
        'food_allowance' => (int) $standards['food_allowance'],
        'child_allowance_per_child' => (int) $standards['child_allowance_per_child'],
        'minimum_hourly_rate' => (int) $standards['minimum_hourly_rate'],
        'overtime_multiplier' => (float) $standards['overtime_multiplier'],
        'night_shift_multiplier' => (float) $standards['night_shift_multiplier'],
        'holiday_multiplier' => (float) $standards['holiday_multiplier'],
        'insurance_rate_employee' => (float) $standards['insurance_rate_employee'],
        'insurance_rate_employer' => (float) $standards['insurance_rate_employer'],
        'tax_brackets' => json_decode($standards['tax_brackets'], true)
    ];
}

/**
 * Calculate income tax
 */
function calculateIncomeTax(float $annualIncome): float
{
    $components = calculateSalaryComponents();
    $taxBrackets = $components['tax_brackets'];
    $tax = 0;
    $remainingIncome = $annualIncome;
    $previousLimit = 0;
    
    foreach ($taxBrackets as $bracket) {
        $limit = $bracket['limit'] ?? PHP_INT_MAX;
        $rate = $bracket['rate'];
        
        if ($limit === null) {
            $limit = PHP_INT_MAX;
        }
        
        $taxableInBracket = min($remainingIncome, $limit - $previousLimit);
        
        if ($taxableInBracket > 0) {
            $tax += $taxableInBracket * $rate;
            $remainingIncome -= $taxableInBracket;
        }
        
        $previousLimit = $limit;
        
        if ($remainingIncome <= 0) {
            break;
        }
    }
    
    return $tax;
}
