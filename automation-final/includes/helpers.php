<?php
/**
 * Helper Functions
 * Common utilities for the automation system
 */

function toPersianNumber($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace(range(0, 9), $persianDigits, $number);
}

function fromPersianNumber($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($persianDigits, range(0, 9), $number);
}

function formatCurrency($amount) {
    return number_format($amount) . ' ریال';
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    return $db->fetch("SELECT u.*, r.name as role_name, r.slug as role_slug 
                       FROM users u 
                       JOIN roles r ON u.role_id = r.id 
                       WHERE u.id = ?", [$_SESSION['user_id']]);
}

function hasPermission($module, $action = '*') {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    if ($user['role_slug'] === 'super-admin') {
        return true;
    }
    
    $permissions = json_decode($user['permissions'] ?? '{}', true);
    
    if (isset($permissions[$module]) && $permissions[$module] === '*') {
        return true;
    }
    
    if (isset($permissions[$module]) && is_array($permissions[$module])) {
        return in_array($action, $permissions[$module]) || $permissions[$module] === '*';
    }
    
    return false;
}

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getSalaryStandards($year = 1405) {
    $db = Database::getInstance();
    $standards = $db->fetch("SELECT * FROM salary_standards WHERE year = ?", [$year]);
    
    if (!$standards) {
        // Default values for 1405
        return [
            'year' => 1405,
            'base_salary' => 85000000,
            'housing_allowance' => 18000000,
            'food_allowance' => 12000000,
            'child_allowance' => 6000000,
            'insurance_rate' => 0.23,
            'employee_insurance_rate' => 0.07,
            'tax_brackets' => json_encode([
                ['min' => 0, 'max' => 60000000, 'rate' => 0],
                ['min' => 60000000, 'max' => 120000000, 'rate' => 0.10],
                ['min' => 120000000, 'max' => 200000000, 'rate' => 0.15],
                ['min' => 200000000, 'max' => 300000000, 'rate' => 0.20],
                ['min' => 300000000, 'max' => null, 'rate' => 0.30]
            ]),
            'overtime_multiplier' => 1.4,
            'night_shift_multiplier' => 1.35,
            'holiday_multiplier' => 2.0
        ];
    }
    
    return $standards;
}

function calculateTax($income, $year = 1405) {
    $standards = getSalaryStandards($year);
    $brackets = json_decode($standards['tax_brackets'], true);
    $tax = 0;
    $remaining = $income;
    $previousMax = 0;
    
    foreach ($brackets as $bracket) {
        if ($remaining <= 0) break;
        
        $min = $bracket['min'];
        $max = $bracket['max'];
        $rate = $bracket['rate'];
        
        if ($max === null) {
            $taxableAmount = $remaining;
        } else {
            $taxableAmount = min($remaining, $max - $previousMax);
        }
        
        if ($income > $min) {
            $tax += $taxableAmount * $rate;
            $remaining -= $taxableAmount;
        }
        
        $previousMax = $max ?? $income;
    }
    
    return $tax;
}

function jalaliDate($date = null) {
    if ($date === null) {
        $date = time();
    }
    
    if (is_string($date)) {
        $date = strtotime($date);
    }
    
    $day = date('j', $date);
    $month = date('n', $date);
    $year = date('Y', $date);
    
    // Simple conversion (for production use a proper library like jdf)
    $jYear = $year - 621;
    $jMonth = $month + 3;
    $jDay = $day;
    
    if ($jMonth > 12) {
        $jMonth -= 12;
        $jYear++;
    }
    
    $months = [
        '', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    ];
    
    return "{$jDay} {$months[$jMonth]} {$jYear}";
}

function aiRequest($prompt, $context = []) {
    $apiKey = getenv('GAPGPT_API_KEY') ?: 'your-api-key-here';
    $apiUrl = 'https://gapgpt.app/api/v1/chat/completions';
    
    $systemContext = "شما دستیار هوشمند یک سیستم اتوماسیون اداری و مدیریت منابع انسانی هستید. " .
                     "پاسخ‌ها باید به زبان فارسی، حرفه‌ای و مفید باشند. " .
                     "اطلاعات مربوط به قوانین کار ایران و استانداردهای سال ۱۴۰۵ را در نظر بگیرید.";
    
    $messages = [
        ['role' => 'system', 'content' => $systemContext],
        ['role' => 'user', 'content' => $prompt]
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'gapgpt-4',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1000
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? 'خطا در دریافت پاسخ از هوش مصنوعی';
    }
    
    return 'خطا در ارتباط با سرویس هوش مصنوعی';
}
