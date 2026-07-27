<?php
/**
 * فایل فعال‌سازی پلاگین
 */

class PluginActivator {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function activate() {
        // ایجاد جداول دیتابیس
        $this->createDatabaseTables();
        
        // افزودن داده‌های اولیه
        $this->seedDefaultData();
        
        // تنظیمات اولیه
        $this->setInitialOptions();
        
        // پاک‌سازی کش
        $this->clearCache();
    }
    
    private function createDatabaseTables() {
        // جداول توسط کلاس اصلی پلاگین ایجاد می‌شوند
        $plugin = new PerformanceEvaluationPlugin($this->db);
        $plugin->createTables();
    }
    
    private function seedDefaultData() {
        // افزودن KPIهای پیش‌فرض
        $defaultKpis = [
            [
                'name' => 'کیفیت کار',
                'description' => 'ارزیابی کیفیت خروجی کار',
                'category' => 'performance',
                'weight' => 1.00,
                'measurement_type' => 'percentage'
            ],
            [
                'name' => 'رعایت زمان‌بندی',
                'description' => 'تحویل به موقع وظایف',
                'category' => 'time_management',
                'weight' => 1.00,
                'measurement_type' => 'percentage'
            ],
            [
                'name' => 'کار تیمی',
                'description' => 'همکاری و تعامل با سایر اعضا',
                'category' => 'teamwork',
                'weight' => 0.80,
                'measurement_type' => 'numeric'
            ],
            [
                'name' => 'نوآوری و خلاقیت',
                'description' => 'ارائه ایده‌های جدید و راهکارهای نوآورانه',
                'category' => 'innovation',
                'weight' => 0.70,
                'measurement_type' => 'numeric'
            ]
        ];
        
        foreach ($defaultKpis as $kpi) {
            $stmt = $this->db->prepare("
                INSERT INTO wp_performance_kpis (name, description, category, weight, measurement_type, is_active)
                VALUES (:name, :description, :category, :weight, :type, 1)
                ON DUPLICATE KEY UPDATE name = :name
            ");
            $stmt->execute([
                ':name' => $kpi['name'],
                ':description' => $kpi['description'],
                ':category' => $kpi['category'],
                ':weight' => $kpi['weight'],
                ':type' => $kpi['measurement_type']
            ]);
        }
    }
    
    private function setInitialOptions() {
        update_option('performance_evaluation_review_cycle', 'quarterly');
        update_option('performance_evaluation_auto_notify', 1);
        update_option('performance_evaluation_allow_self_eval', 1);
    }
    
    private function clearCache() {
        // پاک‌سازی کش سیستم
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
}
