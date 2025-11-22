<?php
include '../header.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    redirect('inventory.php');
}

// جلب بيانات المنتج
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('inventory.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = cleanInput($_POST['name']);
    $code = cleanInput($_POST['code']);
    $description = cleanInput($_POST['description']);
    $category_id = cleanInput($_POST['category_id']);
    $cost_price = cleanInput($_POST['cost_price']);
    $selling_price = cleanInput($_POST['selling_price']);
    $quantity = cleanInput($_POST['quantity']);
    $min_quantity = cleanInput($_POST['min_quantity']);

    try {
        $stmt = $pdo->prepare("UPDATE products SET name=?, code=?, description=?, category_id=?, cost_price=?, selling_price=?, quantity=?, min_quantity=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$name, $code, $description, $category_id, $cost_price, $selling_price, $quantity, $min_quantity, $id]);
        
        $success = "تم تحديث المنتج بنجاح";
        // تحديث بيانات المنتج بعد التعديل
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
    } catch(PDOException $e) {
        $error = "حدث خطأ في تحديث المنتج: " . $e->getMessage();
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="container mx-auto max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">تعديل المنتج</h1>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- الاسم والكود -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم المنتج *</label>
                    <input type="text" name="name" value="<?php echo $product['name']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">كود المنتج *</label>
                    <input type="text" name="code" value="<?php echo $product['code']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- التصنيف والوصف -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">التصنيف</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">اختر التصنيف</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo $product['description']; ?></textarea>
                </div>

                <!-- الأسعار -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">سعر التكلفة *</label>
                    <input type="number" name="cost_price" step="0.01" value="<?php echo $product['cost_price']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">سعر البيع *</label>
                    <input type="number" name="selling_price" step="0.01" value="<?php echo $product['selling_price']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- الكميات -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الكمية المتاحة *</label>
                    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الحد الأدنى للمخزون *</label>
                    <input type="number" name="min_quantity" value="<?php echo $product['min_quantity']; ?>" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- أزرار -->
            <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                <a href="inventory.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-save ml-2"></i>تحديث المنتج
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>