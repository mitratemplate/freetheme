<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'اتوماسیون اداری' ?> - سیستم اتوماسیون اداری ۱۴۰۵</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Vazirmatn Font (Persian Font) -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        
        .sidebar-link:hover {
            background-color: #374151;
        }
        
        .sidebar-link.active {
            background-color: #1f2937;
            border-right: 4px solid #3b82f6;
        }
        
        /* Persian number display */
        .persian-number {
            font-feature-settings: "ss01";
        }
    </style>
    
    <!-- Tailwind Config for RTL and Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0 hidden md:block">
            <div class="p-4">
                <h1 class="text-xl font-bold text-center mb-6">اتوماسیون اداری</h1>
                <p class="text-xs text-center text-gray-400 mb-4">سال مالی ۱۴۰۵</p>
            </div>
            
            <nav class="mt-4">
                <a href="/automation/index.php" class="sidebar-link block px-4 py-3 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        داشبورد
                    </span>
                </a>
                
                <a href="/automation/modules/hr/employees.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'employees') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        کارکنان
                    </span>
                </a>
                
                <a href="/automation/modules/attendance/attendance.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'attendance') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        حضور و غیاب
                    </span>
                </a>
                
                <a href="/automation/modules/payroll/payroll.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'payroll') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        حقوق و دستمزد
                    </span>
                </a>
                
                <a href="/automation/modules/hr/leave_requests.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'leave') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        مرخصی‌ها
                    </span>
                </a>
                
                <a href="/automation/modules/hr/loans.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'loans') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        وام و مساعده
                    </span>
                </a>
                
                <a href="/automation/modules/reports/reports.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'reports') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        گزارشات
                    </span>
                </a>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="/automation/modules/admin/settings.php" class="sidebar-link block px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : '' ?>">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        تنظیمات
                    </span>
                </a>
                <?php endif; ?>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm">
                <div class="px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <div class="flex items-center">
                        <button class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h2 class="text-lg font-semibold text-gray-800 mr-4"><?= $pageTitle ?? 'داشبورد' ?></h2>
                    </div>
                    
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <span class="text-sm text-gray-600">
                            <?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'کاربر' ?>
                        </span>
                        
                        <a href="/automation/modules/auth/logout.php" class="text-red-600 hover:text-red-800 text-sm">
                            خروج
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="/automation/assets/js/app.js"></script>
</body>
</html>
