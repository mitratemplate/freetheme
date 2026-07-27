<?php
/**
 * خروج از سامانه
 */

require_once dirname(__DIR__) . '/index.php';

// حذف سشن کاربر
session_unset();
session_destroy();

// هدایت به صفحه ورود
redirect('login.php');
