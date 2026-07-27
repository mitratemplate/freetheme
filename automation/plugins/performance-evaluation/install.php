<?php
/**
 * فایل نصب پلاگین - ایجاد جداول و داده‌های اولیه
 */

class PluginInstaller {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function install($manifest) {
        // ایجاد جداول سفارشی پلاگین
        if (isset($manifest['tables']) && is_array($manifest['tables'])) {
            foreach ($manifest['tables'] as $table) {
                $this->createTable($table);
            }
        }
        
        // افزودن گزینه‌های پیش‌فرض
        $this->addDefaultOptions($manifest);
        
        // افزودن قابلیت‌ها به نقش‌ها
        $this->addCapabilities($manifest);
        
        return true;
    }
    
    private function createTable($tableName) {
        // جداول بر اساس manifest ایجاد می‌شوند
        // این متد توسط کلاس اصلی پلاگین اجرا می‌شود
    }
    
    private function addDefaultOptions($manifest) {
        $options = [
            $manifest['slug'] . '_version' => $manifest['version'],
            $manifest['slug'] . '_installed_at' => date('Y-m-d H:i:s'),
            $manifest['slug'] . '_active' => 0
        ];
        
        foreach ($options as $name => $value) {
            update_option($name, $value);
        }
    }
    
    private function addCapabilities($manifest) {
        if (isset($manifest['capabilities'])) {
            foreach ($manifest['capabilities'] as $role => $caps) {
                foreach ($caps as $cap) {
                    $this->addCapabilityToRole($role, $cap);
                }
            }
        }
    }
    
    private function addCapabilityToRole($role, $capability) {
        // افزودن قابلیت به نقش
        $existing = get_option("role_{$role}_capabilities", []);
        if (!in_array($capability, $existing)) {
            $existing[] = $capability;
            update_option("role_{$role}_capabilities", $existing);
        }
    }
}
