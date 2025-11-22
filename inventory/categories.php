<?php
include '../header.php';

// إضافة تصنيف جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = cleanInput($_POST['name']);
    $description = cleanInput($_POST['description']);

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $success = "تم إضافة التصنيف بنجاح";
    } catch(PDOException $e) {
        $error = "حدث خطأ في إضافة التصنيف: " . $e->getMessage();
    }
}

// جلب جميع التصنيفات
$categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC")->fetchAll();
?>

<div class="container mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- نموذج إضافة تصنيف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">إضافة تصنيف جديد</h2>
            
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
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم التصنيف *</label>
                    <input type="text" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <button type="submit" name="add_category" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                    <i class="fas fa-plus ml-2"></i>إضافة التصنيف
                </button>
            </form>
        </div>

        <!-- قائمة التصنيفات -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">قائمة التصنيفات</h2>
            
            <div class="space-y-4">
                <?php foreach ($categories as $category): 
                    $product_count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                    $product_count->execute([$category['id']]);
                    $count = $product_count->fetchColumn();
                ?>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <h3 class="font-medium text-gray-900"><?php echo $category['name']; ?></h3>
                        <p class="text-sm text-gray-500"><?php echo $category['description']; ?></p>
                        <span class="text-xs text-blue-600"><?php echo $count; ?> منتج</span>
                    </div>
                    <div class="flex space-x-2 space-x-reverse">
                        <button class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>