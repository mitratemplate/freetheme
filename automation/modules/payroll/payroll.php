<?php
/**
 * Payroll Calculation and Management
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_login();

$db = Database::getInstance()->getConnection();
$pageTitle = 'حقوق و دستمزد';
$message = '';
$messageType = '';

// Get active year standards
$active_year = get_active_year($db);
$salary_standards = get_salary_standards($db, $active_year['id']);

// Handle payroll calculation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $message = 'خطای امنیتی. لطفاً مجدداً تلاش کنید.';
        $messageType = 'error';
    } else {
        try {
            if ($_POST['action'] === 'calculate') {
                $employee_id = (int)$_POST['employee_id'];
                $month_shamsi = $_POST['month_shamsi'];
                $year_shamsi = $_POST['year_shamsi'];
                
                // Get employee data
                $stmt = $db->prepare("SELECT * FROM employees WHERE id = ? AND status = 'active'");
                $stmt->execute([$employee_id]);
                $employee = $stmt->fetch();
                
                if ($employee) {
                    // Calculate working days in month (assuming 30 days for simplicity)
                    $working_days = 30;
                    
                    // Get attendance data for the month
                    $gregorian_date = jalaali_to_gregorian((int)$year_shamsi, (int)$month_shamsi, 1);
                    $month_start = date('Y-m-01', mktime(0, 0, 0, $gregorian_date[1], 1, $gregorian_date[0]));
                    
                    $stmt = $db->prepare("
                        SELECT 
                            SUM(CASE WHEN absence_type = 'present' THEN 1 ELSE 0 END) as present_days,
                            SUM(overtime_hours) as total_overtime,
                            SUM(CASE WHEN absence_type = 'sick_leave' THEN 1 ELSE 0 END) as sick_days
                        FROM attendance 
                        WHERE employee_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
                    ");
                    $stmt->execute([$employee_id, date('Y-m', strtotime($month_start))]);
                    $attendance = $stmt->fetch();
                    
                    $present_days = $attendance['present_days'] ?? $working_days;
                    $overtime_hours = $attendance['total_overtime'] ?? 0;
                    
                    // Calculate components
                    $base_salary = $salary_standards['base_salary'] * $present_days;
                    $housing_allowance = $salary_standards['daily_allowance'] * $present_days;
                    $benefit_allowance = $salary_standards['daily_benefit'] * $present_days;
                    $child_allowance = $salary_standards['child_allowance'] * $employee['children_count'];
                    $overtime_pay = $salary_standards['overtime_hourly_rate'] * $overtime_hours;
                    
                    // Gross salary
                    $gross_salary = $base_salary + $housing_allowance + $benefit_allowance + 
                                   $child_allowance + $overtime_pay;
                    
                    // Deductions
                    $insurance = $gross_salary * ($salary_standards['insurance_rate_employee'] / 100);
                    $tax = calculate_tax($gross_salary, $salary_standards['tax_brackets']);
                    
                    // Net salary
                    $total_deductions = $insurance + $tax;
                    $net_salary = $gross_salary - $total_deductions;
                    
                    // Insert or update payroll
                    $stmt = $db->prepare("
                        INSERT INTO payroll (
                            employee_id, year_id, month_shamsi, year_shamsi,
                            base_salary, allowance_housing, allowance_benefit,
                            allowance_children, allowance_overtime,
                            gross_salary, deduction_insurance, deduction_tax,
                            total_deductions, net_salary
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            base_salary = VALUES(base_salary),
                            allowance_housing = VALUES(allowance_housing),
                            allowance_benefit = VALUES(allowance_benefit),
                            allowance_children = VALUES(allowance_children),
                            allowance_overtime = VALUES(allowance_overtime),
                            gross_salary = VALUES(gross_salary),
                            deduction_insurance = VALUES(deduction_insurance),
                            deduction_tax = VALUES(deduction_tax),
                            total_deductions = VALUES(total_deductions),
                            net_salary = VALUES(net_salary)
                    ");
                    
                    $stmt->execute([
                        $employee_id, $active_year['id'], $month_shamsi, $year_shamsi,
                        $base_salary, $housing_allowance, $benefit_allowance,
                        $child_allowance, $overtime_pay,
                        $gross_salary, $insurance, $tax,
                        $total_deductions, $net_salary
                    ]);
                    
                    $message = 'حقوق با موفقیت محاسبه و ثبت شد.';
                    $messageType = 'success';
                } else {
                    $message = 'کارمند یافت نشد.';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            $message = 'خطا در محاسبه حقوق: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$action = $_GET['action'] ?? 'list';
ob_start();
?>

<div class="space-y-6">
    <!-- Message -->
    <?php if ($message): ?>
    <div class="p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-50 border-r-4 border-green-500' : 'bg-red-50 border-r-4 border-red-500' ?>">
        <p class="<?= $messageType === 'success' ? 'text-green-700' : 'text-red-700' ?>"><?= htmlspecialchars($message) ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Salary Standards Info -->
    <div class="bg-blue-50 border-r-4 border-blue-500 p-4 rounded-lg">
        <h3 class="font-semibold text-blue-800 mb-2">استانداردهای حقوق و دستمزد سال <?= to_persian_number($active_year['year_shamsi']) ?></h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">حقوق پایه روزانه:</span>
                <span class="block font-bold text-gray-800"><?= format_toman($salary_standards['base_salary']) ?></span>
            </div>
            <div>
                <span class="text-gray-600">حق مسکن روزانه:</span>
                <span class="block font-bold text-gray-800"><?= format_toman($salary_standards['daily_allowance']) ?></span>
            </div>
            <div>
                <span class="text-gray-600">بن خواربار روزانه:</span>
                <span class="block font-bold text-gray-800"><?= format_toman($salary_standards['daily_benefit']) ?></span>
            </div>
            <div>
                <span class="text-gray-600">حق اولاد:</span>
                <span class="block font-bold text-gray-800"><?= format_toman($salary_standards['child_allowance']) ?></span>
            </div>
            <div>
                <span class="text-gray-600">نرخ اضافه کاری:</span>
                <span class="block font-bold text-gray-800"><?= format_toman($salary_standards['overtime_hourly_rate']) ?></span>
            </div>
            <div>
                <span class="text-gray-600">سهم بیمه کارمند:</span>
                <span class="block font-bold text-gray-800"><?= $salary_standards['insurance_rate_employee'] ?>%</span>
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">
            <?= $action === 'calculate' ? 'محاسبه حقوق' : 'لیست فیش‌های حقوقی' ?>
        </h2>
        
        <?php if ($action === 'list'): ?>
        <a href="?action=calculate" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            محاسبه حقوق جدید
        </a>
        <?php else: ?>
        <a href="payroll.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            بازگشت به لیست
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Calculation Form -->
    <?php if ($action === 'calculate'): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="calculate">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">کارمند *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">انتخاب کنید</option>
                        <?php
                        $stmt = $db->query("SELECT * FROM employees WHERE status = 'active' ORDER BY last_name, first_name");
                        $employees = $stmt->fetchAll();
                        foreach ($employees as $emp):
                        ?>
                        <option value="<?= $emp['id'] ?>">
                            <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?> (<?= $emp['employee_code'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ماه *</label>
                    <select name="month_shamsi" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php
                        $months = [
                            '01' => 'فروردین', '02' => 'اردیبهشت', '03' => 'خرداد',
                            '04' => 'تیر', '05' => 'مرداد', '06' => 'شهریور',
                            '07' => 'مهر', '08' => 'آبان', '09' => 'آذر',
                            '10' => 'دی', '11' => 'بهمن', '12' => 'اسفند'
                        ];
                        foreach ($months as $num => $name):
                        ?>
                        <option value="<?= $num ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">سال *</label>
                    <input type="text" name="year_shamsi" required value="<?= $active_year['year_shamsi'] ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    محاسبه و ثبت حقوق
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Payroll List -->
    <?php if ($action === 'list'): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کارمند</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ماه/سال</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">حقوق ناخالص</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">بیمه</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">مالیات</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">خالص پرداختی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">وضعیت پرداخت</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $db->query("
                    SELECT p.*, e.first_name, e.last_name, e.employee_code
                    FROM payroll p
                    JOIN employees e ON p.employee_id = e.id
                    ORDER BY p.year_shamsi DESC, p.month_shamsi DESC, p.created_at DESC
                    LIMIT 50
                ");
                $payrolls = $stmt->fetchAll();
                
                if (empty($payrolls)):
                ?>
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">هیچ فیش حقوقی یافت نشد.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($payrolls as $payroll): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="font-medium text-gray-900"><?= htmlspecialchars($payroll['first_name'] . ' ' . $payroll['last_name']) ?></div>
                        <div class="text-gray-500 text-xs"><?= to_persian_number($payroll['employee_code']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        ماه <?= to_persian_number($payroll['month_shamsi']) ?> سال <?= to_persian_number($payroll['year_shamsi']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= format_toman($payroll['gross_salary']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600"><?= format_toman($payroll['deduction_insurance']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600"><?= format_toman($payroll['deduction_tax']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600"><?= format_toman($payroll['net_salary']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?= $payroll['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                               ($payroll['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                            <?= $payroll['payment_status'] === 'paid' ? 'پرداخت شده' : 
                               ($payroll['payment_status'] === 'pending' ? 'در انتظار' : 'لغو شده') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="#" class="text-blue-600 hover:text-blue-900 ml-2">مشاهده</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header.php';
