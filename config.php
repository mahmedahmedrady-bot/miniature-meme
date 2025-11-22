<?php
session_start();

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'factory_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// دوال المساعدة
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasPermission($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}
?>