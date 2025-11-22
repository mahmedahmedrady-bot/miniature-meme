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
?>

<div class="container mx-auto max-w-2xl">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">إضافة راتب موظف</h1>

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
            <div class="grid grid-cols-1 gap-6">
                <!-- اختيار الموظف -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الموظف *</label>
                    <select name="employee_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">اختر الموظف</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>">
                                <?php echo $employee['name']; ?> - <?php echo $employee['position']; ?> - <?php echo number_format($employee['basic_salary'], 2); ?> ر.س
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- الراتب والمكافآت والخصومات -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                </div>

                <!-- الراتب الصافي -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">الراتب الصافي</label>
                    <div id="netSalary" class="text-2xl font-bold text-blue-600">0.00 ر.س</div>
                </div>

                <!-- تاريخ الدفع والملاحظات -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الدفع *</label>
                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="ملاحظات إضافية..."></textarea>
                    </div>
                </div>
            </div>

            <!-- أزرار -->
            <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                <a href="salary.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                    إلغاء
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                    <i class="fas fa-save ml-2"></i>حفظ الراتب
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// حساب الراتب الصافي تلقائياً
function calculateNetSalary() {
    const basicSalary = parseFloat(document.querySelector('input[name="basic_salary"]').value) || 0;
    const bonuses = parseFloat(document.querySelector('input[name="bonuses"]').value) || 0;
    const deductions = parseFloat(document.querySelector('input[name="deductions"]').value) || 0;
    
    const netSalary = basicSalary + bonuses - deductions;
    document.getElementById('netSalary').textContent = netSalary.toFixed(2) + ' ر.س';
}

// إضافة مستمعات الأحداث للحقول
document.querySelector('input[name="basic_salary"]').addEventListener('input', calculateNetSalary);
document.querySelector('input[name="bonuses"]').addEventListener('input', calculateNetSalary);
document.querySelector('input[name="deductions"]').addEventListener('input', calculateNetSalary);

// تعبئة الراتب الأساسي تلقائياً عند اختيار الموظف
document.querySelector('select[name="employee_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const employeeText = selectedOption.text;
        const salaryMatch = employeeText.match(/(\d+\.?\d*)\s*ر\.س/);
        if (salaryMatch) {
            document.querySelector('input[name="basic_salary"]').value = salaryMatch[1];
            calculateNetSalary();
        }
    }
});
</script>

<?php include '../footer.php'; ?>