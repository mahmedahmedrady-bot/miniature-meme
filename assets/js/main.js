// دوال JavaScript العامة للنظام

// تهيئة النظام
document.addEventListener('DOMContentLoaded', function() {
    initSystem();
});

function initSystem() {
    // تهيئة التواريخ
    initDates();
    
    // تهيئة الأحداث
    initEvents();
    
    // تحميل الإحصائيات
    loadStats();
}

function initDates() {
    // تعيين التاريخ الحالي للحقول التي تحتاجه
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = new Date().toISOString().split('T')[0];
        }
    });
}

function initEvents() {
    // منع إرسال النماذج عند الضغط على Enter
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.type !== 'textarea') {
                e.preventDefault();
            }
        });
    });
}

// دوال التحقق من الصحة
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\+]?[0-9]{10,15}$/;
    return re.test(phone);
}

function validateNumber(input) {
    const value = parseFloat(input.value);
    if (isNaN(value) || value < 0) {
        input.value = 0;
        showError('يرجى إدخال رقم صحيح موجب');
        return false;
    }
    return true;
}

// دوال التنبيهات
function showSuccess(message) {
    Swal.fire({
        title: 'تم بنجاح!',
        text: message,
        icon: 'success',
        confirmButtonText: 'حسناً'
    });
}

function showError(message) {
    Swal.fire({
        title: 'خطأ!',
        text: message,
        icon: 'error',
        confirmButtonText: 'حسناً'
    });
}

function showWarning(message) {
    Swal.fire({
        title: 'تحذير!',
        text: message,
        icon: 'warning',
        confirmButtonText: 'حسناً'
    });
}

// دوال التحميل
function showLoading() {
    Swal.fire({
        title: 'جاري التحميل...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

// دوال API
async function apiCall(url, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data) {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'حدث خطأ في الاتصال');
        }
        
        return result;
    } catch (error) {
        showError(error.message);
        throw error;
    }
}

// دوال التنسيق
function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-SA', {
        style: 'currency',
        currency: 'SAR'
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('ar-SA');
}

function formatDateTime(date) {
    return new Date(date).toLocaleString('ar-SA');
}

// دوال المساعدة
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

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// تصدير الدوال للاستخدام العالمي
window.System = {
    validateEmail,
    validatePhone,
    validateNumber,
    showSuccess,
    showError,
    showWarning,
    showLoading,
    hideLoading,
    apiCall,
    formatCurrency,
    formatDate,
    formatDateTime,
    debounce,
    generateId
};