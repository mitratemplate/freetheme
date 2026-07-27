<?php
/**
 * Employees Management Page
 * Iranian Office Automation System - 1405
 */

session_start();
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

require_login();

$db = Database::getInstance()->getConnection();
$pageTitle = 'مدیریت کارکنان';
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
                case 'add':
                    $stmt = $db->prepare("
                        INSERT INTO employees (
                            employee_code, first_name, last_name, father_name, national_code,
                            birth_date, gender, marital_status, children_count, education_level,
                            department_id, position_title, employment_type, hire_date,
                            bank_account_number, bank_name, phone, address
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        sanitize_input($_POST['employee_code']),
                        sanitize_input($_POST['first_name']),
                        sanitize_input($_POST['last_name']),
                        sanitize_input($_POST['father_name']),
                        sanitize_input($_POST['national_code']),
                        $_POST['birth_date'] ?: null,
                        $_POST['gender'],
                        $_POST['marital_status'],
                        (int)($_POST['children_count'] ?? 0),
                        $_POST['education_level'],
                        $_POST['department_id'] ?: null,
                        sanitize_input($_POST['position_title']),
                        $_POST['employment_type'],
                        $_POST['hire_date'],
                        sanitize_input($_POST['bank_account_number']),
                        sanitize_input($_POST['bank_name']),
                        sanitize_input($_POST['phone']),
                        sanitize_input($_POST['address'])
                    ]);
                    
                    $message = 'کارمند با موفقیت افزوده شد.';
                    $messageType = 'success';
                    break;
                    
                case 'edit':
                    $stmt = $db->prepare("
                        UPDATE employees SET
                            first_name = ?, last_name = ?, father_name = ?, national_code = ?,
                            birth_date = ?, gender = ?, marital_status = ?, children_count = ?,
                            education_level = ?, department_id = ?, position_title = ?,
                            employment_type = ?, hire_date = ?, bank_account_number = ?,
                            bank_name = ?, phone = ?, address = ?
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        sanitize_input($_POST['first_name']),
                        sanitize_input($_POST['last_name']),
                        sanitize_input($_POST['father_name']),
                        sanitize_input($_POST['national_code']),
                        $_POST['birth_date'] ?: null,
                        $_POST['gender'],
                        $_POST['marital_status'],
                        (int)($_POST['children_count'] ?? 0),
                        $_POST['education_level'],
                        $_POST['department_id'] ?: null,
                        sanitize_input($_POST['position_title']),
                        $_POST['employment_type'],
                        $_POST['hire_date'],
                        sanitize_input($_POST['bank_account_number']),
                        sanitize_input($_POST['bank_name']),
                        sanitize_input($_POST['phone']),
                        sanitize_input($_POST['address']),
                        (int)$_POST['id']
                    ]);
                    
                    $message = 'اطلاعات کارمند با موفقیت بروزرسانی شد.';
                    $messageType = 'success';
                    break;
                    
                case 'delete':
                    $stmt = $db->prepare("UPDATE employees SET status = 'terminated' WHERE id = ?");
                    $stmt->execute([(int)$_POST['id']]);
                    $message = 'کارمند با موفقیت حذف شد.';
                    $messageType = 'success';
                    break;
            }
        } catch (Exception $e) {
            $message = 'خطا در عملیات: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get employees list
$action = $_GET['action'] ?? 'list';
$employee = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $employee = $stmt->fetch();
}

// Get departments
$departments = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();

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
            <?= $action === 'add' ? 'افزودن کارمند جدید' : ($action === 'edit' ? 'ویرایش اطلاعات کارمند' : 'لیست کارکنان') ?>
        </h2>
        
        <?php if ($action === 'list'): ?>
        <a href="?action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            افزودن کارمند
        </a>
        <?php else: ?>
        <a href="employees.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            بازگشت به لیست
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Form -->
    <?php if ($action === 'add' || $action === 'edit'): ?>
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="<?= $action ?>">
            <?php if ($action === 'edit' && $employee): ?>
            <input type="hidden" name="id" value="<?= $employee['id'] ?>">
            <?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">کد پرسنلی *</label>
                    <input type="text" name="employee_code" required 
                           value="<?= htmlspecialchars($employee['employee_code'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نام *</label>
                    <input type="text" name="first_name" required 
                           value="<?= htmlspecialchars($employee['first_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نام خانوادگی *</label>
                    <input type="text" name="last_name" required 
                           value="<?= htmlspecialchars($employee['last_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نام پدر</label>
                    <input type="text" name="father_name" 
                           value="<?= htmlspecialchars($employee['father_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">کد ملی *</label>
                    <input type="text" name="national_code" required maxlength="10"
                           value="<?= htmlspecialchars($employee['national_code'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ تولد</label>
                    <input type="date" name="birth_date" 
                           value="<?= htmlspecialchars($employee['birth_date'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">جنسیت *</label>
                    <select name="gender" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="male" <?= ($employee['gender'] ?? '') === 'male' ? 'selected' : '' ?>>مرد</option>
                        <option value="female" <?= ($employee['gender'] ?? '') === 'female' ? 'selected' : '' ?>>زن</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">وضعیت تأهل *</label>
                    <select name="marital_status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="single" <?= ($employee['marital_status'] ?? '') === 'single' ? 'selected' : '' ?>>مجرد</option>
                        <option value="married" <?= ($employee['marital_status'] ?? '') === 'married' ? 'selected' : '' ?>>متأهل</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تعداد فرزندان</label>
                    <input type="number" name="children_count" min="0" 
                           value="<?= htmlspecialchars($employee['children_count'] ?? '0') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">مدرک تحصیلی</label>
                    <select name="education_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">انتخاب کنید</option>
                        <option value="diploma" <?= ($employee['education_level'] ?? '') === 'diploma' ? 'selected' : '' ?>>دیپلم</option>
                        <option value="associate" <?= ($employee['education_level'] ?? '') === 'associate' ? 'selected' : '' ?>>کاردانی</option>
                        <option value="bachelor" <?= ($employee['education_level'] ?? '') === 'bachelor' ? 'selected' : '' ?>>کارشناسی</option>
                        <option value="master" <?= ($employee['education_level'] ?? '') === 'master' ? 'selected' : '' ?>>کارشناسی ارشد</option>
                        <option value="phd" <?= ($employee['education_level'] ?? '') === 'phd' ? 'selected' : '' ?>>دکتری</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">واحد سازمانی</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">انتخاب کنید</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ($employee['department_id'] ?? 0) == $dept['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">عنوان شغلی</label>
                    <input type="text" name="position_title" 
                           value="<?= htmlspecialchars($employee['position_title'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع همکاری *</label>
                    <select name="employment_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="permanent" <?= ($employee['employment_type'] ?? '') === 'permanent' ? 'selected' : '' ?>>رسمی</option>
                        <option value="contract" <?= ($employee['employment_type'] ?? '') === 'contract' ? 'selected' : '' ?>>قراردادی</option>
                        <option value="temporary" <?= ($employee['employment_type'] ?? '') === 'temporary' ? 'selected' : '' ?>>موقت</option>
                        <option value="part_time" <?= ($employee['employment_type'] ?? '') === 'part_time' ? 'selected' : '' ?>>پاره‌وقت</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ استخدام *</label>
                    <input type="date" name="hire_date" required 
                           value="<?= htmlspecialchars($employee['hire_date'] ?? date('Y-m-d')) ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">شماره حساب</label>
                    <input type="text" name="bank_account_number" 
                           value="<?= htmlspecialchars($employee['bank_account_number'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نام بانک</label>
                    <input type="text" name="bank_name" 
                           value="<?= htmlspecialchars($employee['bank_name'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تلفن تماس</label>
                    <input type="text" name="phone" 
                           value="<?= htmlspecialchars($employee['phone'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">آدرس</label>
                    <textarea name="address" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($employee['address'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2 space-x-reverse pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    ذخیره اطلاعات
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Employees List -->
    <?php if ($action === 'list'): ?>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">کد پرسنلی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نام و نام خانوادگی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">واحد سازمانی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عنوان شغلی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع همکاری</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وضعیت</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $db->query("
                    SELECT e.*, d.name as department_name 
                    FROM employees e 
                    LEFT JOIN departments d ON e.department_id = d.id 
                    ORDER BY e.id DESC
                ");
                $employees = $stmt->fetchAll();
                
                if (empty($employees)):
                ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">هیچ کارمندی یافت نشد.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= to_persian_number($emp['employee_code']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($emp['department_name'] ?? '-') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($emp['position_title'] ?? '-') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?= $emp['employment_type'] === 'permanent' ? 'bg-green-100 text-green-800' : 
                               ($emp['employment_type'] === 'contract' ? 'bg-blue-100 text-blue-800' : 
                               ($emp['employment_type'] === 'temporary' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) ?>">
                            <?= $emp['employment_type'] === 'permanent' ? 'رسمی' : 
                               ($emp['employment_type'] === 'contract' ? 'قراردادی' : 
                               ($emp['employment_type'] === 'temporary' ? 'موقت' : 'پاره‌وقت')) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full 
                            <?= $emp['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $emp['status'] === 'active' ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2 space-x-reverse">
                            <a href="?action=edit&id=<?= $emp['id'] ?>" class="text-blue-600 hover:text-blue-900">ویرایش</a>
                            <form method="POST" action="" class="inline" onsubmit="return confirm('آیا از حذف این کارمند اطمینان دارید؟')">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">حذف</button>
                            </form>
                        </div>
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
