<?php
include '../header.php';

// جلب المنتجات منخفضة المخزون
$stmt = $pdo->query("SELECT * FROM products WHERE quantity <= min_quantity ORDER BY quantity ASC");
$low_stock_products = $stmt->fetchAll();

// جلب المنتجات التي نفذت
$stmt = $pdo->query("SELECT * FROM products WHERE quantity = 0 ORDER BY name");
$out_of_stock_products = $stmt->fetchAll();
?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">تنبيهات المخزون</h1>

    <!-- المنتجات المنخفضة المخزون -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-yellow-800">
                <i class="fas fa-exclamation-triangle ml-2"></i>
                المنتجات منخفضة المخزون
            </h2>
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                <?php echo count($low_stock_products); ?> منتج
            </span>
        </div>

        <?php if (!empty($low_stock_products)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-yellow-50">
                        <tr>
                            <th class="px-6 py-3">المنتج</th>
                            <th class="px-6 py-3">المخزون الحالي</th>
                            <th class="px-6 py-3">الحد الأدنى</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $product): ?>
                        <tr class="border-b hover:bg-yellow-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo $product['name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $product['code']; ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-yellow-600"><?php echo $product['quantity']; ?></span>
                            </td>
                            <td class="px-6 py-4"><?php echo $product['min_quantity']; ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                    منخفض
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-edit ml-1"></i>تحديث
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-400 mb-4"></i>
                <p class="text-green-600">لا توجد منتجات منخفضة المخزون</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- المنتجات التي نفذت -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-red-800">
                <i class="fas fa-times-circle ml-2"></i>
                المنتجات التي نفذت
            </h2>
            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                <?php echo count($out_of_stock_products); ?> منتج
            </span>
        </div>

        <?php if (!empty($out_of_stock_products)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-red-50">
                        <tr>
                            <th class="px-6 py-3">المنتج</th>
                            <th class="px-6 py-3">الكود</th>
                            <th class="px-6 py-3">سعر البيع</th>
                            <th class="px-6 py-3">الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($out_of_stock_products as $product): ?>
                        <tr class="border-b hover:bg-red-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?php echo $product['name']; ?></div>
                            </td>
                            <td class="px-6 py-4"><?php echo $product['code']; ?></td>
                            <td class="px-6 py-4"><?php echo number_format($product['selling_price'], 2); ?> ر.س</td>
                            <td class="px-6 py-4">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900 text-sm">
                                    <i class="fas fa-edit ml-1"></i>تحديث المخزون
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-400 mb-4"></i>
                <p class="text-green-600">لا توجد منتجات نفذت من المخزون</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../footer.php'; ?>