<?php
/**
 * Main Entry Point
 * Automation System - Enterprise Edition 1405
 */

session_start();
date_default_timezone_set('Asia/Tehran');

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/includes/',
        __DIR__ . '/modules/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load helpers
require_once __DIR__ . '/includes/helpers.php';

// Simple Router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($basePath !== '/') {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestUri = trim($requestUri, '/');

if (empty($requestUri)) {
    $requestUri = 'index';
}

// Public routes (no authentication required)
$publicRoutes = ['login', 'register', 'api/public'];

// Check authentication
if (!in_array($requestUri, $publicRoutes) && !isLoggedIn()) {
    if ($requestUri === 'api/login') {
        // API login endpoint
    } else {
        header('Location: login');
        exit;
    }
}

// Route mapping
$routes = [
    'index' => 'dashboard.php',
    'login' => 'auth/login.php',
    'logout' => 'auth/logout.php',
    'employees' => 'hr/employees.php',
    'attendance' => 'attendance/attendance.php',
    'leave' => 'hr/leave_requests.php',
    'payroll' => 'payroll/payroll.php',
    'ai-assistant' => 'ai/assistant.php',
];

// Determine which page to load
$page = isset($routes[$requestUri]) ? $routes[$requestUri] : null;

if (!$page) {
    // Try to find in modules
    $parts = explode('/', $requestUri);
    if (count($parts) >= 2) {
        $module = $parts[0];
        $action = $parts[1];
        $moduleFile = __DIR__ . "/modules/{$module}/{$action}.php";
        if (file_exists($moduleFile)) {
            require_once $moduleFile;
            exit;
        }
    }
    
    http_response_code(404);
    echo "صفحه مورد نظر یافت نشد.";
    exit;
}

// Load the page
$pagePath = __DIR__ . '/modules/' . $page;
if (file_exists($pagePath)) {
    require_once $pagePath;
} else {
    http_response_code(404);
    echo "صفحه مورد نظر یافت نشد.";
}
