<?php
// دوال عامة للمشروع

// تنظيف المدخلات
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// حساب الربح
function calculateProfit($costPrice, $sellingPrice, $quantity = 1) {
    return ($sellingPrice - $costPrice) * $quantity;
}

// تنسيق التاريخ
function formatDate($date) {
    return date('Y-m-d H:i:s', strtotime($date));
}

// التحقق من البريد الإلكتروني
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// إنشاء كود عشوائي
function generateRandomCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// حساب العمر
function getAge($birthDate) {
    $birthDate = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($birthDate);
    return $age->y;
}
?>