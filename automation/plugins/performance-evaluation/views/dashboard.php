<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">داشبورد ارزیابی عملکرد</h2>
    
    <!-- کارت‌های آمار -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-50 rounded-lg p-6 border-r-4 border-blue-500">
            <div class="text-gray-500 text-sm mb-2">ارزیابی‌های در انتظار</div>
            <div class="text-3xl font-bold text-blue-600"><?php echo $pendingReviews; ?></div>
        </div>
        
        <div class="bg-green-50 rounded-lg p-6 border-r-4 border-green-500">
            <div class="text-gray-500 text-sm mb-2">ارزیابی‌های تکمیل شده</div>
            <div class="text-3xl font-bold text-green-600"><?php echo $completedReviews; ?></div>
        </div>
        
        <div class="bg-yellow-50 rounded-lg p-6 border-r-4 border-yellow-500">
            <div class="text-gray-500 text-sm mb-2">میانگین امتیازات</div>
            <div class="text-3xl font-bold text-yellow-600"><?php echo $averageScore; ?>/100</div>
        </div>
        
        <div class="bg-purple-50 rounded-lg p-6 border-r-4 border-purple-500">
            <div class="text-gray-500 text-sm mb-2">اهداف فعال</div>
            <div class="text-3xl font-bold text-purple-600"><?php echo $activeGoals; ?></div>
        </div>
    </div>
    
    <!-- نمودارها و جداول -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- آخرین ارزیابی‌ها -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">آخرین ارزیابی‌ها</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">کارمند</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">دوره</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">امتیاز</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentReviews as $review): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($review['employee_name']); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                <?php echo toPersianDate($review['period_start']); ?> - <?php echo toPersianDate($review['period_end']); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium 
                                    <?php echo $review['final_score'] >= 80 ? 'bg-green-100 text-green-800' : 
                                          ($review['final_score'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                    <?php echo number_format($review['final_score'], 1); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <?php echo getStatusBadge($review['status']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- اهداف در حال انجام -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">اهداف در حال انجام</h3>
            <div class="space-y-4">
                <?php foreach ($ongoingGoals as $goal): ?>
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($goal['title']); ?></h4>
                        <span class="px-2 py-1 rounded text-xs 
                            <?php echo $goal['priority'] === 'high' ? 'bg-red-100 text-red-800' : 
                                  ($goal['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'); ?>">
                            <?php echo translatePriority($goal['priority']); ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($goal['description']); ?></p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $goal['progress_percentage']; ?>%"></div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500 text-left">
                        پیشرفت: <?php echo number_format($goal['progress_percentage']); ?>٪
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- دکمه‌های اقدام سریع -->
    <div class="mt-8 flex gap-4">
        <a href="?page=performance-evaluation-reviews&action=new" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            ایجاد ارزیابی جدید
        </a>
        
        <a href="?page=performance-evaluation-kpis" 
           class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            مدیریت شاخص‌ها
        </a>
        
        <a href="?page=performance-evaluation-reports" 
           class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            مشاهده گزارشات
        </a>
    </div>
</div>

<script>
// اسکریپت‌های داشبورد
document.addEventListener('DOMContentLoaded', function() {
    // بارگذاری نمودارها با Chart.js
    loadPerformanceCharts();
});

function loadPerformanceCharts() {
    // کد مربوط به نمودارها
}
</script>
