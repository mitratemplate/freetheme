<?php
/**
 * Main Entry Point
 * Enterprise HR Automation System v2.0
 */

require_once __DIR__ . '/bootstrap/app.php';

// Simple Router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$requestUri = str_replace($basePath, '', $requestUri);
$requestUri = trim($requestUri, '/');

if (empty($requestUri)) {
    $requestUri = 'dashboard';
}

// Route mapping
$routes = [
    '' => 'modules/auth/login.php',
    'login' => 'modules/auth/login.php',
    'logout' => 'modules/auth/logout.php',
    'dashboard' => 'modules/dashboard.php',
    'employees' => 'modules/hr/employees.php',
    'attendance' => 'modules/attendance/attendance.php',
    'payroll' => 'modules/payroll/payroll.php',
    'ai-assistant' => 'modules/ai/assistant.php',
    'plugins' => 'modules/plugins/manager.php'
];

// Check authentication for protected routes
$publicRoutes = ['login', ''];
if (!in_array($requestUri, $publicRoutes) && !isAuthenticated()) {
    redirect('login');
}

// Load view
if (isset($routes[$requestUri])) {
    $viewFile = __DIR__ . '/' . $routes[$requestUri];
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        http_response_code(404);
        echo "صفحه مورد نظر یافت نشد";
    }
} else {
    // Try to load module dynamically
    $parts = explode('/', $requestUri);
    $moduleDir = __DIR__ . '/modules/' . $parts[0];
    
    if (is_dir($moduleDir) && file_exists($moduleDir . '/index.php')) {
        require $moduleDir . '/index.php';
    } else {
        http_response_code(404);
        echo "صفحه مورد نظر یافت نشد";
    }
}
