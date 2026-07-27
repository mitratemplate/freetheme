<?php
/**
 * حضور و غیاب
 */

session_start();
if (!isset($_SESSION['user_id']) || !checkPermission('attendance', 'view')) {
    header('Location: ../../modules/auth/login.php');
    exit;
}

require_once '../../includes/Database.php';
require_once '../../includes/helpers.php';

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// ثبت ورود/خروج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی';
    } else {
        try {
            $employeeId = $_POST['employee_id'];
            $type = $_POST['type']; // checkin یا checkout
            $now = date('Y-m-d H:i:s');
            $today = date('Y-m-d');
            
            if ($type === 'checkin') {
                $stmt = $db->prepare("
                    INSERT INTO ap_attendance (employee_id, date, check_in, status)
                    VALUES (?, ?, ?, 'present')
                    ON DUPLICATE KEY UPDATE check_in = ?
                ");
                $stmt->execute([$employeeId, $today, $now, $now]);
                $message = 'ورود با موفقیت ثبت شد';
            } else {
                $stmt = $db->prepare("UPDATE ap_attendance SET check_out = ? WHERE employee_id = ? AND date = ?");
                $stmt->execute([$now, $employeeId, $today]);
                $message = 'خروج با موفقیت ثبت شد';
            }
            
            logEvent('ATTENDANCE_' . strtoupper($type), $_SESSION['user_id'], "کارمند: $employeeId");
        } catch (Exception $e) {
            $error = 'خطا در ثبت: ' . $e->getMessage();
        }
    }
}

// دریافت لیست حضور امروز
$today = date('Y-m-d');
$attendances = $db->prepare("
    SELECT a.*, e.first_name, e.last_name, e.personnel_code
    FROM ap_attendance a
    JOIN ap_employees e ON a.employee_id = e.id
    WHERE a.date = ?
    ORDER BY a.check_in DESC
");
$attendances->execute([$today]);
$attendances = $attendances->fetchAll();

$pageTitle = 'حضور و غیاب';
include '../../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">حضور و غیاب - <?= toJalali($today) ?></h2>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <!-- فرم ثبت سریع -->
    <div class="bg-blue-50 p-4 rounded-lg mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">ثبت سریع ورود/خروج</h3>
        <form method="POST" class="grid grid-cols-3 gap-4">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <select name="employee_id" required class="border border-gray-300 rounded-md px-3 py-2">
                <option value="">انتخاب کارمند</option>
                <?php
                $employees = $db->query("SELECT id, first_name, last_name, personnel_code FROM ap_employees WHERE is_active = 1")->fetchAll();
                foreach ($employees as $emp):
                ?>
                <option value="<?= $emp['id'] ?>"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?> (<?= $emp['personnel_code'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <select name="type" required class="border border-gray-300 rounded-md px-3 py-2">
                <option value="checkin">ورود</option>
                <option value="checkout">خروج</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">ثبت</button>
        </form>
    </div>

    <!-- جدول حضور امروز -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کد پرسنلی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نام و نام خانوادگی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ساعت ورود</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ساعت خروج</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">وضعیت</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($attendances as $att): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= toPersianNumbers($att['personnel_code']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $att['first_name'] . ' ' . $att['last_name'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $att['check_in'] ? date('H:i', strtotime($att['check_in'])) : '-' ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $att['check_out'] ? date('H:i', strtotime($att['check_out'])) : '-' ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $att['status'] === 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $att['status'] === 'present' ? 'حاضر' : 'غایب' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900">ویرایش</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($attendances)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">هیچ داده‌ای یافت نشد</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
