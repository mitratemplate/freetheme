<?php
/**
 * Login Page
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'نام کاربری و رمز عبور الزامی است.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['employee_id'] = $user['employee_id'];
                
                // Update last login
                $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                redirect('/automation/index.php');
            } else {
                $error = 'نام کاربری یا رمز عبور اشتباه است.';
            }
        } catch (Exception $e) {
            $error = 'خطا در ورود به سیستم. لطفاً مجدداً تلاش کنید.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم - اتوماسیون اداری ۱۴۰۵</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">اتوماسیون اداری</h1>
                <p class="text-gray-600">سال مالی ۱۴۰۵</p>
            </div>
            
            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-r-4 border-red-500 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">نام کاربری</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="نام کاربری خود را وارد کنید"
                        value="<?= htmlspecialchars($username ?? '') ?>"
                    >
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">رمز عبور</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="رمز عبور خود را وارد کنید"
                    >
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="mr-2 text-sm text-gray-600">مرا به خاطر بسپار</span>
                    </label>
                    
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">رمز عبور را فراموش کرده‌اید؟</a>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                >
                    ورود به سیستم
                </button>
            </form>
            
            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-xs text-gray-500">
                    © ۱۴۰۵ - سیستم اتوماسیون اداری
                </p>
            </div>
        </div>
        
        <!-- Demo Credentials -->
        <div class="mt-6 bg-white/10 backdrop-blur-sm rounded-lg p-4 text-white">
            <p class="text-sm font-semibold mb-2">اطلاعات ورود آزمایشی:</p>
            <div class="text-xs space-y-1">
                <p>نام کاربری: <span class="font-mono bg-white/20 px-2 py-1 rounded">admin</span></p>
                <p>رمز عبور: <span class="font-mono bg-white/20 px-2 py-1 rounded">admin123</span></p>
            </div>
        </div>
    </div>
</body>
</html>
