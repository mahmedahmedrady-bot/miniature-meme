<?php
include '../header.php';

// حساب التكاليف
$current_month = date('Y-m');

// تكلفة المخزون الحالي
$stmt = $pdo->query("SELECT SUM(quantity * cost_price) as total_cost FROM products");
$inventory_cost = $stmt->fetch()['total_cost'] ?? 0;

// تكلفة المبيعات الشهر
$stmt = $pdo->prepare("SELECT SUM(ii.quantity * p.cost_price) as cost_of_sales 
                       FROM invoice_items ii 
                       JOIN products p ON ii.product_id = p.id 
                       JOIN invoices i ON ii.invoice_id = i.id 
                       WHERE DATE_FORMAT(i.created_at, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$cost_of_sales = $stmt->fetch()['cost_of_sales'] ?? 0;

// المصاريف الشهر
$stmt = $pdo->prepare("SELECT SUM(amount) as total_expenses FROM expenses WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$monthly_expenses = $stmt->fetch()['total_expenses'] ?? 0;

// الرواتب الشهر
$stmt = $pdo->prepare("SELECT SUM(net_salary) as total_salaries FROM salaries WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$monthly_salaries = $stmt->fetch()['total_salaries'] ?? 0;

// إجمالي التكاليف
$total_costs = $cost_of_sales + $monthly_expenses + $monthly_salaries;
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">حساب التكاليف</h1>

    <!-- إحصائيات التكاليف -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- تكلفة المخزون -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">تكلفة المخزون</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($inventory_cost, 2); ?> ر.س</h3>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- تكلفة المبيعات -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">تكلفة المبيعات</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($cost_of_sales, 2); ?> ر.س</h3>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- المصاريف التشغيلية -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">المصاريف التشغيلية</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($monthly_expenses, 2); ?> ر.س</h3>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- تكاليف الرواتب -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">تكاليف الرواتب</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($monthly_salaries, 2); ?> ر.س</h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- تحليل التكاليف -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- توزيع التكاليف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">توزيع التكاليف الشهرية</h3>
            <canvas id="costDistributionChart" width="400" height="200"></canvas>
        </div>

        <!-- ملخص التكاليف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">ملخص التكاليف</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                    <span class="text-red-700">تكلفة المبيعات</span>
                    <span class="font-bold"><?php echo number_format($cost_of_sales, 2); ?> ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                    <span class="text-yellow-700">المصاريف التشغيلية</span>
                    <span class="font-bold"><?php echo number_format($monthly_expenses, 2); ?> ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                    <span class="text-purple-700">تكاليف الرواتب</span>
                    <span class="font-bold"><?php echo number_format($monthly_salaries, 2); ?> ر.س</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-100 rounded-lg border-t">
                    <span class="text-gray-800 font-bold">الإجمالي</span>
                    <span class="font-bold text-lg"><?php echo number_format($total_costs, 2); ?> ر.س</span>
                </div>
            </div>
        </div>
    </div>

    <!-- تحليل الربحية -->
    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">تحليل الربحية</h3>
        <?php
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as revenue FROM invoices WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$current_month]);
        $revenue = $stmt->fetch()['revenue'] ?? 0;
        
        $gross_profit = $revenue - $cost_of_sales;
        $net_profit = $gross_profit - $monthly_expenses - $monthly_salaries;
        
        $gross_margin = $revenue > 0 ? ($gross_profit / $revenue) * 100 : 0;
        $net_margin = $revenue > 0 ? ($net_profit / $revenue) * 100 : 0;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600"><?php echo number_format($revenue, 2); ?></div>
                <div class="text-sm text-gray-600">الإيرادات</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600"><?php echo number_format($gross_profit, 2); ?></div>
                <div class="text-sm text-gray-600">الربح الإجمالي</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600"><?php echo number_format($net_profit, 2); ?></div>
                <div class="text-sm text-gray-600">الربح الصافي</div>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600"><?php echo number_format($net_margin, 1); ?>%</div>
                <div class="text-sm text-gray-600">هامش الربح</div>
            </div>
        </div>
    </div>
</div>

<script>
// مخطط توزيع التكاليف
const costDistributionChart = new Chart(document.getElementById('costDistributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['تكلفة المبيعات', 'المصاريف التشغيلية', 'الرواتب'],
        datasets: [{
            data: [<?php echo $cost_of_sales; ?>, <?php echo $monthly_expenses; ?>, <?php echo $monthly_salaries; ?>],
            backgroundColor: ['#ef4444', '#f59e0b', '#8b5cf6'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                rtl: true
            }
        }
    }
});
</script>

<?php include '../footer.php'; ?>