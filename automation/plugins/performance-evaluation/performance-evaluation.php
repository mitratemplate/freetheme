<?php
/**
 * فایل اصلی پلاگین ارزیابی عملکرد
 * Performance Evaluation Plugin Main File
 */

// جلوگیری از دسترسی مستقیم
if (!defined('AUTOMATION_CORE')) {
    die('دسترسی مستقیم مجاز نیست');
}

class PerformanceEvaluationPlugin {
    
    private $db;
    private $pluginSlug = 'performance-evaluation';
    
    public function __construct($db) {
        $this->db = $db;
        $this->initHooks();
    }
    
    /**
     * راه‌اندازی هوک‌ها
     */
    private function initHooks() {
        // افزودن منو به پنل ادمین
        add_action('admin_menu', [$this, 'addAdminMenu']);
        
        // بارگذاری اسکریپت‌ها و استایل‌ها
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        
        // هوک‌های سفارشی
        add_action('performance_review_created', [$this, 'notifyEmployee'], 10, 2);
        add_action('performance_review_completed', [$this, 'updateEmployeeRecord'], 10, 2);
    }
    
    /**
     * افزودن منو به پنل مدیریت
     */
    public function addAdminMenu() {
        add_menu_page(
            'ارزیابی عملکرد',
            'ارزیابی عملکرد',
            'manage_performance',
            $this->pluginSlug . '-dashboard',
            [$this, 'renderDashboard'],
            'dashboard',
            5
        );
        
        add_submenu_page(
            $this->pluginSlug . '-dashboard',
            'داشبورد ارزیابی',
            'داشبورد',
            'view_own_review',
            $this->pluginSlug . '-dashboard',
            [$this, 'renderDashboard']
        );
        
        add_submenu_page(
            $this->pluginSlug . '-dashboard',
            'تعریف شاخص‌ها',
            'شاخص‌های عملکرد',
            'manage_performance',
            $this->pluginSlug . '-kpis',
            [$this, 'renderKPIs']
        );
        
        add_submenu_page(
            $this->pluginSlug . '-dashboard',
            'ارزیابی کارکنان',
            'ارزیابی کارکنان',
            'submit_review',
            $this->pluginSlug . '-reviews',
            [$this, 'renderReviews']
        );
        
        add_submenu_page(
            $this->pluginSlug . '-dashboard',
            'گزارشات',
            'گزارشات ارزیابی',
            'view_all_reviews',
            $this->pluginSlug . '-reports',
            [$this, 'renderReports']
        );
    }
    
    /**
     * بارگذاری فایل‌های CSS و JS
     */
    public function enqueueAssets($hook) {
        if (strpos($hook, $this->pluginSlug) !== false) {
            wp_enqueue_style(
                $this->pluginSlug . '-style',
                plugins_url('assets/css/style.css', __FILE__),
                [],
                '1.0.0'
            );
            
            wp_enqueue_script(
                $this->pluginSlug . '-script',
                plugins_url('assets/js/app.js', __FILE__),
                ['jquery'],
                '1.0.0',
                true
            );
            
            wp_localize_script($this->pluginSlug . '-script', 'performanceEval', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('performance_eval_nonce')
            ]);
        }
    }
    
    /**
     * نمایش داشبورد
     */
    public function renderDashboard() {
        include plugin_dir_path(__FILE__) . 'views/dashboard.php';
    }
    
    /**
     * نمایش صفحه KPIها
     */
    public function renderKPIs() {
        include plugin_dir_path(__FILE__) . 'views/kpis.php';
    }
    
    /**
     * نمایش صفحه ارزیابی‌ها
     */
    public function renderReviews() {
        include plugin_dir_path(__FILE__) . 'views/reviews.php';
    }
    
    /**
     * نمایش صفحه گزارشات
     */
    public function renderReports() {
        include plugin_dir_path(__FILE__) . 'views/reports.php';
    }
    
    /**
     * ایجاد جداول دیتابیس
     */
    public function createTables() {
        $charset = DB_CHARSET;
        $collate = DB_COLLATE;
        
        // جدول بررسی‌های عملکرد
        $sql1 = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}performance_reviews (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            employee_id BIGINT UNSIGNED NOT NULL,
            reviewer_id BIGINT UNSIGNED NOT NULL,
            review_period_start DATE NOT NULL,
            review_period_end DATE NOT NULL,
            status ENUM('draft', 'pending', 'completed', 'approved') DEFAULT 'draft',
            self_evaluation_score DECIMAL(5,2),
            manager_score DECIMAL(5,2),
            final_score DECIMAL(5,2),
            strengths TEXT,
            weaknesses TEXT,
            goals_next_period TEXT,
            comments TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employee_id (employee_id),
            KEY reviewer_id (reviewer_id),
            KEY status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};";
        
        // جدول شاخص‌های عملکرد (KPIs)
        $sql2 = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}performance_kpis (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            weight DECIMAL(5,2) DEFAULT 1.00,
            measurement_type ENUM('numeric', 'percentage', 'boolean', 'text') DEFAULT 'numeric',
            target_value VARCHAR(100),
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category),
            KEY is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};";
        
        // جدول بازخوردها
        $sql3 = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}performance_feedbacks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            review_id BIGINT UNSIGNED NOT NULL,
            feedback_provider_id BIGINT UNSIGNED NOT NULL,
            feedback_type ENUM('peer', 'subordinate', 'self', 'manager') NOT NULL,
            kpi_id BIGINT UNSIGNED,
            score DECIMAL(5,2),
            comments TEXT,
            is_anonymous BOOLEAN DEFAULT FALSE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY review_id (review_id),
            KEY feedback_provider_id (feedback_provider_id),
            KEY feedback_type (feedback_type)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};";
        
        // جدول اهداف
        $sql4 = "CREATE TABLE IF NOT EXISTS {$this->db->prefix}performance_goals (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            employee_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            kpi_id BIGINT UNSIGNED,
            target_value VARCHAR(100),
            current_value VARCHAR(100),
            start_date DATE,
            end_date DATE,
            status ENUM('not_started', 'in_progress', 'completed', 'cancelled') DEFAULT 'not_started',
            priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
            progress_percentage DECIMAL(5,2) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employee_id (employee_id),
            KEY status (status),
            KEY priority (priority)
        ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};";
        
        $this->db->exec($sql1);
        $this->db->exec($sql2);
        $this->db->exec($sql3);
        $this->db->exec($sql4);
    }
    
    /**
     * اطلاع‌رسانی به کارمند پس از ایجاد ارزیابی
     */
    public function notifyEmployee($reviewId, $employeeId) {
        // ارسال ایمیل یا نوتیفیکیشن
        $employee = $this->getEmployee($employeeId);
        $subject = 'ارزیابی عملکرد جدید برای شما ثبت شد';
        $message = "کارمند گرامی،\n\nارزیابی عملکرد دوره جدید برای شما ثبت شده است.\nلطفاً به پنل کاربری خود مراجعه کنید.";
        
        // wp_mail($employee['email'], $subject, $message);
    }
    
    /**
     * بروزرسانی رکورد کارمند پس از تکمیل ارزیابی
     */
    public function updateEmployeeRecord($reviewId, $employeeId) {
        // محاسبه میانگین امتیازات و بروزرسانی پروفایل کارمند
        $stmt = $this->db->prepare("
            UPDATE {$this->db->prefix}employees 
            SET last_review_score = (
                SELECT AVG(final_score) 
                FROM {$this->db->prefix}performance_reviews 
                WHERE employee_id = :employee_id AND status = 'completed'
            ),
            last_review_date = NOW()
            WHERE id = :employee_id
        ");
        $stmt->execute([':employee_id' => $employeeId]);
    }
    
    /**
     * دریافت اطلاعات کارمند
     */
    private function getEmployee($employeeId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->db->prefix}employees WHERE id = :id");
        $stmt->execute([':id' => $employeeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * ایجاد یک ارزیابی جدید
     */
    public function createReview($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->db->prefix}performance_reviews 
            (employee_id, reviewer_id, review_period_start, review_period_end, status)
            VALUES (:employee_id, :reviewer_id, :start_date, :end_date, :status)
        ");
        
        $result = $stmt->execute([
            ':employee_id' => $data['employee_id'],
            ':reviewer_id' => $data['reviewer_id'],
            ':start_date' => $data['period_start'],
            ':end_date' => $data['period_end'],
            ':status' => 'draft'
        ]);
        
        if ($result) {
            $reviewId = $this->db->lastInsertId();
            do_action('performance_review_created', $reviewId, $data['employee_id']);
            return $reviewId;
        }
        
        return false;
    }
    
    /**
     * ثبت بازخورد برای ارزیابی
     */
    public function submitFeedback($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->db->prefix}performance_feedbacks 
            (review_id, feedback_provider_id, feedback_type, kpi_id, score, comments, is_anonymous)
            VALUES (:review_id, :provider_id, :type, :kpi_id, :score, :comments, :anonymous)
        ");
        
        return $stmt->execute([
            ':review_id' => $data['review_id'],
            ':provider_id' => $data['provider_id'],
            ':type' => $data['feedback_type'],
            ':kpi_id' => $data['kpi_id'] ?? null,
            ':score' => $data['score'],
            ':comments' => $data['comments'] ?? null,
            ':anonymous' => $data['is_anonymous'] ? 1 : 0
        ]);
    }
}

// نمونه پلاگین
$performancePlugin = new PerformanceEvaluationPlugin($db);
