<?php
/**
 * مدیریت کارکنان
 */

session_start();
if (!isset($_SESSION['user_id']) || !checkPermission('hr', 'view')) {
    header('Location: ../../modules/auth/login.php');
    exit;
}

require_once '../../includes/Database.php';
require_once '../../includes/helpers.php';

$db = Database::getInstance()->getConnection();
$message = '';
$error = '';

// افزودن کارمند جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'خطای امنیتی';
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO ap_employees 
                (first_name, last_name, father_name, national_code, personnel_code, department_id, position, contract_type, base_salary, hire_date, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['father_name'],
                $_POST['national_code'],
                $_POST['personnel_code'],
                $_POST['department_id'],
                $_POST['position'],
                $_POST['contract_type'],
                $_POST['base_salary'],
                $_POST['hire_date']
            ]);
            $message = 'کارمند با موفقیت افزوده شد';
            logEvent('EMPLOYEE_ADD', $_SESSION['user_id'], "کارمند جدید: {$_POST['personnel_code']}");
        } catch (Exception $e) {
            $error = 'خطا در افزودن کارمند: ' . $e->getMessage();
        }
    }
}

// دریافت لیست کارکنان
$employees = $db->query("
    SELECT e.*, d.name as department_name 
    FROM ap_employees e 
    LEFT JOIN ap_departments d ON e.department_id = d.id 
    WHERE e.is_active = 1
    ORDER BY e.id DESC
")->fetchAll();

$pageTitle = 'مدیریت کارکنان';
include '../../includes/header.php';
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">مدیریت کارکنان</h2>
        <button onclick="toggleModal('addEmployeeModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            افزودن کارمند جدید
        </button>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <!-- جدول کارکنان -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">کد پرسنلی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نام و نام خانوادگی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">واحد سازمانی</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">سمت</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع قرارداد</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">حقوق پایه</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">عملیات</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($employees as $emp): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= toPersianNumbers($emp['personnel_code']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $emp['department_name'] ?? '-' ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $emp['position'] ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?= $emp['contract_type'] === 'permanent' ? 'bg-green-100 text-green-800' : 
                               ($emp['contract_type'] === 'temporary' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') ?>">
                            <?= $emp['contract_type'] === 'permanent' ? 'رسمی' : ($emp['contract_type'] === 'temporary' ? 'موقت' : 'قراردادی') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= formatMoney($emp['base_salary']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-blue-600 hover:text-blue-900 ml-2">ویرایش</a>
                        <a href="#" class="text-red-600 hover:text-red-900">غیرفعال</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- مودال افزودن کارمند -->
<div id="addEmployeeModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">افزودن کارمند جدید</h3>
            <button onclick="toggleModal('addEmployeeModal')" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">نام</label>
                    <input type="text" name="first_name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">نام خانوادگی</label>
                    <input type="text" name="last_name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">نام پدر</label>
                    <input type="text" name="father_name" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">کد ملی</label>
                    <input type="text" name="national_code" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">کد پرسنلی</label>
                    <input type="text" name="personnel_code" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">تاریخ استخدام</label>
                    <input type="date" name="hire_date" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">واحد سازمانی</label>
                    <select name="department_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="">انتخاب کنید</option>
                        <?php
                        $depts = $db->query("SELECT id, name FROM ap_departments")->fetchAll();
                        foreach ($depts as $dept):
                        ?>
                        <option value="<?= $dept['id'] ?>"><?= $dept['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">سمت</label>
                    <input type="text" name="position" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">نوع قرارداد</label>
                    <select name="contract_type" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        <option value="permanent">رسمی</option>
                        <option value="contract">قراردادی</option>
                        <option value="temporary">موقت</option>
                        <option value="parttime">پاره‌وقت</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">حقوق پایه (ریال)</label>
                    <input type="number" name="base_salary" value="<?= getSalaryStandards()['base_salary'] ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
            </div>
            
            <div class="flex justify-end space-x-2 space-x-reverse pt-4">
                <button type="button" onclick="toggleModal('addEmployeeModal')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">انصراف</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">ذخیره</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.toggle('hidden');
}
</script>

<?php include '../../includes/footer.php'; ?>
