<?php
include 'config.php';
include 'header.php';



// إحصائيات سريعة
try {
    // عدد المنتجات
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products");
    $total_products = $stmt->fetch()['total_products'];
    
    // إجمالي المبيعات اليوم
    $stmt = $pdo->query("SELECT SUM(total_amount) as today_sales FROM invoices WHERE DATE(created_at) = CURDATE()");
    $today_sales = $stmt->fetch()['today_sales'] ?? 0;
    
    // المنتجات منخفضة المخزون
    $stmt = $pdo->query("SELECT COUNT(*) as low_stock FROM products WHERE quantity <= min_quantity");
    $low_stock = $stmt->fetch()['low_stock'];
    
    // إجمالي الأرباح الشهر
    $stmt = $pdo->query("SELECT SUM(profit) as monthly_profit FROM invoices WHERE MONTH(created_at) = MONTH(CURDATE())");
    $monthly_profit = $stmt->fetch()['monthly_profit'] ?? 0;
    
} catch(PDOException $e) {
    $error = "خطأ في جلب البيانات: " . $e->getMessage();
}
?>

<div class="container mx-auto">
    <!-- الإحصائيات السريعة -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- إجمالي المنتجات -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">إجمالي المنتجات</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_products; ?></h3>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-boxes text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- المبيعات اليوم -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">المبيعات اليوم</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($today_sales, 2); ?> ر.س</h3>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- منتجات منخفضة المخزون -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">منخفضة المخزون</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $low_stock; ?></h3>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- الأرباح الشهرية -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-r-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">الأرباح الشهرية</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($monthly_profit, 2); ?> ر.س</h3>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- الرسوم البيانية والإحصائيات -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- مخطط المبيعات -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">مخطط المبيعات</h3>
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>

        <!-- أحدث الفواتير -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">أحدث الفواتير</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">رقم الفاتورة</th>
                            <th class="px-4 py-3">المبلغ</th>
                            <th class="px-4 py-3">التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM invoices ORDER BY created_at DESC LIMIT 5");
                        while ($invoice = $stmt->fetch()):
                        ?>
                        <tr class="border-b">
                            <td class="px-4 py-3">#<?php echo $invoice['id']; ?></td>
                            <td class="px-4 py-3"><?php echo number_format($invoice['total_amount'], 2); ?> ر.س</td>
                            <td class="px-4 py-3"><?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- التنبيهات -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">تنبيهات المخزون</h3>
            <?php
            $stmt = $pdo->query("SELECT * FROM products WHERE quantity <= min_quantity LIMIT 5");
            if ($stmt->rowCount() > 0):
                while ($product = $stmt->fetch()):
            ?>
            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg mb-2">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 ml-3"></i>
                    <span class="text-red-700"><?php echo $product['name']; ?> - المخزون منخفض (<?php echo $product['quantity']; ?>)</span>
                </div>
                <a href="../inventory/edit_product.php?id=<?php echo $product['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm">تحديث</a>
            </div>
            <?php
                endwhile;
            else:
            ?>
            <p class="text-green-600 text-center py-4">لا توجد تنبيهات للمخزون</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// مخطط المبيعات
const salesChart = new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
        datasets: [{
            label: 'المبيعات الشهرية',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
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

<?php include 'footer.php'; ?>