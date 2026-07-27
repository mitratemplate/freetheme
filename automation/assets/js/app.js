// Main Application JavaScript
// Iranian Office Automation System - 1405

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('button.md\\:hidden');
    const sidebar = document.querySelector('aside');
    
    if (mobileMenuButton && sidebar) {
        mobileMenuButton.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('h-full');
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('آیا از انجام این عملیات اطمینان دارید؟')) {
                e.preventDefault();
            }
        });
    });
    
    // Number input formatting (Persian numbers)
    const numberInputs = document.querySelectorAll('input[type="number"], input[name*="amount"], input[name*="salary"]');
    numberInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    
    // National code validation (Iranian)
    const nationalCodeInput = document.querySelector('input[name="national_code"]');
    if (nationalCodeInput) {
        nationalCodeInput.addEventListener('blur', function() {
            const code = this.value;
            if (code.length === 10 && !isValidIranianNationalCode(code)) {
                alert('کد ملی وارد شده معتبر نیست.');
                this.focus();
            }
        });
    }
    
    // Form validation for required fields
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    
                    setTimeout(function() {
                        field.classList.remove('border-red-500');
                    }, 2000);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('لطفاً تمام فیلدهای الزامی را پر کنید.');
            }
        });
    });
});

// Iranian National Code Validation
function isValidIranianNationalCode(code) {
    if (!/^\d{10}$/.test(code)) return false;
    
    const check = parseInt(code[9]);
    const sum = Array.from(code.substring(0, 9), d => +d)
        .reduce((x, v, i) => x + (10 - i) * v) % 11;
    
    return (sum < 2 && check === sum) || (sum >= 2 && check + sum === 11);
}

// Format number with commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Convert Persian numbers to English
function toEnglishDigits(str) {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str.replace(/[۰-۹]/g, function(w) {
        return persianDigits.indexOf(w);
    });
}

// AJAX helper for API calls
async function apiCall(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 left-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(function() {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(function() {
            notification.remove();
        }, 500);
    }, 3000);
}

// Print payroll slip
function printPayroll(payrollId) {
    window.open(`/automation/modules/payroll/print.php?id=${payrollId}`, '_blank', 'width=800,height=600');
}

// Export to Excel
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length - 1; j++) { // Skip last column (actions)
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        
        csv.push(row.join(','));
    }
    
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
