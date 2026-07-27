<?php
/**
 * صفحه ورود به سیستم
 * Automation Pro v3.0 - Enterprise Edition
 */

session_start();

// اگر کاربر قبلاً وارد شده، به داشبورد هدایت شود
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// پردازش فرم ورود
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCsrfToken($csrf_token)) {
        $error = 'خطای امنیتی: توکن CSRF نامعتبر است';
    } elseif (empty($username) || empty($password)) {
        $error = 'نام کاربری و رمز عبور الزامی است';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT id, username, password, full_name, role_id, is_active FROM ap_users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if (!$user['is_active']) {
                    $error = 'حساب کاربری شما غیرفعال است';
                } else {
                    // ثبت اطلاعات جلسه
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role_id'];
                    
                    // لاگ ورود
                    logEvent('LOGIN_SUCCESS', $user['id'], 'ورود موفق');
                    
                    header('Location: ../index.php');
                    exit;
                }
            } else {
                $error = 'نام کاربری یا رمز عبور اشتباه است';
                logEvent('LOGIN_FAILED', null, "تلاش برای ورود با نام کاربری: $username");
            }
        } catch (Exception $e) {
            $error = 'خطا در ارتباط با پایگاه داده';
            logEvent('LOGIN_ERROR', null, $e->getMessage());
        }
    }
}

$pageTitle = 'ورود به سیستم';
include '../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-900 to-blue-700 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-2xl">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900">
                سامانه اتوماسیون اداری
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                نسخه Enterprise v3.0 - سال ۱۴۰۵
            </p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            <?= $success ?>
        </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-6" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">
                    نام کاربری
                </label>
                <input id="username" name="username" type="text" required 
                       class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="نام کاربری خود را وارد کنید">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    رمز عبور
                </label>
                <input id="password" name="password" type="password" required 
                       class="mt-1 appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="رمز عبور خود را وارد کنید">
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="mr-2 block text-sm text-gray-900">
                        مرا به خاطر بسپار
                    </label>
                </div>
                
                <div class="text-sm">
                    <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                        رمز عبور را فراموش کرده‌اید؟
                    </a>
                </div>
            </div>
            
            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    ورود به سیستم
                </button>
            </div>
            
            <div class="text-center text-xs text-gray-500 mt-4">
                <p>کاربر آزمایشی:</p>
                <p>نام کاربری: <strong>admin</strong></p>
                <p>رمز عبور: <strong>admin123</strong></p>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
