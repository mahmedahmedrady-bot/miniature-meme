<?php
include '../header.php';

// معاملات التقرير
$report_type = $_GET['report_type'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// بيانات التقرير
$sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as period,
               SUM(total_amount) as revenue,
               SUM(profit) as gross_profit,
               COUNT(*) as invoice_count
        FROM invoices 
        WHERE created_at BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY period DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$sales_data = $stmt->fetchAll();

// بيانات المصاريف
$expense_sql = "SELECT DATE_FORMAT(date, '%Y-%m') as period,
                       SUM(amount) as total_expenses
                FROM expenses 
                WHERE date BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(date, '%Y-%m')";

$expense_stmt = $pdo->prepare($expense_sql);
$expense_stmt->execute([$start_date, $end_date]);
$expense_data = $expense_stmt->fetchAll();

// بيانات الرواتب
$salary_sql = "SELECT DATE_FORMAT(payment_date, '%Y-%m') as period,
                      SUM(net_salary) as total_salaries
               FROM salaries 
               WHERE payment_date BETWEEN ? AND ?
               GROUP BY DATE_FORMAT(payment_date, '%Y-%m')";

$salary_stmt = $pdo->prepare($salary_sql);
$salary_stmt->execute([$start_date, $end_date]);
$salary_data = $salary_stmt->fetchAll();

// دمج البيانات
$report_data = [];
foreach ($sales_data as $sale) {
    $period = $sale['period'];
    $expenses = 0;
    $salaries = 0;
    
    // البحث عن المصاريف المقابلة
    foreach ($expense_data as $expense) {
        if ($expense['period'] == $period) {
            $expenses = $expense['total_expenses'];
            break;
        }
    }
    
    // البحث عن الرواتب المقابلة
    foreach ($salary_data as $salary) {
        if ($salary['period'] == $period) {
            $salaries = $salary['total_salaries'];
            break;
        }
    }
    
    $total_costs = $expenses + $salaries;
    $net_profit = $sale['gross_profit'] - $total_costs;
    $net_margin = $sale['revenue'] > 0 ? ($net_profit / $sale['revenue']) * 100 : 0;
    
    $report_data[$period] = [
        'revenue' => $sale['revenue'],
        'gross_profit' => $sale['gross_profit'],
        'expenses' => $expenses,
        'salaries' => $salaries,
        'total_costs' => $total_costs,
        'net_profit' => $net_profit,
        'net_margin' => $net_margin,
        'invoice_count' => $sale['invoice_count']
    ];
}

// إحصائيات إجمالية
$total_revenue = 0;
$total_gross_profit = 0;
$total_expenses = 0;
$total_salaries = 0;
$total_net_profit = 0;

foreach ($report_data as $data) {
    $total_revenue += $data['revenue'];
    $total_gross_profit += $data['gross_profit'];
    $total_expenses += $data['expenses'];
    $total_salaries += $data['salaries'];
    $total_net_profit += $data['net_profit'];
}
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تقرير الأرباح</h1>

    <!-- فلتر التقرير -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نوع التقرير</label>
                <select name="report_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <a href="export_excel.php?report=profit&type=<?php echo $report_type; ?>&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-file-excel ml-2"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- الإحصائيات الإجمالية -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي الإيرادات</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($total_revenue, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">الربح الإجمالي</h3>
            <p class="text-2xl font-bold text-blue-600"><?php echo number_format($total_gross_profit, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-receipt text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي المصاريف</h3>
            <p class="text-2xl font-bold text-red-600"><?php echo number_format($total_expenses, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-purple-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-users text-purple-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي الرواتب</h3>
            <p class="text-2xl font-bold text-purple-600"><?php echo number_format($total_salaries, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-yellow-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-coins text-yellow-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">الربح الصافي</h3>
            <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($total_net_profit, 2); ?> ر.س</p>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- تطور الأرباح -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">تطور الأرباح عبر الزمن</h3>
            <canvas id="profitTrendChart" width="400" height="200"></canvas>
        </div>

        <!-- توزيع الإيرادات والتكاليف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">توزيع الإيرادات والتكاليف</h3>
            <canvas id="revenueCostChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- جدول التقرير -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">الشهر</th>
                        <th class="px-6 py-3">الإيرادات</th>
                        <th class="px-6 py-3">الربح الإجمالي</th>
                        <th class="px-6 py-3">المصاريف</th>
                        <th class="px-6 py-3">الرواتب</th>
                        <th class="px-6 py-3">إجمالي التكاليف</th>
                        <th class="px-6 py-3">الربح الصافي</th>
                        <th class="px-6 py-3">هامش الربح</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $period => $data): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium"><?php echo $period; ?></td>
                        <td class="px-6 py-4 text-green-600"><?php echo number_format($data['revenue'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-blue-600"><?php echo number_format($data['gross_profit'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-red-600"><?php echo number_format($data['expenses'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-purple-600"><?php echo number_format($data['salaries'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-orange-600"><?php echo number_format($data['total_costs'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 font-bold <?php echo $data['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo number_format($data['net_profit'], 2); ?> ر.س
                        </td>
                        <td class="px-6 py-4 font-bold <?php echo $data['net_margin'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo number_format($data['net_margin'], 1); ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($report_data)): ?>
        <div class="text-center py-8">
            <i class="fas fa-chart-line text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">لا توجد بيانات للعرض في الفترة المحددة</p>
        </div>
    <?php endif; ?>
</div>

<script>
// مخطط تطور الأرباح
const profitTrendChart = new Chart(document.getElementById('profitTrendChart'), {
    type: 'line',
    data: {
        labels: [<?php foreach ($report_data as $period => $data) echo "'" . $period . "',"; ?>],
        datasets: [{
            label: 'الإيرادات',
            data: [<?php foreach ($report_data as $data) echo $data['revenue'] . ','; ?>],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'الربح الصافي',
            data: [<?php foreach ($report_data as $data) echo $data['net_profit'] . ','; ?>],
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4,
            fill: true
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

// مخطط توزيع الإيرادات والتكاليف
const revenueCostChart = new Chart(document.getElementById('revenueCostChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach ($report_data as $period => $data) echo "'" . $period . "',"; ?>],
        datasets: [{
            label: 'الإيرادات',
            data: [<?php foreach ($report_data as $data) echo $data['revenue'] . ','; ?>],
            backgroundColor: '#10b981',
            borderColor: '#10b981',
            borderWidth: 1
        }, {
            label: 'إجمالي التكاليف',
            data: [<?php foreach ($report_data as $data) echo $data['total_costs'] . ','; ?>],
            backgroundColor: '#ef4444',
            borderColor: '#ef4444',
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