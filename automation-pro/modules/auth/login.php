<?php
/**
 * Login Page
 * Enterprise HR Automation System v2.0
 */

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['_csrf'] ?? '')) {
        $error = 'خطا در اعتبارسنجی فرم. لطفاً دوباره تلاش کنید.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'نام کاربری و رمز عبور الزامی است';
        } else {
            require_once APP_ROOT . '/app/Core/Database.php';
            $db = App\Core\Database::getInstance();
            
            $user = $db->fetch("SELECT * FROM users WHERE username = ? OR email = ?", [$username, $username]);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                if (!$user['is_active']) {
                    $error = 'حساب کاربری شما غیرفعال است. با مدیر سیستم تماس بگیرید.';
                } else {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];
                    
                    // Update last login
                    $db->update('users', ['last_login_at' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
                    
                    // Log activity
                    logActivity('login', 'auth', 'users', $user['id']);
                    
                    redirect('dashboard');
                }
            } else {
                $error = 'نام کاربری یا رمز عبور اشتباه است';
                logActivity('failed_login', 'auth', null, null, null, ['username' => $username]);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سامانه | اتوماسیون اداری</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-4 shadow-lg">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">سامانه جامع اتوماسیون اداری</h1>
            <p class="text-blue-200">نسخه سازمانی ۲.۰ - سال ۱۴۰۵</p>
        </div>
        
        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">ورود به حساب کاربری</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border-r-4 border-red-500 p-4 mb-6 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 text-sm"><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 border-r-4 border-green-500 p-4 mb-6 rounded">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-700 text-sm"><?php echo $success; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <?php echo csrfField(); ?>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        نام کاربری یا ایمیل
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required 
                               autofocus
                               class="pr-10 pl-3 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="نام کاربری خود را وارد کنید">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        رمز عبور
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="pr-10 pl-3 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="رمز عبور خود را وارد کنید">
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="mr-2 text-sm text-gray-600">مرا به خاطر بسپار</span>
                    </label>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700">رمز عبور را فراموش کرده‌اید؟</a>
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    ورود به سامانه
                </button>
            </form>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-center text-sm text-gray-500">
                    اطلاعات ورود پیش‌فرض:<br>
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">admin</span> / 
                    <span class="font-mono bg-gray-100 px-2 py-1 rounded">admin123</span>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8 text-blue-200 text-sm">
            <p>© ۱۴۰۵ - تمامی حقوق محفوظ است</p>
            <p class="mt-2">پشتیبانی فنی: support@example.com</p>
        </div>
    </div>
</body>
</html>
