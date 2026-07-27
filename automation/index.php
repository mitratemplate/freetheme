<?php
/**
 * Main Dashboard Page
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/helpers.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('modules/auth/login.php');
}

$db = Database::getInstance()->getConnection();
$pageTitle = 'داشبورد';

// Get statistics
$stats = [];

// Total employees
$stmt = $db->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
$stats['total_employees'] = $stmt->fetch()['total'];

// Present today
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(*) as total FROM attendance WHERE date = ? AND absence_type = 'present'");
$stmt->execute([$today]);
$stats['present_today'] = $stmt->fetch()['total'];

// Pending leave requests
$stmt = $db->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'");
$stats['pending_leaves'] = $stmt->fetch()['total'];

// Payroll this month
$current_month = date('m');
$current_year = date('Y');
$stmt = $db->prepare("SELECT SUM(net_salary) as total FROM payroll WHERE month_shamsi = ? AND year_shamsi = ?");
$stmt->execute([jalali_to_gregorian(1405, (int)$current_month, 1)[1], 1405]);
$result = $stmt->fetch();
$stats['payroll_total'] = $result['total'] ?? 0;

ob_start();
?>

<div class="space-y-6">
    <!-- Welcome Message -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">خوش آمدید</h2>
        <p class="text-gray-600">سیستم اتوماسیون اداری - سال مالی ۱۴۰۵</p>
        <p class="text-sm text-gray-500 mt-2">تاریخ امروز: <?= date('Y/m/d') ?></p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Employees -->
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">کل کارکنان فعال</p>
                    <p class="text-3xl font-bold text-gray-800"><?= to_persian_number($stats['total_employees']) ?></p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Present Today -->
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">حاضرین امروز</p>
                    <p class="text-3xl font-bold text-gray-800"><?= to_persian_number($stats['present_today']) ?></p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Pending Leaves -->
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">درخواست‌های مرخصی</p>
                    <p class="text-3xl font-bold text-gray-800"><?= to_persian_number($stats['pending_leaves']) ?></p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Payroll Total -->
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">مجموع حقوق ماه جاری</p>
                    <p class="text-2xl font-bold text-gray-800"><?= format_toman($stats['payroll_total']) ?></p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">دسترسی سریع</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="modules/hr/employees.php?action=add" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span class="text-sm text-gray-700">افزودن کارمند</span>
            </a>
            
            <a href="modules/attendance/attendance.php" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-gray-700">ثبت حضور و غیاب</span>
            </a>
            
            <a href="modules/payroll/payroll.php?action=calculate" class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm text-gray-700">محاسبه حقوق</span>
            </a>
            
            <a href="modules/reports/reports.php" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm text-gray-700">گزارشات</span>
            </a>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">فعالیت‌های اخیر</h3>
        <div class="space-y-3">
            <?php
            // Get recent activities (example query)
            $stmt = $db->query("
                SELECT 'leave' as type, employee_id, created_at, status 
                FROM leave_requests 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $activities = $stmt->fetchAll();
            
            if (empty($activities)) {
                echo '<p class="text-gray-500 text-sm">هیچ فعالیتی ثبت نشده است.</p>';
            } else {
                foreach ($activities as $activity) {
                    $status_colors = [
                        'pending' => 'yellow',
                        'approved' => 'green',
                        'rejected' => 'red'
                    ];
                    $color = $status_colors[$activity['status']] ?? 'gray';
                    
                    echo '<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">';
                    echo '<div class="flex items-center">';
                    echo '<div class="w-2 h-2 bg-' . $color . '-500 rounded-full ml-3"></div>';
                    echo '<span class="text-sm text-gray-700">درخواست مرخصی</span>';
                    echo '</div>';
                    echo '<span class="text-xs text-gray-500">' . $activity['created_at'] . '</span>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/header.php';
