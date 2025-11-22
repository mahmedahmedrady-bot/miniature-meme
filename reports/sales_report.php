<?php
include '../header.php';

// معاملات التقرير
$report_type = $_GET['report_type'] ?? 'daily';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// بناء الاستعلام حسب نوع التقرير
switch ($report_type) {
    case 'daily':
        $sql = "SELECT DATE(created_at) as period, 
                       COUNT(*) as invoice_count,
                       SUM(total_amount) as total_sales,
                       SUM(profit) as total_profit
                FROM invoices 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY period DESC";
        break;
    case 'monthly':
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as period, 
                       COUNT(*) as invoice_count,
                       SUM(total_amount) as total_sales,
                       SUM(profit) as total_profit
                FROM invoices 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY period DESC";
        break;
    case 'yearly':
        $sql = "SELECT YEAR(created_at) as period, 
                       COUNT(*) as invoice_count,
                       SUM(total_amount) as total_sales,
                       SUM(profit) as total_profit
                FROM invoices 
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY YEAR(created_at)
                ORDER BY period DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$report_data = $stmt->fetchAll();

// إحصائيات إجمالية
$total_invoices = 0;
$total_sales = 0;
$total_profit = 0;

foreach ($report_data as $row) {
    $total_invoices += $row['invoice_count'];
    $total_sales += $row['total_sales'];
    $total_profit += $row['total_profit'];
}
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تقرير المبيعات</h1>

    <!-- فلتر التقرير -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التقرير</label>
                <select name="report_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="daily" <?php echo $report_type == 'daily' ? 'selected' : ''; ?>>يومي</option>
                    <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>شهري</option>
                    <option value="yearly" <?php echo $report_type == 'yearly' ? 'selected' : ''; ?>>سنوي</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">من تاريخ</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">إلى تاريخ</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end space-x-2 space-x-reverse">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                    <i class="fas fa-filter ml-2"></i>عرض
                </button>
                <a href="export_excel.php?report=sales&type=<?php echo $report_type; ?>&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-file-excel ml-2"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- الإحصائيات الإجمالية -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-receipt text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي الفواتير</h3>
            <p class="text-2xl font-bold text-blue-600"><?php echo $total_invoices; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي المبيعات</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($total_sales, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-purple-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي الأرباح</h3>
            <p class="text-2xl font-bold text-purple-600"><?php echo number_format($total_profit, 2); ?> ر.س</p>
        </div>
    </div>

    <!-- جدول التقرير -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">الفترة</th>
                        <th class="px-6 py-3">عدد الفواتير</th>
                        <th class="px-6 py-3">إجمالي المبيعات</th>
                        <th class="px-6 py-3">إجمالي الأرباح</th>
                        <th class="px-6 py-3">متوسط الفاتورة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): 
                        $avg_invoice = $row['invoice_count'] > 0 ? $row['total_sales'] / $row['invoice_count'] : 0;
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">
                            <?php 
                            if ($report_type == 'daily') {
                                echo date('Y-m-d', strtotime($row['period']));
                            } elseif ($report_type == 'monthly') {
                                echo date('Y-m', strtotime($row['period'] . '-01'));
                            } else {
                                echo $row['period'];
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4"><?php echo $row['invoice_count']; ?></td>
                        <td class="px-6 py-4"><?php echo number_format($row['total_sales'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-green-600"><?php echo number_format($row['total_profit'], 2); ?> ر.س</td>
                        <td class="px-6 py-4"><?php echo number_format($avg_invoice, 2); ?> ر.س</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($report_data)): ?>
        <div class="text-center py-8">
            <i class="fas fa-chart-bar text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">لا توجد بيانات للعرض في الفترة المحددة</p>
        </div>
    <?php endif; ?>

    <!-- مخطط بياني -->
    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">مخطط المبيعات</h3>
        <canvas id="salesChart" width="400" height="200"></canvas>
    </div>
</div>

<script>
// مخطط المبيعات
const salesChart = new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: [
            <?php 
            foreach ($report_data as $row) {
                if ($report_type == 'daily') {
                    echo "'" . date('m-d', strtotime($row['period'])) . "',";
                } elseif ($report_type == 'monthly') {
                    echo "'" . date('Y-m', strtotime($row['period'] . '-01')) . "',";
                } else {
                    echo "'" . $row['period'] . "',";
                }
            }
            ?>
        ],
        datasets: [{
            label: 'المبيعات',
            data: [<?php foreach ($report_data as $row) echo $row['total_sales'] . ','; ?>],
            backgroundColor: '#3b82f6',
            borderColor: '#3b82f6',
            borderWidth: 1
        }, {
            label: 'الأرباح',
            data: [<?php foreach ($report_data as $row) echo $row['total_profit'] . ','; ?>],
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
</script>

<?php include '../footer.php'; ?>