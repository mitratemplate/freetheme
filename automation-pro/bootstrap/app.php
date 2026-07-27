<?php
/**
 * Application Bootstrap
 * Enterprise HR Automation System v2.0
 */

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Timezone
date_default_timezone_set('Asia/Tehran');

// Define Constants
define('APP_ROOT', dirname(__DIR__));
define('APP_VERSION', '2.0.0-enterprise');
define('APP_DEBUG', getenv('APP_DEBUG') ?: false);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Helper Functions
require_once APP_ROOT . '/app/Helpers/functions.php';

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Load Environment Variables
if (file_exists(APP_ROOT . '/.env')) {
    $envVars = parse_ini_file(APP_ROOT . '/.env');
    foreach ($envVars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

return true;
