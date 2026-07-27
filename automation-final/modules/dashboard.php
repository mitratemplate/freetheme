<?php
/**
 * Dashboard - Main Page
 */
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (!isLoggedIn()) {
    redirect('login');
}

$user = getCurrentUser();
$db = Database::getInstance();

// Get statistics
$stats = [
    'employees' => $db->fetch("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'],
    'present_today' => $db->fetch("SELECT COUNT(*) as count FROM attendance_records WHERE date = CURDATE() AND status = 'present'")['count'],
    'pending_leaves' => $db->fetch("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'")['count'],
    'payroll_pending' => $db->fetch("SELECT COUNT(*) as count FROM payroll_records WHERE payment_status = 'pending' AND month = MONTH(CURDATE())")['count']
];

// Recent activities
$recentActivities = $db->fetchAll("
    SELECT action, table_name, created_at 
    FROM audit_log 
    ORDER BY created_at DESC 
    LIMIT 10
");

// Pending tasks
$pendingTasks = [
    ['title' => 'بررسی درخواست‌های مرخصی', 'count' => $stats['pending_leaves'], 'icon' => 'calendar', 'color' => 'yellow'],
    ['title' => 'محاسبه حقوق ماه جاری', 'count' => $stats['payroll_pending'], 'icon' => 'currency', 'color' => 'green'],
    ['title' => 'کارکنان حاضر امروز', 'count' => $stats['present_today'], 'icon' => 'users', 'color' => 'blue'],
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد | اتوماسیون اداری</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">اتوماسیون اداری</h1>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-sm text-gray-600"><?= htmlspecialchars($user['full_name']) ?> (<?= htmlspecialchars($user['role_name']) ?>)</span>
                    <a href="logout" class="text-red-600 hover:text-red-700 text-sm">خروج</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 space-x-reverse py-4 overflow-x-auto">
                <a href="index" class="text-blue-600 font-medium whitespace-nowrap">داشبورد</a>
                <a href="employees" class="text-gray-600 hover:text-gray-900 whitespace-nowrap">کارکنان</a>
                <a href="attendance" class="text-gray-600 hover:text-gray-900 whitespace-nowrap">حضور و غیاب</a>
                <a href="leave" class="text-gray-600 hover:text-gray-900 whitespace-nowrap">مرخصی‌ها</a>
                <a href="payroll" class="text-gray-600 hover:text-gray-900 whitespace-nowrap">حقوق و دستمزد</a>
                <a href="ai-assistant" class="text-purple-600 hover:text-purple-700 whitespace-nowrap">دستیار هوشمند</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 mb-8 text-white">
            <h2 class="text-2xl font-bold mb-2">خوش آمدید، <?= htmlspecialchars($user['first_name'] ?? $user['full_name']) ?>!</h2>
            <p class="opacity-90">تاریخ امروز: <?= jalaliDate() ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">کل کارکنان</p>
                        <p class="text-3xl font-bold text-gray-800"><?= toPersianNumber($stats['employees']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">حاضرین امروز</p>
                        <p class="text-3xl font-bold text-green-600"><?= toPersianNumber($stats['present_today']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">درخواست‌های مرخصی</p>
                        <p class="text-3xl font-bold text-yellow-600"><?= toPersianNumber($stats['pending_leaves']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">حقوق در انتظار پرداخت</p>
                        <p class="text-3xl font-bold text-red-600"><?= toPersianNumber($stats['payroll_pending']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">دسترسی سریع</h3>
                <div class="space-y-3">
                    <a href="employees?action=add" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">افزودن کارمند جدید</span>
                    </a>
                    <a href="attendance?action=today" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center ml-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">ثبت حضور امروز</span>
                    </a>
                    <a href="payroll?action=calculate" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center ml-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">محاسبه حقوق</span>
                    </a>
                    <a href="ai-assistant" class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center ml-3">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">دستیار هوشمند AI</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-4">فعالیت‌های اخیر</h3>
                <div class="space-y-3">
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="flex items-start p-3 rounded-lg bg-gray-50">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 ml-3"></div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-700"><?= htmlspecialchars($activity['action']) ?> در جدول <?= htmlspecialchars($activity['table_name'] ?? '-') ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= jalaliDate($activity['created_at']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($recentActivities)): ?>
                        <p class="text-gray-500 text-sm">هیچ فعالیتی ثبت نشده است.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
            <p>سامانه جامع اتوماسیون اداری و مدیریت منابع انسانی - نسخه Enterprise v3.0</p>
            <p class="mt-1">مطابق با استانداردهای وزارت کار ایران سال ۱۴۰۵</p>
        </div>
    </footer>
</body>
</html>
