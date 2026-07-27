<?php
/**
 * صفحه ورود به سامانه
 * نسخه Enterprise v4.0
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = 'توکن امنیتی نامعتبر است';
    } elseif (empty($username) || empty($password)) {
        $error = 'نام کاربری و رمز عبور الزامی است';
    } else {
        try {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT * FROM ap_users WHERE username = ? AND is_active = 1",
                [$username]
            );
            
            if ($user && verify_password($password, $user['password_hash'])) {
                // بروزرسانی آخرین ورود
                $db->update('ap_users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
                
                // تنظیم سشن
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role_id'];
                $_SESSION['user_permissions'] = json_decode($user['permissions'] ?? '{}', true);
                
                // لاگ ورود
                $db->insert('ap_login_logs', [
                    'user_id' => $user['id'],
                    'login_time' => date('Y-m-d H:i:s'),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'status' => 'success'
                ]);
                
                set_flash_message('success', 'خوش آمدید، ' . $user['full_name']);
                redirect('/automation-v4.0/index.php');
            } else {
                $error = 'نام کاربری یا رمز عبور اشتباه است';
                
                // لاگ ورود ناموفق
                if ($user) {
                    $db->insert('ap_login_logs', [
                        'user_id' => $user['id'],
                        'login_time' => date('Y-m-d H:i:s'),
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        'status' => 'failed'
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'خطا در ورود به سیستم';
        }
    }
}

$page_title = 'ورود به سامانه';
ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-900 via-primary-800 to-primary-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">سامانه اتوماسیون اداری</h2>
            <p class="mt-2 text-center text-sm text-primary-200">نسخه Enterprise v4.0</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-xl p-8">
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">نام کاربری</label>
                    <input id="username" name="username" type="text" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-right"
                           placeholder="نام کاربری خود را وارد کنید"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">رمز عبور</label>
                    <input id="password" name="password" type="password" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-right"
                           placeholder="رمز عبور خود را وارد کنید">
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="remember_me" class="mr-2 block text-sm text-gray-900">مرا به خاطر بسپار</label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="#" class="font-medium text-primary-600 hover:text-primary-500">رمز عبور را فراموش کرده‌اید؟</a>
                    </div>
                </div>
                
                <div>
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-150">
                        ورود به سامانه
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">اطلاعات ورود پیش‌فرض</span>
                    </div>
                </div>
                
                <div class="mt-4 text-center text-sm text-gray-600">
                    <p><strong>کاربری:</strong> admin</p>
                    <p><strong>رمز عبور:</strong> admin123</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header.php';
?>
