<?php
include '../header.php';

// معاملات التقرير
$report_type = $_GET['report_type'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// بناء الاستعلام حسب نوع التقرير
switch ($report_type) {
    case 'daily':
        $sql = "SELECT DATE(date) as period, 
                       category,
                       SUM(amount) as total_amount,
                       COUNT(*) as expense_count
                FROM expenses 
                WHERE date BETWEEN ? AND ?
                GROUP BY DATE(date), category
                ORDER BY period DESC, total_amount DESC";
        break;
    case 'monthly':
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m') as period, 
                       category,
                       SUM(amount) as total_amount,
                       COUNT(*) as expense_count
                FROM expenses 
                WHERE date BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(date, '%Y-%m'), category
                ORDER BY period DESC, total_amount DESC";
        break;
    case 'yearly':
        $sql = "SELECT YEAR(date) as period, 
                       category,
                       SUM(amount) as total_amount,
                       COUNT(*) as expense_count
                FROM expenses 
                WHERE date BETWEEN ? AND ?
                GROUP BY YEAR(date), category
                ORDER BY period DESC, total_amount DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$report_data = $stmt->fetchAll();

// إحصائيات إجمالية
$total_expenses = 0;
$expense_count = 0;

foreach ($report_data as $row) {
    $total_expenses += $row['total_amount'];
    $expense_count += $row['expense_count'];
}

// توزيع المصاريف حسب التصنيف
$category_sql = "SELECT category, SUM(amount) as total 
                 FROM expenses 
                 WHERE date BETWEEN ? AND ? 
                 GROUP BY category 
                 ORDER BY total DESC";
$category_stmt = $pdo->prepare($category_sql);
$category_stmt->execute([$start_date, $end_date]);
$category_data = $category_stmt->fetchAll();
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تقرير المصاريف</h1>

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
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                    <i class="fas fa-filter ml-2"></i>عرض
                </button>
            </div>
        </form>
    </div>

    <!-- الإحصائيات الإجمالية -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي المصاريف</h3>
            <p class="text-2xl font-bold text-red-600"><?php echo number_format($total_expenses, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-receipt text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">عدد المصاريف</h3>
            <p class="text-2xl font-bold text-blue-600"><?php echo $expense_count; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-chart-pie text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">متوسط المصروف</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo $expense_count > 0 ? number_format($total_expenses / $expense_count, 2) : 0; ?> ر.س</p>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- توزيع المصاريف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">توزيع المصاريف حسب التصنيف</h3>
            <canvas id="expenseCategoryChart" width="400" height="200"></canvas>
        </div>

        <!-- مخطط المصاريف عبر الزمن -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">المصاريف عبر الزمن</h3>
            <canvas id="expenseTrendChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- جدول التقرير -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">الفترة</th>
                        <th class="px-6 py-3">التصنيف</th>
                        <th class="px-6 py-3">عدد المصاريف</th>
                        <th class="px-6 py-3">إجمالي المبلغ</th>
                        <th class="px-6 py-3">النسبة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): 
                        $percentage = $total_expenses > 0 ? ($row['total_amount'] / $total_expenses) * 100 : 0;
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
                        <td class="px-6 py-4">
                            <?php
                            $categories = [
                                'rent' => 'إيجار',
                                'electricity' => 'كهرباء',
                                'water' => 'مياه',
                                'internet' => 'انترنت',
                                'maintenance' => 'صيانة',
                                'transportation' => 'مواصلات',
                                'supplies' => 'مستلزمات',
                                'other' => 'أخرى'
                            ];
                            echo $categories[$row['category']] ?? $row['category'];
                            ?>
                        </td>
                        <td class="px-6 py-4"><?php echo $row['expense_count']; ?></td>
                        <td class="px-6 py-4 font-medium text-red-600"><?php echo number_format($row['total_amount'], 2); ?> ر.س</td>
                        <td class="px-6 py-4"><?php echo number_format($percentage, 1); ?>%</td>
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
</div>

<script>
// مخطط توزيع المصاريف
const expenseCategoryChart = new Chart(document.getElementById('expenseCategoryChart'), {
    type: 'pie',
    data: {
        labels: [
            <?php 
            foreach ($category_data as $category) {
                $cat_names = [
                    'rent' => 'إيجار',
                    'electricity' => 'كهرباء',
                    'water' => 'مياه',
                    'internet' => 'انترنت',
                    'maintenance' => 'صيانة',
                    'transportation' => 'مواصلات',
                    'supplies' => 'مستلزمات',
                    'other' => 'أخرى'
                ];
                echo "'" . ($cat_names[$category['category']] ?? $category['category']) . "',";
            }
            ?>
        ],
        datasets: [{
            data: [<?php foreach ($category_data as $category) echo $category['total'] . ','; ?>],
            backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#f97316', '#06b6d4', '#84cc16'],
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

// مخطط اتجاه المصاريف
const expenseTrendChart = new Chart(document.getElementById('expenseTrendChart'), {
    type: 'line',
    data: {
        labels: [
            <?php 
            $periods = array_unique(array_column($report_data, 'period'));
            foreach ($periods as $period) {
                if ($report_type == 'daily') {
                    echo "'" . date('m-d', strtotime($period)) . "',";
                } elseif ($report_type == 'monthly') {
                    echo "'" . date('Y-m', strtotime($period . '-01')) . "',";
                } else {
                    echo "'" . $period . "',";
                }
            }
            ?>
        ],
        datasets: [{
            label: 'إجمالي المصاريف',
            data: [
                <?php 
                $period_totals = [];
                foreach ($report_data as $row) {
                    $period = $row['period'];
                    if (!isset($period_totals[$period])) {
                        $period_totals[$period] = 0;
                    }
                    $period_totals[$period] += $row['total_amount'];
                }
                foreach ($period_totals as $total) {
                    echo $total . ',';
                }
                ?>
            ],
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
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
</script>

<?php include '../footer.php'; ?>