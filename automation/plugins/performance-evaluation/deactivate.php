<?php
/**
 * فایل غیرفعال‌سازی پلاگین
 */

class PluginDeactivator {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function deactivate() {
        // پاک‌سازی کش
        $this->clearCache();
        
        // غیرفعال کردن هوک‌ها
        $this->removeHooks();
        
        // بروزرسانی وضعیت در دیتابیس
        update_option('performance-evaluation_active', 0);
    }
    
    private function clearCache() {
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    private function removeHooks() {
        // حذف هوک‌های ثبت شده
        // این کار به صورت خودکار با غیرفعال شدن پلاگین انجام می‌شود
    }
}
