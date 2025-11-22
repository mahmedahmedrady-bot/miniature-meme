<?php
include '../header.php';

// تاريخ التقرير
$report_date = $_GET['report_date'] ?? date('Y-m-d');

// إجمالي المبيعات حتى التاريخ
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total_sales, SUM(profit) as total_profit FROM invoices WHERE DATE(created_at) <= ?");
$stmt->execute([$report_date]);
$sales_data = $stmt->fetch();

// إجمالي المصاريف حتى التاريخ
$stmt = $pdo->prepare("SELECT SUM(amount) as total_expenses FROM expenses WHERE DATE(date) <= ?");
$stmt->execute([$report_date]);
$expenses_data = $stmt->fetch();

// إجمالي الرواتب حتى التاريخ
$stmt = $pdo->prepare("SELECT SUM(net_salary) as total_salaries FROM salaries WHERE DATE(payment_date) <= ?");
$stmt->execute([$report_date]);
$salaries_data = $stmt->fetch();

// الرصيد الحالي
$total_income = $sales_data['total_sales'] ?? 0;
$total_expenses = ($expenses_data['total_expenses'] ?? 0) + ($salaries_data['total_salaries'] ?? 0);
$current_balance = $total_income - $total_expenses;
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">ملخص مالي شامل</h1>

    <!-- فلتر التاريخ -->
    <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
        <form method="GET" class="flex items-end space-x-4 space-x-reverse">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ التقرير</label>
                <input type="date" name="report_date" value="<?php echo $report_date; ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                <i class="fas fa-filter ml-2"></i>عرض التقرير
            </button>
        </form>
    </div>

    <!-- الإحصائيات الرئيسية -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- إجمالي الدخل -->
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي الدخل</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($total_income, 2); ?> ر.س</p>
        </div>

        <!-- إجمالي المصاريف -->
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-chart-line-down text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي المصاريف</h3>
            <p class="text-2xl font-bold text-red-600"><?php echo number_format($total_expenses, 2); ?> ر.س</p>
        </div>

        <!-- الرصيد الحالي -->
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-scale-balanced text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">الرصيد الحالي</h3>
            <p class="text-2xl font-bold <?php echo $current_balance >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                <?php echo number_format($current_balance, 2); ?> ر.س
            </p>
        </div>
    </div>

    <!-- التفاصيل -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- تفاصيل الدخل -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">تفاصيل الدخل</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-green-700">المبيعات</span>
                    <span class="font-bold"><?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?> ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                    <span class="text-green-700">الأرباح الإجمالية</span>
                    <span class="font-bold"><?php echo number_format($sales_data['total_profit'] ?? 0, 2); ?> ر.س</span>
                </div>
            </div>
        </div>

        <!-- تفاصيل المصاريف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">تفاصيل المصاريف</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <span class="text-red-700">المصاريف التشغيلية</span>
                    <span class="font-bold"><?php echo number_format($expenses_data['total_expenses'] ?? 0, 2); ?> ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <span class="text-red-700">الرواتب</span>
                    <span class="font-bold"><?php echo number_format($salaries_data['total_salaries'] ?? 0, 2); ?> ر.س</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ملخص الأداء -->
    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">ملخص الأداء المالي</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600"><?php echo number_format($current_balance, 2); ?></div>
                <div class="text-sm text-gray-600">الرصيد الحالي</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600"><?php echo number_format($sales_data['total_profit'] ?? 0, 2); ?></div>
                <div class="text-sm text-gray-600">إجمالي الأرباح</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600"><?php echo $total_income > 0 ? number_format(($sales_data['total_profit'] / $total_income) * 100, 1) : 0; ?>%</div>
                <div class="text-sm text-gray-600">هامش الربح</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600"><?php echo $total_income > 0 ? number_format(($total_expenses / $total_income) * 100, 1) : 0; ?>%</div>
                <div class="text-sm text-gray-600">نسبة المصاريف</div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>