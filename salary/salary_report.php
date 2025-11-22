<?php
include '../header.php';

// معاملات التقرير
$report_type = $_GET['report_type'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// بناء الاستعلام حسب نوع التقرير
switch ($report_type) {
    case 'monthly':
        $sql = "SELECT DATE_FORMAT(s.payment_date, '%Y-%m') as period,
                       e.name as employee_name,
                       e.position,
                       SUM(s.basic_salary) as total_basic,
                       SUM(s.bonuses) as total_bonuses,
                       SUM(s.deductions) as total_deductions,
                       SUM(s.net_salary) as total_net
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                WHERE s.payment_date BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(s.payment_date, '%Y-%m'), s.employee_id
                ORDER BY period DESC, total_net DESC";
        break;
    case 'yearly':
        $sql = "SELECT YEAR(s.payment_date) as period,
                       e.name as employee_name,
                       e.position,
                       SUM(s.basic_salary) as total_basic,
                       SUM(s.bonuses) as total_bonuses,
                       SUM(s.deductions) as total_deductions,
                       SUM(s.net_salary) as total_net
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                WHERE s.payment_date BETWEEN ? AND ?
                GROUP BY YEAR(s.payment_date), s.employee_id
                ORDER BY period DESC, total_net DESC";
        break;
    default:
        $sql = "SELECT s.payment_date as period,
                       e.name as employee_name,
                       e.position,
                       s.basic_salary as total_basic,
                       s.bonuses as total_bonuses,
                       s.deductions as total_deductions,
                       s.net_salary as total_net
                FROM salaries s
                JOIN employees e ON s.employee_id = e.id
                WHERE s.payment_date BETWEEN ? AND ?
                ORDER BY s.payment_date DESC, s.net_salary DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$report_data = $stmt->fetchAll();

// إحصائيات إجمالية
$total_basic = 0;
$total_bonuses = 0;
$total_deductions = 0;
$total_net = 0;

foreach ($report_data as $row) {
    $total_basic += $row['total_basic'];
    $total_bonuses += $row['total_bonuses'];
    $total_deductions += $row['total_deductions'];
    $total_net += $row['total_net'];
}
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تقرير المرتبات</h1>

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
                <a href="export_excel.php?report=salary&type=<?php echo $report_type; ?>&start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-file-excel ml-2"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- الإحصائيات الإجمالية -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-money-bill text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">الرواتب الأساسية</h3>
            <p class="text-2xl font-bold text-blue-600"><?php echo number_format($total_basic, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-gift text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">المكافآت</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($total_bonuses, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-minus-circle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">الخصومات</h3>
            <p class="text-2xl font-bold text-red-600"><?php echo number_format($total_deductions, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-purple-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-calculator text-purple-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">الصافي</h3>
            <p class="text-2xl font-bold text-purple-600"><?php echo number_format($total_net, 2); ?> ر.س</p>
        </div>
    </div>

    <!-- مخطط توزيع الرواتب -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4">توزيع الرواتب</h3>
        <canvas id="salaryDistributionChart" width="400" height="200"></canvas>
    </div>

    <!-- جدول التقرير -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">الفترة</th>
                        <th class="px-6 py-3">الموظف</th>
                        <th class="px-6 py-3">الوظيفة</th>
                        <th class="px-6 py-3">الراتب الأساسي</th>
                        <th class="px-6 py-3">المكافآت</th>
                        <th class="px-6 py-3">الخصومات</th>
                        <th class="px-6 py-3">الصافي</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">
                            <?php 
                            if ($report_type == 'monthly') {
                                echo date('Y-m', strtotime($row['period'] . '-01'));
                            } else {
                                echo $row['period'];
                            }
                            ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo $row['employee_name']; ?></div>
                        </td>
                        <td class="px-6 py-4"><?php echo $row['position']; ?></td>
                        <td class="px-6 py-4"><?php echo number_format($row['total_basic'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-green-600"><?php echo number_format($row['total_bonuses'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 text-red-600"><?php echo number_format($row['total_deductions'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 font-bold text-blue-600"><?php echo number_format($row['total_net'], 2); ?> ر.س</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($report_data)): ?>
        <div class="text-center py-8">
            <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">لا توجد بيانات للعرض في الفترة المحددة</p>
        </div>
    <?php endif; ?>
</div>

<script>
// مخطط توزيع الرواتب
const salaryDistributionChart = new Chart(document.getElementById('salaryDistributionChart'), {
    type: 'bar',
    data: {
        labels: [
            <?php 
            $employees = array_unique(array_column($report_data, 'employee_name'));
            foreach ($employees as $employee) {
                echo "'" . $employee . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'الراتب الأساسي',
            data: [
                <?php 
                $employee_totals = [];
                foreach ($report_data as $row) {
                    $employee = $row['employee_name'];
                    if (!isset($employee_totals[$employee])) {
                        $employee_totals[$employee] = 0;
                    }
                    $employee_totals[$employee] += $row['total_basic'];
                }
                foreach ($employee_totals as $total) {
                    echo $total . ',';
                }
                ?>
            ],
            backgroundColor: '#3b82f6',
            borderColor: '#3b82f6',
            borderWidth: 1
        }, {
            label: 'المكافآت',
            data: [
                <?php 
                $employee_bonuses = [];
                foreach ($report_data as $row) {
                    $employee = $row['employee_name'];
                    if (!isset($employee_bonuses[$employee])) {
                        $employee_bonuses[$employee] = 0;
                    }
                    $employee_bonuses[$employee] += $row['total_bonuses'];
                }
                foreach ($employee_bonuses as $total) {
                    echo $total . ',';
                }
                ?>
            ],
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