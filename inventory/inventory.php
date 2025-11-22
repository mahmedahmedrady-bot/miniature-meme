<?php
include '../header.php';

// البحث والتصفية
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.code LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($category)) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// جلب التصنيفات للقائمة المنسدلة
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">إدارة المخزون</h1>
        <a href="add_product.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            <i class="fas fa-plus ml-2"></i>إضافة منتج جديد
        </a>
    </div>

    <!-- شريط البحث والتصفية -->
    <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">بحث</label>
                <input type="text" name="search" value="<?php echo $search; ?>" 
                       placeholder="ابحث بالاسم، الكود أو الوصف..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">التصنيف</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع التصنيفات</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                    <i class="fas fa-search ml-2"></i>بحث
                </button>
            </div>
        </form>
    </div>

    <!-- جدول المنتجات -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">المعلومات</th>
                        <th class="px-6 py-3">السعر والكمية</th>
                        <th class="px-6 py-3">الحالة</th>
                        <th class="px-6 py-3">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): 
                        $stock_status = $product['quantity'] <= $product['min_quantity'] ? 'low' : ($product['quantity'] == 0 ? 'out' : 'normal');
                    ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $product['name']; ?></div>
                                    <div class="text-sm text-gray-500">كود: <?php echo $product['code']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $product['category_name']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm">
                                <div>سعر التكلفة: <span class="font-medium"><?php echo number_format($product['cost_price'], 2); ?> ر.س</span></div>
                                <div>سعر البيع: <span class="font-medium"><?php echo number_format($product['selling_price'], 2); ?> ر.س</span></div>
                                <div>الكمية: <span class="font-medium"><?php echo $product['quantity']; ?></span></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($stock_status == 'low'): ?>
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">منخفض</span>
                            <?php elseif ($stock_status == 'out'): ?>
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">نفذ</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">متوفر</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2 space-x-reverse">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo $product['name']; ?>')" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-8">
            <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">لا توجد منتجات</p>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteProduct(id, name) {
    confirmDelete(`هل تريد حذف المنتج "${name}"؟`).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_product.php?id=${id}`;
        }
    });
}
</script>

<?php include '../footer.php'; ?>