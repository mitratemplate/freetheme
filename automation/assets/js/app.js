// JavaScript اصلی سامانه اتوماسیون اداری
// Automation Pro v3.0 - Enterprise Edition

// نمایش اعداد به فارسی
function toPersianNum(num) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return num.toString().replace(/\d/g, x => persianDigits[x]);
}

// فرمت کردن پول
function formatMoney(amount) {
    return new Intl.NumberFormat('fa-IR').format(amount) + ' ریال';
}

// تأییدیه عملیات
function confirmAction(message) {
    return confirm(message);
}

// نمایش پیام Toast
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 left-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 transition-all transform translate-x-0 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// AJAX Request Helper
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

// بارگذاری پویای محتوا
async function loadContent(url, targetId) {
    try {
        const response = await fetch(url);
        const html = await response.text();
        document.getElementById(targetId).innerHTML = html;
    } catch (error) {
        console.error('Load Error:', error);
        showToast('خطا در بارگذاری محتوا', 'error');
    }
}

// Export به Excel
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    
    const rows = table.querySelectorAll('tr');
    for (let row of rows) {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        for (let col of cols) {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        }
        csv.push(rowData.join(','));
    }
    
    const csvContent = '\uFEFF' + csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
}

// Print صفحه
function printPage() {
    window.print();
}

// تاریخ شمسی
function toJalali(date) {
    // تبدیل ساده تاریخ میلادی به شمسی
    const d = new Date(date);
    const year = d.getFullYear();
    const month = d.getMonth() + 1;
    const day = d.getDate();
    
    // الگوریتم ساده شده (برای استفاده واقعی از کتابخانه jalaali-js استفاده کنید)
    const jalaliYear = year - 621;
    return `${jalaliYear}/${month.toString().padStart(2, '0')}/${day.toString().padStart(2, '0')}`;
}

// اعتبارسنجی کد ملی
function validateNationalCode(code) {
    if (!/^\d{10}$/.test(code)) return false;
    
    const check = parseInt(code[9]);
    let sum = 0;
    
    for (let i = 0; i < 9; i++) {
        sum += parseInt(code[i]) * (10 - i);
    }
    
    const remainder = sum % 11;
    return (remainder < 2 && check === remainder) || (remainder >= 2 && check === 11 - remainder);
}

// Debounce برای جستجو
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ذخیره در LocalStorage
function saveToLocal(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

// بازیابی از LocalStorage
function getFromLocal(key) {
    const value = localStorage.getItem(key);
    return value ? JSON.parse(value) : null;
}

// Dark Mode Toggle
function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    saveToLocal('darkMode', isDark);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // بازیابی حالت Dark Mode
    const isDark = getFromLocal('darkMode');
    if (isDark) {
        document.documentElement.classList.add('dark');
    }
    
    // افزودن کلاس persian-num به تمام اعداد
    document.querySelectorAll('.persian-number').forEach(el => {
        el.textContent = toPersianNum(el.textContent);
    });
    
    console.log('Automation Pro v3.0 loaded successfully');
});
