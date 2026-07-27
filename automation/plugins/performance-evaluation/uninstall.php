<?php
/**
 * فایل حذف کامل پلاگین (Uninstall)
 */

// بررسی مجوز حذف
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

class PluginUninstaller {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function uninstall() {
        // حذف جداول دیتابیس
        $this->dropTables();
        
        // حذف گزینه‌ها از دیتابیس
        $this->deleteOptions();
        
        // حذف داده‌های موقت
        $this->cleanTransientData();
    }
    
    private function dropTables() {
        $tables = [
            'performance_reviews',
            'performance_kpis',
            'performance_feedbacks',
            'performance_goals'
        ];
        
        foreach ($tables as $table) {
            $this->db->exec("DROP TABLE IF EXISTS {$this->db->prefix}{$table}");
        }
    }
    
    private function deleteOptions() {
        $options = [
            'performance-evaluation_version',
            'performance-evaluation_installed_at',
            'performance-evaluation_active',
            'performance_evaluation_review_cycle',
            'performance_evaluation_auto_notify',
            'performance_evaluation_allow_self_eval',
            'active_plugins'
        ];
        
        foreach ($options as $option) {
            $stmt = $this->db->prepare("DELETE FROM wp_options WHERE option_name = :name");
            $stmt->execute([':name' => $option]);
        }
    }
    
    private function cleanTransientData() {
        // حذف داده‌های موقت و کش
        $stmt = $this->db->exec("DELETE FROM wp_options WHERE option_name LIKE '_transient_performance_%'");
    }
}

// اجرای حذف
$uninstaller = new PluginUninstaller($db);
$uninstaller->uninstall();
