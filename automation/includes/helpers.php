<?php
/**
 * توابع کمکی
 * Automation Pro v3.0 - Enterprise Edition
 */

// تبدیل اعداد انگلیسی به فارسی
function toPersianNumbers($str) {
    $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace(range(0, 9), $persianNumbers, $str);
}

// فرمت کردن پول
function formatMoney($amount) {
    return number_format($amount) . ' ریال';
}

// محاسبه مالیات پلکانی ۱۴۰۵
function calculateTax($amount) {
    // معافیت سالانه ۱۴۰۵ (فرضی - قابل بروزرسانی)
    $annualExemption = 1200000000; // ۱۲۰ میلیون تومان سالانه
    $monthlyExemption = $annualExemption / 12;
    
    $taxableAmount = max(0, $amount - $monthlyExemption);
    
    if ($taxableAmount <= 0) return 0;
    
    // نرخ‌های پلکانی ۱۴۰۵
    $brackets = [
        ['limit' => 15000000, 'rate' => 0.10],   // تا ۱.۵ میلیون: ۱۰٪
        ['limit' => 40000000, 'rate' => 0.15],   // تا ۴ میلیون: ۱۵٪
        ['limit' => 80000000, 'rate' => 0.20],   // تا ۸ میلیون: ۲۰٪
        ['limit' => 150000000, 'rate' => 0.25],  // تا ۱۵ میلیون: ۲۵٪
        ['limit' => null, 'rate' => 0.30]        // بالاتر: ۳۰٪
    ];
    
    $tax = 0;
    $previousLimit = 0;
    
    foreach ($brackets as $bracket) {
        $limit = $bracket['limit'] ?? PHP_INT_MAX;
        $rate = $bracket['rate'];
        
        if ($taxableAmount > $limit) {
            $tax += ($limit - $previousLimit) * $rate;
            $previousLimit = $limit;
        } else {
            $tax += ($taxableAmount - $previousLimit) * $rate;
            break;
        }
    }
    
    return round($tax);
}

// محاسبه بیمه (۷٪ سهم کارمند)
function calculateInsurance($amount) {
    $maxInsuranceBase = 25000000; // سقف مبنا ۱۴۰۵ (قابل بروزرسانی)
    $base = min($amount, $maxInsuranceBase);
    return round($base * 0.07);
}

// دریافت استانداردهای حقوق ۱۴۰۵ از دیتابیس
function getSalaryStandards() {
    static $standards = null;
    
    if ($standards === null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM ap_salary_standards WHERE year = 1405");
        $standards = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$standards) {
            // مقادیر پیش‌فرض ۱۴۰۵
            $standards = [
                'base_salary' => 12000000,      // حقوق پایه ماهانه
                'housing_allowance' => 3000000, // حق مسکن
                'food_allowance' => 1500000,    // بن خواربار
                'child_allowance' => 800000,    // حق اولاد (هر فرزند)
                'overtime_hourly' => 55000,     // نرخ هر ساعت اضافه کاری
                'night_shift_bonus' => 0.35,    // درصد اضافه کاری شب
                'holiday_bonus' => 0.40,        // درصد اضافه کاری تعطیل
                'min_wage' => 12000000          // حداقل دستمزد
            ];
        }
    }
    
    return $standards;
}

// بررسی دسترسی کاربر
function checkPermission($module, $action = 'view') {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $role = $_SESSION['user_role'] ?? 'employee';
    
    // مدیر کل دسترسی کامل دارد
    if ($role === 'super-admin') {
        return true;
    }
    
    // بررسی دسترسی بر اساس نقش
    $permissions = [
        'hr-manager' => ['hr', 'employees', 'recruitment', 'attendance', 'leave'],
        'finance-manager' => ['payroll', 'loans', 'reports'],
        'department-manager' => ['employees', 'attendance', 'leave'],
        'employee' => ['profile', 'attendance', 'leave']
    ];
    
    if (isset($permissions[$role]) && in_array($module, $permissions[$role])) {
        return true;
    }
    
    return false;
}

// تولید توکن CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// بررسی توکن CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// لاگ‌گیری رویدادها
function logEvent($event, $userId = null, $details = '') {
    $logFile = __DIR__ . '/../logs/events.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = $userId ?? ($_SESSION['user_id'] ?? 'guest');
    
    $logEntry = "[$timestamp] [$ip] [$user] $event: $details\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// ارسال درخواست به GapGPT API
function callGapGPT($prompt, $systemMessage = '') {
    $apiKey = getenv('GAPGPT_API_KEY') ?: 'your-api-key-here';
    $apiUrl = 'https://gapgpt.app/api/v1/chat/completions';
    
    $data = [
        'model' => 'gapgpt-4',
        'messages' => [
            ['role' => 'system', 'content' => $systemMessage ?: 'شما یک دستیار هوشمند برای سیستم اتوماسیون اداری هستید.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 1000
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? null;
    }
    
    return null;
}
