<?php
/**
 * سامانه جامع اتوماسیون اداری و منابع انسانی
 * نسخه Enterprise v5.0
 * 
 * @package AutomationPro
 * @version 5.0.0
 */

// تعریف ثابت‌های اصلی
define('APP_NAME', 'سامانه اتوماسیون اداری');
define('APP_VERSION', '5.0.0');
define('APP_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

// گزارش خطاها (در محیط تولید غیرفعال شود)
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Tehran');

// بارگذاری فایل پیکربندی
require_once APP_ROOT . '/config/database.php';

// شروع سشن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// توابع کمکی
function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return $_SESSION['user'];
}

function hasPermission($module, $action = '*') {
    if (!isLoggedIn()) return false;
    $user = $_SESSION['user'];
    if ($user['role_slug'] === 'super-admin') return true;
    
    $permissions = json_decode($user['role_permissions'], true);
    if (!$permissions) return false;
    
    if (isset($permissions[$module]) && $permissions[$module] === '*') return true;
    if (isset($permissions[$module]) && $permissions[$module] === $action) return true;
    if (isset($permissions['*'])) return true;
    
    return false;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
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

function toPersianNumber($number) {
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace(range(0, 9), $persianDigits, $number);
}

function formatCurrency($amount) {
    return number_format($amount) . ' ریال';
}

// بارگذاری کلاس دیتابیس
require_once APP_ROOT . '/includes/Database.php';
