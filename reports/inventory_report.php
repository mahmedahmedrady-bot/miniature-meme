<?php
include '../header.php';

// جلب تقرير المخزون
$products = $pdo->query("
    SELECT p.*, c.name as category_name,
           (p.quantity * p.cost_price) as total_cost,
           (p.quantity * p.selling_price) as total_value,
           ((p.selling_price - p.cost_price) * p.quantity) as potential_profit
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.quantity ASC
")->fetchAll();

// إحصائيات المخزون
$total_products = count($products);
$total_cost = 0;
$total_value = 0;
$total_potential_profit = 0;

foreach ($products as $product) {
    $total_cost += $product['total_cost'];
    $total_value += $product['total_value'];
    $total_potential_profit += $product['potential_profit'];
}
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تقرير المخزون</h1>

    <!-- الإحصائيات الإجمالية -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-boxes text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي المنتجات</h3>
            <p class="text-2xl font-bold text-blue-600"><?php echo $total_products; ?></p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-money-bill text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">إجمالي التكلفة</h3>
            <p class="text-2xl font-bold text-red-600"><?php echo number_format($total_cost, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-chart-line text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">القيمة السوقية</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($total_value, 2); ?> ر.س</p>
        </div>
        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
            <div class="bg-purple-100 p-3 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                <i class="fas fa-coins text-purple-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">ربح محتمل</h3>
            <p class="text-2xl font-bold text-purple-600"><?php echo number_format($total_potential_profit, 2); ?> ر.س</p>
        </div>
    </div>

    <!-- جدول تقرير المخزون -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">المنتج</th>
                        <th class="px-6 py-3">التصنيف</th>
                        <th class="px-6 py-3">المخزون</th>
                        <th class="px-6 py-3">سعر التكلفة</th>
                        <th class="px-6 py-3">سعر البيع</th>
                        <th class="px-6 py-3">القيمة</th>
                        <th class="px-6 py-3">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): 
                        $stock_status = $product['quantity'] <= $product['min_quantity'] ? 'low' : ($product['quantity'] == 0 ? 'out' : 'normal');
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo $product['name']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $product['code']; ?></div>
                        </td>
                        <td class="px-6 py-4"><?php echo $product['category_name']; ?></td>
                        <td class="px-6 py-4">
                            <span class="font-medium <?php echo $stock_status == 'low' ? 'text-yellow-600' : ($stock_status == 'out' ? 'text-red-600' : 'text-green-600'); ?>">
                                <?php echo $product['quantity']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?php echo number_format($product['cost_price'], 2); ?> ر.س</td>
                        <td class="px-6 py-4"><?php echo number_format($product['selling_price'], 2); ?> ر.س</td>
                        <td class="px-6 py-4 font-medium"><?php echo number_format($product['total_value'], 2); ?> ر.س</td>
                        <td class="px-6 py-4">
                            <?php if ($stock_status == 'low'): ?>
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">منخفض</span>
                            <?php elseif ($stock_status == 'out'): ?>
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">نفذ</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">جيد</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- أزرار التصدير -->
    <div class="flex justify-end mt-4">
        <a href="export_excel.php?report=inventory" 
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md">
            <i class="fas fa-file-excel ml-2"></i>تصدير إلى Excel
        </a>
    </div>
</div>

<?php include '../footer.php'; ?>