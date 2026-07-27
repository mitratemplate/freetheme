<?php
/**
 * تنظیمات اتصال به پایگاه داده
 * این فایل را با اطلاعات سرور خود ویرایش کنید
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'automation_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// گزینه‌های PDO
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
