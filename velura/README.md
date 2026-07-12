# 🛍️ Velura - قالب فروشگاه پوشاک زنانه

> قالب مدرن و واکنش‌پذیر برای فروشگاه‌های پوشاک زنانه آنلاین

![Velura](https://img.shields.io/badge/Version-1.0.0-blue) ![License](https://img.shields.io/badge/License-MIT-green) ![Status](https://img.shields.io/badge/Status-Active-success)

## ✨ ویژگی‌های اصلی

### 🎨 طراحی حرفه‌ای
- طراحی مدرن و جذاب با رنگ‌های گرم (صورتی و بنفش)
- رابط کاربری تمیز و حرفه‌ای
- انیمیشن‌های روان و جذاب
- پشتیبانی کامل RTL برای فارسی

### 📱 ریسپانسیو (Responsive)
- طراحی Mobile-First
- بهینه‌سازی کامل برای تمام اندازه‌های صفحه
- منوی همبرگری برای موبایل
- تجربه کاربری شبیه اپلیکیشن‌های فروشگاهی

### 🎠 کمپوننت‌های جلب‌کننده
- **Owl Carousel**: اسلایدرهای تصویری و محصولات
- **Modal Dialogs**: برای ورود، ثبت‌نام، سبد خرید، علاقه‌مندی‌ها و مقایسه
- **Product Cards**: کارت‌های محصول با اثرات Hover
- **Category Grid**: نمایش دسته‌بندی‌ها

### 🛒 عملکردهای فروشگاهی
- ✅ **سبد خرید**: افزودن/حذف محصولات، تغییر تعداد
- ✅ **علاقه‌مندی**: ذخیره محصولات مورد علاقه
- ✅ **مقایسه**: مقایسه ویژگی‌های محصولات
- ✅ **جستجو**: جستجوی محصولات
- ✅ **ورود/ثبت‌نام**: سیستم احراز هویت

### 🎯 بخش‌های اصلی
- Header و Navigation
- Hero Slider
- Product Categories
- Featured Products
- Special Offers Carousel
- About Section
- Contact Section
- Newsletter Subscription
- Footer

## 🛠️ تکنولوژی‌های مورد استفاده

### فریم‌ورک‌ها و کتابخانه‌ها
```json
{
  "Tailwind CSS": "طراحی و استایل‌دهی",
  "Owl Carousel": "اسلایدرها و کروسل‌ها",
  "Font Awesome": "آیکون‌های زیبا",
  "jQuery": "تعاملات JavaScript",
  "Vanilla JS": "عملکردهای سفارشی"
}
```

### نسخه‌های استفاده شده
- **Tailwind CSS 3.x**: CDN
- **Owl Carousel 2.3.4**: CDN
- **Font Awesome 6.4.0**: CDN
- **jQuery 3.6.0**: CDN

## 📂 ساختار فایل‌ها

```
velura/
├── index.html           # فایل HTML اصلی
├── script.js            # توابع JavaScript
├── styles.css           # استایل‌های اضافی
├── README.md            # این فایل
└── showcase.html        # صفحه معرفی قالب
```

## 🚀 نحوه استفاده

### 1. دانلود فایل‌ها
تمام فایل‌های قالب را دانلود کنید:
- `index.html`
- `script.js`
- `styles.css`

### 2. باز کردن در مرورگر
فایل `index.html` را در مرورگر خود باز کنید یا روی سرور قرار دهید.

### 3. سفارشی‌سازی
- **رنگ‌ها**: رنگ اصلی را در `styles.css` تغییر دهید
  ```css
  :root {
      --primary: #d946ef;
      --primary-dark: #ec4899;
  }
  ```
- **محصولات**: محصولات را در `index.html` جایگزین کنید
- **متن**: متن‌های فارسی را تغییر دهید
- **تصاویر**: لینک‌های تصاویر را آپدیت کنید

## 🎨 رنگ‌های استفاده شده

| نام | رنگ | کد هگز |
|-----|-----|--------|
| Primary | صورتی | `#d946ef` |
| Primary Dark | صورتی تیره | `#ec4899` |
| Success | سبز | `#22c55e` |
| Error | قرمز | `#ef4444` |
| Gray | خاکستری | `#6b7280` |

## 📱 پشتیبانی اندازه‌های صفحه

| دستگاه | عرض |
|--------|-----|
| Mobile | < 640px |
| Tablet | 640px - 1024px |
| Desktop | > 1024px |
| Large Desktop | > 1280px |

## ✨ ویژگی‌های تعاملی

### سبد خرید
```javascript
// افزودن محصول
addToCart("نام محصول", قیمت);

// حذف محصول
removeFromCart(productId);

// تغییر تعداد
changeQuantity(productId, تغییر);
```

### علاقه‌مندی‌ها
```javascript
// تبدیل علاقه‌مندی
toggleFavorite(button);
```

### مودال‌ها
```javascript
// باز کردن مودال
openModal('modalId');

// بستن مودال
closeModal('modalId');

// تبدیل مودال
switchModal('closeId', 'openId');
```

## 🔧 سفارشی‌سازی پیشرفته

### تغییر فونت
```html
<!-- افزودن فونت Google -->
<link href="https://fonts.googleapis.com/css2?family=YOUR_FONT:wght@400;700&display=swap" rel="stylesheet">

<!-- تغییر در CSS -->
body {
    font-family: 'YOUR_FONT', sans-serif;
}
```

### اضافه کردن زبان‌های بیشتر
قالب کاملاً به فارسی است اما می‌توانید:
1. تمام متن‌های فارسی را جایگزین کنید
2. جهت `dir="rtl"` را تغییر دهید به `dir="ltr"`
3. CSS را برای جهت چپ به راست تنظیم کنید

## 🐛 حل مشکلات

### مشکل: Carousel کار نمی‌کند
**حل**: اطمینان حاصل کنید jQuery و Owl Carousel درست بارگذاری شده‌اند:
```html
<!-- بررسی CDN‌ها -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
```

### مشکل: استایل‌ها درست نمایش داده نمی‌شوند
**حل**: Tailwind CSS CDN را بررسی کنید:
```html
<script src="https://cdn.tailwindcss.com"></script>
```

### مشکل: مودال‌ها باز نمی‌شوند
**حل**: فایل `script.js` را بررسی کنید و اطمینان حاصل کنید کلیدهای درست است

## 📊 نمودار صفحه

```
┌─────────────────────────┐
│      Header/Nav         │
├─────────────────────────┤
│    Hero Carousel        │
├─────────────────────────┤
│    Categories Grid      │
├─────────────────────────┤
│  Featured Products      │
├─────────────────────────┤
│  Special Offers Slider  │
├─────────────────────────┤
│   About Section         │
├─────────────────────────┤
│   Contact Section       │
├─────────────────────────┤
│   Newsletter            │
├─────────────────────────┤
│      Footer             │
└─────────────────────────┘
```

## 🎯 بهینه‌سازی عملکرد

### نکات مهم
- ✅ تصاویر بهینه‌سازی شده (WebP + Fallback)
- ✅ Lazy loading برای تصاویر
- ✅ Minified CSS و JavaScript
- ✅ CDN برای کتابخانه‌ها
- ✅ Smooth animations (60fps)

### توصیه‌ها
1. تصاویر خود را فشرده‌سازی کنید
2. کش مرورگر را فعال کنید
3. GZIP compression استفاده کنید
4. CDN استفاده کنید برای میزبانی تصاویر

## 📝 لایسنس

MIT License - می‌توانید آزادانه استفاده کنید و تغییر دهید

## 👨‍💻 ایجاد‌کننده

**Mitra Template**
- وب‌سایت: https://mitratemplate.ir
- ایمیل: info@mitratemplate.ir

## 🤝 مشارکت

اگر مشکلی پیدا کردید یا پیشنهادی دارید، لطفاً issue ایجاد کنید یا PR ارسال کنید.

## 🎁 مزایای استفاده

✅ **رایگان**: بدون هیچ هزینه اضافی
✅ **متن‌باز**: کد کاملاً قابل دسترسی و تغییر
✅ **پشتیبانی فارسی**: کاملاً بهینه‌شده برای فارسی
✅ **واکنش‌پذیر**: کار کامل بر روی تمام دستگاه‌ها
✅ **مدرن**: استفاده از آخرین تکنولوژی‌ها
✅ **سریع**: بهینه‌سازی شده برای سرعت

---

**برای اطلاعات بیشتر و نمونه‌های زنده، صفحه `showcase.html` را بازدید کنید.**

**نسخه**: 1.0.0  
**آخرین به‌روزرسانی**: 12 مهر 1402  
**وضعیت**: ✅ فعال و تحت پشتیبانی
