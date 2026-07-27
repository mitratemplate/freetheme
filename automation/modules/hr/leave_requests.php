<?php
/**
 * Leave Requests Management Page
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_login();

$db = Database::getInstance()->getConnection();
$pageTitle = 'مدیریت مرخصی‌ها';
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
                case 'request':
                    $stmt = $db->prepare("
                        INSERT INTO leave_requests (
                            employee_id, leave_type, start_date, end_date, 
                            days_count, reason, status
                        ) VALUES (?, ?, ?, ?, ?, ?, 'pending')
                    ");
                    
                    // Calculate days count
                    $start = new DateTime($_POST['start_date']);
                    $end = new DateTime($_POST['end_date']);
                    $days = $start->diff($end)->days + 1;
                    
                    $stmt->execute([
                        (int)$_POST['employee_id'],
                        $_POST['leave_type'],
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $days,
                        sanitize_input($_POST['reason'])
                    ]);
                    
                    $message = 'درخواست مرخصی با موفقیت ثبت شد.';
                    $messageType = 'success';
                    break;
                    
                case 'approve':
                    $stmt = $db->prepare("
                        UPDATE leave_requests 
                        SET status = 'approved', approved_by = ?, approved_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], (int)$_POST['id']]);
                    $message = 'درخواست مرخصی تایید شد.';
                    $messageType = 'success';
                    break;
                    
                case 'reject':
                    $stmt = $db->prepare("
                        UPDATE leave_requests 
                        SET status = 'rejected', approved_by = ?, approved_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id'], (int)$_POST['id']]);
                    $message = 'درخواست مرخصی رد شد.';
                    $messageType = 'success';
                    break;
            }
        } catch (Exception $e) {
            $message = 'خطا: ' . $e->getMessage();
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
    
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800">
            <?= $action === 'request' ? 'درخواست مرخصی جدید' : 'لیست درخواست‌های مرخصی' ?>
        </h2>
        
        <?php if ($action === 'list'): ?>
        <a href="?action=request" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            درخواست مرخصی
        </a>
        <?php else: ?>
        <a href="leave_requests.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            بازگشت به لیست
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Leave Balance Info -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-green-50 border-r-4 border-green-500 p-4 rounded-lg">
            <div class="text-sm text-gray-600">مانده مرخصی استحقاقی</div>
            <div class="text-2xl font-bold text-green-700"><?= to_persian_number(26) ?> روز</div>
        </div>
        <div class="bg-blue-50 border-r-4 border-blue-500 p-4 rounded-lg">
            <div class="text-sm text-gray-600">مرخصی استعلاجی استفاده شده</div>
            <div class="text-2xl font-bold text-blue-700"><?= to_persian_number(3) ?> روز</div>
        </div>
        <div class="bg-yellow-50 border-r-4 border-yellow-500 p-4 rounded-lg">
            <div class="text-sm text-gray-600">درخواست‌های در انتظار</div>
            <div class="text-2xl font-bold text-yellow-700"><?= to_persian_number(2) ?> مورد</div>
        </div>
        <div class="bg-purple-50 border-r-4 border-purple-500 p-4 rounded-lg">
            <div class="text-sm text-gray-600">سابقه کار</div>
            <div class="text-2xl font-bold text-purple-700"><?= to_persian_number(5) ?> سال</div>
        </div>
    </div>
    
    <!-- Request Form -->
    <?php if ($action === 'request'): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="request">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع مرخصی *</label>
                    <select name="leave_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">انتخاب کنید</option>
                        <option value="annual">استحقاقی</option>
                        <option value="sick">استعلاجی</option>
                        <option value="unpaid">بدون حقوق</option>
                        <option value="emergency">اضطراری</option>
                        <option value="maternity">زایمان</option>
                        <option value="paternity">پدری</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ شروع *</label>
                    <input type="date" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ پایان *</label>
                    <input type="date" name="end_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">علت درخواست</label>
                    <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    ثبت درخواست
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Leave Requests List -->
    <?php if ($action === 'list'): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کارمند</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع مرخصی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">از تاریخ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">تا تاریخ</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">روزها</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">وضعیت</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $db->query("
                    SELECT l.*, e.first_name, e.last_name
                    FROM leave_requests l
                    JOIN employees e ON l.employee_id = e.id
                    ORDER BY l.created_at DESC
                    LIMIT 50
                ");
                $requests = $stmt->fetchAll();
                
                $leave_types = [
                    'annual' => 'استحقاقی',
                    'sick' => 'استعلاجی',
                    'unpaid' => 'بدون حقوق',
                    'emergency' => 'اضطراری',
                    'maternity' => 'زایمان',
                    'paternity' => 'پدری'
                ];
                
                $status_colors = [
                    'pending' => 'yellow',
                    'approved' => 'green',
                    'rejected' => 'red',
                    'cancelled' => 'gray'
                ];
                
                $status_labels = [
                    'pending' => 'در انتظار',
                    'approved' => 'تایید شده',
                    'rejected' => 'رد شده',
                    'cancelled' => 'لغو شده'
                ];
                
                if (empty($requests)):
                ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">هیچ درخواستی یافت نشد.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?= $leave_types[$req['leave_type']] ?? $req['leave_type'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= to_persian_number(date('Y/m/d', strtotime($req['start_date']))) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= to_persian_number(date('Y/m/d', strtotime($req['end_date']))) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= to_persian_number($req['days_count']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full bg-<?= $status_colors[$req['status']] ?>-100 text-<?= $status_colors[$req['status']] ?>-800">
                            <?= $status_labels[$req['status']] ?? $req['status'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <?php if ($req['status'] === 'pending' && $_SESSION['user_role'] !== 'employee'): ?>
                        <div class="flex space-x-2 space-x-reverse">
                            <form method="POST" action="" class="inline" onsubmit="return confirm('آیا از تایید این درخواست اطمینان دارید؟')">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                <button type="submit" class="text-green-600 hover:text-green-900">تایید</button>
                            </form>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('آیا از رد این درخواست اطمینان دارید؟')">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">رد</button>
                            </form>
                        </div>
                        <?php else: ?>
                        <span class="text-gray-400">-</span>
                        <?php endif; ?>
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
