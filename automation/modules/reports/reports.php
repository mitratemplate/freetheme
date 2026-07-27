<?php
/**
 * Reports and Analytics Page
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_login();

$db = Database::getInstance()->getConnection();
$pageTitle = 'گزارشات و آمار';

// Get report type
$report_type = $_GET['type'] ?? 'summary';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">گزارشات و آمار</h2>
        
        <!-- Export Buttons -->
        <div class="flex space-x-2 space-x-reverse">
            <button onclick="exportToExcel('report-table', 'report-<?= $report_type ?>.csv')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                خروجی اکسل
            </button>
        </div>
    </div>
    
    <!-- Report Type Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6 space-x-reverse">
            <a href="?type=summary" class="<?= $report_type === 'summary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                گزارش خلاصه
            </a>
            <a href="?type=payroll" class="<?= $report_type === 'payroll' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                گزارش حقوق
            </a>
            <a href="?type=attendance" class="<?= $report_type === 'attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                گزارش حضور و غیاب
            </a>
            <a href="?type=leaves" class="<?= $report_type === 'leaves' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                گزارش مرخصی‌ها
            </a>
        </nav>
    </div>
    
    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="type" value="<?= $report_type ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">از تاریخ</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تا تاریخ</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                اعمال فیلتر
            </button>
        </form>
    </div>
    
    <!-- Report Content -->
    <?php if ($report_type === 'summary'): ?>
    <!-- Summary Report -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        // Total Employees
        $stmt = $db->query("SELECT COUNT(*) as total FROM employees WHERE status = 'active'");
        $total_employees = $stmt->fetch()['total'];
        
        // Total Payroll This Month
        $current_month = date('m');
        $stmt = $db->prepare("SELECT SUM(net_salary) as total FROM payroll WHERE month_shamsi = ?");
        $stmt->execute([$current_month]);
        $total_payroll = $stmt->fetch()['total'] ?? 0;
        
        // Attendance Rate Today
        $today = date('Y-m-d');
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN absence_type = 'present' THEN 1 ELSE 0 END) as present
            FROM attendance 
            WHERE date = ?
        ");
        $stmt->execute([$today]);
        $attendance_data = $stmt->fetch();
        $attendance_rate = $attendance_data['total'] > 0 ? 
            round(($attendance_data['present'] / $attendance_data['total']) * 100, 2) : 0;
        
        // Pending Leave Requests
        $stmt = $db->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'pending'");
        $pending_leaves = $stmt->fetch()['total'];
        ?>
        
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-blue-500">
            <div class="text-sm text-gray-600 mb-2">کل کارکنان فعال</div>
            <div class="text-3xl font-bold text-gray-800"><?= to_persian_number($total_employees) ?></div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-green-500">
            <div class="text-sm text-gray-600 mb-2">مجموع حقوق ماه جاری</div>
            <div class="text-2xl font-bold text-gray-800"><?= format_toman($total_payroll) ?></div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-purple-500">
            <div class="text-sm text-gray-600 mb-2">نرخ حضور امروز</div>
            <div class="text-3xl font-bold text-gray-800"><?= to_persian_number($attendance_rate) ?>%</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 border-r-4 border-yellow-500">
            <div class="text-sm text-gray-600 mb-2">درخواست‌های مرخصی در انتظار</div>
            <div class="text-3xl font-bold text-gray-800"><?= to_persian_number($pending_leaves) ?></div>
        </div>
    </div>
    
    <!-- Department Summary -->
    <div class="bg-white rounded-lg shadow mt-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">آمار به تفکیک واحدها</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200" id="report-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">واحد سازمانی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تعداد پرسنل</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">حقوق ماه جاری</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">میانگین حقوق</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $db->query("
                    SELECT 
                        d.name as department_name,
                        COUNT(e.id) as employee_count,
                        COALESCE(SUM(p.net_salary), 0) as total_salary,
                        COALESCE(AVG(p.net_salary), 0) as avg_salary
                    FROM departments d
                    LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'active'
                    LEFT JOIN payroll p ON e.id = p.employee_id AND p.month_shamsi = ?
                    GROUP BY d.id, d.name
                    ORDER BY employee_count DESC
                ");
                $stmt->execute([$current_month]);
                $departments = $stmt->fetchAll();
                
                foreach ($departments as $dept):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($dept['department_name'] ?? 'بدون واحد') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= to_persian_number($dept['employee_count']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= format_toman($dept['total_salary']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= format_toman($dept['avg_salary']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php elseif ($report_type === 'payroll'): ?>
    <!-- Payroll Report -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">گزارش تفصیلی حقوق و دستمزد</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200" id="report-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کارمند</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ماه/سال</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">حقوق پایه</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">مزایا</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کسورات</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">خالص پرداختی</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $db->prepare("
                    SELECT p.*, e.first_name, e.last_name
                    FROM payroll p
                    JOIN employees e ON p.employee_id = e.id
                    WHERE p.created_at BETWEEN ? AND ?
                    ORDER BY p.created_at DESC
                ");
                $stmt->execute([$start_date, $end_date]);
                $payrolls = $stmt->fetchAll();
                
                foreach ($payrolls as $p):
                    $benefits = $p['allowance_housing'] + $p['allowance_benefit'] + 
                               $p['allowance_children'] + $p['allowance_overtime'];
                    $deductions = $p['deduction_insurance'] + $p['deduction_tax'];
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ماه <?= to_persian_number($p['month_shamsi']) ?> سال <?= to_persian_number($p['year_shamsi']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= format_toman($p['base_salary']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                        <?= format_toman($benefits) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                        <?= format_toman($deductions) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                        <?= format_toman($p['net_salary']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php else: ?>
    <div class="bg-yellow-50 border-r-4 border-yellow-500 p-4 rounded-lg">
        <p class="text-yellow-700">این نوع گزارش در حال توسعه است.</p>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header.php';
