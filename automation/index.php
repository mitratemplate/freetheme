<?php
/**
 * داشبورد اصلی سیستم
 * Automation Pro v3.0 - Enterprise Edition
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: modules/auth/login.php');
    exit;
}

require_once 'includes/Database.php';
require_once 'includes/helpers.php';

$pageTitle = 'داشبورد مدیریت';
include 'includes/header.php';

// دریافت آمار کلی
$db = Database::getInstance()->getConnection();

// تعداد کل کارکنان
$stmt = $db->query("SELECT COUNT(*) as total FROM ap_employees WHERE is_active = 1");
$totalEmployees = $stmt->fetch()['total'] ?? 0;

// حضور امروز
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COUNT(DISTINCT employee_id) as present FROM ap_attendance WHERE date = ? AND status = 'present'");
$stmt->execute([$today]);
$presentToday = $stmt->fetch()['present'] ?? 0;

// درخواست‌های مرخصی در انتظار
$stmt = $db->query("SELECT COUNT(*) as pending FROM ap_leave_requests WHERE status = 'pending'");
$pendingLeaves = $stmt->fetch()['pending'] ?? 0;

// حقوق پرداختی این ماه
$currentMonth = date('Y-m');
$stmt = $db->prepare("SELECT SUM(net_salary) as total FROM ap_payroll WHERE month = ? AND status = 'paid'");
$stmt->execute([$currentMonth]);
$paidSalary = $stmt->fetch()['total'] ?? 0;

?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- کارت کارکنان -->
    <div class="bg-white rounded-lg shadow-md p-6 border-r-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">تعداد کارکنان فعال</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= toPersianNumbers($totalEmployees) ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- کارت حضور امروز -->
    <div class="bg-white rounded-lg shadow-md p-6 border-r-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">حاضرین امروز</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= toPersianNumbers($presentToday) ?></p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- کارت مرخصی‌ها -->
    <div class="bg-white rounded-lg shadow-md p-6 border-r-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">درخواست‌های مرخصی</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= toPersianNumbers($pendingLeaves) ?></p>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- کارت حقوق -->
    <div class="bg-white rounded-lg shadow-md p-6 border-r-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm">حقوق پرداختی این ماه</p>
                <p class="text-xl font-bold text-gray-800 mt-2"><?= formatMoney($paidSalary) ?></p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- بخش دسترسی سریع -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- دسترسی سریع به ماژول‌ها -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">دسترسی سریع به ماژول‌ها</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="modules/hr/employees.php" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition">
                <svg class="w-8 h-8 mx-auto text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-gray-700">مدیریت کارکنان</span>
            </a>
            
            <a href="modules/attendance/attendance.php" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center transition">
                <svg class="w-8 h-8 mx-auto text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-gray-700">حضور و غیاب</span>
            </a>
            
            <a href="modules/payroll/payroll.php" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg text-center transition">
                <svg class="w-8 h-8 mx-auto text-purple-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-gray-700">حقوق و دستمزد</span>
            </a>
            
            <a href="modules/ai/assistant.php" class="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg text-center transition">
                <svg class="w-8 h-8 mx-auto text-orange-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span class="text-gray-700">هوش مصنوعی</span>
            </a>
        </div>
    </div>
    
    <!-- اطلاعیه‌ها -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">اطلاعیه‌های مهم</h3>
        <div class="space-y-3">
            <div class="bg-blue-50 border-r-4 border-blue-500 p-3 rounded">
                <p class="text-sm text-gray-700">📊 استانداردهای حقوق و دستمزد سال ۱۴۰۵ اعمال شد</p>
                <p class="text-xs text-gray-500 mt-1">۱ فروردین ۱۴۰۵</p>
            </div>
            <div class="bg-green-50 border-r-4 border-green-500 p-3 rounded">
                <p class="text-sm text-gray-700">✅ سیستم هوش مصنوعی GapGPT فعال شد</p>
                <p class="text-xs text-gray-500 mt-1">امروز</p>
            </div>
            <div class="bg-yellow-50 border-r-4 border-yellow-500 p-3 rounded">
                <p class="text-sm text-gray-700">⚠️ مهلت ثبت مرخصی‌های عید تا پایان هفته</p>
                <p class="text-xs text-gray-500 mt-1">دیروز</p>
            </div>
        </div>
    </div>
</div>

<!-- نمودارها و تحلیل‌ها -->
<div class="mt-8 bg-white rounded-lg shadow-md p-6">
    <h3 class="text-xl font-bold text-gray-800 mb-4">تحلیل هوشمند وضعیت سازمان</h3>
    <div id="ai-analysis" class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-lg">
        <p class="text-gray-700">در حال تحلیل داده‌ها توسط هوش مصنوعی...</p>
        <button onclick="getAIAnalysis()" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
            دریافت تحلیل جدید
        </button>
        <div id="ai-result" class="mt-4 hidden"></div>
    </div>
</div>

<script>
async function getAIAnalysis() {
    const resultDiv = document.getElementById('ai-result');
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<p class="text-gray-600">هوش مصنوعی در حال تحلیل است...</p>';
    
    // اینجا می‌توانید به API هوش مصنوعی متصل شوید
    setTimeout(() => {
        resultDiv.innerHTML = `
            <div class="bg-white p-4 rounded shadow">
                <h4 class="font-bold text-gray-800 mb-2">📊 تحلیل وضعیت سازمان:</h4>
                <ul class="text-sm text-gray-700 space-y-2">
                    <li>✅ نرخ حضور کارکنان: <?= round(($presentToday / max($totalEmployees, 1)) * 100) ?>٪ (عالی)</li>
                    <li>⚠️ <?= $pendingLeaves ?> درخواست مرخصی در انتظار بررسی</li>
                    <li>💰 مجموع حقوق پرداختی این ماه: <?= formatMoney($paidSalary) ?></li>
                    <li>📈 پیشنهاد: با توجه به آمار، زمان مناسبی برای برنامه‌ریزی جذب نیروی جدید است</li>
                </ul>
            </div>
        `;
    }, 1500);
}
</script>

<?php include 'includes/footer.php'; ?>
