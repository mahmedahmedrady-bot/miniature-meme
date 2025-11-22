<?php
if (!isLoggedIn()) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة المصنع</title>
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- الشريط العلوي -->
    <nav class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4 space-x-reverse">
                <h1 class="text-xl font-bold"><i class="fas fa-industry ml-2"></i>نظام إدارة المصنع</h1>
            </div>
            <div class="flex items-center space-x-4 space-x-reverse">
                <span class="text-sm">مرحباً، <?php echo $_SESSION['user_name'] ?? 'مستخدم'; ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm">
                    <i class="fas fa-sign-out-alt ml-1"></i>خروج
                </a>
            </div>
        </div>
    </nav>

    <!-- القائمة الجانبية -->
    <div class="flex">
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="index.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-blue-100 text-blue-600' : ''; ?>">
                            <i class="fas fa-tachometer-alt ml-3"></i>
                            لوحة التحكم
                        </a>
                    </li>
                    
                    <!-- إدارة المخزون -->
                    <li class="mt-4">
                        <span class="text-sm text-gray-500 px-3">المخزون</span>
                    </li>
                    <li>
                        <a href="inventory/inventory.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-boxes ml-3"></i>
                            عرض المخزون
                        </a>
                    </li>
                    <li>
                        <a href="inventory/add_product.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-plus ml-3"></i>
                            إضافة منتج
                        </a>
                    </li>
                    
                    <!-- المبيعات -->
                    <li class="mt-4">
                        <span class="text-sm text-gray-500 px-3">المبيعات</span>
                    </li>
                    <li>
                        <a href="sales/sales.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-cash-register ml-3"></i>
                            إنشاء فاتورة
                        </a>
                    </li>
                    <li>
                        <a href="sales/invoices_list.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-receipt ml-3"></i>
                            سجل الفواتير
                        </a>
                    </li>
                    
                    <!-- المحاسبة -->
                    <li class="mt-4">
                        <span class="text-sm text-gray-500 px-3">المحاسبة</span>
                    </li>
                    <li>
                        <a href="accounting/profit.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-chart-line ml-3"></i>
                            حساب الأرباح
                        </a>
                    </li>
                    
                    <!-- المصاريف -->
                    <li class="mt-4">
                        <span class="text-sm text-gray-500 px-3">المصاريف</span>
                    </li>
                    <li>
                        <a href="expenses/expenses.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-money-bill-wave ml-3"></i>
                            تسجيل المصاريف
                        </a>
                    </li>
                    
                    <!-- التقارير -->
                    <li class="mt-4">
                        <span class="text-sm text-gray-500 px-3">التقارير</span>
                    </li>
                    <li>
                        <a href="reports/sales_report.php" class="flex items-center p-3 text-gray-700 hover:bg-blue-50 rounded">
                            <i class="fas fa-chart-bar ml-3"></i>
                            تقارير المبيعات
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- المحتوى الرئيسي -->
        <main class="flex-1 p-6">