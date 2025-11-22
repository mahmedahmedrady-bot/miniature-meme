<?php
include '../header.php';

// حساب الإحصائيات
$today = date('Y-m-d');
$month = date('Y-m');
$year = date('Y');

// إجمالي المبيعات اليوم
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total, SUM(profit) as profit FROM invoices WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$today_stats = $stmt->fetch();

// إجمالي المبيعات الشهر
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total, SUM(profit) as profit FROM invoices WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
$stmt->execute([$month]);
$month_stats = $stmt->fetch();

// إجمالي المبيعات السنة
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total, SUM(profit) as profit FROM invoices WHERE YEAR(created_at) = ?");
$stmt->execute([$year]);
$year_stats = $stmt->fetch();

// إجمالي المصاريف الشهر
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$month]);
$month_expenses = $stmt->fetch();

// الربح الصافي
$net_profit = $month_stats['profit'] - $month_expenses['total'];
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">حساب الأرباح</h1>

    <!-- الإحصائيات السريعة -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- المبيعات اليوم -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">المبيعات اليوم</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($today_stats['total'] ?? 0, 2); ?> ر.س</h3>
                    <p class="text-green-600 text-sm">ربح: <?php echo number_format($today_stats['profit'] ?? 0, 2); ?> ر.س</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-sun text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- المبيعات الشهر -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">المبيعات الشهرية</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($month_stats['total'] ?? 0, 2); ?> ر.س</h3>
                    <p class="text-green-600 text-sm">ربح: <?php echo number_format($month_stats['profit'] ?? 0, 2); ?> ر.س</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-calendar text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- المصاريف الشهر -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">المصاريف الشهرية</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($month_expenses['total'] ?? 0, 2); ?> ر.س</h3>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- الربح الصافي -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">الربح الصافي</p>
                    <h3 class="text-2xl font-bold text-gray-800 <?php echo $net_profit >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo number_format($net_profit, 2); ?> ر.س
                    </h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- مخطط المبيعات الشهرية -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">المبيعات الشهرية</h3>
            <canvas id="monthlySalesChart" width="400" height="200"></canvas>
        </div>

        <!-- توزيع الأرباح -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">توزيع الأرباح</h3>
            <canvas id="profitDistributionChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- أحدث الفواتير -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">أحدث الفواتير المربحة</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">رقم الفاتورة</th>
                        <th class="px-6 py-3">العميل</th>
                        <th class="px-6 py-3">المبلغ الإجمالي</th>
                        <th class="px-6 py-3">الربح</th>
                        <th class="px-6 py-3">التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM invoices ORDER BY profit DESC LIMIT 10");
                    while ($invoice = $stmt->fetch()):
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">#<?php echo $invoice['id']; ?></td>
                        <td class="px-6 py-4"><?php echo $invoice['customer_name']; ?></td>
                        <td class="px-6 py-4"><?php echo number_format($invoice['total_amount'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-green-600 font-medium"><?php echo number_format($invoice['profit'], 2); ?> ر.س</td>
                        <td class="px-6 py-4"><?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// مخطط المبيعات الشهرية
const monthlySalesChart = new Chart(document.getElementById('monthlySalesChart'), {
    type: 'bar',
    data: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        datasets: [{
            label: 'المبيعات',
            data: [15000, 18000, 22000, 19000, 25000, 30000],
            backgroundColor: '#3b82f6',
            borderColor: '#3b82f6',
            borderWidth: 1
        }, {
            label: 'الأرباح',
            data: [5000, 6000, 8000, 6500, 9000, 12000],
            backgroundColor: '#10b981',
            borderColor: '#10b981',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                rtl: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// مخطط توزيع الأرباح
const profitDistributionChart = new Chart(document.getElementById('profitDistributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['المبيعات', 'المصاريف', 'الربح الصافي'],
        datasets: [{
            data: [<?php echo $month_stats['total'] ?? 0; ?>, <?php echo $month_expenses['total'] ?? 0; ?>, <?php echo $net_profit; ?>],
            backgroundColor: ['#3b82f6', '#ef4444', '#10b981'],
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