# 🏢 سیستم اتوماسیون اداری ماژولار - نسخه پلاگین‌محور

## 📋 معرفی

این سیستم یک پلتفرم اتوماسیون اداری کامل برای شرکت‌های ایرانی است که با الهام از معماری وردپرس، **ماژولار و قابل توسعه** طراحی شده است. شما می‌توانید امکانات جدید را به صورت **پکیج/پلاگین** از مارکت‌پلیس خریداری و نصب کنید.

---

## 🎯 ویژگی‌های کلیدی

### 1. **سیستم پلاگین‌محور (مشابه وردپرس)**
- ✅ نصب و حذف پلاگین‌ها بدون تغییر در هسته اصلی
- ✅ فعال‌سازی/غیرفعال‌سازی آنی
- ✅ سیستم هوک (Hook) برای توسعه‌دهندگان
- ✅ بروزرسانی خودکار از مارکت‌پلیس
- ✅ بررسی وابستگی‌ها و سازگاری

### 2. **مارکت‌پلیس داخلی**
- 🛒 خرید و دانلود پلاگین‌های جدید
- 📦 مدیریت لایسنس و مجوزها
- 🔔 اطلاع‌رسانی بروزرسانی‌ها
- ⭐ امتیازدهی و نظرات کاربران

### 3. **مدیریت منابع انسانی کامل**
- 👥 مدیریت کارکنان و پرونده پرسنلی
- 📊 ارزیابی عملکرد (KPI, OKR)
- 🎓 آموزش و توسعه مهارت‌ها
- 💰 حقوق و دستمزد مطابق قانون کار ۱۴۰۵
- 📅 حضور و غیاب و مرخصی‌ها
- 🏠 وام و مساعده
- 🪑 اموال و تجهیزات

### 4. **استانداردهای ایران - سال ۱۴۰۵**
- 💵 محاسبه حقوق بر اساس نرخ‌های ۱۴۰۵
- 📊 مالیات پلکانی مطابق قانون
- 🏥 بیمه تأمین اجتماعی (سهم کارمند و کارفرما)
- 📝 انواع قرارداد (رسمی، موقت، پاره‌وقت)
- 🎁 حق اولاد، مسکن، بن خواربار

---

## 📁 ساختار پروژه

```
automation/
├── core/                      # هسته اصلی سیستم
│   ├── PluginManager.php      # مدیریت پلاگین‌ها
│   ├── Database.php           # کلاس دیتابیس
│   ├── Hooks.php              # سیستم هوک‌ها
│   └── Loader.php             # بارگذار اولیه
│
├── plugins/                   # پوشه پلاگین‌ها
│   ├── performance-evaluation/
│   │   ├── plugin.json        # Manifest پلاگین
│   │   ├── performance-evaluation.php
│   │   ├── install.php
│   │   ├── activate.php
│   │   ├── deactivate.php
│   │   ├── uninstall.php
│   │   ├── includes/
│   │   ├── views/
│   │   ├── assets/
│   │   └── languages/
│   └── [other-plugins]/
│
├── marketplace/               # ارتباط با مارکت‌پلیس
│   ├── api-client.php
│   └── license-manager.php
│
├── modules/                   # ماژول‌های اصلی (هسته)
│   ├── auth/
│   ├── hr/
│   ├── payroll/
│   ├── attendance/
│   └── reports/
│
├── config/
│   └── database.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── uploads/                   # فایل‌های آپلود شده
│
├── database.sql               # اسکریپت پایگاه داده
├── index.php                  # نقطه ورود اصلی
└── README.md                  # این فایل
```

---

## 🚀 نصب و راه‌اندازی

### پیش‌نیازها
- PHP 7.4 یا بالاتر
- MySQL 5.7 یا بالاتر / MariaDB 10.3+
- Web Server (Apache/Nginx)
- PHP Extensions: PDO, ZIP, cURL, GD, mbstring

### مراحل نصب

#### 1. ایجاد پایگاه داده
```bash
mysql -u root -p < database.sql
```

#### 2. تنظیمات اتصال به دیتابیس
فایل `config/database.php` را ویرایش کنید:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'automation_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');
```

#### 3. تنظیم مجوزهای پوشه
```bash
chmod 755 automation/
chmod 777 automation/uploads/
chmod 777 automation/plugins/
```

#### 4. ورود به سیستم
- آدرس: `http://your-domain/automation`
- نام کاربری: `admin`
- رمز عبور: `admin123`

---

## 📦 ساختار پلاگین

### فایل‌های ضروری هر پلاگین

#### 1. `plugin.json` - Manifest پلاگین
```json
{
    "slug": "your-plugin-name",
    "name": "نام پلاگین",
    "version": "1.0.0",
    "description": "توضیحات پلاگین",
    "author": "توسعه‌دهنده",
    "min_core_version": "1.0.0",
    "requires_php": "7.4",
    "license": "GPL-2.0+",
    "tables": ["table1", "table2"],
    "capabilities": {...},
    "menu": {...}
}
```

#### 2. `your-plugin.php` - فایل اصلی
```php
<?php
if (!defined('AUTOMATION_CORE')) die('دسترسی مستقیم مجاز نیست');

class YourPlugin {
    public function __construct($db) {
        $this->db = $db;
        $this->initHooks();
    }
    
    private function initHooks() {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('custom_hook', [$this, 'customFunction']);
    }
}

new YourPlugin($db);
```

#### 3. `install.php` - نصب‌کننده
```php
class PluginInstaller {
    public function install($manifest) {
        // ایجاد جداول
        // افزودن داده‌های اولیه
        return true;
    }
}
```

#### 4. `activate.php` - فعال‌ساز
```php
class PluginActivator {
    public function activate() {
        // کدهای زمان فعال‌سازی
    }
}
```

#### 5. `deactivate.php` - غیرفعال‌ساز
```php
class PluginDeactivator {
    public function deactivate() {
        // کدهای زمان غیرفعال‌سازی
    }
}
```

#### 6. `uninstall.php` - حذف‌کننده
```php
class PluginUninstaller {
    public function uninstall() {
        // حذف جداول و داده‌ها
    }
}
```

---

## 🔌 API مدیریت پلاگین

### متدهای اصلی PluginManager

```php
$pluginManager = new PluginManager($db);

// دریافت لیست پلاگین‌های نصب شده
$plugins = $pluginManager->getInstalledPlugins();

// دریافت پلاگین‌های فعال
$activePlugins = $pluginManager->getActivePlugins();

// نصب پلاگین از فایل ZIP
$pluginManager->installPlugin($zipFile, 'plugin-slug');

// فعال‌سازی پلاگین
$result = $pluginManager->activatePlugin('plugin-slug');

// غیرفعال‌سازی پلاگین
$result = $pluginManager->deactivatePlugin('plugin-slug');

// حذف پلاگین
$result = $pluginManager->uninstallPlugin('plugin-slug');

// بررسی بروزرسانی
$updates = $pluginManager->checkForUpdates();

// بروزرسانی پلاگین
$result = $pluginManager->updatePlugin('plugin-slug', $licenseKey);

// دانلود از مارکت‌پلیس
$zipFile = $pluginManager->downloadPlugin($pluginId, $licenseKey);
```

---

## 🎣 سیستم هوک (Hook System)

### افزودن هوک (Action)
```php
// در پلاگین
add_action('performance_review_created', 'myCustomFunction', 10, 2);

function myCustomFunction($reviewId, $employeeId) {
    // کد سفارشی شما
    sendNotification($employeeId);
}
```

### اجرای هوک
```php
// در هسته یا پلاگین دیگر
do_action('performance_review_created', $reviewId, $employeeId);
```

### فیلترها (Filters)
```php
// تغییر مقدار با فیلتر
$score = apply_filters('calculate_final_score', $baseScore, $employeeId);

// تعریف فیلتر
add_filter('calculate_final_score', 'modifyScore', 10, 2);

function modifyScore($score, $employeeId) {
    return $score * 1.1; // 10% افزایش
}
```

---

## 🛒 مارکت‌پلیس

### اتصال به مارکت‌پلیس

```php
$marketplaceUrl = 'https://marketplace.automation.ir/api/';

// دریافت لیست پلاگین‌های موجود
$response = wp_remote_get($marketplaceUrl . 'plugins');

// خرید و دانلود
$downloadUrl = $marketplaceUrl . 'plugins/' . $pluginId . '/download';
```

### ساختار API مارکت‌پلیس

| Endpoint | Method | توضیحات |
|----------|--------|---------|
| `/plugins` | GET | لیست همه پلاگین‌ها |
| `/plugins/{id}` | GET | اطلاعات یک پلاگین |
| `/plugins/{id}/download` | POST | دانلود پلاگین |
| `/plugins/{slug}/check-update` | POST | بررسی بروزرسانی |
| `/licenses/validate` | POST | بررسی لایسنس |

---

## 📊 پایگاه داده

### جداول اصلی

#### `plugins` - اطلاعات پلاگین‌ها
```sql
CREATE TABLE plugins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(20),
    author VARCHAR(100),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    installed_at DATETIME,
    activated_at DATETIME,
    updated_at DATETIME
);
```

#### `options` - تنظیمات سیستم
```sql
CREATE TABLE options (
    option_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_name VARCHAR(191) UNIQUE NOT NULL,
    option_value LONGTEXT,
    autoload BOOLEAN DEFAULT TRUE
);
```

---

## 🔐 امنیت

### بهترین روش‌ها

1. **Prepared Statements** - جلوگیری از SQL Injection
```php
$stmt = $db->prepare("SELECT * FROM employees WHERE id = :id");
$stmt->execute([':id' => $employeeId]);
```

2. **CSRF Protection** - توکن امنیتی فرم‌ها
```php
$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;
```

3. **Nonce Verification** - بررسی درخواست‌ها
```php
if (!verifyNonce($submittedNonce, 'action_name')) {
    die('خطای امنیتی');
}
```

4. **Capability Check** - بررسی دسترسی
```php
if (!currentUserCan('manage_performance')) {
    die('دسترسی ندارید');
}
```

5. **File Upload Security** - بررسی فایل‌های آپلودی
```php
$allowedTypes = ['application/zip'];
if (!in_array($_FILES['file']['type'], $allowedTypes)) {
    die('نوع فایل مجاز نیست');
}
```

---

## 🧩 پلاگین‌های نمونه

### 1. ارزیابی عملکرد (Performance Evaluation)
- ✅ تعریف KPI و اهداف
- ✅ بازخورد 360 درجه
- ✅ گزارش‌گیری پیشرفته

### 2. آموزش و توسعه (Training & Development)
- 📚 مدیریت دوره‌های آموزشی
- 📝 آزمون و ارزیابی
- 📈 پیگیری پیشرفت

### 3. جلسات و تقویم (Meetings & Calendar)
- 📅 برنامه‌ریزی جلسات
- 🔔 یادآوری خودکار
- 📝 صورتجلسات

### 4. چارت سازمانی (Organizational Chart)
- 🏢 نمایش سلسله مراتب
- 👥 گزارش‌دهی
- 🔄 جابجایی پرسنل

---

## 🔄 بروزرسانی سالانه

### بروزرسانی نرخ‌های حقوق و دستمزد

برای سال جدید، کافیست جدول `salary_standards` را بروزرسانی کنید:

```sql
UPDATE salary_standards SET 
    base_salary = 85000000,
    housing_allowance = 18000000,
    food_allowance = 12000000,
    child_allowance = 8000000,
    year = 1406
WHERE year = 1405;
```

یا از پنل مدیریت:
```
تنظیمات → حقوق و دستمزد → بروزرسانی نرخ‌ها
```

---

## 📝 مجوز و لایسنس

- **هسته اصلی**: GPL-2.0+
- **پلاگین‌ها**: بسته به توسعه‌دهنده
- **مستندات**: CC-BY-SA-4.0

---

## 🤝 مشارکت در توسعه

### راهنمای توسعه پلاگین

1. Fork کردن مخزن
2. ایجاد برنچ جدید (`feature/my-plugin`)
3. توسعه پلاگین
4. تست کامل
5. ارسال Pull Request

### استانداردهای کدنویسی

- رعایت PSR-12 برای PHP
- استفاده از TailwindCSS برای UI
- پشتیبانی کامل از RTL
- کامنت‌گذاری فارسی/انگلیسی

---

## 📞 پشتیبانی

- 📧 ایمیل: support@automation.ir
- 🌐 وب‌سایت: https://automation.ir
- 📚 مستندات: https://docs.automation.ir
- 💬 تالار گفتگو: https://forum.automation.ir

---

## 🗺️ نقشه راه

### نسخه 1.0 (کنونی)
- ✅ هسته ماژولار
- ✅ مدیریت پلاگین‌ها
- ✅ ماژول منابع انسانی
- ✅ حقوق و دستمزد ۱۴۰۵

### نسخه 1.1 (آتی)
- 🔄 مارکت‌پلیس آنلاین
- 🔄 سیستم تیکتینگ
- 🔄 امضای دیجیتال

### نسخه 2.0 (آتی)
- 🔄 API RESTful کامل
- 🔄 اپلیکیشن موبایل
- 🔄 هوش مصنوعی در ارزیابی

---

## 📄 Changelog

### نسخه 1.0.0
- اولین انتشار رسمی
- سیستم پلاگین‌محور کامل
- ماژول ارزیابی عملکرد نمونه
- پشتیبانی از استانداردهای ۱۴۰۵

---

**تهیه شده توسط تیم توسعه اتوماسیون اداری**  
**آخرین بروزرسانی: ۱۴۰۵**
