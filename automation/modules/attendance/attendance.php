<?php
/**
 * Attendance Management Page
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_login();

$db = Database::getInstance()->getConnection();
$pageTitle = 'حضور و غیاب';
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $message = 'خطای امنیتی. لطفاً مجدداً تلاش کنید.';
        $messageType = 'error';
    } else {
        try {
            switch ($_POST['action']) {
                case 'check_in':
                    $stmt = $db->prepare("
                        INSERT INTO attendance (employee_id, date, check_in, absence_type)
                        VALUES (?, CURDATE(), CURTIME(), 'present')
                        ON DUPLICATE KEY UPDATE check_in = VALUES(check_in)
                    ");
                    $stmt->execute([(int)$_POST['employee_id']]);
                    $message = 'ورود با موفقیت ثبت شد.';
                    $messageType = 'success';
                    break;
                    
                case 'check_out':
                    $stmt = $db->prepare("
                        UPDATE attendance 
                        SET check_out = CURTIME()
                        WHERE employee_id = ? AND date = CURDATE()
                    ");
                    $stmt->execute([(int)$_POST['employee_id']]);
                    $message = 'خروج با موفقیت ثبت شد.';
                    $messageType = 'success';
                    break;
                    
                case 'add_overtime':
                    $stmt = $db->prepare("
                        UPDATE attendance 
                        SET overtime_hours = overtime_hours + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$_POST['overtime_hours'], (int)$_POST['attendance_id']]);
                    $message = 'اضافه کاری ثبت شد.';
                    $messageType = 'success';
                    break;
            }
        } catch (Exception $e) {
            $message = 'خطا: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

ob_start();
?>

<div class="space-y-6">
    <!-- Message -->
    <?php if ($message): ?>
    <div class="p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-50 border-r-4 border-green-500' : 'bg-red-50 border-r-4 border-red-500' ?>">
        <p class="<?= $messageType === 'success' ? 'text-green-700' : 'text-red-700' ?>"><?= htmlspecialchars($message) ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">مدیریت حضور و غیاب</h2>
        <div class="text-sm text-gray-600">
            تاریخ امروز: <?= to_persian_number(date('Y/m/d')) ?>
        </div>
    </div>
    
    <!-- Quick Check In/Out -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">ثبت ورود و خروج سریع</h3>
        
        <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">کارمند</label>
                <select name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">انتخاب کنید</option>
                    <?php
                    $stmt = $db->query("SELECT * FROM employees WHERE status = 'active' ORDER BY last_name, first_name");
                    $employees = $stmt->fetchAll();
                    foreach ($employees as $emp):
                    ?>
                    <option value="<?= $emp['id'] ?>">
                        <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex items-end space-x-2 space-x-reverse">
                <button type="submit" name="action" value="check_in" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ثبت ورود
                </button>
                <button type="submit" name="action" value="check_out" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                    ثبت خروج
                </button>
            </div>
        </form>
    </div>
    
    <!-- Today's Attendance -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">حضور و غیاب امروز</h3>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کارمند</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ساعت ورود</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ساعت خروج</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اضافه کاری</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">وضعیت</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $today = date('Y-m-d');
                $stmt = $db->prepare("
                    SELECT a.*, e.first_name, e.last_name
                    FROM attendance a
                    JOIN employees e ON a.employee_id = e.id
                    WHERE a.date = ?
                    ORDER BY a.check_in DESC
                ");
                $stmt->execute([$today]);
                $attendances = $stmt->fetchAll();
                
                if (empty($attendances)):
                ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">هیچ رکوردی برای امروز ثبت نشده است.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($attendances as $att): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?= htmlspecialchars($att['first_name'] . ' ' . $att['last_name']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $att['check_in'] ? to_persian_number(date('H:i', strtotime($att['check_in']))) : '-' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $att['check_out'] ? to_persian_number(date('H:i', strtotime($att['check_out']))) : '-' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $att['overtime_hours'] > 0 ? to_persian_number($att['overtime_hours']) . ' ساعت' : '-' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($att['check_in'] && !$att['check_out']): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">حاضر</span>
                        <?php elseif ($att['check_in'] && $att['check_out']): ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">پایان کار</span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">غایب</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($att['check_in'] && !$att['check_out']): ?>
                        <form method="POST" action="" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                            <input type="hidden" name="action" value="add_overtime">
                            <input type="hidden" name="attendance_id" value="<?= $att['id'] ?>">
                            <input type="number" name="overtime_hours" step="0.5" min="0.5" max="8" placeholder="ساعت" 
                                   class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                            <button type="submit" class="text-blue-600 hover:text-blue-900 mr-2">ثبت اضافه کاری</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Attendance Rules -->
    <div class="bg-blue-50 border-r-4 border-blue-500 p-4 rounded-lg">
        <h4 class="font-semibold text-blue-800 mb-2">قوانین حضور و غیاب:</h4>
        <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
            <li>ساعت شروع کار: ۷:۳۰ صبح</li>
            <li>ساعت پایان کار: ۱۶:۰۰ عصر</li>
            <li>حداکثر اضافه کاری مجاز روزانه: ۴ ساعت</li>
            <li>تاخیر بیش از ۱۵ دقیقه کسر کار محسوب می‌شود</li>
        </ul>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header.php';
