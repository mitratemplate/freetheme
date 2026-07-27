<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'اتوماسیون اداری حرفه‌ای' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Vazirmatn', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1e40af',
                        secondary: '#059669',
                        accent: '#f59e0b',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .persian-num { font-feature-settings: "ss01"; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- نوار ناوبری -->
    <nav class="bg-primary shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="/automation/index.php" class="text-white font-bold text-xl">اتوماسیون اداری</a>
                    <div class="hidden md:flex space-x-4 space-x-reverse">
                        <a href="/automation/modules/hr/employees.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded">کارکنان</a>
                        <a href="/automation/modules/attendance/attendance.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded">حضور و غیاب</a>
                        <a href="/automation/modules/payroll/payroll.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded">حقوق و دستمزد</a>
                        <a href="/automation/modules/ai/assistant.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded">هوش مصنوعی</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-white"><?= $_SESSION['user_name'] ?? 'کاربر' ?> (<?= $_SESSION['user_role'] ?? 'کارمند' ?>)</span>
                    <a href="/automation/modules/auth/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">خروج</a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- محتوای اصلی -->
    <main class="container mx-auto px-4 py-8">
    <?php endif; ?>
