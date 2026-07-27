<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'سامانه اتوماسیون اداری' ?></title>
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
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .sidebar-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar-link.active { background-color: rgba(255,255,255,0.2); border-right: 3px solid #fff; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-primary-900 text-white flex-shrink-0 overflow-y-auto">
            <div class="p-4 border-b border-primary-800">
                <h1 class="text-xl font-bold">اتوماسیون اداری</h1>
                <p class="text-xs text-primary-300 mt-1">نسخه Enterprise v4.0</p>
            </div>
            
            <nav class="mt-4">
                <a href="/automation-v4.0/index.php" class="sidebar-link flex items-center px-4 py-3 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    داشبورد
                </a>
                
                <div class="px-4 py-2 mt-4 text-xs text-primary-300 uppercase">منابع انسانی</div>
                
                <a href="/automation-v4.0/modules/hr/employees.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'employees') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    کارکنان
                </a>
                
                <a href="/automation-v4.0/modules/hr/recruitment.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'recruitment') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    استخدام
                </a>
                
                <a href="/automation-v4.0/modules/hr/performance.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'performance') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    ارزیابی عملکرد
                </a>
                
                <div class="px-4 py-2 mt-4 text-xs text-primary-300 uppercase">حقوق و دستمزد</div>
                
                <a href="/automation-v4.0/modules/payroll/payroll.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'payroll') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    فیش حقوقی
                </a>
                
                <div class="px-4 py-2 mt-4 text-xs text-primary-300 uppercase">حضور و غیاب</div>
                
                <a href="/automation-v4.0/modules/attendance/attendance.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'attendance') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    حضور و غیاب
                </a>
                
                <a href="/automation-v4.0/modules/leave/leave_requests.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'leave') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    مرخصی‌ها
                </a>
                
                <div class="px-4 py-2 mt-4 text-xs text-primary-300 uppercase">هوش مصنوعی</div>
                
                <a href="/automation-v4.0/modules/ai/assistant.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'assistant') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                    دستیار هوشمند
                </a>
                
                <div class="px-4 py-2 mt-4 text-xs text-primary-300 uppercase">سیستم</div>
                
                <a href="/automation-v4.0/modules/plugins/plugins.php" class="sidebar-link flex items-center px-4 py-3 <?= strpos($_SERVER['PHP_SELF'], 'plugins') ? 'active' : '' ?>">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                    پلاگین‌ها
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800"><?= $page_title ?? 'داشبورد' ?></h2>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <span class="text-sm text-gray-600"><?= $_SESSION['user_name'] ?? 'کاربر' ?></span>
                        <a href="/automation-v4.0/modules/auth/logout.php" class="text-sm text-red-600 hover:text-red-800">خروج</a>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <?php if ($flash = get_flash_message()): ?>
                    <?php foreach ($flash as $type => $message): ?>
                        <div class="mb-4 p-4 rounded-lg <?= $type === 'success' ? 'bg-green-100 text-green-800' : ($type === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') ?>">
                            <?= $message ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>
    <?php else: ?>
        <?= $content ?? '' ?>
    <?php endif; ?>
    
    <script src="/automation-v4.0/assets/js/app.js"></script>
</body>
</html>
