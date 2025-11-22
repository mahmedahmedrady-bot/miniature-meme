<?php
include '../header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = cleanInput($_POST['description']);
    $amount = cleanInput($_POST['amount']);
    $type = cleanInput($_POST['type']);
    $date = cleanInput($_POST['date']);
    $notes = cleanInput($_POST['notes']);

    try {
        $stmt = $pdo->prepare("INSERT INTO losses (description, amount, type, date, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$description, $amount, $type, $date, $notes]);
        
        $success = "تم تسجيل الخسارة بنجاح";
    } catch(PDOException $e) {
        $error = "حدث خطأ في تسجيل الخسارة: " . $e->getMessage();
    }
}

// جلب الخسائر
$losses = $pdo->query("SELECT * FROM losses ORDER BY date DESC, created_at DESC")->fetchAll();

// إجمالي الخسائر الشهر
$current_month = date('Y-m');
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM losses WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$month_total = $stmt->fetch();
?>

<div class="container mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- نموذج تسجيل خسارة -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">تسجيل خسارة</h2>
            
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">وصف الخسارة *</label>
                        <input type="text" name="description" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="مثال: تلف منتجات، عيوب إنتاج...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">قيمة الخسارة *</label>
                        <input type="number" name="amount" step="0.01" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع الخسارة *</label>
                        <select name="type" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">اختر النوع</option>
                            <option value="damage">تلف منتجات</option>
                            <option value="defect">عيوب إنتاج</option>
                            <option value="theft">سرقة</option>
                            <option value="waste">هدر</option>
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
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md w-full">
                        <i class="fas fa-exclamation-triangle ml-2"></i>تسجيل الخسارة
                    </button>
                </div>
            </form>
        </div>

        <!-- قائمة الخسائر -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">سجل الخسائر</h2>
                <div class="bg-red-100 px-3 py-1 rounded-full">
                    <span class="text-red-700 font-medium">إجمالي الشهر: <?php echo number_format($month_total['total'] ?? 0, 2); ?> ر.س</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">الوصف</th>
                            <th class="px-4 py-3">القيمة</th>
                            <th class="px-4 py-3">النوع</th>
                            <th class="px-4 py-3">التاريخ</th>
                            <th class="px-4 py-3">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($losses as $loss): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?php echo $loss['description']; ?></div>
                                <?php if (!empty($loss['notes'])): ?>
                                <div class="text-sm text-gray-500"><?php echo $loss['notes']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-red-600"><?php echo number_format($loss['amount'], 2); ?> ر.س</td>
                            <td class="px-4 py-3">
                                <?php
                                $types = [
                                    'damage' => 'تلف منتجات',
                                    'defect' => 'عيوب إنتاج',
                                    'theft' => 'سرقة',
                                    'waste' => 'هدر',
                                    'other' => 'أخرى'
                                ];
                                echo $types[$loss['type']] ?? $loss['type'];
                                ?>
                            </td>
                            <td class="px-4 py-3"><?php echo date('Y-m-d', strtotime($loss['date'])); ?></td>
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

            <?php if (empty($losses)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">لا توجد خسائر مسجلة</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>