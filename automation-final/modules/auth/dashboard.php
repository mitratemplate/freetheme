<?php
/**
 * داشبورد اصلی سامانه
 */

require_once dirname(__DIR__) . '/index.php';

// بررسی ورود کاربر
if (!isLoggedIn()) {
    redirect('modules/auth/login.php');
}

$user = getCurrentUser();
$db = Database::getInstance();

// دریافت آمار کلی
$stats = [
    'employees' => $db->fetchOne("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'],
    'attendance_today' => $db->fetchOne("SELECT COUNT(*) as count FROM attendance WHERE date = CURDATE() AND status = 'present'")['count'],
    'pending_leaves' => $db->fetchOne("SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'")['count'],
    'payroll_current' => $db->fetchOne("SELECT COUNT(*) as count FROM payrolls WHERE month = MONTH(CURDATE()) AND year = YEAR(CURDATE())")['count']
];

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <!-- هدر -->
    <header class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></h1>
                    <span class="mr-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">v<?php echo APP_VERSION; ?></span>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-600"><?php echo $user['username']; ?> (<?php echo $user['role_name']; ?>)</span>
                    <a href="logout.php" class="text-red-600 hover:text-red-800 transition">خروج</a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- کارت‌های آمار -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="mr-4">
                        <p class="text-gray-500 text-sm">کارکنان فعال</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo toPersianNumber($stats['employees']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="mr-4">
                        <p class="text-gray-500 text-sm">حاضرین امروز</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo toPersianNumber($stats['attendance_today']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="mr-4">
                        <p class="text-gray-500 text-sm">مرخصی‌های در انتظار</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo toPersianNumber($stats['pending_leaves']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border-r-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="mr-4">
                        <p class="text-gray-500 text-sm">فیش حقوقی ماه جاری</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo toPersianNumber($stats['payroll_current']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- منوی ماژول‌ها -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">ماژول‌های سامانه</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="../modules/hr/employees.php" class="group p-4 text-center rounded-lg hover:bg-blue-50 transition">
                    <div class="w-16 h-16 mx-auto mb-2 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">مدیریت کارکنان</p>
                </a>

                <a href="../modules/attendance/attendance.php" class="group p-4 text-center rounded-lg hover:bg-green-50 transition">
                    <div class="w-16 h-16 mx-auto mb-2 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">حضور و غیاب</p>
                </a>

                <a href="../modules/hr/leave_requests.php" class="group p-4 text-center rounded-lg hover:bg-yellow-50 transition">
                    <div class="w-16 h-16 mx-auto mb-2 bg-yellow-100 rounded-full flex items-center justify-center group-hover:bg-yellow-200 transition">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">مرخصی‌ها</p>
                </a>

                <a href="../modules/payroll/payroll.php" class="group p-4 text-center rounded-lg hover:bg-purple-50 transition">
                    <div class="w-16 h-16 mx-auto mb-2 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">حقوق و دستمزد</p>
                </a>

                <a href="../modules/ai/assistant.php" class="group p-4 text-center rounded-lg hover:bg-indigo-50 transition">
                    <div class="w-16 h-16 mx-auto mb-2 bg-indigo-100 rounded-full flex items-center justify-center group-hover:bg-indigo-200 transition">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">هوش مصنوعی</p>
                </a>

                <a href="../modules/reports/reports.php" class="group p-4 text-center rounded-lg hover:bg-red-50 transition">
                    <div class="w-16 h-16 mx-auto mb-2 bg-red-100 rounded-full flex items-center justify-center group-hover:bg-red-200 transition">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-medium">گزارشات</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
