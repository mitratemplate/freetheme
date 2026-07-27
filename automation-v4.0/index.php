<?php
/**
 * داشبورد اصلی سامانه
 * نسخه Enterprise v4.0
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: modules/auth/login.php');
    exit;
}

require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/helpers.php';

$db = Database::getInstance();

// آمار کلی
$stats = [
    'total_employees' => $db->fetch("SELECT COUNT(*) as count FROM ap_employees WHERE is_active = 1")['count'] ?? 0,
    'present_today' => $db->fetch("SELECT COUNT(*) as count FROM ap_attendance WHERE DATE(checked_at) = CURDATE() AND status = 'present'")['count'] ?? 0,
    'on_leave' => $db->fetch("SELECT COUNT(*) as count FROM ap_leave_requests WHERE status = 'approved' AND ? BETWEEN start_date AND end_date", [date('Y-m-d')])['count'] ?? 0,
    'pending_leaves' => $db->fetch("SELECT COUNT(*) as count FROM ap_leave_requests WHERE status = 'pending'")['count'] ?? 0,
    'new_candidates' => $db->fetch("SELECT COUNT(*) as count FROM ap_job_applications WHERE status = 'new'")['count'] ?? 0,
    'pending_evaluations' => $db->fetch("SELECT COUNT(*) as count FROM ap_performance_reviews WHERE status = 'pending'")['count'] ?? 0,
];

// آخرین رویدادها
$recent_activities = $db->fetchAll("
    SELECT 
        al.action,
        al.table_name,
        al.record_id,
        al.description,
        al.created_at,
        u.full_name as user_name
    FROM ap_activity_logs al
    LEFT JOIN ap_users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");

// کارکنان جدید
$new_employees = $db->fetchAll("
    SELECT e.*, d.name as department_name
    FROM ap_employees e
    LEFT JOIN ap_departments d ON e.department_id = d.id
    WHERE e.is_active = 1
    ORDER BY e.hire_date DESC
    LIMIT 5
");

// درخواست‌های مرخصی در انتظار
$pending_leaves = $db->fetchAll("
    SELECT lr.*, e.full_name as employee_name, lt.name as leave_type
    FROM ap_leave_requests lr
    JOIN ap_employees e ON lr.employee_id = e.id
    JOIN ap_leave_types lt ON lr.leave_type_id = lt.id
    WHERE lr.status = 'pending'
    ORDER BY lr.created_at DESC
    LIMIT 5
");

$page_title = 'داشبورد';
ob_start();
?>

<div class="space-y-6">
    <!-- کارت‌های آمار -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border-r-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">کل کارکنان</p>
                    <p class="text-2xl font-bold text-gray-800"><?= to_persian_digits($stats['total_employees']) ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-r-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">حاضرین امروز</p>
                    <p class="text-2xl font-bold text-gray-800"><?= to_persian_digits($stats['present_today']) ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-r-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">در مرخصی</p>
                    <p class="text-2xl font-bold text-gray-800"><?= to_persian_digits($stats['on_leave']) ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-r-4 border-red-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">مرخصی در انتظار</p>
                    <p class="text-2xl font-bold text-gray-800"><?= to_persian_digits($stats['pending_leaves']) ?></p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-r-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">متقاضیان جدید</p>
                    <p class="text-2xl font-bold text-gray-800"><?= to_persian_digits($stats['new_candidates']) ?></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border-r-4 border-indigo-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm text-gray-500">ارزیابی در انتظار</p>
                    <p class="text-2xl font-bold text-gray-800"><?= to_persian_digits($stats['pending_evaluations']) ?></p>
                </div>
                <div class="bg-indigo-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- بخش‌های اصلی -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- درخواست‌های مرخصی در انتظار -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">درخواست‌های مرخصی در انتظار</h3>
                <a href="/automation-v4.0/modules/leave/leave_requests.php" class="text-sm text-primary-600 hover:text-primary-800">مشاهده همه</a>
            </div>
            <div class="p-4">
                <?php if (empty($pending_leaves)): ?>
                    <p class="text-gray-500 text-center py-4">هیچ درخواستی در انتظار نیست</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($pending_leaves as $leave): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($leave['employee_name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($leave['leave_type']) ?> - از <?= display_date_shamsi($leave['start_date']) ?> تا <?= display_date_shamsi($leave['end_date']) ?></p>
                                </div>
                                <div class="flex space-x-2 space-x-reverse">
                                    <button class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">تایید</button>
                                    <button class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">رد</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- کارکنان جدید -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">کارکنان جدید</h3>
                <a href="/automation-v4.0/modules/hr/employees.php" class="text-sm text-primary-600 hover:text-primary-800">مشاهده همه</a>
            </div>
            <div class="p-4">
                <?php if (empty($new_employees)): ?>
                    <p class="text-gray-500 text-center py-4">هیچ کارمندی یافت نشد</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($new_employees as $emp): ?>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center ml-3">
                                    <span class="text-primary-600 font-bold"><?= mb_substr($emp['full_name'], 0, 1) ?></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($emp['full_name']) ?></p>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($emp['department_name'] ?? '-') ?> - استخدام: <?= display_date_shamsi($emp['hire_date']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- آخرین فعالیت‌ها -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">آخرین فعالیت‌های سیستم</h3>
        </div>
        <div class="p-4">
            <?php if (empty($recent_activities)): ?>
                <p class="text-gray-500 text-center py-4">هیچ فعالیتی ثبت نشده است</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">کاربر</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">توضیحات</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-800"><?= htmlspecialchars($activity['user_name'] ?? 'سیستم') ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"><?= htmlspecialchars($activity['action']) ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($activity['description']) ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?= display_date_shamsi($activity['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/includes/header.php';
?>
