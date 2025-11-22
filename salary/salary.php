<?php
include '../header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = cleanInput($_POST['employee_id']);
    $basic_salary = cleanInput($_POST['basic_salary']);
    $bonuses = cleanInput($_POST['bonuses'] ?? 0);
    $deductions = cleanInput($_POST['deductions'] ?? 0);
    $payment_date = cleanInput($_POST['payment_date']);
    $notes = cleanInput($_POST['notes']);

    $net_salary = $basic_salary + $bonuses - $deductions;

    try {
        $stmt = $pdo->prepare("INSERT INTO salaries (employee_id, basic_salary, bonuses, deductions, net_salary, payment_date, notes) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $basic_salary, $bonuses, $deductions, $net_salary, $payment_date, $notes]);
        
        $success = "تم تسجيل الراتب بنجاح";
    } catch(PDOException $e) {
        $error = "حدث خطأ في تسجيل الراتب: " . $e->getMessage();
    }
}

// جلب الموظفين
$employees = $pdo->query("SELECT * FROM employees WHERE status = 'active'")->fetchAll();

// جلب سجل الرواتب
$salaries = $pdo->query("SELECT s.*, e.name as employee_name 
                         FROM salaries s 
                         JOIN employees e ON s.employee_id = e.id 
                         ORDER BY s.payment_date DESC")->fetchAll();

// إجمالي الرواتب الشهر
$current_month = date('Y-m');
$stmt = $pdo->prepare("SELECT SUM(net_salary) as total FROM salaries WHERE DATE_FORMAT(payment_date, '%Y-%m') = ?");
$stmt->execute([$current_month]);
$month_total = $stmt->fetch();
?>

<div class="container mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- نموذج تسجيل راتب -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">تسجيل راتب موظف</h2>
            
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">الموظف *</label>
                        <select name="employee_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">اختر الموظف</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo $employee['name']; ?> - <?php echo $employee['position']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الراتب الأساسي *</label>
                        <input type="number" name="basic_salary" step="0.01" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">المكافآت</label>
                        <input type="number" name="bonuses" step="0.01" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الخصومات</label>
                        <input type="number" name="deductions" step="0.01" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الدفع *</label>
                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required 
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
                        <i class="fas fa-money-bill-wave ml-2"></i>تسجيل الراتب
                    </button>
                </div>
            </form>
        </div>

        <!-- قائمة الرواتب -->
        <div class="bg-white rounded-lg shadow-lg p-6 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">سجل الرواتب</h2>
                <div class="bg-purple-100 px-3 py-1 rounded-full">
                    <span class="text-purple-700 font-medium">إجمالي الشهر: <?php echo number_format($month_total['total'] ?? 0, 2); ?> ر.س</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">الموظف</th>
                            <th class="px-4 py-3">الراتب الأساسي</th>
                            <th class="px-4 py-3">المكافآت</th>
                            <th class="px-4 py-3">الخصومات</th>
                            <th class="px-4 py-3">الصافي</th>
                            <th class="px-4 py-3">تاريخ الدفع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salaries as $salary): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?php echo $salary['employee_name']; ?></div>
                                <?php if (!empty($salary['notes'])): ?>
                                <div class="text-sm text-gray-500"><?php echo $salary['notes']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3"><?php echo number_format($salary['basic_salary'], 2); ?> ر.س</td>
                            <td class="px-4 py-3 text-green-600"><?php echo number_format($salary['bonuses'], 2); ?> ر.س</td>
                            <td class="px-4 py-3 text-red-600"><?php echo number_format($salary['deductions'], 2); ?> ر.س</td>
                            <td class="px-4 py-3 font-bold text-blue-600"><?php echo number_format($salary['net_salary'], 2); ?> ر.س</td>
                            <td class="px-4 py-3"><?php echo date('Y-m-d', strtotime($salary['payment_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($salaries)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">لا توجد رواتب مسجلة</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>