<?php
/**
 * صفحه ورود به سامانه
 */

require_once dirname(__DIR__) . '/index.php';

// اگر کاربر لاگین است، به داشبورد هدایت شود
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی توکن CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی. لطفاً دوباره تلاش کنید.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'نام کاربری و رمز عبور الزامی است.';
        } else {
            try {
                $db = Database::getInstance();
                $user = $db->fetchOne(
                    "SELECT u.*, r.name as role_name, r.slug as role_slug, r.permissions as role_permissions 
                     FROM users u 
                     JOIN roles r ON u.role_id = r.id 
                     WHERE u.username = :username AND u.is_active = 1",
                    ['username' => $username]
                );
                
                if ($user && password_verify($password, $user['password'])) {
                    // ذخیره اطلاعات کاربر در سشن
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role_id' => $user['role_id'],
                        'role_name' => $user['role_name'],
                        'role_slug' => $user['role_slug'],
                        'role_permissions' => $user['role_permissions']
                    ];
                    
                    // بروزرسانی زمان آخرین ورود
                    $db->update('users', 
                        ['last_login' => date('Y-m-d H:i:s')], 
                        'id = :id', 
                        ['id' => $user['id']]
                    );
                    
                    redirect('dashboard.php');
                } else {
                    $error = 'نام کاربری یا رمز عبور اشتباه است.';
                }
            } catch (Exception $e) {
                $error = 'خطا در ورود به سیستم. لطفاً دوباره تلاش کنید.';
                error_log($e->getMessage());
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
    <title>ورود به سامانه - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo APP_NAME; ?></h1>
            <p class="text-gray-600">نسخه <?php echo APP_VERSION; ?></p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-50 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p class="font-medium"><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نام کاربری</label>
                <input type="text" name="username" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       placeholder="نام کاربری خود را وارد کنید">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">رمز عبور</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       placeholder="رمز عبور خود را وارد کنید">
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105">
                ورود به سامانه
            </button>
        </form>
        
        <div class="mt-6 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
            <p>کاربر پیش‌فرض: admin | رمز عبور: admin123</p>
        </div>
    </div>
</body>
</html>
