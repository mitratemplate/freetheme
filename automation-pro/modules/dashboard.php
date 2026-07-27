<?php
/**
 * Dashboard - Main Page
 * Enterprise HR Automation System v2.0
 */

require_once APP_ROOT . '/app/Core/Database.php';
$db = App\Core\Database::getInstance();

$user = currentUser();
$stats = [];

// Get statistics based on user role
if (hasPermission('hr') || hasPermission('employees')) {
    $stats['total_employees'] = $db->fetch("SELECT COUNT(*) as count FROM employees")['count'] ?? 0;
    $stats['new_hires_this_month'] = $db->fetch("SELECT COUNT(*) as count FROM employees WHERE MONTH(hire_date) = MONTH(CURRENT_DATE())")['count'] ?? 0;
    $stats['on_leave_today'] = $db->fetch("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'approved' AND CURDATE() BETWEEN start_date AND end_date")['count'] ?? 0;
}

if (hasPermission('payroll') || hasPermission('finance')) {
    $stats['pending_payroll'] = $db->fetch("SELECT COUNT(*) as count FROM payroll_periods WHERE status = 'pending'")['count'] ?? 0;
    $stats['pending_loans'] = $db->fetch("SELECT COUNT(*) as count FROM loans WHERE status = 'pending'")['count'] ?? 0;
}

if (hasPermission('attendance')) {
    $stats['absent_today'] = $db->fetch("SELECT COUNT(*) as count FROM attendance WHERE date = CURDATE() AND status = 'absent'")['count'] ?? 0;
    $stats['late_today'] = $db->fetch("SELECT COUNT(*) as count FROM attendance WHERE date = CURDATE() AND status = 'late'")['count'] ?? 0;
}

if (hasPermission('recruitment')) {
    $stats['open_positions'] = $db->fetch("SELECT COUNT(*) as count FROM job_postings WHERE status = 'active'")['count'] ?? 0;
    $stats['pending_applications'] = $db->fetch("SELECT COUNT(*) as count FROM job_applications WHERE status = 'pending'")['count'] ?? 0;
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد | سامانه اتوماسیون اداری</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-blue-600 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="font-bold text-xl text-gray-800">سامانه اتوماسیون اداری</span>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-600"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></span>
                    <a href="logout" class="text-red-600 hover:text-red-700">خروج</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Message -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">خوش آمدید، <?php echo $user['first_name']; ?> عزیز</h1>
            <p class="text-gray-600">تاریخ امروز: <?php echo jalaliDate(); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php if (isset($stats['total_employees'])): ?>
            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg ml-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">کل کارکنان</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo formatNumber($stats['total_employees']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($stats['on_leave_today'])): ?>
            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg ml-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">در مرخصی امروز</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo formatNumber($stats['on_leave_today']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($stats['pending_payroll'])): ?>
            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg ml-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">حقوق در انتظار پرداخت</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo formatNumber($stats['pending_payroll']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($stats['open_positions'])): ?>
            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg ml-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">موقعیت‌های شغلی باز</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo formatNumber($stats['open_positions']); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">دسترسی سریع</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php if (hasPermission('employees')): ?>
                <a href="employees" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-blue-50 transition">
                    <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-sm text-gray-700">کارکنان</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('attendance')): ?>
                <a href="attendance" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-green-50 transition">
                    <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-gray-700">حضور و غیاب</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('payroll')): ?>
                <a href="payroll" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-yellow-50 transition">
                    <svg class="w-8 h-8 text-yellow-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-gray-700">حقوق و دستمزد</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('ai')): ?>
                <a href="ai-assistant" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-purple-50 transition">
                    <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span class="text-sm text-gray-700">هوش مصنوعی</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('plugins')): ?>
                <a href="plugins" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-indigo-50 transition">
                    <svg class="w-8 h-8 text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    </svg>
                    <span class="text-sm text-gray-700">پلاگین‌ها</span>
                </a>
                <?php endif; ?>

                <a href="#" class="flex flex-col items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm text-gray-700">گزارشات</span>
                </a>
            </div>
        </div>

        <!-- AI Assistant Banner -->
        <?php if (hasPermission('ai')): ?>
        <div class="bg-gradient-to-l from-purple-600 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold mb-2">دستیار هوشمند منابع انسانی</h2>
                    <p class="text-purple-100">با استفاده از هوش مصنوعی GapGPT، تحلیل‌های پیشرفته‌ای از داده‌های سازمان خود دریافت کنید</p>
                </div>
                <a href="ai-assistant" class="bg-white text-purple-600 px-6 py-3 rounded-lg font-bold hover:bg-purple-50 transition">
                    شروع گفتگو
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
