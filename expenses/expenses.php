<?php
include '../header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = cleanInput($_POST['description']);
    $amount = cleanInput($_POST['amount']);
    $category = cleanInput($_POST['category']);
    $date = cleanInput($_POST['date']);
    $notes = cleanInput($_POST['notes']);

    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (description, amount, category, date, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$description, $amount, $category, $date, $notes]);
        
        $success = "تم تسجيل المصروف بنجاح";
    } catch(PDOException $e) {
        $error = "حدث خطأ في تسجيل المصروف: " . $e->getMessage();
    }
}

// جلب المصاريف
$expenses = $pdo->query("SELECT * FROM expenses ORDER BY date DESC, created_at DESC")->fetchAll();

// إجمالي المصاريف الشهر
$current_month = date('Y-m');
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$month_total = $stmt->fetch();
?>

<div class="container mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- نموذج إضافة مصروف -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">تسجيل مصروف جديد</h2>
            
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
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">وصف المصروف *</label>
                        <input type="text" name="description" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="مثال: فاتورة كهرباء">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المبلغ *</label>
                        <input type="number" name="amount" step="0.01" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">التصنيف *</label>
                        <select name="category" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">اختر التصنيف</option>
                            <option value="rent">إيجار</option>
                            <option value="electricity">كهرباء</option>
                            <option value="water">مياه</option>
                            <option value="internet">انترنت</option>
                            <option value="maintenance">صيانة</option>
                            <option value="transportation">مواصلات</option>
                            <option value="supplies">مستلزمات</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">التاريخ *</label>
                        <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="ملاحظات إضافية..."></textarea>
                    </div>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md w-full">
                        <i class="fas fa-save ml-2"></i>تسجيل المصروف
                    </button>
                </div>
            </form>
        </div>

        <!-- قائمة المصاريف -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">سجل المصاريف</h2>
                <div class="bg-red-100 px-3 py-1 rounded-full">
                    <span class="text-red-700 font-medium">إجمالي الشهر: <?php echo number_format($month_total['total'] ?? 0, 2); ?> ر.س</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">الوصف</th>
                            <th class="px-4 py-3">المبلغ</th>
                            <th class="px-4 py-3">التصنيف</th>
                            <th class="px-4 py-3">التاريخ</th>
                            <th class="px-4 py-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?php echo $expense['description']; ?></div>
                                <?php if (!empty($expense['notes'])): ?>
                                <div class="text-sm text-gray-500"><?php echo $expense['notes']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-red-600"><?php echo number_format($expense['amount'], 2); ?> ر.س</td>
                            <td class="px-4 py-3">
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
                                echo $categories[$expense['category']] ?? $expense['category'];
                                ?>
                            </td>
                            <td class="px-4 py-3"><?php echo date('Y-m-d', strtotime($expense['date'])); ?></td>
                            <td class="px-4 py-3">
                                <button class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($expenses)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-receipt text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">لا توجد مصاريف مسجلة</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>