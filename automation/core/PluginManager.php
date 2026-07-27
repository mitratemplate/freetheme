<?php
/**
 * Core Plugin Manager - سیستم مدیریت پلاگین‌ها
 * مشابه وردپرس، امکان نصب، فعال‌سازی و حذف پکیج‌ها را فراهم می‌کند
 */

class PluginManager {
    private $db;
    private $pluginsDir = 'plugins/';
    private $marketplaceUrl = 'https://marketplace.automation.ir/api/';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * دریافت لیست پلاگین‌های نصب شده
     */
    public function getInstalledPlugins() {
        $stmt = $this->db->query("SELECT * FROM plugins ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * دریافت اطلاعات یک پلاگین از فایل manifest
     */
    public function getPluginInfo($pluginSlug) {
        $manifestPath = $this->pluginsDir . $pluginSlug . '/plugin.json';
        if (file_exists($manifestPath)) {
            return json_decode(file_get_contents($manifestPath), true);
        }
        return false;
    }
    
    /**
     * دانلود پلاگین از مارکت‌پلیس
     */
    public function downloadPlugin($pluginId, $licenseKey) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->marketplaceUrl . 'plugins/' . $pluginId . '/download');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'license_key' => $licenseKey,
            'site_url' => $this->getSiteUrl(),
            'version' => $this->getCoreVersion()
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            // ذخیره فایل ZIP
            $tempFile = tempnam(sys_get_temp_dir(), 'plugin_') . '.zip';
            file_put_contents($tempFile, $response);
            return $tempFile;
        }
        
        return false;
    }
    
    /**
     * نصب پلاگین از فایل ZIP
     */
    public function installPlugin($zipFile, $pluginSlug) {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            $extractPath = $this->pluginsDir . $pluginSlug . '/';
            
            // ایجاد پوشه پلاگین
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }
            
            // استخراج فایل‌ها
            $zip->extractTo($extractPath);
            $zip->close();
            
            // خواندن manifest
            $manifest = $this->getPluginInfo($pluginSlug);
            if ($manifest) {
                // اجرای نصب‌کننده پلاگین
                if (file_exists($extractPath . 'install.php')) {
                    require_once $extractPath . 'install.php';
                    $installer = new PluginInstaller($this->db);
                    $installer->install($manifest);
                }
                
                // ثبت در دیتابیس
                $this->registerPlugin($manifest);
                
                // حذف فایل موقت
                unlink($zipFile);
                
                return true;
            }
        }
        return false;
    }
    
    /**
     * ثبت پلاگین در دیتابیس
     */
    private function registerPlugin($manifest) {
        $stmt = $this->db->prepare("
            INSERT INTO plugins (slug, name, version, author, description, status, installed_at)
            VALUES (:slug, :name, :version, :author, :description, 'inactive', NOW())
            ON DUPLICATE KEY UPDATE 
                version = :version,
                name = :name,
                author = :author,
                description = :description
        ");
        
        $stmt->execute([
            ':slug' => $manifest['slug'],
            ':name' => $manifest['name'],
            ':version' => $manifest['version'],
            ':author' => $manifest['author'],
            ':description' => $manifest['description']
        ]);
    }
    
    /**
     * فعال‌سازی پلاگین
     */
    public function activatePlugin($pluginSlug) {
        // بررسی وجود پلاگین
        if (!$this->pluginExists($pluginSlug)) {
            return ['success' => false, 'message' => 'پلاگین یافت نشد'];
        }
        
        // اجرای هوک فعال‌سازی
        $activateFile = $this->pluginsDir . $pluginSlug . '/activate.php';
        if (file_exists($activateFile)) {
            require_once $activateFile;
            try {
                $activator = new PluginActivator($this->db);
                $activator->activate();
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'خطا در فعال‌سازی: ' . $e->getMessage()];
            }
        }
        
        // بروزرسانی وضعیت در دیتابیس
        $stmt = $this->db->prepare("UPDATE plugins SET status = 'active', activated_at = NOW() WHERE slug = :slug");
        $stmt->execute([':slug' => $pluginSlug]);
        
        // اضافه کردن به لیست پلاگین‌های فعال
        $this->addToActivePlugins($pluginSlug);
        
        return ['success' => true, 'message' => 'پلاگین با موفقیت فعال شد'];
    }
    
    /**
     * غیرفعال‌سازی پلاگین
     */
    public function deactivatePlugin($pluginSlug) {
        // اجرای هوک غیرفعال‌سازی
        $deactivateFile = $this->pluginsDir . $pluginSlug . '/deactivate.php';
        if (file_exists($deactivateFile)) {
            require_once $deactivateFile;
            try {
                $deactivator = new PluginDeactivator($this->db);
                $deactivator->deactivate();
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'خطا در غیرفعال‌سازی: ' . $e->getMessage()];
            }
        }
        
        // بروزرسانی وضعیت در دیتابیس
        $stmt = $this->db->prepare("UPDATE plugins SET status = 'inactive' WHERE slug = :slug");
        $stmt->execute([':slug' => $pluginSlug]);
        
        // حذف از لیست پلاگین‌های فعال
        $this->removeFromActivePlugins($pluginSlug);
        
        return ['success' => true, 'message' => 'پلاگین غیرفعال شد'];
    }
    
    /**
     * حذف پلاگین
     */
    public function uninstallPlugin($pluginSlug) {
        // ابتدا غیرفعال شود
        $this->deactivatePlugin($pluginSlug);
        
        // اجرای هوک حذف
        $uninstallFile = $this->pluginsDir . $pluginSlug . '/uninstall.php';
        if (file_exists($uninstallFile)) {
            require_once $uninstallFile;
            try {
                $uninstaller = new PluginUninstaller($this->db);
                $uninstaller->uninstall();
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'خطا در حذف: ' . $e->getMessage()];
            }
        }
        
        // حذف از دیتابیس
        $stmt = $this->db->prepare("DELETE FROM plugins WHERE slug = :slug");
        $stmt->execute([':slug' => $pluginSlug]);
        
        // حذف فایل‌ها
        $this->deletePluginFiles($pluginSlug);
        
        return ['success' => true, 'message' => 'پلاگین حذف شد'];
    }
    
    /**
     * بررسی بروزرسانی برای پلاگین‌ها
     */
    public function checkForUpdates() {
        $plugins = $this->getInstalledPlugins();
        $updates = [];
        
        foreach ($plugins as $plugin) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->marketplaceUrl . 'plugins/' . $plugin['slug'] . '/check-update');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'current_version' => $plugin['version'],
                'site_url' => $this->getSiteUrl()
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $updateInfo = json_decode($response, true);
            if ($updateInfo && isset($updateInfo['new_version']) && version_compare($updateInfo['new_version'], $plugin['version'], '>')) {
                $updates[] = [
                    'slug' => $plugin['slug'],
                    'name' => $plugin['name'],
                    'current_version' => $plugin['version'],
                    'new_version' => $updateInfo['new_version'],
                    'download_url' => $updateInfo['download_url'],
                    'changelog' => $updateInfo['changelog'] ?? ''
                ];
            }
        }
        
        return $updates;
    }
    
    /**
     * بروزرسانی پلاگین
     */
    public function updatePlugin($pluginSlug, $licenseKey) {
        $updateInfo = $this->checkForUpdates();
        $pluginUpdate = null;
        
        foreach ($updateInfo as $update) {
            if ($update['slug'] === $pluginSlug) {
                $pluginUpdate = $update;
                break;
            }
        }
        
        if (!$pluginUpdate) {
            return ['success' => false, 'message' => 'بروزرسانی موجود نیست'];
        }
        
        // دانلود نسخه جدید
        $zipFile = $this->downloadPlugin($pluginSlug, $licenseKey);
        if (!$zipFile) {
            return ['success' => false, 'message' => 'خطا در دانلود بروزرسانی'];
        }
        
        // غیرفعال‌سازی موقت
        $this->deactivatePlugin($pluginSlug);
        
        // حذف نسخه قدیمی
        $this->deletePluginFiles($pluginSlug);
        
        // نصب نسخه جدید
        if ($this->installPlugin($zipFile, $pluginSlug)) {
            $this->activatePlugin($pluginSlug);
            return ['success' => true, 'message' => 'پلاگین با موفقیت بروزرسانی شد'];
        }
        
        return ['success' => false, 'message' => 'خطا در نصب بروزرسانی'];
    }
    
    /**
     * دریافت لیست پلاگین‌های فعال
     */
    public function getActivePlugins() {
        $stmt = $this->db->query("SELECT * FROM plugins WHERE status = 'active'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * بارگذاری پلاگین‌های فعال
     */
    public function loadActivePlugins() {
        $activePlugins = $this->getActivePlugins();
        foreach ($activePlugins as $plugin) {
            $mainFile = $this->pluginsDir . $plugin['slug'] . '/' . $plugin['slug'] . '.php';
            if (file_exists($mainFile)) {
                require_once $mainFile;
            }
        }
    }
    
    /**
     * افزودن هوک‌های پلاگین
     */
    public function addHook($hookName, $callback, $priority = 10) {
        global $wp_filter;
        $wp_filter[$hookName][$priority][] = $callback;
    }
    
    /**
     * اجرای هوک‌ها
     */
    public function doHook($hookName, ...$args) {
        global $wp_filter;
        if (isset($wp_filter[$hookName])) {
            ksort($wp_filter[$hookName]);
            foreach ($wp_filter[$hookName] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    call_user_func_array($callback, $args);
                }
            }
        }
    }
    
    // Helper methods
    private function pluginExists($slug) {
        return file_exists($this->pluginsDir . $slug . '/plugin.json');
    }
    
    private function addToActivePlugins($slug) {
        $active = get_option('active_plugins', []);
        if (!in_array($slug, $active)) {
            $active[] = $slug;
            update_option('active_plugins', $active);
        }
    }
    
    private function removeFromActivePlugins($slug) {
        $active = get_option('active_plugins', []);
        $key = array_search($slug, $active);
        if ($key !== false) {
            unset($active[$key]);
            update_option('active_plugins', $active);
        }
    }
    
    private function deletePluginFiles($slug) {
        $dir = $this->pluginsDir . $slug;
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        $this->deleteDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            rmdir($dir);
        }
    }
    
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    private function getSiteUrl() {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
    }
    
    private function getCoreVersion() {
        return '1.0.0'; // نسخه هسته اصلی
    }
}

// توابع کمکی گلوبال
function get_option($name, $default = '') {
    global $db;
    $stmt = $db->prepare("SELECT option_value FROM options WHERE option_name = :name");
    $stmt->execute([':name' => $name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['option_value'] : $default;
}

function update_option($name, $value) {
    global $db;
    $stmt = $db->prepare("
        INSERT INTO options (option_name, option_value) 
        VALUES (:name, :value)
        ON DUPLICATE KEY UPDATE option_value = :value
    ");
    return $stmt->execute([':name' => $name, ':value' => $value]);
}
